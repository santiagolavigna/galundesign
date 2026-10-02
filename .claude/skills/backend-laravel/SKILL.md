---
name: backend-laravel
description: Laravel 12 + Livewire 4 + Tailwind 4 patterns - Livewire single-file components, transactional Services, Spatie permissions, Eloquent, DomPDF reports. Use when working on Laravel projects (artisan file present).
---

# Backend Laravel 12 + Livewire 4

Reference patterns proven in SISTEMA-GYM and SISTEMA-GESTION. Follow the same architecture
in every Laravel project.

## Architecture

```
app/
├── Models/                 <- Eloquent models, relationships, casts, scopes
├── Services/               <- business logic with transactions (RegisterSaleService, etc.)
├── Http/Controllers/       <- ONLY for PDF/CSV downloads, logout, special endpoints
├── Livewire/  or  resources/views/pages/*.blade.php   <- Livewire components (UI + CRUD)
├── Policies/               <- authorization
resources/views/
├── layouts/                <- app layout, sidebar, navigation
├── pages/                  <- Livewire single-file components
routes/web.php              <- Livewire pages + few controller routes
project.md                  <- product source of truth (NOT the stock Laravel README)
```

Division of responsibility:

- **Livewire component**: form state, validation, calling services, UI feedback. Keep it thin.
- **Service**: anything touching more than one table, money, or stock. One public method per
  use case, wrapped in a transaction.
- **Controller**: only when Livewire doesn't fit (file downloads, webhooks, external APIs).

## Transactional Service pattern

```php
final class RegisterSaleService
{
    public function execute(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $sale = Sale::create([...]);

            foreach ($data['items'] as $item) {
                $product = Product::whereKey($item['product_id'])
                    ->lockForUpdate()      // prevents overselling under concurrency
                    ->firstOrFail();

                if ($product->stock < $item['qty']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$product->name}",
                    ]);
                }
                $product->decrement('stock', $item['qty']);
                $sale->items()->create([...]);
            }

            CashMovement::create([...]);   // same transaction: sale + stock + cash always consistent
            return $sale;
        });
    }
}
```

Rules: `lockForUpdate()` on any row whose value you read-then-write (stock, cash balances).
Money uses `decimal:2` casts and `bcmath` for arithmetic. Throw `ValidationException` with
Spanish messages — Livewire renders them automatically.

## Livewire component conventions

- Validate with `#[Validate]` attributes or `rules()`; always validate before calling a service.
- `wire:model.live` only where reactivity is needed (search boxes, calculated totals);
  plain `wire:model` elsewhere to avoid request storms.
- Feedback after actions: dispatch a browser event for toast notifications, Spanish text.
- Tables: paginate (`WithPagination`), search server-side with `where('name', 'like', ...)` on
  indexed columns, never load full tables into memory.
- Do NOT put multi-table writes inside the component — delegate to a Service.

## Auth and permissions

- Laravel session auth + **Spatie Permission** roles (`admin`, `staff`, ...).
- Rate limit login (`RateLimiter` or throttle middleware).
- Check permissions in three places: route middleware, `authorize()` in the component/policy,
  and hide UI elements with `@can`. Never rely on hidden UI alone.
- Audit sensitive actions (login, deletions, cash operations) via listener/observer into an
  audit table — pattern already used in SISTEMA-GESTION.

## API endpoints (when needed)

Token-protected JSON endpoints (e.g. biometric device callbacks) get: a dedicated controller,
token check middleware, CSRF exemption ONLY for that route, request validation via FormRequest,
and rate limiting.

## PDF / exports

DomPDF through a dedicated controller (`SalePdfController`), Blade view with inline CSS,
filename pattern `venta-{id}-{fecha}.pdf`. CSV exports stream with `response()->streamDownload()`.

## Migrations

- Never edit a migration that already ran in production — create a new one.
- Every `foreignId` gets `->constrained()` and an explicit `onDelete` decision.
- Add indexes for every column used in `where`/`orderBy` of list pages.
- Seeders for catalogs (roles, payment methods); factories for tests.

## Environment

- `.env` gitignored; keep `.env.example` AND `.env.production.example` updated when adding keys.
- `APP_LOCALE=es`, `APP_TIMEZONE=America/Argentina/Buenos_Aires` (adjust per client).
- Frontend: Tailwind 4 via Vite (`npm run build` before deploy). No CDN Tailwind.

## Quality gates

Run before declaring a task done (if installed in the project):

```bash
vendor/bin/pint --dirty
vendor/bin/phpstan analyse
php artisan test
```
