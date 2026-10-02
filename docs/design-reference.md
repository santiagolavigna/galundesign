# Dirección visual — Galun Design

Personalidad en una línea: **corte editorial nocturno**, de estudio que trabaja con artistas de ahora, no de portfolio genérico con tres columnas de servicios.

El catálogo UI UX Pro Max no está instalado en esta máquina (los scripts apuntan a una ruta que no existe). La dirección sale del brief y de las reglas del kit (`frontend`): oscuro, una sola voz tipográfica display, motion con sentido, mobile-first, `prefers-reduced-motion`.

## Qué se tomó del cliente

- Intro que se parte al medio al entrar.
- Pocas secciones: intro, presentación, últimos trabajos (reels y fotos), contacto.
- Tiene que llamar la atención de clientes de artistas actuales: contraste alto, formato vertical de reel, cero look corporativo.

## Qué se decidió acá

- Paleta, tipografía, el relato exacto del corte y la grilla de trabajos.
- No hay branding previo (logo, Instagram, colores de marca). Cuando el diseñador pase logo o una foto real de intro, esos assets pisan la prueba. La paleta se revisa solo si él trae colores propios.

## Tokens

```css
:root {
  --color-bg: #070708;
  --color-surface: #121214;
  --color-line: #2a2a2e;
  --color-text: #f3efe6;
  --color-muted: #9a958c;
  --color-accent: #d6ff3f;
  --color-accent-ink: #14160a;
  --radius: 0px;
  --shadow-soft: none;
  --font-display: "Syne", sans-serif;
  --font-body: "Manrope", sans-serif;
  --space: 8px;
}
```

Radio en cero a propósito: el corte es una navaja, no una card redondeada. La suavidad va en el motion, no en las esquinas.

El acento `#d6ff3f` se usa en tres lugares: la línea del corte, el botón de WhatsApp (fondo acento, texto `--color-accent-ink`) y el hover del título de una pieza. No es color de texto de párrafo.

## Tipografía

- Display: Syne, pesos 600 y 700. El nombre en el corte usa `clamp(3.2rem, 12vw, 9rem)`, tracking ligeramente negativo.
- Cuerpo: Manrope, 16–18px, line-height 1.6. Una sola frase bajo el nombre, no un párrafo de presentación.
- Carga: preconnect a Google Fonts, `display=swap`.

## Layout

1. **Intro fija al entrar** (100dvh). No es un hero con botón «ver trabajos» encima de un stock. Es la foto, el corte, el nombre.
2. **Presentación**: una línea de oficio, alineada a la izquierda, mucho aire. Sin iconos de servicios.
3. **Trabajos**: tira editorial. En desktop, mezcla de piezas 9:16 (reels) altas y alguna 4:5. En 375px, una columna, la pieza a ancho completo, ratio respetado (`aspect-ratio`, sin recorte sorpresa). Hover en desktop: la imagen escala 1.04 dentro del marco (`overflow: hidden`) y, si es video, reproduce muted. En touch: tap abre el video con controles.
4. **Contacto**: fila de links grandes (WhatsApp, Instagram, TikTok, Behance, email). En móvil, WhatsApp también queda como barra fija inferior.

Header mínimo: marca a la izquierda, «Trabajos» y «Contacto» a la derecha. Al hacer scroll, fondo `--color-bg` al 80% con `backdrop-filter: blur(14px)`.

## Motion

- Intro: ver `project.md` (zoom, línea, split en `translateX`, nombre en opacidad). Duración total ~2.4s. Ease-out. Solo `transform` y `opacity`.
- Después del corte, el header y la frase entran con un stagger de 80ms.
- Las piezas aparecen al entrar en viewport (IntersectionObserver o ScrollTrigger), stagger 70ms, 0.5s.
- `prefers-reduced-motion: reduce`: sin split, sin zoom, sin stagger. Contenido visible de una.

## Qué no hacer

- Bento de servicios, testimonios inventados, contadores, «mi proceso en 4 pasos».
- Glass en todas las cards. El blur queda solo en el header.
- Gradientes violeta, emojis como iconos, sombras blandas de template.
- Texto chico sobre el chartreuse. El acento es línea o botón con tinta oscura.
