# Checked Pinjol Backend Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Create a reusable native PHP backend scaffold with API routing, configuration, database bootstrap, and auth placeholders.

**Architecture:** The backend uses a front-controller entrypoint in `public/index.php`, a small reusable core in `src/Core`, and feature modules in `src/Modules`. Configuration is env-driven and database access goes through a thin PDO wrapper so the codebase stays lightweight and maintainable.

**Tech Stack:** PHP 8.1+, Composer autoloading, PDO for MySQL/MariaDB, native routing/bootstrap structure.

---

### Task 1: Create project foundation

**Files:**
- Create: `checked-pinjol-backend/composer.json`
- Create: `checked-pinjol-backend/.env.example`
- Create: `checked-pinjol-backend/.gitignore`

**Step 1: Write the project manifest**
Create `composer.json` with PHP version requirement, PSR-4 autoload for `App\\`, and basic scripts for serving and smoke testing.

**Step 2: Add environment template**
Create `.env.example` with app, database, auth, and CORS defaults.

**Step 3: Add ignore rules**
Create `.gitignore` for env files, dependencies, and runtime files.

### Task 2: Build bootstrap and core runtime

**Files:**
- Create: `checked-pinjol-backend/public/index.php`
- Create: `checked-pinjol-backend/bootstrap/app.php`
- Create: `checked-pinjol-backend/src/Bootstrap/ApplicationFactory.php`
- Create: `checked-pinjol-backend/src/Core/Application.php`
- Create: `checked-pinjol-backend/src/Core/Container.php`
- Create: `checked-pinjol-backend/src/Core/Routing/Router.php`
- Create: `checked-pinjol-backend/src/Core/Http/Request.php`
- Create: `checked-pinjol-backend/src/Core/Http/Response.php`

**Step 1: Add front controller**
Wire a request lifecycle from `public/index.php` to the application object.

**Step 2: Add application factory**
Create the application, load env/config, register services, and load routes.

**Step 3: Add core request/response/routing classes**
Implement the minimum runtime needed for API request handling.

### Task 3: Add reusable infrastructure

**Files:**
- Create: `checked-pinjol-backend/src/Core/Config/ConfigRepository.php`
- Create: `checked-pinjol-backend/src/Core/Database/ConnectionFactory.php`
- Create: `checked-pinjol-backend/src/Core/Database/DatabaseManager.php`
- Create: `checked-pinjol-backend/src/Core/Middleware/CorsMiddleware.php`
- Create: `checked-pinjol-backend/src/Core/Exceptions/HttpException.php`
- Create: `checked-pinjol-backend/src/Support/helpers.php`

**Step 1: Add env/config access helpers**
Support config loading without framework dependencies.

**Step 2: Add database factory and manager**
Keep DB access centralized and reusable.

**Step 3: Add middleware and exceptions**
Support API-friendly cross-origin behavior and consistent JSON errors.

### Task 4: Add initial modules and routes

**Files:**
- Create: `checked-pinjol-backend/routes/api.php`
- Create: `checked-pinjol-backend/src/Modules/Health/Controllers/HealthController.php`
- Create: `checked-pinjol-backend/src/Modules/Auth/Controllers/AuthController.php`
- Create: `checked-pinjol-backend/src/Modules/Auth/Services/AuthService.php`

**Step 1: Add health endpoint**
Return a predictable JSON response for smoke testing.

**Step 2: Add auth placeholders**
Return not-implemented JSON messages so route contracts are visible before JWT logic exists.

### Task 5: Add database and docs scaffolding

**Files:**
- Create: `checked-pinjol-backend/database/schema/init.sql`
- Create: `checked-pinjol-backend/database/migrations/.gitkeep`
- Create: `checked-pinjol-backend/database/seeders/.gitkeep`
- Create: `checked-pinjol-backend/README.md`

**Step 1: Add SQL bootstrap**
Provide a minimal SQL starter for local database creation.

**Step 2: Write README**
Explain architecture, setup, workflow, and maintainability conventions.
