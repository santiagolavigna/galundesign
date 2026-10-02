---
name: database
description: MySQL schema design, types, indexes, migrations and backups for business systems. Use when creating or altering tables, diagnosing slow queries, or planning schema changes.
---

# Database — MySQL

## Schema conventions

- Engine InnoDB, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Tables plural snake_case (`sale_items`), PK `id` BIGINT UNSIGNED AUTO_INCREMENT.
- FKs named `<singular>_id` with real FOREIGN KEY constraints and an explicit ON DELETE
  decision (RESTRICT for business records, CASCADE only for pure children like sale_items).
- Timestamps `created_at` / `updated_at` on business tables. Soft state via `active TINYINT(1)`
  or a `status` enum-like VARCHAR — prefer soft-disable over DELETE for catalog rows referenced
  by history.

## Types (get these right the first time)

| Data | Type |
|---|---|
| Money | `DECIMAL(12,2)` — NEVER FLOAT/DOUBLE |
| Quantities | INT or `DECIMAL(10,3)` if fractional (kg) |
| Phones, DNI/CUIT | VARCHAR (leading zeros, "+54...") |
| Flags | TINYINT(1) |
| Long text | TEXT; JSON only for truly unstructured blobs |
| Dates | DATE / DATETIME (store server timezone consistently, document it in project.md) |

## Indexes

- Index every column used in WHERE/JOIN/ORDER BY of list screens (FKs, `status`, `created_at`).
- Composite indexes for the common filter pair: `(branch_id, created_at)` for per-branch reports.
- UNIQUE for business identity: `users.email`, `products.sku`, `members.dni`.
- Diagnose slow lists with `EXPLAIN` before adding caches.

## History is immutable

Sales, payments, cash movements, attendance: never UPDATE amounts in history rows — insert
compensating records (devolución, ajuste). Snapshot denormalized data at write time
(`sale_items.unit_price`, `product_name`) so history survives catalog edits.

## Schema changes

- Laravel: migrations only; never edit a migration already run in production (see backend-laravel).
- Vanilla: keep `sql/schema.sql` current + write incremental change scripts
  `sql/updates/2026-07-31-add-x.sql`; document each change with `docs/templates/migration.md`
  including the rollback statement.
- Destructive changes (DROP/renames) on client DBs: backup first, always:

```powershell
& C:\xampp\mysql\bin\mysqldump.exe -u root --databases nombre_db --routines > backup_2026-07-31.sql
```

## Backups

- Before every deploy that touches schema: dump production DB (hosting panel or mysqldump via SSH).
- Dumps with client data NEVER get committed to Git — `.gitignore` covers `*.sql` dumps
  (except `sql/schema.sql`).
