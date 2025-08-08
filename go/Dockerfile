FROM golang:1.24-alpine AS builder

WORKDIR /app

RUN echo "TIMESTAMP=$(date -u +"%Y-%m-%d %H:%M:%S")" > .env
RUN cat .env

COPY go.mod go.sum ./
RUN go mod download

COPY main.go ./
COPY internal/ ./internal/

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build \
    -a -installsuffix cgo \
    -ldflags="-w -s -extldflags '-static'" \
    -gcflags="-N -l" \
    -o main .

# Финальный образ
FROM scratch

WORKDIR /app

COPY --from=builder /app/main .
COPY --from=builder /app/.env .env

ENV GOMAXPROCS=4
ENV GOGC=100
ENV GODEBUG=netdns=go

EXPOSE 8081

CMD ["./main"]
