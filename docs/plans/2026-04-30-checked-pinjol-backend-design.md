# Checked Pinjol Backend Design

## Goal
Build an initial native PHP backend scaffold for API-first development with a clean, reusable, and maintainable structure.

## Decisions
- Native PHP as the main backend runtime.
- MySQL/MariaDB as the default SQL database.
- JWT-oriented auth structure, but only as placeholders for now.
- API-first structure so the backend can connect cleanly to a future Vite + React frontend.

## Structure Summary
- `public/` as the web entrypoint.
- `src/Core/` for reusable foundation code.
- `src/Modules/` for domain-specific modules.
- `config/` for application configuration.
- `routes/` for route registration.
- `database/` for migrations, seeders, and SQL bootstrap.
- `storage/` for runtime logs and cache.
- `docs/` for architecture and implementation notes.

## Workflow Summary
1. Define or update a route in `routes/api.php`.
2. Implement controller logic in a module.
3. Put business rules inside services.
4. Put persistence logic inside repositories.
5. Use config and env values instead of hardcoding behavior.
6. Keep shared infrastructure inside `src/Core`.
