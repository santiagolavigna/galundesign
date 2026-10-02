# Endpoint: [MÉTODO] /api/[ruta]

> Contrato del endpoint. Completar antes de implementar; mantener actualizado si cambia.

## Propósito

Una línea: qué hace y quién lo consume (frontend propio, dispositivo, sistema externo).

## Request

- **Método**: POST / GET / PUT / DELETE
- **Auth**: sesión admin / sesión usuario / token / público
- **CSRF**: sí (header `X-CSRF-Token` o campo `csrf`) / exento porque [motivo documentado]
- **Content-Type**: application/json / form-data

### Parámetros

| Campo | Tipo | Obligatorio | Validación |
|-------|------|-------------|------------|
| `product_id` | int | sí | existe en `products`, activo |
| `qty` | int | sí | >= 1, <= stock disponible |

## Response

### Éxito (200)

```json
{ "ok": true, "data": { } }
```

### Errores

| HTTP | Cuándo | Body |
|------|--------|------|
| 422 | Validación falla | `{ "ok": false, "error": "Cantidad inválida" }` |
| 401 | Sin sesión | `{ "ok": false, "error": "Sesión expirada" }` |
| 403 | CSRF inválido | `{ "ok": false, "error": "Sesión expirada, recargá la página" }` |
| 500 | Error interno (detalle solo al log) | `{ "ok": false, "error": "Error interno" }` |

## Efectos

- Tablas que escribe: [tabla + operación]
- ¿Transaccional?: sí/no — [por qué]
- Side effects: [email, WhatsApp, log de auditoría...]

## Prueba rápida

```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost/PROYECTO/api/ruta.php" -Body (@{ product_id = 1; qty = 2 } | ConvertTo-Json) -ContentType "application/json"
```
