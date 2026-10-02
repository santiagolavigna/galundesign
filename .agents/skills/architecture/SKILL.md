---
name: architecture
description: Decide the right stack and structure for a new project, plan modules and DB schema before coding, set up project.md. Use at project kickoff or when adding a major module.
---

# Architecture — Design Before Code

## Choosing the stack (decision rule)

| Project type | Stack | Reference |
|---|---|---|
| Administrative system: users, roles, stock, cash, reports | Laravel 12 + Livewire 4 + Tailwind 4 + MySQL | SISTEMA-GESTION |
| Gym / memberships / attendance | Laravel 12 + Livewire (or + Vue 3 PWA if athletes need an app) | SISTEMA-GYM, FORCE |
| Landing + lead forms + small admin | Vanilla PHP 8.2 + PDO + MySQL | VISAHUNTER |
| E-commerce catalog + cart + WhatsApp checkout | Vanilla PHP + PDO + session cart | TRACKSUITARG |
| Marketing site, no DB | Vanilla PHP includes or static HTML | FIXSYS, OLIVO |
| Virtual tour | Static Marzipano export (+ optional PHP extras) | mars1pano2 |

Default to the SIMPLEST stack that covers the requirement. Laravel only when there are
roles/permissions, 4+ CRUD modules, or money/stock logic. Don't build a framework into a landing.

## Kickoff checklist (before writing code)

1. Fill `docs/templates/feature.md` (or a full spec) with the client requirements — modules,
   users/roles, screens, integrations (WhatsApp, MercadoPago, biometric devices...).
2. Design the DB schema on paper first: tables, columns, types, FKs, indexes. Review it with
   the `database` skill. Get user confirmation on anything involving money rules or permissions.
3. Create `project.md` from `docs/templates/project.md` — this is the source of truth that
   every future session reads first.
4. Define folder structure (see backend skills) and the design tokens (see `frontend` skill).
5. Only then start implementing, module by module, smallest useful slice first.

## Structural rules

- One concern per file. Vanilla PHP: shared logic in `includes/`, endpoints in `api/`,
  admin in `admin/` with `_auth.php` guard.
- Configuration is data, not code: catalogs/settings in DB or config arrays, not scattered constants.
- Duplicated logic across 2+ projects (cash audit, branding, PDF reports) — flag it to the
  user as a candidate for a shared starter template rather than copy-pasting a third time.
- Name things in English in code, Spanish in UI. Tables: plural snake_case (`sale_items`).

## Anti-patterns seen in htdocs to avoid

- "PROYECTO - Copy", "PROYECTO - bkp antes de X" folders as version control => use Git from
  day one (`git init` at kickoff, commit per feature).
- Stock Laravel README left as-is => project.md carries the real documentation.
- Credentials committed in config.php => config.local.php pattern from day one.
