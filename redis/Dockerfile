FROM golang:1.25-alpine AS builder

WORKDIR /app

RUN echo "TIMESTAMP=$(date -u +"%Y-%m-%d %H:%M:%S")" > .env
RUN cat .env

COPY go.mod go.sum ./
RUN go mod download

COPY main.go ./

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build \
    -a -installsuffix cgo \
    -ldflags="-w -s -extldflags '-static'" \
    -gcflags="-N -l"

# Финальный образ
FROM scratch

WORKDIR /app

COPY --from=builder /app/redis-manager .
COPY --from=builder /app/.env .env


CMD ["./redis-manager"]
