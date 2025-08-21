#!/usr/bin/make
SWOOLE_PORT := $(shell cat .env | grep "SWOOLE_PORT" | cut -d '=' -f2)
GO_PORT := $(shell cat .env | grep "GO_PORT" | cut -d '=' -f2)
HYPERF_PORT := $(shell cat .env | grep "HYPERF_PORT" | cut -d '=' -f2)
LARAVEL_PORT := $(shell cat .env | grep "LARAVEL_PORT" | cut -d '=' -f2)
OCTANE_PORT := $(shell cat .env | grep "OCTANE_PORT" | cut -d '=' -f2)
NODE_PORT := $(shell cat .env | grep "NODE_PORT" | cut -d '=' -f2)


# Первоначальная сборка и запуск серверов
first-up:
	cp .env.example .env
	docker compose up -d --build

# Бенчмарки сервера+БД+io
bench-swoole:
	ab -n 20000 -c 50 -k http://localhost:${SWOOLE_PORT}/users/ser56

bench-go:
	ab -n 20000 -c 50 -k http://localhost:${GO_PORT}/users/ser56

bench-hyperf:
	ab -n 20000 -c 50 -k http://localhost:${HYPERF_PORT}/users/ser56

bench-laravel:
	ab -n 20000 -c 50 -k http://localhost:${LARAVEL_PORT}/users/ser56

bench-octane:
	ab -n 20000 -c 50 -k http://localhost:${OCTANE_PORT}/users/ser56

bench-node:
	ab -n 20000 -c 50 -k http://localhost:${NODE_PORT}/users/ser56

# Бенчмарки нагрузки сервера
bench-swoole-sample:
	ab -n 5000000 -c 800 -k http://localhost:${SWOOLE_PORT}/sample

bench-go-sample:
	ab -n 5000000 -c 800 -k http://localhost:${GO_PORT}/sample

bench-hyperf-sample:
	ab -n 5000000 -c 800 -k http://localhost:${HYPERF_PORT}/sample

bench-laravel-sample:
	ab -n 5000000 -c 800 -k http://localhost:${LARAVEL_PORT}/sample

bench-octane-sample:
	ab -n 5000000 -c 800 -k http://localhost:${OCTANE_PORT}/sample

bench-node-sample:
	ab -n 5000000 -c 800 -k http://localhost:${NODE_PORT}/sample

# Сборка контейнеров
build-swoole:
	docker compose build --no-cache swoole-server

build-go:
	docker compose build --no-cache go-server

build-hyperf:
	docker compose build --no-cache hyperf-server

build-laravel:
	docker compose build --no-cache laravel-server

build-octane:
	docker compose build --no-cache octane-server

build-node:
	docker compose build --no-cache node-server
