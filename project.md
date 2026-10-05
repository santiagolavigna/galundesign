# Galun Design

> Fuente de verdad del proyecto. Toda sesión de trabajo (humana o IA) empieza leyendo este archivo
> y lo actualiza al terminar si cambió algo. NUNCA poner credenciales acá.

## Qué es

- **Cliente**: Galun Design
- **Descripción**: landing de una sola página, visualmente extrema y corta. Intro a pantalla completa con foto que se parte al medio al entrar, bloque de últimos trabajos (fotos y reels cargados desde un backoffice) y franja de contacto (WhatsApp y redes). Sin blog, sin tienda, sin formulario de leads.
- **URL producción**: pendiente
- **Hosting**: pendiente (XAMPP local; destino típico Hostinger / VPS)

## Stack

- **Backend**: PHP 8.2 vanilla + PDO
- **DB**: MySQL `galundesign`
- **Frontend**: CSS propio con tokens (sin Tailwind CDN) + GSAP + ScrollTrigger para la intro y el reveal. JS en módulos vanilla.
- **Por qué este stack**: es una landing más un admin chico (login, trabajos, datos de contacto). No hay roles, caja ni stock. Laravel sobra.

## Cómo correrlo local

```
XAMPP: http://localhost/galundesign/
DB local: galundesign, importar sql/schema.sql
Credenciales/config: config.local.php desde config.example.php
```

## Módulos

| Módulo | Estado | Notas |
|--------|--------|-------|
| 1. Landing visual de prueba | hecho | Intro con corte, grilla y contacto. Imágenes generadas en `assets/img/demo/`. Todavía no lee la DB. |
| 2. Base de datos y config | pendiente | `sql/schema.sql`, PDO, CSRF, sesión. |
| 3. Admin: login | pendiente | Un solo usuario. Rate limit. `session_regenerate_id(true)`. |
| 4. Admin: trabajos | pendiente | Alta, orden, publicar/ocultar, borrar. Subida de foto o video. |
| 5. Admin: contacto e intro | pendiente | WhatsApp, redes, nombre, frase, foto del corte. |
| 6. Landing conectada al BO | pendiente | La home lee `works` publicados y `settings`. |

Orden de dependencia: 1 puede verse ya (es el corte que el cliente tiene que aprobar). 2 antes de 3. 3 antes de 4 y 5. 6 al final.

## Esquema de datos (resumen)

- `users` — un administrador. Email único, `password_hash` con `password_hash()`.
- `works` — piezas del portafolio. Foto o video, orden manual, publicado sí/no.
- `settings` — una sola fila (`id = 1`): marca, frase, foto de intro, WhatsApp, redes, email.
- `login_attempts` — IP + fecha para frenar fuerza bruta. No guarda contraseñas.

Detalle de columnas: ver sección «Esquema» más abajo. Motor InnoDB, `utf8mb4_unicode_ci`.

## Reglas de negocio importantes

- La página pública tiene cinco momentos: intro, presentación corta, trabajos, clientes y contacto.
- El contacto no es un formulario. Son links: WhatsApp (con mensaje precargado) y redes.
- Los trabajos los carga el diseñador desde el backoffice. La home muestra solo `is_published = 1`, orden `sort_order ASC, id DESC`.
- En la landing de prueba los trabajos son imágenes en formato reel (9:16) y alguna horizontal. No se sube video: en un hosting chico pesa y tarda. Cuando exista el backoffice, cada pieza sigue siendo una imagen; el video queda fuera hasta que haga falta.
- Contacto público: WhatsApp +54 9 2346 57-1207 (`wa.me/5492346571207`) e Instagram https://www.instagram.com/galun.psd/
- Cada trabajo es un enlace a su publicación de Instagram. Hoy apuntan al perfil `https://www.instagram.com/galun.psd/` hasta tener la URL de cada post (`instagram.com/p/...`). No se incrusta el widget de Instagram.
- La sección de clientes muestra, por persona, un carrusel de fotos, el nombre y una frase. Los textos actuales son de relleno.
- No hay precios, no hay cuentas de clientes, no hay comentarios.
- Idioma de la interfaz: español.
- Un solo usuario admin. No hay roles.
- Piezas de prueba (imágenes generadas) viven en `assets/img/demo/` hasta que el diseñador suba las suyas. No se commitean uploads reales de clientes.
- Zona horaria de `created_at` / `updated_at`: hora del servidor MySQL, documentada como local del hosting (Argentina en XAMPP).

## Decisiones de diseño visual

Dirección: **corte editorial nocturno**. Fondo tinta, tipo display contemporáneo, una sola línea de acento chartreuse en el corte y en el CTA de WhatsApp. Detalle de tokens, motion y lo que no se hace: `docs/design-reference.md`.

La intro, en concreto:

1. Foto a pantalla completa (la que pida el diseñador; en la prueba, una imagen generada).
2. Al cargar, la foto entra con un leve zoom inverso.
3. Una línea vertical de luz recorre el centro.
4. La imagen se parte en dos mitades que se abren hacia los costados y dejan ver el nombre en el negro del medio.
5. Las mitades se retiran y queda la landing scrolleable.
6. Con `prefers-reduced-motion: reduce`: foto quieta, nombre visible, sin corte.

