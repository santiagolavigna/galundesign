# Migración: [fecha] — [descripción corta]

> Documentar TODO cambio de esquema en base de datos de un proyecto ya entregado/en producción.

## Motivo

Por qué se cambia el esquema (feature, bug, performance).

## Cambios

```sql
-- Laravel: nombre del archivo de migración
-- Vanilla: guardar también como sql/updates/AAAA-MM-DD-descripcion.sql

ALTER TABLE products ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER active;
CREATE INDEX idx_products_featured ON products (featured);
```

## Rollback

```sql
DROP INDEX idx_products_featured ON products;
ALTER TABLE products DROP COLUMN featured;
```

## Impacto en datos existentes

- ¿Necesita backfill / UPDATE de filas existentes?: [script si aplica]
- ¿Rompe código viejo si se aplica antes del deploy de código?: [orden correcto: código primero / DB primero]

## Aplicación en producción

- [ ] Backup de la DB hecho (`mysqldump`) — obligatorio antes de aplicar
- [ ] Aplicado en local y probado
- [ ] Aplicado en producción el: [fecha]
- [ ] `project.md` (sección esquema) actualizado
- [ ] Vanilla: `sql/schema.sql` regenerado para reflejar el estado final
