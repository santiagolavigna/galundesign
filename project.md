# Galun Design

> Fuente de verdad del proyecto. Toda sesión de trabajo (humana o IA) empieza leyendo este archivo
> y lo actualiza al terminar si cambió algo. NUNCA poner credenciales acá.

## Qué es

- **Cliente**: Galun Design
- **Descripción**: landing de una sola página, visualmente extrema y corta. Intro a pantalla completa con foto que se parte al medio al entrar, bloque de últimos trabajos (fotos y reels cargados desde un backoffice) y franja de contacto (WhatsApp y redes). Sin blog y sin formulario de leads. La home sigue siendo solo el portfolio. Un catálogo de flyers aparte está diseñado y no está implementado (`docs/features/catalogo-flyers.md`).
- **URL producción**: https://galundesign.com
- **Clase**: no crítico. Queda en Hostinger compartido. No va al servidor dedicado.
- **Hosting**: Hostinger compartido. El sitio se publica solo al pushear `main` en GitHub (`santiagolavigna/galundesign`). Credenciales en hPanel, no en el repo.

## Stack

- **Backend**: PHP 8.2 vanilla + PDO
- **DB**: MySQL `galundesign`
- **Frontend**: CSS propio con tokens (sin Tailwind CDN) + GSAP + ScrollTrigger para la intro y el reveal. JS en módulos vanilla.
- **Por qué este stack**: es una landing más un admin chico (login, trabajos, visuales, clientes, contacto). No hay roles, caja ni stock. Laravel sobra. El servidor no convierte archivos: el admin exporta foto y video en el formato que pide el formulario.

## Cómo correrlo local

```
XAMPP: http://localhost/galundesign/
DB local: galundesign, importar sql/schema.sql
Credenciales/config: config.local.php desde config.example.php
Admin: http://localhost/galundesign/admin/login.php
Usuario: galun@galundesign.com
La clave no se escribe en este archivo.
```

## Módulos

| Módulo | Estado | Notas |
|--------|--------|-------|
| 1. Landing visual | hecho | Intro, grilla y contacto. La home lee lo publicado. |
| 2. Menú hamburguesa | hecho | Solo móvil, bajo 800px. No bloquea el scroll. |
| 3. Base de datos y config | hecho | `sql/schema.sql` importado en local. PDO, CSRF, sesión, carpetas de upload. |
| 4. Admin: login | hecho | Un usuario. Rate limit. `session_regenerate_id(true)`. |
| 5. Admin: trabajos | hecho | JPG o WEBP. Historia 9:16. Post 4:5 o cuadrado. Tope 8 MB. |
| 6. Admin: visuales | hecho | MP4 H.264 1920×960 sin audio, más portada JPG. |
| 7. Admin: clientes | hecho | Nombre, @, JPG 400×400, link de Instagram. |
| 8. Home viva y carrusel | hecho | Carrusel en Trabajos, Visuales y Clientes. |
| 9. Mensajes y errores | hecho | Sin 403 crudo. El admin dice si se guardó o por qué no. |
| 10. Catálogo de flyers | en producción, sin cobro | Página `catalogo.php`. PSD con marca de agua. Cuentas y panel de cobros. Mercado Pago espera las tres claves en el servidor. |

Orden: 2 puede salir solo. 3 antes de 4. 4 antes de 5, 6 y 7. 8 al final. El detalle de la home está en `docs/features/backoffice-y-carrusel.md`. El catálogo, en `docs/features/catalogo-flyers.md`: primero la página y la carga, después el cobro de un flyer, después la membresía, y el editor solo si la prueba cierra.

## Esquema de datos (resumen)

- `users` — un administrador. Email único, `password_hash` con `password_hash()`.
- `works` — diseños (solo imagen). Formato story o feed, orden manual, publicado sí/no, un destacado.
- `visuals` — videos de la sección Visuales. MP4, portada, orden, publicado sí/no.
- `clients` — perfiles. Nombre, @, foto, URL.
- `settings` — una sola fila (`id = 1`): marca, frase, WhatsApp, redes.
- `login_attempts` — IP + fecha para frenar fuerza bruta. No guarda contraseñas.
- Catálogo: `buyers`, `flyers`, `plans`, `orders`, `order_items`, `subscriptions`, `payments`. El precio va en `DECIMAL(12,2)` y la venta guarda el precio cobrado. El comprador no comparte tabla con el admin. Migración `sql/updates/2026-11-26-catalogo-flyers.sql`, aplicada en local y en producción el 26 nov 2026, después de un backup.

