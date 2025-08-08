#!/usr/bin/make

bench-swoole:
	ab -n 20000 -c 50 -k http://localhost:8080/users/ser56

bench-go:
	ab -n 20000 -c 50 -k http://localhost:8081/users/ser56

bench-hyperf:
	ab -n 20000 -c 50 -k http://localhost:8082/users/ser56

bench-swoole-sample:
	ab -n 1000000 -c 100 -k http://localhost:8080/sample

bench-go-sample:
	ab -n 1000000 -c 100 -k http://localhost:8081/sample

bench-hyperf-sample:
	ab -n 1000000 -c 100 -k http://localhost:8082/sample

build-swoole:
	docker compose build --no-cache swoole-server

build-go:
	docker compose build --no-cache go-server

build-hyperf:
	docker compose build --no-cache hyperf-server
