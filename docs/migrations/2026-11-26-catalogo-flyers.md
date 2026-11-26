# Migración: 2026-11-26 — catálogo de flyers

## Motivo

Sumar el catálogo, las cuentas de compradores y los cobros sin tocar la home.

## Cambios

`sql/updates/2026-11-26-catalogo-flyers.sql`

Tablas nuevas: `buyers`, `flyers`, `plans`, `orders`, `order_items`, `subscriptions`, `payments`. Una fila de plan `monthly` en 0,00 hasta que el admin cargue el precio.

## Rollback

```sql
DROP TABLE payments;
DROP TABLE subscriptions;
DROP TABLE order_items;
DROP TABLE orders;
DROP TABLE flyers;
DROP TABLE plans;
DROP TABLE buyers;
```

## Impacto en datos existentes

- No modifica trabajos, visuales, clientes ni el admin actual.
- Hay que aplicarla antes de usar el catálogo en ese servidor. El código nuevo, sin estas tablas, falla al abrir `catalogo.php`.

## Aplicación en producción

- [x] Backup de la DB hecho
- [x] Aplicado en local y probado
- [x] Aplicado en producción el 26 nov 2026
- [x] `project.md` actualizado
- [x] `sql/schema.sql` actualizado
