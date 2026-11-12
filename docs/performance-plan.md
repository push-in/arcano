# Arcano performance plan

Target: every scenario in `benchmarks/bench.php` at least 5x faster than Cycle
ORM 2.18.1 (`../compare.sh`). The PDO column is the floor.

## Done
- Unit of work iteration is linear (TupleStorage rescanned holes: quadratic).
- Heap lookups without exceptions.
- Per-role context in EntityFactory, per-class proxy plan, bound field setters.
- No-op destructors removed (Node, State, Heap, Select, loaders, parser nodes).

## Read path (select_all, find_pk, eager, small_queries)
Budget for 5x: about 0.25 µs of ORM work per row above hand-written PDO and
about 3 µs per query. Cycle spends ~3 µs per row and ~10-25 µs per query.

1. Lean heap: store a compact snapshot (role, data) per entity and build the
   `Node` only when the unit of work or a relation needs it (`Heap::get`).
2. Lazy relation references: the proxy prototype keeps relation properties
   unset; `__get` creates the reference from the snapshot on first access
   instead of `RelationMap::init` creating one per relation per row.
3. Generated hydrator per role: clone a prototype, assign typed fields with
   inlined casts (int, bool, float, datetime) in one scoped closure.
4. Query plan cache: SQL and column positions compiled once per query shape
   (role, where columns and operators, order, limit/offset, loads); rows
   fetched with FETCH_NUM and mapped by position.
5. Fast `findByPK` / `findOne(scope)` for roles without scope, inheritance or
   eager relations.
6. Native Arcano query API (named arguments, Nimbus style) on the fast path;
   the Cycle-compatible `Select` stays for everything else.

## Write path (insert, update)
- Cheaper change detection: compare against the snapshot, skip State for
  untouched entities.
- Prepared statement per (table, column set), no query object per row.
- Batch inserts where the driver can return keys (SQLite/Postgres RETURNING).
