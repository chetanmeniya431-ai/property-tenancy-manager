# Property Management & Tenancy Intelligence System — CLAUDE.md

## What this product does

A property management system for landlords and property managers. It tracks
properties, tenants, lease agreements, and maintenance requests in one place.
AI (Ollama, no external API key) reads each uploaded lease and answers questions
like "is the landlord responsible for this repair?" It detects recurring
maintenance problems at the same property using embeddings. Signals fire
automatically for rent overdue, lease expiry approaching, maintenance SLA
missed, and repeated issues. When a dispute goes to a tribunal, one click
generates a complete timestamped PDF evidence pack of every communication,
request, and action taken for that tenancy.

Sells to: private landlords managing 5–50 properties, small property management
companies, and real estate agents with a rental portfolio.

---

## Tech stack (fixed — do not change)

- **Backend:** Laravel (PHP 8.3)
- **Database:** PostgreSQL 16 with the `pgvector` extension (native vector columns
  + `<=>` cosine-distance operator for similarity search — lease chunk retrieval
  and maintenance-request pattern detection both query the vector index directly
  instead of pulling all rows and computing cosine similarity in PHP)
- **Vector access layer:** `pgvector/pgvector` PHP package (Eloquent `Vector` cast
  + query macros) on top of the `pgvector/pgvector` Postgres image
- **Frontend:** Laravel Blade + Livewire + Tailwind CSS v4 + Alpine.js + Vite
- **Runtime:** Docker (docker-compose) — no local PHP or Postgres needed
- **Icons:** Heroicons inline SVG only
- **Font:** Inter (Google Fonts)
- **LLM generation:** Ollama — `llama3.2:3b` (lease Q&A, issue summaries)
- **Embeddings:** Ollama — `nomic-embed-text` (768-dim vectors — lease chunks and
  maintenance request similarity)
- **PDF export:** `barryvdh/laravel-dompdf`
- **PDF parsing:** `smalot/pdfparser` (lease document upload)
- **Permissions:** `spatie/laravel-permission` (5 roles)

No external API keys. All AI runs locally via Ollama.

---

## Assigned ports

