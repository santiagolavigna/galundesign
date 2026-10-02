---
description: Preparar o ejecutar deploy. Pide hosting/credenciales si faltan. No sube hasta confirmación.
---

Leé la skill `deployment`, `docs/templates/deploy.md` (o `DEPLOY.md` del proyecto) y `project.md`.

## Fase 1 — inventario (siempre primero)

Completá o preguntá en español, SIN pedir que peguen passwords en el chat si se pueden
evitar:

1. ¿Dónde está producción? Hostinger compartido, VPS (cuál), u otro cloud/servicio.
2. ¿Cómo se sube? FTP, hPanel, git pull por SSH, rsync, CI.
3. ¿Dónde viven las credenciales? Gestor, panel del hosting, clave SSH — documentar la
   UBICACIÓN en `project.md` (sección Credenciales/Deploys), nunca los valores.
4. Si no hay hosting todavía: sugerí la opción más simple para este stack
   (sitio PHP/vitrina → Hostinger; Laravel API → VPS con SSH). El kit no está atado
   a un solo proveedor.

Si falta cualquiera de 1–3: STOP. No subas archivos. Dejá el checklist y las preguntas.

Actualizá `docs/templates/deploy.md` (copia de trabajo del deploy) y `project.md` con
tipo de hosting y cómo se publica.

## Fase 2 — ejecutar (solo si el usuario confirmó este deploy)

Condiciones: dijo claramente que subas AHORA (ej. "sí, deploy", "subí") AND backup de
DB de producción hecho o el usuario aceptó el riesgo por escrito.

Entonces seguí la skill `deployment` y el checklist: build, excludes, migrate, verificar
HTTPS y que `.env`/`config.local.php` no sean descargables.

Nunca commitees ni subas `.env` local ni `config.local.php` local.
Contexto extra: $ARGUMENTS
