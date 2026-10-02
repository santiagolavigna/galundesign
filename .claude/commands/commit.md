---
description: Git commit del trabajo actual. Push solo si el usuario lo pide en el mensaje.
---

Leé el estado de git (status, diff, log reciente).

Reglas:
- NO actualices git config.
- NO uses --no-verify ni saltees hooks.
- NO incluyas `.env`, `config.local.php`, dumps SQL con datos de cliente, ni secretos.
- Mensaje de commit en español, 1-2 oraciones, el "por qué".
- Por defecto: `git add` de los archivos del trabajo + `git commit`. NO hagas `git push`
  salvo que el usuario lo pida explícito en este mensaje (ej. "y push", "pusheá").
- Si no hay cambios, decilo y no crees un commit vacío.
- Si el hook bloquea el commit, arreglá la causa; no fuerces.

Después: mostrá `git status` y el hash del commit.
Contexto extra: $ARGUMENTS
