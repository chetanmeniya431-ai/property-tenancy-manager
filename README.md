# Property Tenancy Manager

A property and tenancy management system for landlords and property managers. Upload lease PDFs and ask questions in plain English — the AI reads every clause and gives cited answers. Maintenance requests, SLA tracking, and dispute-ready reports are included.

Runs entirely on your own server. No third-party AI costs — uses Ollama for local language models.

---

## Requirements

- Docker and Docker Compose
- [Ollama](https://ollama.com) running on the host machine with these models pulled:
  ```bash
  ollama pull llama3.2:3b
  ollama pull nomic-embed-text
  ```

No local PHP, Node, or PostgreSQL install needed.

---

## Quick start

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Open **http://localhost:8005**

---

## Demo logins

All demo accounts use password `password`.

| Email | Role |
|---|---|
| admin@propertymanager.local | Property Owner |
| manager@propertymanager.local | Property Manager |
| maintenance@propertymanager.local | Maintenance Coordinator |
| contractor@propertymanager.local | Contractor |
| tenant@propertymanager.local | Tenant |

---

## Features

- Upload any lease PDF — AI reads and indexes every clause
- Ask questions like "who pays for this repair?" and get a cited answer
- Maintenance request workflow with SLA tracking
- Automatic alerts for rent overdue, lease expiry, and SLA breaches
- One-click dispute evidence PDF for tribunal submissions
- Role-based access for owners, managers, maintenance staff, contractors, and tenants

---

## Key configuration

All settings are in `.env`. Copy `.env.example` to `.env` and adjust:

| Variable | Description |
|---|---|
| `APP_URL` | Public URL of the app |
| `APP_PORT` | Host port (default `8005`) |
| `OLLAMA_BASE_URL` | Ollama server URL (default `http://host.docker.internal:11434`) |
| `OLLAMA_GENERATION_MODEL` | LLM for AI responses (default `llama3.2:3b`) |
| `OLLAMA_EMBEDDING_MODEL` | Embedding model for document search (default `nomic-embed-text`) |
| `MAIL_MAILER` | Set to `smtp` with SMTP credentials for real email delivery |

For production: set `APP_ENV=production`, `APP_DEBUG=false`, and use a strong `DB_PASSWORD`.

---

## Tech stack

Laravel 11 · PHP 8.3 · PostgreSQL + pgvector · Livewire v3 · Tailwind CSS · Ollama · Docker