Detalle de columnas: ver sección «Esquema» más abajo. Motor InnoDB, `utf8mb4_unicode_ci`.

## Reglas de negocio importantes

- La página pública tiene cinco momentos: portada, trabajos (destacado + grupos), visuales, clientes y contacto.
- El contacto no es un formulario. Son links: WhatsApp (con mensaje precargado) y redes.
- Los trabajos, los visuales y los clientes los carga el diseñador desde el backoffice. La home muestra solo `is_published = 1`, orden `sort_order ASC, id DESC`.
- El servidor no comprime ni recorta. Si el archivo no cumple el formato, el formulario lo rechaza y dice ahí mismo qué exportar. El admin sabe convertir.
- Trabajos: JPG o WEBP. Historia 9:16 (1080×1920). Post de Instagram 4:5 (1080×1350) o cuadrado (1080×1080). Tope 8 MB. Si no coincide, el formulario dice la medida real y no guarda la fila.
- Visuales: MP4 H.264, 1920×960, sin audio, tope 40 MB. La portada es un JPG 1920×960 aparte, tope 400 KB.
- Clientes: JPG cuadrado 400×400, tope 200 KB.
- Cada trabajo lleva una etiqueta de grupo (Eliastom Kick, Eventos, Historias u otra). Se muestra en la pieza. Hay un solo destacado, fuera del carrusel: al marcar otro, el anterior deja de serlo.
- El link de Instagram es obligatorio en trabajos, visuales y clientes. Tiene que ser `https`.
- Ocultar no borra. `is_published = 0` saca la pieza de la home y deja el archivo. Borrar elimina la fila y el archivo.
- Carrusel infinito (centro más grande, scroll a ambos lados) solo cuando la sección tiene piezas de más. Trabajos desde 7, sin contar el destacado. Visuales desde 5. Clientes desde 8, y en círculos iguales, sin agrandar el del centro. Por debajo de eso queda la grilla actual.
- Contacto público: WhatsApp +54 9 2346 57-1207 (`wa.me/5492346571207`) e Instagram https://www.instagram.com/galun.psd/
- Cada trabajo es un enlace a su publicación de Instagram. Hoy apuntan al perfil `https://www.instagram.com/galun.psd/` hasta tener la URL de cada post (`instagram.com/p/...`). No se incrusta el widget de Instagram.
- Trabajos usa los archivos de `assets/assets-web-gd/`: destacado Fuego x Soho; Eliastom Kick (Amor de su cama, Componemos con Lauta, Zaina x Tomhouse); Eventos (Perro Primo, Tomas Marenzana, Frenesi); Historias (Fuego x Soho y Fuego x El Club).
- Visuales son los mp4 comprimidos en `assets/media/` (Lali DJ, Lali 360, Luciana Idiart, Candu, Lola Bottini). Se reproducen en mute al entrar en pantalla.
- La portada no usa la foto. El fondo es un degradado blanco y negro hecho en CSS, con grano. El título usa Unbounded, más grande. Al scrollear, «Bienvenido a» y «Galundesign» giran en sentido contrario.
- Clientes se muestran en círculos con la foto de perfil actual, el nombre y el @. Las fotos están en `assets/img/clients/` (150 px, copiadas del perfil público). Si falta el archivo, el círculo muestra las iniciales.
- No llegó pieza de Perreo Macarena ni de Nicole Fernandez. `TOMI.png` es la misma fecha que el flyer de Tomas Marenzana; en la grilla quedó el flyer.
- Clientes son siete perfiles de Instagram (Elias Tom, Candu Dominguez, Nicole Fernandez, Lali DJ, Lola Bottini, Frenesi Fiesta, Boveda Fiesta). Cada nombre abre ese perfil.
- No hay precios, no hay cuentas de clientes, no hay comentarios.
- Idioma de la interfaz: español.
- El admin no muestra errores crudos del servidor. Al guardar, el listado dice «Se guardó “nombre”.» Si el archivo no sirve, el formulario se queda abierto y explica el motivo. Una carpeta de uploads, abierta sin archivo, vuelve a la home. Un 403, 404 o 500 muestra una página estática en español con link al inicio. Detalle: `docs/features/mensajes-y-errores.md`.
- Un solo usuario admin. No hay roles.
- Piezas de prueba (imágenes generadas) viven en `assets/img/demo/` hasta que el diseñador suba las suyas. No se commitean uploads reales de clientes.
- Zona horaria de `created_at` / `updated_at`: hora del servidor MySQL, documentada como local del hosting (Argentina en XAMPP).

