package handler

import (
	"encoding/json"
	"go-bench/internal/metrics"
	"go-bench/internal/users"
	"log"
	"strings"

	"github.com/google/uuid"
	"github.com/valyala/fasthttp"
)

const (
	SamplePath  = "/sample"
	MetricsPath = "/metrics/"
	UsersPath   = "/users/"

	MethodGet = "GET"

	JsonContentType = "application/json; charset=utf-8"
)

type Handler struct {
	metrics *metrics.Metrics
	users   *users.Users
}

func New(metrics *metrics.Metrics, users *users.Users) *Handler {
	return &Handler{
		metrics: metrics,
		users:   users,
	}
}

func (h *Handler) Router(ctx *fasthttp.RequestCtx) {
	path := string(ctx.Path())
	method := string(ctx.Method())

	loadTest := strings.Contains(path, UsersPath)

	var requestId uuid.UUID
	if loadTest {
		requestId, _ = uuid.NewV7()
		h.metrics.Save(ctx, metrics.MemoryStart, metrics.GetMemoryUsage())
		log.Printf("Start request [%s]: %s %s", requestId.String(), method, path)
	}

	switch {
	case strings.HasPrefix(path, MetricsPath) && method == MethodGet:
		h.HandleMetrics(ctx, path)
	case strings.HasPrefix(path, UsersPath) && method == MethodGet:
		h.HandleUsers(ctx, path)
	case strings.HasPrefix(path, SamplePath) && method == MethodGet:
		h.HandleSample(ctx, path)
	default:
		ctx.Error("Not Found", fasthttp.StatusNotFound)
	}

	if loadTest {
		h.metrics.Save(ctx, metrics.MemoryEnd, metrics.GetMemoryUsage())
		log.Printf("End request [%s]: %s %s", requestId.String(), method, path)
	}
}

func (h *Handler) HandleMetrics(ctx *fasthttp.RequestCtx, path string) {
	metricType, ok := h.getUriParam(path, 2)
	if !ok {
		ctx.Error("Invalid metrics path", fasthttp.StatusBadRequest)
		return
	}

	stats := h.metrics.GetStats(ctx, metrics.Type(metricType))

	response, _ := json.Marshal(stats)
	ctx.SetContentType(JsonContentType)
	ctx.SetStatusCode(fasthttp.StatusOK)
	ctx.SetBody(response)
}

func (h *Handler) HandleUsers(ctx *fasthttp.RequestCtx, path string) {
	searchName, ok := h.getUriParam(path, 2)
	if !ok {
		ctx.Error("Invalid user path", fasthttp.StatusBadRequest)
		return
	}

	users, total := h.users.GetUsers(ctx, searchName)

	h.metrics.Save(ctx, metrics.MemoryProcess, metrics.GetMemoryUsage())

	data, _ := json.Marshal(map[string]any{
		"total": total,
		"data":  users,
	})
	ctx.SetContentType(JsonContentType)
	ctx.SetStatusCode(fasthttp.StatusOK)
	ctx.SetBody(data)
}

func (h *Handler) getUriParam(path string, position int) (string, bool) {
	parts := strings.Split(path, "/")
	if len(parts) < position+1 {
		return "", false
	}
	return parts[position], true
}

func (h *Handler) HandleSample(ctx *fasthttp.RequestCtx, path string) {
	ctx.SetContentType(JsonContentType)
	ctx.SetStatusCode(fasthttp.StatusOK)
	payload, _ := json.Marshal(map[string]string{
		"status": "ok",
	})
	ctx.SetBody(payload)
}