En móvil el corte es el mismo eje vertical. Las mitades se abren menos (no se salen del viewport de forma brusca) y el nombre queda legible a 375px.

## Credenciales

- Local: `config.local.php` (gitignored). Plantilla: `config.example.php`.
- Admin inicial: se crea en el seed de `sql/schema.sql` con un hash. La contraseña de desarrollo se entrega aparte, no va en `project.md` ni en el repo.
- Producción: las guarda el usuario en el hosting (panel o gestor de contraseñas). Mismo archivo `config.local.php`, no se sube por git.

## Deploys

- Último deploy: ninguno
- Procedimiento: ver `DEPLOY.md` cuando exista. Antes de subir: dump de la DB si ya hay trabajos cargados.

## Pendientes / deuda técnica

- [x] Marca: Galun Design. WhatsApp +54 9 2346 57-1207. Instagram @galun.psd
- [x] Trabajos de la prueba: solo imagen. Video no entra en esta versión.
- [ ] Hosting y dominio
- [ ] Reemplazar imágenes de prueba cuando el diseñador entregue las suyas

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
  media_type VARCHAR(16) NOT NULL,          -- 'image' | 'video'
  file_path VARCHAR(255) NOT NULL,          -- nombre aleatorio dentro de uploads/works/
  poster_path VARCHAR(255) NULL,            -- frame o portada; obligatorio si media_type = video
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_works_public (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id TINYINT UNSIGNED NOT NULL,
  brand_name VARCHAR(120) NOT NULL,
  tagline VARCHAR(240) NOT NULL,
  intro_image VARCHAR(255) NULL,            -- uploads/intro/
  whatsapp_e164 VARCHAR(20) NULL,           -- solo dígitos, con código de país, ej. 54911...
  whatsapp_message VARCHAR(240) NULL,
  instagram_url VARCHAR(255) NULL,
  tiktok_url VARCHAR(255) NULL,
  behance_url VARCHAR(255) NULL,
  email VARCHAR(190) NULL,
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

### 2. Base y config

- `config.example.php`, `config.local.php`
- `includes/init.php`, `includes/db.php`, `includes/csrf.php`
- `sql/schema.sql` + seed de `settings` y del admin
- `uploads/works/.htaccess` y `uploads/intro/.htaccess` (denegar ejecución de PHP)
- `.gitignore` ya debe ignorar `config.local.php` y `uploads/**` salvo `.htaccess`

### 3. Login

- `admin/_auth.php` — guard al tope de cada archivo admin
- `admin/login.php`, `admin/logout.php`
- Rate limit: 5 intentos fallidos por IP en 15 minutos (`login_attempts`)
- CSRF en el POST. Regenerar id de sesión al entrar.

### 4. Trabajos

- `admin/works.php` — listado, reordenar (campo numérico), publicar, borrar
- `admin/work-edit.php` — alta y edición
- Validación de upload: whitelist de extensión **y** MIME.
  - Imagen: jpg, jpeg, png, webp
  - Video: mp4, webm, tope propuesto 20 MB
  - Poster de video: imagen, obligatorio
- Renombrar con nombre aleatorio. Borrar el archivo del disco al borrar la fila, en la misma petición (si el unlink falla, no dejar la fila apuntando a un archivo que ya no existe: borrar archivo primero, después la fila, o al revés con limpieza). No hace falta transacción de dinero; sí no publicar una fila si el archivo no se guardó.

### 5. Contacto e intro

- `admin/settings.php` — marca, frase, WhatsApp, mensaje, Instagram, TikTok, Behance, email, foto de intro
- La foto de intro reemplaza a la anterior y borra el archivo viejo

### 6. Home viva

- `index.php` pasa a leer `settings` y `works`
- Vacío de trabajos: la sección no se muestra, o un texto corto («Pronto, trabajos nuevos.»). Decisión de implementación: texto corto, no una grilla vacía.
- WhatsApp: `https://wa.me/{digits}?text={mensaje}`. Si no hay número, no se renderiza el botón.

## Riesgos

- **Uploads**: ejecución de PHP en `uploads/`, MIME mentido, archivos enormes. Whitelist + MIME + nombre aleatorio + `.htaccess` + límite de tamaño en PHP y en la app.
- **Video en hosting compartido**: un reel pesado rompe el disco y el tiempo de subida. Por eso el tope y la pregunta de «archivo vs link».
- **XSS**: título, frase y URLs salen con `htmlspecialchars`. Las URLs de redes se aceptan solo si el esquema es `https`.
- **CSRF y sesión**: todo POST del admin lleva token. Cookie de sesión `HttpOnly`, `SameSite=Lax`, `Secure` en producción.
- **Fuerza bruta**: tabla `login_attempts`.
- **Performance de la intro**: una sola imagen de intro optimizada (WebP, ancho máximo ~2000px). Los trabajos below the fold con `loading="lazy"`. GSAP solo para el corte y un reveal corto; animar `transform` y `opacity`.
- **Accesibilidad**: el corte no puede ser la única forma de ver el nombre. Con reduced-motion la home es usable. Contraste del hueso `#f3efe6` sobre `#070708` supera 4.5:1. El chartreuse no lleva texto chico encima: solo la línea y el botón (texto negro sobre el botón).
