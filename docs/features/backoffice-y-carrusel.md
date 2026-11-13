# Feature: Backoffice, menú móvil y carrusel

> Plan cerrado el 13 nov 2026. Las cuatro preguntas quedaron en sí.

## Qué se necesita

El diseñador carga trabajos, visuales y clientes sin tocar el código. El formulario le dice el formato y rechaza lo que no sirve: el servidor no comprime. En el teléfono la navegación pasa a un menú. Cuando una sección tiene piezas de más, se ve como una cartelera: la del centro más grande, scroll a los dos lados, bucle infinito.

## Quién lo usa

- Público: la landing.
- Admin: una sola persona. No hay roles nuevos.

## Pantallas / flujos

1. Móvil, bajo 800px: botón «Menú». Panel con Trabajos, Visuales, Clientes y Contacto. Escritorio sin cambios.
2. `admin/login.php`: email y contraseña. 5 fallos por IP en 15 minutos.
3. Trabajos: listado, orden, publicar, destacado, borrar. Alta con título, formato (story o feed), archivo, link y etiqueta de grupo.
4. Visuales: listado y alta con título, MP4, portada JPG y link.
5. Clientes: nombre, @, foto cuadrada y URL de Instagram.
6. Home: lee solo lo publicado. Carrusel si pasa el umbral; si no, la grilla de hoy.

## Reglas de negocio

- El admin exporta él. No hay conversión en el servidor.
- Cada trabajo tiene etiqueta de grupo. Se ve en la pieza.
- Hay un solo destacado, fuera de la tira. Al marcar uno nuevo, el anterior deja de serlo.
- El link de Instagram es obligatorio en trabajos, visuales y clientes, y tiene que ser `https`.
- Ocultar (`is_published = 0`) deja el archivo. Borrar saca la fila y el archivo.
- Trabajos: JPG o WEBP. Story 1080×1920. Feed 1080×1350. Tope 3 MB. Tolerancia de proporción 3 %.
- Visuales: MP4 H.264, 1920×960, sin pista de audio, tope 40 MB. Portada JPG 1920×960, tope 400 KB, obligatoria.
- Clientes: JPG 400×400, tope 200 KB. La URL tiene que ser `https`.
- El error se muestra al lado del archivo, en español, y no se guarda la fila.
- Home: `is_published = 1`, orden `sort_order ASC, id DESC`. Sección vacía: no se renderiza.
- Carrusel manual, sin autoplay. Centro un poco más grande en trabajos y visuales. Clientes en círculos iguales.
- Umbral para prender el carrusel: trabajos 7 (el destacado no entra en la tira), visuales 5, clientes 8. Por debajo, grilla actual.
- Con `prefers-reduced-motion`, el carrusel se mueve sin animación suave. El menú no bloquea el scroll.

## Datos

- Tablas nuevas: `visuals`, `clients`. `works` deja de mezclar foto y video. Ver `project.md`.
- Alta publicada en 0 hasta que el admin la publique.
- Migración: no hay filas reales. El contenido de hoy está hardcodeado y se carga a mano cuando el BO exista.

## Endpoints / integraciones

- Formularios POST del admin, con CSRF. No hay API pública.
- WhatsApp sigue siendo un link `wa.me`.

## Fuera de alcance

- Comprimir, recortar o sacar un frame con ffmpeg.
- Varios usuarios o roles.
- Autoplay del carrusel.
- Mover el sitio del hosting compartido.

## Preguntas abiertas

Ninguna. El 13 nov 2026 se confirmó: etiqueta de grupo en cada pieza, un solo destacado, link obligatorio, y ocultar sin borrar.

## Checklist de cierre

- [ ] Implementado siguiendo la skill del stack
- [ ] Casos borde probados (skill `testing`)
- [ ] Revisión pasada (skill `review`)
- [ ] `project.md` actualizado
