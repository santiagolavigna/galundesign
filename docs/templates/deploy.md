# Deploy: [proyecto] — [fecha]

> Checklist a ejecutar en CADA deploy. Basado en la skill `deployment`.
> Credenciales: documentar DÓNDE viven, nunca los valores en git.

## Hosting

- Tipo: [Hostinger compartido / VPS / otro: ________]
- Cómo se sube: [FTP / hPanel / git pull SSH / otro]
- Dónde están las credenciales: [gestor de contraseñas / panel / SSH key — no pegar claves]

## Pre-deploy (local)

- [ ] Tests / smoke tests pasan (skill `testing`)
- [ ] Revisión de código pasada sin críticos (skill `review`)
- [ ] Auditoría de seguridad si hubo cambios en auth/uploads/pagos (agente `security`)
- [ ] Build de frontend: `npm run build` (si aplica)
- [ ] Config de producción lista (`.env` producción / `config.local.php` del server) con `display_errors Off` / `APP_DEBUG=false`
- [ ] Claves nuevas agregadas también a `.env.example` / `config.example.php`

## Backup (obligatorio, antes de tocar nada)

- [ ] Dump de la DB de producción descargado: `backup_[fecha].sql`
- [ ] Copia de los archivos actuales (o tag/commit de la versión desplegada)

## Subida

- [ ] Archivos subidos (FTP/hPanel/git pull) SIN: `.git`, `node_modules`, dumps locales, `test-*.php`, `update_db.php`
- [ ] Cambios de DB aplicados en orden: [migraciones / `sql/updates/*.sql`]
- [ ] Laravel: `composer install --no-dev`, `php artisan migrate --force`, `config:cache`, `route:cache`, `view:cache`

## Verificación en producción

- [ ] Home y páginas clave cargan sin errores ni warnings visibles
- [ ] Flujo crítico completo probado: [login admin / alta de registro / carrito → WhatsApp]
- [ ] Formularios envían correctamente (prueba real)
- [ ] HTTPS forzado; `config.local.php` / `.env` no descargables por URL
- [ ] Revisión en celular real de home + flujo principal

## Cierre

- [ ] `project.md` actualizado (fecha de deploy, qué se subió)
- [ ] Este archivo actualizado si el procedimiento cambió
- [ ] Cliente notificado: [qué se le informó]