- **APP_PORT:** 8005
- **DB_EXTERNAL_PORT:** 55465 (Postgres, mapped from container's 5432)
- **OLLAMA_PORT:** 11434 (shared host Ollama instance)

---

## Shared infrastructure rules

**Ollama — use the host, never add a container:**
Do NOT add an `ollama` service to this project's `docker-compose.yml`.
Ollama runs once on the host machine and is shared by all Nirmantra projects.
The app reaches it at `http://host.docker.internal:11434` — Docker maps this
address to the host from inside any container automatically.

Models are pulled once on the host and reused by every project:
```bash
ollama pull llama3.2:3b
ollama pull nomic-embed-text
```

If models are already pulled (check with `ollama list`), skip the pull step.
Loading the same model twice — once per project container — wastes 2 GB of RAM
per duplicate. One host instance serves all projects simultaneously.

**Database — fully isolated, do not share:**
This project's database runs in its own container with its own Docker volume.
No other project connects to it. Never point this project at another project's
database port. Never share volumes between projects.

---

## Docker setup

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

App at `http://localhost:8005`
Default login: `admin@propertymanager.local` / `password`

### Manual lease import (not just the seeder)

The lease PDF pipeline must work two ways, both hitting the exact same
`LeaseIngestionService`:

1. **UI upload** — the "Upload lease" action on a tenancy page (Livewire
   `LeaseUpload` component). Stores the file via `Storage::disk('leases')`,
   dispatches `ProcessLeaseDocument` on the queue.
2. **Manual/CLI import** — `php artisan leases:import {tenancy_id} {path}` for
   re-importing or bulk-loading a lease PDF from disk (e.g. re-processing a
   scanned lease dropped on the server, or importing outside the seeder). Same
   service, same chunking/embedding job, so seeded leases and manually imported
   leases are indistinguishable once ingested.

Both paths must overwrite any existing `lease_chunks` for that tenancy (delete
+ re-insert) so re-importing a corrected PDF doesn't leave stale chunks behind.

---

## Roles

Five roles using Spatie Laravel Permission:

- **Property Owner** — full access. Sees all properties and tenancies they own.
  Can configure settings and manage users.
- **Property Manager** — manages day-to-day operations. Creates maintenance
  requests, communicates with contractors, runs reports. Cannot delete properties
  or leases.
- **Maintenance Coordinator** — assigns and tracks maintenance requests. Views
  properties and requests. Cannot access lease documents or financial records.
- **Contractor** — external login. Sees only maintenance requests assigned to
  them. Can update status to "in progress" and "completed". Cannot see other
  tenancies or financial data.
- **Tenant** — external login. Can submit new maintenance requests for their
  own property. Views the status of their own requests. Cannot see other tenants
  or lease financial details.

---

## MVP Features (5 only — build these, nothing else)

### 1. Property and tenancy register

**Properties:**
- Address (street, city, postcode)
- Property type: Residential flat / Residential house / Commercial unit / HMO
- Number of bedrooms (residential) or floor area m² (commercial)
- Owner (linked user)
- Notes (access codes, parking, etc.)

**Tenancies:**
- Linked to a property
- Tenant name, email, phone
- Lease start date, lease end date
- Monthly rent amount, payment due day (1–28)
- Deposit amount held
- Lease document upload (PDF) — this is the source for the AI lease assistant
- Status: Active / Expired / Notice given / Ended

One property can have only one active tenancy at a time. Past tenancies are
kept for record.

### 2. Maintenance request workflow

Any user with access to a property can log a maintenance request:
- Category: Plumbing / Electrical / Structural / Heating & Cooling / Appliances /
  Pest Control / Cleaning / Other
- Urgency: Emergency (24h SLA) / Urgent (3-day SLA) / Routine (14-day SLA)
- Description (free text)
- Photos (attachments)
- Reported by

Status workflow:
```
Logged → Assigned to Contractor → In Progress → Resolved → Closed
```

Each stage is timestamped and records who took the action.

Assigning to a contractor sends them a notification (in-app — no email for now).
The contractor updates status to "in progress" when they start and "resolved"
when they finish.

The manager reviews "resolved" requests and closes them, optionally adding a
resolution note and cost.

SLA tracking: the system calculates whether each request was resolved within
its SLA target. Overdue requests are highlighted in red on the dashboard.

### 3. Lease RAG and obligation check

Each uploaded lease PDF is parsed with `smalot/pdfparser`, split into 500-token
chunks, embedded with `nomic-embed-text`, and stored in `lease_chunks`.

A "Lease Assistant" is available on each tenancy page. The manager types a
question about that specific tenancy's lease:
- "Is the landlord responsible for fixing the boiler under this lease?"
- "What notice period does the tenant need to give to end the tenancy?"
- "Does this lease allow pets?"

The system retrieves the top 5 most relevant chunks from that lease using a
pgvector `<=>` cosine-distance nearest-neighbor query (`ORDER BY embedding <=>
:query LIMIT 5`, filtered `WHERE tenancy_id = :tenancy_id`), passes them to
`llama3.2:3b` with the question, and shows the answer with a quoted section
from the lease.

Only the specific tenancy's lease is searched — never another tenant's lease.

When a new maintenance request is logged, the system also runs an automatic
check: it searches the lease for the category of issue reported and shows a
one-line note:
> "Under this lease, the landlord is typically responsible for structural
> repairs. Verify section 4.2."

This is informational only — it does not make a decision.

### 4. AI pattern detection using embeddings

All maintenance requests are embedded with `nomic-embed-text` when logged.

Two pattern detections run daily via the scheduler:

**Recurring issue at property:**
For each property, use a pgvector self-join (`embedding <=> embedding < 0.15`,
i.e. cosine similarity > 0.85) over maintenance requests logged in the last 90
days at that property. If any pair matches, log a "recurring issue" signal
event. This catches the same problem being reported multiple times by
different tenants or at different times.

**Contractor resolution quality:**
After a contractor marks a request "resolved", if the same request is reopened
within 30 days, log a "poor resolution" signal. Over 3 poor resolutions, a
"contractor quality" signal fires.

Embeddings are computed asynchronously via the queue (not blocking the request).

### 5. Signals engine and dispute export PDF

**8 default signals (seeded, active by default):**

| # | Name | Condition | Severity |
|---|------|-----------|----------|
| 1 | Rent Overdue | Rent payment date passed with no payment logged | High |
| 2 | Lease Expiry — 60 Days | Tenancy end date is 60 days away | Medium |
| 3 | Lease Expiry — 30 Days | Tenancy end date is 30 days away | High |
| 4 | Maintenance SLA Breached | Request not resolved within its SLA | High |
| 5 | Recurring Issue at Property | Same issue detected 3+ times in 90 days | Medium |
| 6 | Emergency Request Open 24h | Emergency urgency request not assigned after 24h | Critical |
| 7 | Contractor Reopened Request | A resolved request reopened within 30 days | Medium |
| 8 | Expired Tenancy — No Action | Tenancy end date passed with status still "Active" | High |

**Dispute Export PDF:**

On any tenancy detail page, a "Generate Dispute Report" button creates a PDF:
- Tenancy details: property, tenant, lease period, rent
- Full lease summary (first 500 words)
- Complete maintenance request history (all requests, every status change, dates,
  assigned contractors, resolution notes)
- All signal events that fired for this tenancy
- Rent payment log

The PDF is timestamped at generation time. It is designed to be handed to a
housing tribunal or solicitor as a complete record.

---

## Database schema

Enums are Postgres native `enum` types (created via raw SQL in migrations,
e.g. `property_type_enum`, `tenancy_status_enum`), not Laravel string
`enum()` columns. Embedding columns are pgvector `vector(768)` (matching
`nomic-embed-text` output dimensions), cast to/from PHP arrays via the
`pgvector/pgvector` package's `Vector` Eloquent cast — never `json`.

```
properties
  id, address_line1, address_line2, city, postcode,
  property_type (enum), bedrooms (nullable int), floor_area_sqm (nullable decimal),
  owner_id (FK users), notes (text nullable), created_at, updated_at

tenancies
  id, property_id (FK), tenant_name, tenant_email, tenant_phone,
  lease_start (date), lease_end (date), monthly_rent (decimal),
  payment_due_day (int 1-28), deposit_amount (decimal),
  lease_file_path (string nullable), lease_embedded_at (timestamp nullable),
  status (enum: active|expired|notice_given|ended),
  end_reason (text nullable), created_by (FK users), created_at, updated_at

rent_payments
  id, tenancy_id (FK), amount (decimal), payment_date (date),
  method (string), reference (string nullable), notes (text nullable),
  recorded_by (FK users), created_at

maintenance_requests
  id, property_id (FK), tenancy_id (FK nullable),
  category (enum), urgency (enum: emergency|urgent|routine),
  description (text), attachments (json),
  reported_by_name (string), reported_by_user_id (FK users nullable),
  status (enum: logged|assigned|in_progress|resolved|closed),
  contractor_id (FK users nullable),
  resolved_at (timestamp nullable), closed_at (timestamp nullable),
  resolution_note (text nullable), cost (decimal nullable),
  sla_hours (int), sla_breached (boolean default false),
  embedding (vector(768) nullable), created_at, updated_at

maintenance_status_history
  id, request_id (FK), old_status, new_status,
  changed_by (FK users), note (text nullable), created_at

lease_chunks
  id, tenancy_id (FK), chunk_index (int), chunk_text (text),
  embedding (vector(768)), created_at
  -- HNSW index: (embedding vector_cosine_ops), plus a plain index on tenancy_id
  -- so retrieval always filters to one tenancy before the vector scan.

signals
  id, name, condition_key, severity (enum: low|medium|high|critical),
  active (boolean), created_at, updated_at

signal_events
  id, signal_id (FK), property_id (FK nullable), tenancy_id (FK nullable),
  request_id (FK nullable), triggered_at, resolved_at (nullable),
  resolved_by (FK users nullable), note (text nullable), created_at
```

**Implementation addition (not in the original sketch, required for role
scoping):** `users` gets a nullable `tenancy_id` FK. It is only ever set for
accounts with the Tenant role, and is how the Tenant role is scoped to "their
own property, their own requests" — there is otherwise no link from a login
to a specific tenancy. Contractor scoping needs no equivalent column, since
`maintenance_requests.contractor_id` already links a request to the
contractor's user account.

---

## Scheduled jobs

```php
$schedule->command('signals:check')->hourly();
$schedule->command('embeddings:process-maintenance')->everyThirtyMinutes();
$schedule->command('leases:check-embeddings')->daily();
```

---

## Environment variables

```env
APP_NAME="Property Tenancy Manager"
APP_ENV=local
APP_PORT=8005

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=property_tenancy
DB_USERNAME=property_tenancy
DB_PASSWORD=secret
DB_EXTERNAL_PORT=55465

OLLAMA_BASE_URL=http://host.docker.internal:11434
OLLAMA_GENERATION_MODEL=llama3.2:3b
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_EMBEDDING_DIMENSIONS=768
OLLAMA_TIMEOUT=300

QUEUE_CONNECTION=database

LEASES_DISK=leases
```

---

## Synthetic data plan (seeder)

Demo company: **Hartwell Property Management** (small property management firm
managing residential and commercial properties).

**Users (5):**
- `admin@propertymanager.local` / `password` — Property Owner
- `manager@propertymanager.local` / `password` — Property Manager
- `maintenance@propertymanager.local` / `password` — Maintenance Coordinator
- `contractor@propertymanager.local` / `password` — Contractor
- `tenant@propertymanager.local` / `password` — Tenant (linked to one property)

**Properties (8):**
- 5 residential flats (2-bed and 3-bed)
- 2 residential houses (3-bed)
- 1 commercial unit (office space)

**Tenancies (10):**
- 7 active tenancies across 7 properties
- 1 tenancy with "notice given" status
- 2 ended tenancies (historical records)
- Each active tenancy has a synthetic lease PDF uploaded (realistic but
  synthetic content — landlord/tenant obligations, notice periods, rent review)

**Rent payments (~95 records — fuller history than the original 40-record
estimate, for realism; the important cases below are guaranteed):**
- 12 months of payments for 3 long-running tenancies
- 2 tenancies with a missed payment this month (triggers "Rent Overdue" signal)
- 1 tenancy with a dispute history (payments recorded several days late)

**Maintenance requests (25 total):**
- 8 closed/resolved
- 6 in progress (assigned to contractor)
- 5 logged (not yet assigned)
- 4 closed with reopening within 30 days (triggers "Contractor Reopened" signal)
- 2 emergency requests (1 resolved, 1 breaching SLA)

**Signal events (4 pre-triggered):**
- 2 × Rent Overdue
- 1 × Emergency Request Open 24h
- 1 × Recurring Issue at Property (boiler-related requests at same flat)

**Lease chunks:** All 7 active lease PDFs fully chunked and embedded in the
seed. Demo questions in the UI show pre-populated example Q&A using the
synthetic lease content.

All data is fully synthetic. No real tenant names, real properties, or real
lease documents.

---

## Design

Inherits the Nirmantra design system.

- **Accent colour:** Sky (`sky-600`) — clean, professional, property
- **Layout:**
  - Sidebar + main content area
  - Sidebar links: Dashboard, Properties, Tenancies, Maintenance, Lease Assistant,
    Signals, Reports, Settings (owner/manager only)
- **Property cards on dashboard:** address, active tenancy status, open
  maintenance count, next rent due date
- **Maintenance list:** table with urgency badge (colour-coded), status badge,
  property address, category, days since logged, SLA indicator
- **Urgency badge colours:**
  - Emergency: `red`
  - Urgent: `orange`
  - Routine: `blue`
- **Signal events:** alert cards on dashboard with one-click "resolve" action

---

## Quality checklist

- [ ] Cold start: `docker compose up` + seed works with zero manual steps
- [ ] Property → tenancy → maintenance workflow all link correctly
- [ ] Lease PDF upload triggers chunking and embedding via the queue
- [ ] `php artisan leases:import {tenancy_id} {path}` manually ingests a lease
  PDF from disk through the same `LeaseIngestionService` as the UI upload and
  the seeder — re-importing replaces old `lease_chunks` for that tenancy
- [ ] Lease Assistant answers questions from the correct tenancy's lease only
  (not another tenant's lease)
- [ ] Maintenance request SLA tracking correctly marks overdue requests
- [ ] Contractor role sees only their assigned requests — no other data visible
- [ ] Tenant role can submit a request and view its status — no other tenant's
  data visible
- [ ] Dispute Export PDF generates with correct data including all status changes
- [ ] All 8 signals evaluate correctly — verify the 4 pre-triggered ones fire
- [ ] Pattern detection finds the recurring boiler issue in the seeded data
- [ ] No Ollama API key or external API key anywhere in the codebase
- [ ] `.env.example` documents every variable
- [ ] Works on mobile screen (375px minimum width)
