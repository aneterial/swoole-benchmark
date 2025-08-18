#!/usr/bin/make

# Бенчмарки сервера+БД+io
bench-swoole:
	ab -n 20000 -c 50 -k http://localhost:8080/users/ser56

bench-go:
	ab -n 20000 -c 50 -k http://localhost:8081/users/ser56

bench-hyperf:
	ab -n 20000 -c 50 -k http://localhost:8082/users/ser56

bench-laravel:
	ab -n 20000 -c 50 -k http://localhost:8083/users/ser56

bench-octane:
	ab -n 20000 -c 50 -k http://localhost:8084/users/ser56

# Бенчмарки нагрузки сервера
bench-swoole-sample:
	ab -n 1000000 -c 100 -k http://localhost:8080/sample

bench-go-sample:
	ab -n 1000000 -c 100 -k http://localhost:8081/sample

bench-hyperf-sample:
	ab -n 1000000 -c 100 -k http://localhost:8082/sample

bench-laravel-sample:
	ab -n 1000000 -c 100 -k http://localhost:8083/sample

bench-octane-sample:
	ab -n 1000000 -c 100 -k http://localhost:8084/sample

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