## Decisiones de diseño visual

Dirección: **corte editorial nocturno**. Fondo tinta, tipo display contemporáneo, una sola línea de acento chartreuse en el corte y en el CTA de WhatsApp. Detalle de tokens, motion y lo que no se hace: `docs/design-reference.md`.

La intro, en concreto:

1. La pantalla se parte en vertical: una línea chartreuse y las dos mitades se abren. La navegación sigue visible y el scroll no se bloquea.
2. «Bienvenido a» y «Galundesign» salen del corte, letra por letra, del centro hacia afuera.
3. El título no viaja al logo.
4. Si la animación no termina, a los 4,5 s el corte se va y el texto queda completo.
5. Con `prefers-reduced-motion: reduce`: sin corte y sin revelado. El texto está completo.

En móvil el corte es el mismo y el nombre queda legible a 375px.

## Credenciales

- Local: `config.local.php` (gitignored). Plantilla: `config.example.php`.
- Admin inicial: se crea en el seed de `sql/schema.sql` con un hash. La contraseña de desarrollo se entrega aparte, no va en `project.md` ni en el repo.
- Producción: `config.local.php` está en el document root del servidor y no entra a git. La base es `u550966134_galundesign`, usuario `u550966134_galun`, host `127.0.0.1`. La clave de esa base no va en este archivo.

## Deploys

- Último deploy: 26 nov 2026, https://galundesign.com (el ingreso del catálogo dice Iniciar sesión). Commit `281981f`.
- No había base previa, así que no hubo dump que respaldar.
- PHP de producción: 8.3. `upload_max_filesize` y `post_max_size` ya estaban en 256M.
- `config.local.php` y `sql/` responden 403. El acceso remoto a la base quedó cerrado después de importar.
- Procedimiento: push a `main`. Hostinger despliega el repo en el document root. La config de la base se vuelve a subir a mano si un deploy la pisa, porque no está en git.

## Pendientes / deuda técnica

- [x] Marca: Galun Design. WhatsApp +54 9 2346 57-1207. Instagram @galun.psd
- [x] Trabajos de la prueba: solo imagen. Video no entra en esta versión.
- [ ] Hosting y dominio
- [x] Grupos como etiqueta en cada pieza. Un solo destacado. Link de Instagram obligatorio. Se puede ocultar sin borrar.
- [x] La home local lee la base. La semilla publica las piezas que ya estaban en la landing.
- [x] Base de producción creada e importada el 13 nov 2026. Los topes de subida ya estaban en 256M.

## Esquema

