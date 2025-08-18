package main

import (
	"context"
	"fmt"
	"log"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"

	"github.com/redis/go-redis/v9"
)

type metricsType string
type serviceType string

const (
	limit = 1000

	metricsTypesStart   metricsType = "memory:start"
	metricsTypesProcess metricsType = "memory:process"
	metricsTypesEnd     metricsType = "memory:end"

	serviceGo      serviceType = "go"
	serviceSwoole  serviceType = "swoole"
	serviceHyperf  serviceType = "hyperf"
	serviceLaravel serviceType = "laravel"
	serviceOctane  serviceType = "octane"
	serviceNode    serviceType = "node"
)

var metricsTypes = [...]metricsType{
	metricsTypesStart,
	metricsTypesProcess,
	metricsTypesEnd,
}

var services = [...]serviceType{
	serviceGo,
	serviceSwoole,
	serviceHyperf,
	serviceLaravel,
	serviceOctane,
	serviceNode,
}

func main() {
	redis, err := newClient(context.Background())
	if err != nil {
		log.Fatalf("failed to connect to redis: %s", err)
	}
	defer redis.Close()

	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)

	ticker := time.NewTicker(1 * time.Minute)
	defer ticker.Stop()

	log.Println("Redis manager started. Processing every minute...")

	for {
		select {
		case <-ticker.C:
			processServices(redis)
			log.Println("Processed all services")
		case sig := <-sigChan:
			log.Printf("Received signal %v, shutting down...", sig)
			return
		}
	}
}

func newClient(ctx context.Context) (*redis.Client, error) {
	db := redis.NewClient(&redis.Options{
		Addr:         "redis:6379",
		Password:     "",
		DB:           0,
		Username:     "",
		MaxRetries:   5,
		DialTimeout:  10 * time.Second,
		ReadTimeout:  10 * time.Second,
		WriteTimeout: 10 * time.Second,
	})

	if err := db.Ping(ctx).Err(); err != nil {
		fmt.Printf("failed to connect to redis server: %s\n", err.Error())
		return nil, err
	}

	return db, nil
}

func processServices(redis *redis.Client) {
	var wg sync.WaitGroup
	for _, service := range services {
		for _, metricsType := range metricsTypes {
			wg.Go(func() {
				ltrimList(redis, string(service)+":"+string(metricsType))
			})
		}
	}
	wg.Wait()
}

func ltrimList(redis *redis.Client, key string) {
	ctx := context.Background()
	length, err := redis.LLen(ctx, key).Result()
	if err != nil {
		log.Fatalf("failed to get list length: %s", err)
	}

	if length > limit {
		toRemove := length - limit
		for toRemove > 0 {
			redis.LPop(ctx, key)
			toRemove--
		}
	}
}
