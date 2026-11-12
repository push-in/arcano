# Arcano performance plan

Target: every scenario in `benchmarks/bench.php` at least 5x faster than Cycle
ORM 2.18.1 (`../compare.sh`). The PDO column is the floor.

## Done
- Unit of work iteration is linear (TupleStorage rescanned holes: quadratic).
- Heap lookups without exceptions.
- Per-role context in EntityFactory, per-class proxy plan, bound field setters.
- No-op destructors removed (Node, State, Heap, Select, loaders, parser nodes).
- Deferred relation references: unloaded relations get their reference on
  first read (`Node::getDeferredReference`, proxy `__arcano_node`/`__arcano_read`).
  Note: sqlite suite asserts 43986 vs 43877 upstream; still all green, check why.

- `findByPK` fast path: SQL compiled once per repository (pristine select,
  no scope/inheritance/eager/composite), row named with array_combine.
- Proxies cloned from a prepared prototype per class and relation map.
- Generated field hydrators per entity class (strict_types like before).
- Benchmarks take the fastest of N runs (tars has other agents running).

## Done in round 4
- Row makers: root-only selects go rows → entities without the parser
  (compiled typecast + generated clone/assign hydrator + lean heap attach).
- Query shapes: simple where/orderBy/limit/offset calls are recorded, the
  builder is unset until something else reads it (`Select::__get`); fetchAll
  runs a statement cached per shape. Repository selects share their loader
  until used (copy on write).
- Cached UPDATE and INSERT statements per table/column set (`StatementPlans`);
  insert queries split compile (`run`) from execution (`runStatement`).
- Memoized `State::getChanges`, strict compare first, syncHeap reindexes only
  when an indexed value changed.
- Direct updates: managed entities whose relations were never read/assigned
  are updated without the pool (`UnitOfWork::runDirect`).
- Compiled data makers in `EntityFactory::make` for column-only managed data;
  has-many POSTLOAD loads without the parser (`Select::fetchEager`).
- Fixed two Cycle bugs: `Heap::eraseIndexes` leaked the key between indexes;
  a failed cached PDO statement was reused (SQLite API misuse).

## Status (fastest run, ms; Cycle / Eloquent / Arcano / PDO)
`compare.sh` now also prints overhead ratios: (ORM − PDO) / (Arcano − PDO).
- insert 332/310/153/12 → overhead 2.3x / 2.1x
- select_all 6.2/2.1/1.9/0.8 → 5.2x / 1.35x
- find_pk 21.3/41.7/4.4/1.7 → 7.1x / 14.5x
- eager 41/31.6/24.6/4.8 → 1.8x / 1.4x
- update 43.2/62.3/8.7/1.8 → 6.0x / 8.7x
- small 50.1/60.7/14.6/5.4 → 4.8x / 6.0x

Next: direct insert path for new graphs (new roots + new has-many children,
no masters/embedded), lean heap (no Node per row until needed), then eager
and select_all against Eloquent.

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

## Eloquent too
David wants Arcano 5x faster than Eloquent as well (`../bench-eloquent`,
Eloquent 12.72; `../compare.sh` prints both). Target per scenario =
min(Cycle, Eloquent) / 5. Exception: select_all, where Eloquent (2.4 ms) / 5
is below the PDO floor (0.8 ms); there the goal is the floor itself (~3x).
