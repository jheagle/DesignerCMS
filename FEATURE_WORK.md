# Epic: testable adaptors (DESI-21)

Testing was a key part of DesignerCMS's design, and the adaptor layer (`Adaptor`, `MakeAdaptable`,
`core/config/adaptors.php`) is how third-party packages come in. Today adaptor state is global and sticks between
tests, the real Logger can't be built under PHPUnit, and every test that needs a stand-in hand-builds a
`GenericClass`. This epic makes adaptors easy to test. It blocks the data-layer epic (DESI-13), whose database adaptor
(DESI-25) builds on it. Each slice is its own small PR into this branch (`epic/DESI-21/testable-adaptors`); this file
is removed before the epic merges to `main`.

## Decisions (Joshua, 2026-11-19)

- **Keep the static calls** (`Logger::info(...)`, `CacheRegistry::get(...)`): they are the easy-to-integrate part,
  and callers don't change. What changes is the state behind them: kept in one place, reset in one place.
- **No container** (Laravel's bindings felt tedious). If one is ever wanted, the adaptor registry is where it grows.
- **Fakes come with every adaptor**, like Laravel's `Log::fake()` / `Http::fake()`.
- **A test log**, so testing doesn't build up in `logs/all.log`.
- **Claude builds, Joshua reviews.** The reviewer agent looks at slices 1 and 3.

## Slices

- [x] **1. Every test starts clean (DESI-22).** (#23) One `resetAll()` that clears every adaptor's cached and custom
  instances, plus the other shared static state the tests touch (`Config`, `Lang`'s loaded translations,
  `CacheRegistry`). The base `TestCase::setUp()` calls it. Done when the suite passes in default, random and reverse
  order (fixes DESI-20), and `phpunit.xml` then runs in random order so it stays that way.
- [x] **2. The real Logger in tests, with a test log (DESI-23).** (#24) Fix the path that leaves a bare class name instead
  of a Monolog instance after a reset. `phpunit.xml` sets `LOG_FILE=logs/test.log`, emptied at the start of each run.
- [x] **3. Fakes for every adaptor (DESI-24).** (#26) `fake()` on `MakeAdaptable` swaps in a recording stand-in and
  returns it; checks such as `Logger::assertLogged('error', 'Unable to connect')` and `assertNothingLogged()`.
  Existing tests move from hand-built `GenericClass` mocks to fakes where it reads better.

## Found along the way

- `Pure::getFunction()` failed as the first use of Pure (DESI-38, #27): exposed once tests ran in random order.

## Out of scope

- A dependency-injection container.
- The database adaptor (DESI-25): in the data-layer epic, after this one.
