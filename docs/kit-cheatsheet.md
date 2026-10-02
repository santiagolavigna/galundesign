# Cheatsheet — AI Development Kit

En el chat de Agent escribí `/` y el comando. Un tema por chat.

## Regla de oro

```
/comercial → /arquitecto → (/diseno) → /backend y/o /frontend
    → /qa → /reviewer → (/security) → /cerrar → /commit → (/deploy)
```

- `/diseno` si es vitrina o look nuevo.
- `/security` **siempre** si tocaste login, roles, passwords, uploads o plata.
- `/reviewer` **siempre** antes de commit/push.
- `/deploy` **nunca** sube hasta que confirmes (“sí, deploy ahora”).

`/orquestador` no va al inicio de un repo vacío: va cuando ya hay sistema que auditar.

## Qué es siempre (no saltear)

| Momento | Slash |
|---------|--------|
| Antes de commit o de mostrarle al cliente | `/reviewer` |
| Auth / roles / passwords / uploads / plata | `/security` además |
| Fin de feature o del día | `/cerrar` |
| Guardar en git | `/commit` (`/commit y push` si hay remoto) |
| Producción | `/deploy` (pide hosting si falta) |

Los hooks de git corren solos: bloquean `.env` y dumps.

## Proyecto nuevo

1. Adjuntá el PDF → `/comercial`
2. Respondé las preguntas
3. `/arquitecto`
4. Sitio de impacto → `/diseno`
5. `/frontend` / `/backend` por módulo
6. `/reviewer` → `/cerrar` → `/commit`

## Feature nueva (sistema que ya anda)

1. `/arquitecto` + las reglas de negocio
2. `/backend` y/o `/frontend` (o `/implementar` si ya hay P0 aprobado)
3. Flujo sensible → `/qa`
4. `/reviewer` → (`/security`) → `/cerrar` → `/commit`

## Visual

| Situación | Slash |
|-----------|--------|
| Look desde cero | `/diseno` → `/frontend` |
| App genérica que hay que diferenciar | `/orquestador-visual` → OK → `/frontend` |
| Pulir un módulo | `/frontend` + “solo pulido visual de X” |

## Sistema grande (auditoría)

`/orquestador` → marcás P0 → `/implementar` → `/reviewer` → `/cerrar` → `/commit`

## Todos los slash

| Slash | ¿Codea? | Uso |
|-------|---------|-----|
| `/comercial` | No | PDF/briefing → `project.md` |
| `/arquitecto` | No | Stack, módulos, DB |
| `/diseno` | No | Design system → `docs/design-reference.md` |
| `/orquestador` | No | Auditoría completa |
| `/orquestador-visual` | No | Auditoría + look |
| `/backend` | Sí | Lógica / API / DB |
| `/frontend` | Sí | Pantallas / pulido |
| `/implementar` | Sí | Solo P0 que aprobaste |
| `/qa` | No | Probar flujos (no arregla) |
| `/reviewer` | No | Checklist pre-entrega |
| `/security` | No* | Auth/plata/uploads (*fix solo si lo pedís) |
| `/cerrar` | Solo `project.md` | Dejar el hilo para la próxima sesión |
| `/commit` | Git | Commit; push solo si lo pedís |
| `/deploy` | No hasta confirmar | Hosting/credenciales (ubicación, no passwords) + checklist |
