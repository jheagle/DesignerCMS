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
- **Testable first:** adaptors (and so the database) must be easy to test and fake; see DESI-21.
- **Tests run against MySQL**, in a test database (`designercms_test`, the app's own user), never the app's database.
- **Claude builds, Joshua reviews.** The reviewer agent looks at the slices that change behaviour (1, 2, 3, 5).
- **Branch names** follow the knowledge base: slices are `<type>/DESI-13/<KEY>/<slug>`.

## On hold (2026-11-19)

Blocked by the testable-adaptors epic (DESI-21): the database adaptor builds on it, and tests must be reliable first.
Resume once DESI-21 merges to `main`, with a real merge of `main` into this branch.

## Slices

Database first (Joshua, 2026-11-19): tables are designed in MySQL and models are generated from them. Defining tables
in code (migrations) is a possible later feature, not part of this epic.

- [x] **1. DbConnect clean-up (DESI-14).** (#21) Keyed settings from config, throws on a missing database or user and on
  a failed connect (no password in logs or traces), one PDO per driver, host and database, testing mode only from
  config, `system.php` reads boolean strings; MySQL tests through `UsesTestDatabase`.
- [x] **2. Database as an adaptor (DESI-25).** (#29) PDO behind the adaptor layer, so it can be swapped and faked like the
  others. `DB_CONNECTION` picks the driver: `mysql`, the test database (from slice 1), and `log` (a fake that records
  queries and runs nothing, replacing the old testing flag).
- [x] **3. Prepared statements and transactions (DESI-15).** (#31) `query($sql, $params)` with prepare/execute behind every
  query type; `begin` / `commit` / `rollback` and a `transaction(callable)` helper.
- [ ] **4. Schema to Fields (DESI-16).** Read a table's columns, keys and indexes from `INFORMATION_SCHEMA` into
  `Field` objects. Tests against tables in the test database.
- [ ] **5. Generic Entity (DESI-17).** A subclass declares its table and `Field`s; create, read, update and delete come
  from them through slice 3; `LazyAssignment` replaces the positional constructor. The address-book contact methods
  go, and `createEntity()`'s bugs are fixed. Tests with a small test entity.
- [ ] **6. Trackable and MakeTrackable (DESI-11).** The contract and trait; `core/Tracking/` with a minimal tracking
  entity and its rows as a `DataTransferObject`. The `tracking` table from `feature/tracking-model` (bd062fc, in
  `resources/designercms.sql`) is the one part carried over and stays in that SQL file; the entity's `Field`s are
  checked against the real table with slice 4. Nothing else from that branch.
- [ ] **7. Model generator (DESI-19).** Generation gap: from each table (slice 4), write
  `models/Generated/<Name>Base.php` (extends `Entity`, rebuilt every run, never edited) and create
  `models/<Name>.php` once (extends the base; the developer's own, for custom behaviour such as `MakeTrackable`).

## After the epic

- Delete `feature/tracking-model` (local and on GitHub) once this epic is merged to `main` (Joshua, 2026-11-19).

## Out of scope

- `AjaxDbController`'s raw-SQL endpoint (DESI-10): stays off; the JS/AJAX query protocol comes after the PHP side.
- Migrations (tables defined in code): a possible later feature.
- The MySQL types not built yet (DESI-4) and math/comparison on numbers (DESI-5): slices use the types that exist.
