---
name: documentation
description: Standardized project documentation - project.md as source of truth, README, DEPLOY.md, inline docs policy. Use when creating or updating project documentation.
---

# Documentation

Documentation is written in SPANISH (the user and clients read it). Three documents per
project, no more, no less — kept short and current.

## project.md — the source of truth (mandatory)

Created at kickoff from `docs/templates/project.md`. Every AI session should read it first;
update it whenever a module, table or config key changes. Contains:

- Qué es el proyecto, cliente, URL de producción.
- Stack y cómo correrlo local (XAMPP path / artisan serve, DB name, seeds).
- Módulos y estado (hecho / en curso / pendiente).
- Esquema DB resumido (tablas y relaciones clave, no el dump completo).
- Decisiones importantes y reglas de negocio ("los precios mayoristas aplican desde 3 unidades").
- Dónde están las credenciales (NUNCA las credenciales mismas).
- Pendientes / deuda técnica conocida.

The stock Laravel README is NOT documentation — replace its content or keep project.md as
the real doc (pattern already used in SISTEMA-GYM/SISTEMA-GESTION).

## README.md — for someone opening the repo cold

10-30 lines: one-paragraph description, requirements, install/run steps, link to project.md
and DEPLOY.md. No marketing filler.

## DEPLOY.md — how this specific project ships

From `docs/templates/deploy.md`: hosting (Hostinger/VPS), how files get there, build steps
(`npm run build`, composer install), env/config differences, DB migration procedure, and the
post-deploy verification list. Update it after every deploy that taught you something.

## Inline documentation policy

- Comments explain WHY (business rules, weird client requirements, workarounds), never WHAT
  the code obviously does.
- Complex business calculations (liquidaciones, comisiones, promos) get a short comment block
  with the rule in Spanish as the client stated it — invaluable months later.
- PHPDoc on Services' public methods and non-obvious helpers; skip boilerplate getters.

## When closing a work session

Before ending significant work: update project.md (módulos/estado/pendientes), note any
unfinished edges in "Pendientes" — the next session (human or AI) starts from there.
