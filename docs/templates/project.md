# [NOMBRE DEL PROYECTO]

> Fuente de verdad del proyecto. Toda sesión de trabajo (humana o IA) empieza leyendo este archivo
> y lo actualiza al terminar si cambió algo. NUNCA poner credenciales acá.

## Qué es

- **Cliente**:
- **Descripción**: [qué hace el sistema/sitio en 2-3 líneas]
- **URL producción**:
- **Hosting**: [Hostinger / VPS / ...]

## Stack

- **Backend**: [Laravel 12 + Livewire 4 / PHP 8.2 vanilla + PDO / estático]
- **DB**: [MySQL `nombre_db` / ninguna]
- **Frontend**: [Tailwind 4 + Vite / CSS propio / ...]

## Cómo correrlo local

```
[XAMPP: http://localhost/CARPETA/  |  Laravel: php artisan serve + npm run dev]
DB local: [nombre], importar [sql/schema.sql | migrations + seeders]
Credenciales/config: [config.local.php desde config.example.php | .env desde .env.example]
```

## Módulos

| Módulo | Estado | Notas |
|--------|--------|-------|
| [Ej.: Catálogo público] | hecho / en curso / pendiente | |

## Esquema de datos (resumen)

- `tabla_1` — [qué guarda, relaciones clave]
- `tabla_2` — ...

## Reglas de negocio importantes

- [Ej.: "los precios mayoristas aplican desde 3 unidades"]
- [Decisiones tomadas y por qué]

## Credenciales

- Local: [dónde está el archivo de config — no los valores]
- Producción: [dónde las guarda el usuario — panel, gestor de contraseñas]

## Deploys

- Último deploy: [fecha] — [qué se subió]
- Procedimiento: ver `DEPLOY.md`

## Pendientes / deuda técnica

- [ ] [pendiente 1]
