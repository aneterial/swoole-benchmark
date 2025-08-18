package main

import (
	"context"
	"go-bench/internal/handler"
	"go-bench/internal/metrics"
	"go-bench/internal/users"
	"log"
	"os"
	"runtime"
	"time"

	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/joho/godotenv"
	"github.com/redis/go-redis/v9"
	"github.com/valyala/fasthttp"
)

func init() {
	if err := godotenv.Load(); err != nil {
		log.Print("No .env file found")
	}
}

func main() {
	log.Printf("Build time: %s", os.Getenv("TIMESTAMP"))
	log.Printf("Set GOMAXPROCS to %d", runtime.GOMAXPROCS(0))

	db := initDb()
	defer db.Close()

	redis := initRedis()
	defer redis.Close()

	handler := handler.New(metrics.New(redis), users.New(db))

	server := &fasthttp.Server{
		Handler:                      handler.Router,
		Name:                         "Go-Server",
		ReadTimeout:                  0,
		WriteTimeout:                 0,
		IdleTimeout:                  0,
		DisablePreParseMultipartForm: true,
		NoDefaultServerHeader:        true,
		NoDefaultContentType:         true,
		NoDefaultDate:                true,
		ReadBufferSize:               4096,
		WriteBufferSize:              4096,
		Concurrency:                  0,
	}

	log.Printf("Go FastHTTP Server started at :8081")
	if err := server.ListenAndServe(":8081"); err != nil {
		log.Fatalf("server error: %v", err)
	}
}

func initDb() *pgxpool.Pool {
	config, err := pgxpool.ParseConfig("host=db port=5432 user=test password=test dbname=test sslmode=disable")
	if err != nil {
		log.Fatalf("Failed to parse connection string: %v", err)
	}

	config.MaxConns = 100
	config.MinConns = 25
	config.MaxConnIdleTime = 0
	config.HealthCheckPeriod = 1 * time.Minute

	db, err := pgxpool.NewWithConfig(context.Background(), config)
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}

	if err = db.Ping(context.Background()); err != nil {
		log.Fatalf("Failed to ping database: %v", err)
	}

	return db
}

func initRedis() *redis.Client {
	redis := redis.NewClient(&redis.Options{
		Addr:         "redis:6379",
		Password:     "",
		DB:           0,
		Username:     "",
		MaxRetries:   5,
		DialTimeout:  10 * time.Second,
		ReadTimeout:  10 * time.Second,
		WriteTimeout: 10 * time.Second,
	})

	if err := redis.Ping(context.Background()).Err(); err != nil {
		log.Fatalf("failed to connect to redis server: %s\n", err.Error())
	}

	return redis
}
