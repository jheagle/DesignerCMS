# Epic: the data layer (DESI-13)

Tie up the incomplete database and entity code, then build tracking (an audit log of record changes) as the first
framework feature made with it. DesignerCMS is the tools for building models, not a set of ready-made models, so
tracking is a feature that entities opt into, not a model in `core/Models/`. Each slice is its own small PR into this
branch (`epic/DESI-13/data-layer`). This file is removed before the epic merges to `main`.

## Decisions (Joshua, 2026-11-19)

- **Reuse what is already built.** Classes stay minimal and keep only their purpose: `LazyAssignment` for settings and
  members, `DataTransferObject` for rows, `Field` + the DataTypes for columns, the `*able` contracts with their
  `Make*` traits for opt-in behaviour.
- **Tracking:** a `Trackable` contract and a `MakeTrackable` trait (the `Adaptable` / `MakeAdaptable` pattern). A
  trackable entity records its create, update and delete. The tracking table is declared with `Field`s, like any
  model a user would build.
- **No raw SQL with values pasted in.** Every query with values goes through prepared statements.
- **Tests run against MySQL**, in a test database (`designercms_test`, the app's own user), never the app's database.
- **Claude builds, Joshua reviews.** The reviewer agent looks at the slices that change behaviour (1, 2, 4).
- **Branch names** follow the knowledge base: slices are `<type>/DESI-13/<KEY>/<slug>`.

## Slices

- [ ] **1. DbConnect clean-up (DESI-14).** `instantiateDB()` takes keyed settings (no guessing by type and position);
  fix the constructor's `empty($database)` (an undefined variable) and the `'127.0.0.1'` database-name default (also
  in `config/database.php`); a failed connect throws instead of leaving a half-made object; testing mode comes from
  config only, not from "both ends are localhost"; drop the `global $RESOURCES` fallback. `phpunit.xml` gets the test
  database settings in place of the unused `DB_CONNECTION=memory`. Tests for connecting, settings and failure.
- [ ] **2. Prepared statements and transactions (DESI-15).** `query($sql, $params)` with PDO prepare/execute behind
  every query type; `begin` / `commit` / `rollback` and a `transaction(callable)` helper. Tests against MySQL.
- [ ] **3. Field to DDL (DESI-16).** A column definition from a `Field` (type, length, NOT NULL, UNSIGNED, ZEROFILL,
  AUTO_INCREMENT, default) and `CREATE TABLE` from a list of `Field`s (PRIMARY KEY, UNIQUE, INDEX, FULLTEXT). Tests on
  the generated SQL, and one table really created in the test database.
- [ ] **4. Generic Entity (DESI-17).** A subclass declares its table and `Field`s; create, read, update and delete come
  from them through slice 2; `LazyAssignment` replaces the positional constructor. The address-book contact methods
  go, and `createEntity()`'s bugs are fixed. Tests with a small test entity.
- [ ] **5. Trackable and MakeTrackable (DESI-11).** The contract and trait; `core/Tracking/` with a minimal tracking
  entity and its rows as a `DataTransferObject`. The `tracking` table from `feature/tracking-model` (bd062fc, in
  `resources/designercms.sql`) is the one part carried over: same columns (id, date_time, app_user, action,
  acting_class, class_id) and indexes (`tracking_record`, `tracking_time`), declared as `Field`s. Nothing else from
  that branch; `core/Models/TrackingClass.php` stays in git history.
- [ ] **6. Install tables from Fields (DESI-19).** `install.php` creates the tracking table from its `Field`s (slice 3)
  instead of `resources/designercms.sql`.

## After the epic

- Delete `feature/tracking-model` (local and on GitHub) once this epic is merged to `main` (Joshua, 2026-11-19).

## Out of scope

- `AjaxDbController`'s raw-SQL endpoint (DESI-10): stays off; it gets the query protocol later.
- The MySQL types not built yet (DESI-4) and math/comparison on numbers (DESI-5): slices use the types that exist.
- Migrations beyond creating tables (altering a table that already holds data).
