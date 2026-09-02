# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

OSneo — an internal Laravel 10 business-management app (HR/attendance/leave/payroll, vehicles, a document management system, and Lexoffice/Creditreform accounting integrations) for a German company. UI language is German-first (`config/app.php` locale defaults to `de`), code and identifiers are English, but domain terms leak into the code in German (`Ablage`, `Papierkorb`, `Rechnung`).

## Stack

Laravel 10 / PHP 8.1+ · Livewire 3 (server-rendered, no SPA) · Wire Elements Pro (modals) · rappasoft/laravel-livewire-tables (forked) · Bootstrap 4 + Laravel Mix/webpack · MySQL · Pest 2.

Several dependencies come from private/forked repos (`satis.spatie.be`, `wire-elements-pro.composer.sh`, and `amshehzad/*` forks of `laravel-form-components`, `laravel-livewire-tables`, `laravel-translatable`). `composer install` needs valid credentials in `auth.json`.

## Commands

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # DatabaseSeeder
php artisan storage:link

npm run watch                       # dev asset build (mix)
npm run prod                        # production build

php artisan test                    # or ./vendor/bin/pest
php artisan test --filter="can toggle attendance"
./vendor/bin/pest tests/Unit/Models/LeaveTest.php
./vendor/bin/pest --parallel        # paratest is installed
php artisan test --testsuite=Unit   # suites: Unit, Feature

