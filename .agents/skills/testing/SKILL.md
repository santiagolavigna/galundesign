---
name: testing
description: Testing strategy - Pest/PHPUnit feature tests for Laravel, manual smoke-test scripts for vanilla PHP, what to test in business systems. Use after implementing features and before delivery.
---

# Testing

## Laravel projects (automated — Pest/PHPUnit)

Priority order (limited time => test in this order):

1. **Money/stock services**: feature tests for the Services — sale registers correctly,
   stock decrements, insufficient stock throws, transaction rolls back on failure.
2. **Auth & permissions**: guest redirected from protected routes; role X cannot reach
   module Y (route AND action level).
3. **Critical Livewire flows**: `Livewire::test()` for create/edit forms — valid input
   persists, invalid input shows Spanish validation errors.
4. **Regression tests**: every production bug fixed gets a test reproducing it first.

```php
it('rejects sale when stock is insufficient', function () {
    $product = Product::factory()->create(['stock' => 1]);

    expect(fn () => app(RegisterSaleService::class)->execute([
        'items' => [['product_id' => $product->id, 'qty' => 5]],
    ], User::factory()->create()))->toThrow(ValidationException::class);

    expect($product->fresh()->stock)->toBe(1);   // nothing was decremented
});
```

Use `RefreshDatabase` with sqlite in-memory or a `_test` MySQL DB — NEVER run tests against
a client's real database. Run with `php artisan test` before declaring done.

## Vanilla PHP projects (manual smoke tests + optional scripts)

No framework harness — instead maintain `docs/smoke-tests.md`, a Spanish checklist executed
before every delivery:

```markdown
## Smoke tests — CATALOGO
- [ ] Home carga sin errores/warnings PHP (revisar con display_errors On en local)
- [ ] Catálogo filtra por marca y pagina correctamente
- [ ] Agregar al carrito via AJAX actualiza contador (y falla con 403 sin token CSRF)
- [ ] Checkout arma el mensaje de WhatsApp con los productos y total correctos
- [ ] admin/dashboard.php redirige a login sin sesión (probar URL directa)
- [ ] Login admin: credenciales malas => error genérico; 5 intentos => bloqueo
- [ ] Crear/editar/eliminar producto persiste y escapa caracteres especiales (<script> en nombre)
- [ ] Formulario público: envío inválido muestra errores; válido guarda y notifica
```

For endpoints, quick verification with PowerShell:

```powershell
Invoke-RestMethod http://localhost/PROYECTO/api/products.php?action=list | ConvertTo-Json -Depth 3
```

## Universal edge cases (test these ALWAYS, any stack)

- Empty input, whitespace-only, 0, negative numbers, huge numbers.
- Special characters in text fields: `ñ á é ' " < > &` — must save and display intact.
- Double-submit (click twice fast) on money operations — must not duplicate.
- Direct URL access to admin/AJAX endpoints without session — must deny.
- Deleting/disabling a catalog row referenced by history — must be blocked or soft-disabled.

## Definition of done

A feature is done when: the happy path works, the edge cases above are covered, the smoke
checklist (or test suite) passes, and no PHP warnings/notices appear in the log.
