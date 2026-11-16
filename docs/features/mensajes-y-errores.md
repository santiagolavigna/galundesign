# Feature: Mensajes del admin y páginas sin error crudo

> Diseño cerrado el 16 nov 2026. Sin código de aplicación en este paso.

## Qué se necesita

El diseñador no tiene que ver un 403 en inglés del servidor. Si guarda una pieza, el admin le dice si se guardó o por qué no.

## Quién lo usa

- Rol: el único usuario admin, y cualquiera que abra una dirección que el servidor niega.
- No hay permisos nuevos.

## Pantallas / flujos

1. Guardar trabajo, visual, cliente o contacto. Si entra, vuelve al listado con un aviso verde y el nombre de la pieza: «Se guardó “CANDUVISUAL”.» Contacto: «Se guardó el contacto.»
2. Si el archivo no cumple el formato, se queda en el formulario. El aviso va arriba, en un bloque, en español, y dice qué exportar. La fila no se guarda.
3. Si el POST llega vacío porque el archivo se pasó del tope del servidor, el aviso es «No se guardó. El archivo es demasiado pesado.» No se dice que la sesión expiró.
4. Si la sesión sí expiró, el aviso sigue siendo «La sesión expiró. Volvé a entrar.» dentro de la página del admin, no un texto suelto.
5. Abrir `uploads/`, `uploads/works/`, `uploads/visuals/` o `uploads/clients/` no lista archivos y no muestra 403. Redirige a la home.
6. Cualquier otra dirección inexistente o bloqueada (404, 403, 500) muestra una página estática en español, con el mismo fondo de noche, y un link «Volver al inicio». Sin código de error, sin inglés, sin ruta del archivo.

## Reglas de negocio

- El servidor sigue sin comprimir ni recortar. El rechazo de formato no cambia.
- La carpeta de uploads no se puede listar ni ejecutar PHP. Eso se mantiene.
- `config.local.php`, `sql/` y `project.md` siguen sin servirse. Cambia solo la página que ve la persona.
- No se inventan mensajes nuevos de negocio. Se usan los que ya explica el formulario (medidas, peso, JPG, MP4).

## Datos

- Sin tablas nuevas y sin migración.

## Endpoints / integraciones

- Ninguno.

## Fuera de alcance

- Borrar el video repetido que quedó en el disco sin fila.
- Reintentar una subida cortada por el firewall del hosting. Si el servidor corta el POST antes de PHP, la persona ve la página estática y vuelve a entrar.
- Cambiar el formato que se le pide al diseñador.

## Checklist de cierre

- [ ] Implementado siguiendo la skill del stack
- [ ] Casos borde probados (skill `testing`)
- [ ] Revisión pasada (skill `review`)
- [ ] `project.md` actualizado