php artisan permission:update       # re-seed permissions after editing config/permissions.php
php artisan websockets:serve        # beyondcode/laravel-websockets (notifications)
php artisan queue:work
```

Tests run against a **real MySQL database** named `osneo_testing` (`phpunit.xml`; the sqlite in-memory config is commented out). Create it before running the suite.

### Migrations are versioned by folder

`database/migrations/` holds the original schema; release-specific migrations go in versioned subfolders (`1.0.10/`, `1.2/`, `updates/`). New migrations are written into `updates/`, and at release time that folder is renamed to the version number and deployed with `php artisan migrate --path=/database/migrations/X.X`. `.github/pull_request_template.md` holds the full deploy checklist — keep it in mind when a change needs a migration or a version bump.

Settings migrations (spatie/laravel-settings) live separately in `database/settings/`.

## Architecture

### Routing: Livewire components *are* the pages

`routes/web.php` mounts Livewire classes directly as routes (`Route::get('users', UserDatatable::class)`). Most pages have no controller. Nearly all routes sit inside one group with `['auth', 'vehicle.selected', 'log.activity']`:

- `vehicle.selected` (`EnsureVehicleIsSelected`) forces the user to pick a vehicle before using the app — routes that must bypass it call `->withoutMiddleware('vehicle.selected')`.
- `log.activity` writes a `page_visit` activity entry; the Livewire equivalent is `App\Livewire\Traits\LogsActivity`, whose `bootLogsActivity()` logs whenever the component boots.

`App\Livewire` is organised by role, not by domain: `Datatables/` (full-page list screens), `Forms/` and `Modals/` (both extend `WireElements\Pro\Components\Modal`), `Widgets/`, `Calendars/`, `Layout/`. Datatable rows open forms via `MenuItem::wireElement(['type' => 'modal', 'component' => 'forms.tag-form', ...])`; forms close with `$this->close(andDispatch: ['refresh', 'flashNotification' => [...]])`, and datatables listen for `refresh`.

Every datatable uses `App\LaravelLivewireTables\Traits\WithCustomizations::useDefaults()` for shared table config and authorizes in `boot()` (`$this->authorize('viewAny', Model::class)`).

### Modification approval workflow (core domain concept)

Employees without "without approval" permissions cannot change attendance/leave records directly — they create a pending `Modification` instead. Models opting in implement `App\Contracts\Modifiable` (`Attendance`, `Leave`):

- `createModification($changes, ModificationType)` stores the before-state in the schemaless `source` column and the requested state in `data`.
- `ModificationDatatable::approve()` stamps `approved_at`/`approved_by`, then dispatches to `applyCreation()` / `applyChanges()` / `applyDeletion()` / `applyRestoration()` based on `ModificationType`, and notifies the requester (`ChangesApproved`).
- `getFormattedChanges()` renders the diff shown in `ModificationDetails`.

Declining simply deletes the `Modification`. When adding a modifiable model, implement all four contract methods plus `applyCreation`/`applyRestoration` and the matching permission triplet in `config/permissions.php`.

### Activity logging

Two independent streams both land in `activity_log`:

1. **Model CRUD** — `App\Traits\LogsActivity` wraps spatie/laravel-activitylog with project defaults (log name `crud`, dirty-only). Models control what is recorded via `protected array $loggable` / `protected array $nonLoggable`.
2. **Page visits** — the `log.activity` middleware and `App\Livewire\Traits\LogsActivity` (log name `page_visit`).

Rendering is separate: a model implements `App\Contracts\ActivityFormatter` and `use`s a matching trait from `App\ActivityFormatters\` (e.g. `UserActivityFormatter`) that supplies the icon, human-readable diff, and attribute-name/value formatting. New logged models need both the contract and a formatter trait.

### Permissions and policies

spatie/laravel-permission. `config/permissions.php` is the **source of truth** for the permission list (grouped by module); `PermissionSeeder` syncs it and `php artisan permission:update` re-runs the seeder. There is a policy per model in `app/Policies/`, and `AuthServiceProvider` registers a `Gate::after` that grants everything to super admins.

Many permissions come in `own`/`any`/`without approval` triplets (e.g. `edit own attendance`, `edit any attendance`, `edit attendance without approval`) — the third variant is what bypasses the Modification workflow. Model query scopes named `relevant()` apply the own/any split at the query level.

### Settings vs. preferences

- **Global**: `App\Settings\GeneralSettings` (spatie/laravel-settings), edited via `Settings\GeneralSettingsController`. `holidays` from here powers the `Carbon::isHoliday()` macro.
- **Per user**: `App\Traits\HasPreferences` on `User`, backed by the `preferences` table. Keys must exist in `config/preferences.php` or `setPreference()` throws.

Domain lookup values live in `config/constants.php` and the `constants` table.

### Global helpers and macros

`app/Helpers/general.php` and `app/Helpers/assets.php` are composer-autoloaded, so their functions are global: `user()`, `carbon()`, `formatMins()`, `formatHrs()`, `durationInputToMinutes()`, `minutesToDurationInput()`, `dateToCarbon()`. Working time is stored and computed in **minutes**; `HH:MM` strings are only an input/display format.

`MacroServiceProvider` registers macros used throughout: `Carbon::isHoliday()`, `isOffDay()`, `isWorkingDay()`, `setDefaultTz()`/`toAppTz()`, `date()`, and query-builder macros `past()`, `future()`, `whereMonthOfYear()`.

### Time zones

`config('app.timezone')` comes from the `TIMEZONE` env var (production: `Europe/Berlin`; `phpunit.xml` pins `Asia/Karachi`), and MySQL has its own `MYSQL_TIMEZONE`. Any datetime pulled out of the schemaless `source`/`data` columns or from raw input must be normalised with `->setDefaultTz()` before comparison or display — the existing `Attendance`/`Leave` code does this consistently. Date formats come from `config/dates.php`, not hardcoded strings.

### Document management (DMS)

`Document` uses spatie/laravel-medialibrary (+ Pro) with a custom `App\MediaLibrary\PathGenerator` laying files out as `media/{table}/{model_id}/{media_id}/`. Documents move between three virtual directories held as static props on the model: `$inboxDir` (`Inbox`), `$sortedDir` (`Ablage`), `$trashDir` (`Papierkorb`). `DocumentSorterService` (behind `DocumentSorterFacade`) auto-files documents that arrive from Lexoffice, and `DocumentObserver` handles subscriber assignment and trash-path bookkeeping on delete/restore.

### External integrations

`app/Lexoffice/` and `app/Creditreform/` each follow the same shape: a low-level `*Api` class (HTTP/cURL, bound as a singleton-ish binding in `AppServiceProvider`), a higher-level `*Service` with domain operations, and facades in `Facades/` (aliased as `LexofficeApi`, `Lexoffice`, `CreditreformApi`). Incoming Lexoffice events hit `POST /webhook/lexoffice` → `WebhookController` → `LexofficeEventHandler`; subscriptions are managed with `lexoffice:subscribe-events` / `lexoffice:unsubscribe-events`.

Both APIs have test fakes (`tests/Fakes/`) registered globally in `tests/Pest.php`, with recorded responses in `tests/Fixtures/`.

### Scheduled work

`app/Console/Kernel.php` schedules the nightly chain that keeps attendance data consistent: `user:update-status` (00:01) → `attendance:checkout` (00:05, auto-closes open check-ins) → `attendance:create-summary` (00:10) → `chart:load` (00:30). Other commands (`payroll:create`, `leave:auto-create`, `invoice:sync`, `invoice:export-to-creditreform`, `lexoffice:sort-vouchers`) are run manually or from jobs.

## Testing conventions

`tests/Pest.php` applies to both suites and globally fakes `Bus`, `Http`, `Notification`, S3/public storage, `LexofficeApi`, `CreditreformApi`, and **freezes time** (`testTime()->freeze()`) before each test — advance it explicitly with `testTime()->addHour()` rather than sleeping.

`tests/Helpers.php` provides global test helpers: `login()`, `loginWithPermissions([...])` (seeds permissions on demand), `seedPermissions()`, `seedDocumentTypes()`, `createHolidays()`, `setModelValues()`, and `fixture('Lexoffice/foo.json')`.

`uses(RefreshDatabase::class)` is declared **per test file**, not globally. Livewire components are tested with `Livewire::test(...)` plus `christophrumpel/missing-livewire-assertions`; form-request tests use `worksome/request-factories` (`tests/RequestFactories/`).

## Conventions

- Models group members under `Relations` / `Methods` / `Attributes` / `Scopes` banner comments — follow that layout when editing them.
- Models use `protected $guarded = ['id']`, not `$fillable`.
- Schemaless JSON columns (spatie/laravel-schemaless-attributes) are used heavily (`Modification::$source/$data`, `User::$config`, `Document::$properties`, `Invoice::$lexoffice_payload`); query them through the package's `modelScope()`, not raw JSON paths.
- Every user-facing string goes through `__()`; add both `en` and `de` entries (`resources/lang/`). The PR checklist has a translation item.
- Style is the StyleCI `laravel` preset with `no_unused_imports` disabled (`.styleci.yml`); 4-space indent, LF (`.editorconfig`). No local linter is wired up.
