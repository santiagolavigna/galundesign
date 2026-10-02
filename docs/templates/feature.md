# Feature: [nombre de la funcionalidad]

> Completar ANTES de programar. Si algún punto es ambiguo, preguntar al cliente/usuario — no inventar reglas de negocio.

## Qué se necesita

Descripción en 2-4 líneas de lo que pide el cliente y el problema que resuelve.

## Quién lo usa

- Rol/es que acceden (público, admin, staff, socio...):
- ¿Requiere permisos nuevos?:

## Pantallas / flujos

1. [Pantalla o paso 1: qué ve el usuario, qué acciones puede hacer]
2. [Pantalla o paso 2...]

## Reglas de negocio

- [Regla 1, tal como la dijo el cliente. Ej.: "el precio mayorista aplica desde 3 unidades"]
- [Regla 2...]
- Casos borde acordados: [qué pasa si stock 0, si el socio está vencido, etc.]

## Datos

- Tablas nuevas o modificadas (columnas, tipos, relaciones):
- ¿Afecta datos existentes? ¿Necesita migración?: (usar `migration.md`)

## Endpoints / integraciones

- Endpoints AJAX/API nuevos: (documentar cada uno con `api.md`)
- Integraciones externas (WhatsApp, MercadoPago, email...):

## Fuera de alcance

- [Qué NO incluye esta feature, para evitar malentendidos con el cliente]

## Checklist de cierre

- [ ] Implementado siguiendo la skill del stack
- [ ] Casos borde probados (skill `testing`)
- [ ] Revisión pasada (skill `review`)
- [ ] `project.md` actualizado