```sql
CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE works (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  frame VARCHAR(16) NOT NULL,               -- 'story' (9:16) | 'feed' (4:5)
  file_path VARCHAR(255) NOT NULL,          -- nombre aleatorio en uploads/works/
  link_url VARCHAR(255) NOT NULL,
  group_label VARCHAR(80) NOT NULL,         -- texto libre, ej. Eliastom Kick
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  width INT UNSIGNED NOT NULL,
  height INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_works_public (is_published, is_featured, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visuals (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  file_path VARCHAR(255) NOT NULL,          -- uploads/visuals/
  poster_path VARCHAR(255) NOT NULL,
  link_url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  width INT UNSIGNED NOT NULL,
  height INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_visuals_public (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  handle VARCHAR(80) NOT NULL,
  photo_path VARCHAR(255) NOT NULL,         -- uploads/clients/
  url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_clients_public (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id TINYINT UNSIGNED NOT NULL,
  brand_name VARCHAR(120) NOT NULL,
  tagline VARCHAR(240) NOT NULL,
  whatsapp_e164 VARCHAR(20) NULL,           -- solo dígitos, con código de país, ej. 5492346571207
  whatsapp_message VARCHAR(240) NULL,
  instagram_url VARCHAR(255) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_login_attempts_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`settings.id` vale siempre `1`. No hay más filas.

## Módulos — archivos

### 1. Landing visual de prueba

Pantallas: `index.php` estático (datos hardcodeados de demo).

Archivos:

- `index.php`
- `assets/css/app.css`
- `assets/js/intro.js`
- `assets/img/demo/` — foto de intro, 6 piezas verticales 9:16, 2 horizontales

Sin base de datos todavía. Sirve para que el diseñador diga si el corte y la grilla le cierran.

### 3. Base y config

- `config.example.php`, `config.local.php`
- `includes/init.php` (sesión, CSRF, `e()`), `includes/db.php`, `includes/home.php`, `includes/content.php`, `includes/uploads.php`
- `sql/schema.sql` + seed de `settings` y del admin
- `uploads/works/.htaccess`, `uploads/visuals/.htaccess` y `uploads/clients/.htaccess` (denegar ejecución de PHP)
- `.gitignore` ya debe ignorar `config.local.php` y `uploads/**` salvo `.htaccess`

### 4. Login

- `admin/_auth.php` — guard al tope de cada archivo admin
- `admin/login.php`, `admin/logout.php`
- Rate limit: 5 intentos fallidos por IP en 15 minutos (`login_attempts`)
- CSRF en el POST. Regenerar id de sesión al entrar.

### 2. Menú hamburguesa

- `index.php`, `assets/css/app.css`, `assets/js/nav.js`
- Bajo 800px el nav de texto se reemplaza por un botón «Menú». El panel lista Trabajos, Visuales, Clientes y Contacto.
- En escritorio el nav queda como está. El panel cierra al elegir una sección, con Escape o al tocar afuera. No se bloquea el scroll de la página.

### 5, 6 y 7. Trabajos, visuales y clientes

- `admin/works.php`, `admin/work-edit.php`
- `admin/visuals.php`, `admin/visual-edit.php`
- `admin/clients.php`, `admin/client-edit.php`
- Cada alta muestra, al lado del archivo, el formato exacto. Si no cumple, el error está en ese campo, en español, y no se guarda la fila.
- No hay ffmpeg ni reencode. Imagen: extensión, MIME y `getimagesize`. Video: extensión, MIME, peso, y lectura del átomo `tkhd` para el tamaño. Si el MP4 trae pista de audio, se rechaza.
- Nombre aleatorio. Al borrar, primero el archivo y después la fila. Una fila nueva solo se inserta si el archivo ya quedó en disco.
- Carpetas: `uploads/works/`, `uploads/visuals/`, `uploads/clients/`, cada una con `.htaccess` que niega PHP.

### Contacto

- `admin/settings.php` — marca, frase, WhatsApp, mensaje, Instagram. La intro no tiene foto: el fondo sigue siendo el degradado.

### 8. Home viva y carrusel

- `index.php` lee `settings`, `works`, `visuals` y `clients` publicados.
- Sección sin piezas: no se renderiza. No hay grilla vacía.
- Carrusel: la pieza del centro va un poco más grande. Se scrollea a izquierda y derecha, con arrastre, swipe y flechas. El bucle es manual, sin autoplay. Al cruzar el extremo, el salto no se ve.
- Umbral: trabajos 7 (el destacado queda fuera de la tira), visuales 5, clientes 8. Por debajo, la grilla de hoy. Los clientes no agrandan el del centro.
- `prefers-reduced-motion`: mismo carrusel, sin animación suave.
- WhatsApp: `https://wa.me/{digits}?text={mensaje}`. Si no hay número, no se renderiza el botón.

### 9. Mensajes y errores

Sin tablas nuevas. El stack no cambia.

- `assets/error.php` — fondo noche, texto en español. Si la dirección era del panel, dice que no se pudo guardar. Sin la palabra 403 ni texto en inglés.
- `.htaccess` — `ErrorDocument` 403, 404 y 500 apuntan a esa página.
- `logs/app.log` — anota el corte del servidor y, si PHP llega a ver el archivo, el peso, el formato y el motivo. La carpeta no se sirve. El archivo no va a git.
- `uploads/index.html` y el mismo archivo en `works/`, `visuals/` y `clients/` — redirigen a `/`. No listan archivos. `.gitignore` tiene que dejar entrar esos `index.html`, igual que los `.htaccess`.
- `includes/init.php` — si el POST llega vacío, el aviso es de peso, no de sesión. Si el token no coincide, la respuesta es la página del admin.
- `admin/work-edit.php`, `visual-edit.php`, `client-edit.php`, `settings.php` — el éxito incluye el nombre y se ve en el listado. El error queda en un bloque arriba del formulario.
- `assets/css/admin.css` — el aviso de éxito y el de error se leen de un vistazo.

Orden: se puede hacer solo, después del módulo 8.

### 10. Catálogo de flyers

Publicado el 26 nov 2026. Los precios los carga el admin. Faltan las claves de Mercado Pago en el `config.local.php` del servidor (`mp_access_token`, `mp_public_key`, `mp_webhook_secret`). Sin esas claves el botón avisa que el cobro no está conectado.

La home no cambia de estructura. Se agrega un link del nav a `catalogo.php`. Galun sube, por flyer, una imagen y el PSD. En la página la imagen sale con marca de agua. El PSD no tiene URL pública. El comprador lo usa en sus fiestas y no lo revende como plantilla.

Cobro con Mercado Pago, en pesos: Checkout Pro para un flyer, suscripción para el mes. El comprador autoriza el débito una vez en Mercado Pago. Galun no manda un link cada mes y el sitio no guarda la tarjeta. Con la membresía al día se descarga cualquier publicado, también los nuevos. Si el mes no se acredita, ese día se apagan las descargas. El mes ya cobrado dura hasta el día que cubre. El precio de cada flyer y el de la membresía los escribe el admin; la venta guarda el número cobrado. En el mismo panel se consultan los registrados y los cobros, con la suma de lo acreditado en el mes. El sistema no calcula ni descuenta un porcentaje. El token va en `config.local.php`. Un webhook acredita la venta una sola vez. La primera entrega es la descarga del PSD.

No se programa un Photoshop. Adobe no se puede incrustar para un visitante sin cuenta de Adobe. La opción posterior es Photopea, que abre el PSD en el navegador, y solo después de probar un archivo real y de leer sus condiciones. Si no sirve, se venden los PSD igual.

Detalle, esquema y preguntas: `docs/features/catalogo-flyers.md`.

## Riesgos

- **Uploads**: ejecución de PHP en `uploads/`, MIME mentido, archivos enormes. Whitelist + MIME + nombre aleatorio + `.htaccess` + límite de tamaño en PHP y en la app.
- **Catálogo**: el PSD no puede quedar en una URL abierta. El webhook de Mercado Pago tiene que ser idempotente. Un PSD pesado puede recibir el mismo corte 403 que ya se vio en una subida de unos 2 MB; hay que probar un archivo real en el hosting antes de prometer la carga semanal.
- **Video en hosting compartido**: el tope es 40 MB y el admin lo exporta él. En Hostinger, `upload_max_filesize` y `post_max_size` tienen que estar en 48M o más. No instalar ffmpeg para este flujo.
- **XSS**: título, frase y URLs salen con `htmlspecialchars`. Las URLs de redes se aceptan solo si el esquema es `https`.
- **CSRF y sesión**: todo POST del admin lleva token. Cookie de sesión `HttpOnly`, `SameSite=Lax`, `Secure` en producción.
- **Fuerza bruta**: tabla `login_attempts`.
- **Performance de la intro**: una sola imagen de intro optimizada (WebP, ancho máximo ~2000px). Los trabajos below the fold con `loading="lazy"`. GSAP solo para el corte y un reveal corto; animar `transform` y `opacity`.
- **Accesibilidad**: el corte no puede ser la única forma de ver el nombre. Con reduced-motion la home es usable. Contraste del hueso `#f3efe6` sobre `#070708` supera 4.5:1. El chartreuse no lleva texto chico encima: solo la línea y el botón (texto negro sobre el botón).
