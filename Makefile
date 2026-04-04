.PHONY: help up down restart logs shell test migrate pint build

# ============================================================
# Fluency AI — Makefile
# ============================================================

help: ## Mostra este menu de ajuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Sobe todos os containers em background
	docker compose up -d

down: ## Para e remove os containers
	docker compose down

restart: ## Reinicia os containers
	docker compose restart

logs: ## Exibe logs em tempo real
	docker compose logs -f

logs-backend: ## Exibe logs apenas do backend
	docker compose logs -f backend-app

logs-frontend: ## Exibe logs apenas do frontend
	docker compose logs -f frontend-app

shell: ## Abre shell no container do backend
	docker compose exec backend-app bash

shell-frontend: ## Abre shell no container do frontend
	docker compose exec frontend-app sh

test: ## Roda os testes do backend (PHPUnit)
	docker compose exec backend-app php artisan test

test-coverage: ## Roda testes com cobertura mínima de 80%
	docker compose exec backend-app php artisan test --coverage --min=80

migrate: ## Roda as migrations do banco
	docker compose exec backend-app php artisan migrate

migrate-fresh: ## Recria o banco do zero (DESTRÓI dados)
	docker compose exec backend-app php artisan migrate:fresh --seed

pint: ## Formata código PHP com Laravel Pint
	docker compose exec backend-app vendor/bin/pint --dirty --format agent

artisan: ## Roda comando artisan (uso: make artisan CMD="route:list")
	docker compose exec backend-app php artisan $(CMD)

build: ## Rebuilda as imagens Docker
	docker compose build --no-cache

ps: ## Lista containers e status
	docker compose ps
