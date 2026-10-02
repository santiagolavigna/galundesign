---
name: design-intelligence
description: Bridge to UI UX Pro Max - generate a project design system (style, palette, fonts, UX rules) before writing markup. Use when designing a new site, landing, dashboard, property listing, PWA or when UI looks generic and needs a distinctive direction.
---

# Design Intelligence

This skill does not replace `frontend` or `frontend-vue`. It decides **what the product
should look like**. Those skills decide **how to implement it** in this stack.

## When to use

- New project / new public-facing module (home, landing, catalog, dashboard).
- The UI works but looks generic (default gray admin, stock Tailwind).
- Choosing colors, type, layout pattern, or motion language from scratch.

Skip for pure backend, schema, security, or deploy work.

## Source of design knowledge

Invoke the global skill **`ui-ux-pro-max`** (already installed at user level:
`~/.agents/skills/ui-ux-pro-max`). It has styles, palettes, font pairings, UX rules and
a design-system generator. Do NOT paste its full catalog into every reply — query it,
pick one direction, then stop.

If a project-local copy exists under `.agents/skills/ui-ux-pro-max`, use that.

## Priority (never invert)

1. **Client branding** — logo, Instagram, `docs/design-reference.md`, colors already
   agreed (e.g. FORCE navy `#1E3A5F` + accent `#00A3DA`). These win over any catalog palette.
2. **Product type** — inmobiliaria de lujo, gym de rendimiento, club deportivo, e-commerce,
   admin. Match style to the product (Pro Max `--domain product` / `--design-system`).
3. **Kit `frontend` skill** — tokens as CSS variables, mobile-first 375px, Spanish UI,
   hover/focus/loading/empty, WebP, `prefers-reduced-motion`.
4. **Stack** — Laravel/Vue: Tailwind 4 via Vite. Vanilla/static: hand-written CSS with
   the same tokens. No Tailwind CDN on apps.

## Process (before markup)

1. Read `project.md` and `docs/design-reference.md` if they exist.
2. One-line personality ("cálido y premium", "tech y oscuro", "energía de club").
3. Ask Pro Max for ONE style + palette + font pairing that fit. If the client already
   has colors, keep them and only take type/layout/motion from the catalog.
4. Write the result into `docs/design-reference.md`: personality, CSS tokens
   (`--color-bg`, `--color-surface`, `--color-primary`, `--color-text`, `--font-display`,
   `--font-body`, `--radius`, `--shadow-soft`), layout pattern (hero / bento / editorial),
   motion notes, and what was taken from Pro Max vs from the client.
5. Only then implement, following `frontend` (and `frontend-vue` if the project is Vue).

## Output the user should see

A short Spanish summary: estilo elegido, por qué, tokens, y la ruta del
`docs/design-reference.md` actualizado. Two visual directions only if branding is
ambiguous — pick a recommendation.
