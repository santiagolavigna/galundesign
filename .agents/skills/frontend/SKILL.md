---
name: frontend
description: Modern, innovative UI design and frontend implementation - design tokens, Tailwind 4, GSAP animations, glassmorphism/bento layouts, mobile-first responsive, accessibility, performance. Use for any UI/UX work, landing pages, dashboards or storefronts.
---

# Frontend — Modern & Innovative UI

Goal: every delivered product looks like a 2026 premium product, not a generic Bootstrap admin.
UI text is ALWAYS in Spanish. Design decisions below are defaults — a client brief overrides them.

## Design process (do this BEFORE writing markup)

1. Define the personality in one line: "elegante y sobrio" (admin systems), "audaz y urbano"
   (streetwear e-commerce), "cálido y confiable" (services/restaurants), "tech y preciso" (SaaS).
2. Pick tokens and write them as CSS custom properties (or Tailwind theme) FIRST:

```css
:root {
    --color-bg: #0b0f14;          /* pick per project, never default gray-100 everywhere */
    --color-surface: #121821;
    --color-primary: #22d3ee;
    --color-text: #e6edf3;
    --color-muted: #8b98a5;
    --radius: 14px;
    --shadow-soft: 0 8px 30px rgb(0 0 0 / .25);
    --font-display: 'Sora', sans-serif;      /* headings */
    --font-body: 'Inter', sans-serif;        /* body */
}
```

3. Typography scale: one display font + one body font (Google Fonts, `display=swap`,
   preconnect). Sizes on a scale (e.g. 14/16/18/24/32/48). Line-height 1.5-1.7 for body.
4. Spacing on a 4px/8px grid. Consistent radius. One shadow style, used sparingly.

## Layout patterns that read as "modern"

- **Bento grids** for dashboards and feature sections (CSS grid, mixed card sizes).
- **Glassmorphism** for overlays/nav on top of imagery: `backdrop-filter: blur(14px)` +
  translucent surface + 1px subtle border. Use on 1-2 elements max, not everything.
- **Hero sections**: big display type (clamp()), gradient or image with overlay, single clear CTA.
- Generous whitespace. If a section feels crowded, remove elements before shrinking them.
- Sticky, translucent header that gains background on scroll.

## Motion (GSAP or CSS)

- Micro-interactions: buttons/cards get `transition: transform .2s, box-shadow .2s` with a
  subtle lift (`translateY(-2px)`). Every interactive element must have hover, focus-visible
  and active states.
- Scroll reveals: IntersectionObserver adding a class, or GSAP `ScrollTrigger` (already used
  in TRACKSUITARG). Stagger children 60-90ms. Duration 0.4-0.7s, ease-out.
- Respect `prefers-reduced-motion: reduce` — disable non-essential animation.
- Never animate `width/height/top/left`; animate `transform` and `opacity` only.

## Mobile-first (non-negotiable)

- Write base styles for 375px, add `min-width` breakpoints (768, 1024, 1280).
- Touch targets >= 44px. Test nav, tables and forms mentally on mobile FIRST.
- Tables on mobile: card layout or horizontal scroll with sticky first column — never squashed.
- Real device features: sticky bottom CTA bar on mobile for e-commerce/WhatsApp actions.

## Stack rules

- **Laravel projects**: Tailwind 4 via Vite. Extract repeated patterns into Blade components.
- **Vanilla/static projects**: hand-written CSS with the token system above (as in FIXSYS,
  OLIVO). Tailwind CDN only acceptable on small static marketing sites, never on apps.
- JS: vanilla ES modules; `fetch` with proper error handling and Spanish user feedback
  (toast/inline), never silent `catch {}`. jQuery only if the project already depends on it.

## Forms and feedback UX

- Label every field, placeholder is NOT a label. Inline validation errors in Spanish under the field.
- Submit buttons: disable + spinner while pending, restore on error with a clear message.
- Empty states: never a blank table — icon + explanation + primary action.
- Loading states: skeletons for content areas, not layout-shifting spinners.
- Confirmations for destructive actions ("¿Eliminar producto? Esta acción no se puede deshacer.").

## Accessibility minimum

- Text contrast >= 4.5:1 (check primary-on-bg combinations when picking tokens).
- Semantic HTML: `<button>` for actions, `<a>` for navigation, one `<h1>`, landmarks
  (`<header> <main> <nav> <footer>`).
- `:focus-visible` styles on everything interactive; `alt` on meaningful images
  (`alt=""` for decorative).

## Performance checklist

- Images: WebP (there is a `convert_jpg_to_webp.php` helper in htdocs), explicit
  `width`/`height`, `loading="lazy"` below the fold.
- Fonts: max 2 families, preconnect to fonts.gstatic.com, `display=swap`.
- One CSS file per page type; defer non-critical JS (`defer` attribute).
- Lighthouse mental targets: LCP < 2.5s, no layout shifts from images/fonts.
