# Security policy

## Supported versions

Arcano is released from `main` as `pushinbr/arcano` on Packagist. Security fixes go into the
latest `0.2.x` release; older lines do not get backports.

| Version | Supported |
|---------|-----------|
| 0.2.x (latest tag) | yes |
| < 0.2   | no |

PHP 8.4 and 8.5 are supported, matching the CI matrix.

## Reporting a vulnerability

Report privately through GitHub Security Advisories:
<https://github.com/push-in/arcano/security/advisories/new>

Do not open a public issue, pull request or discussion for a suspected vulnerability.

Include the affected version, the driver (MySQL, Postgres, SQLite or SQL Server), a minimal
reproduction and the impact you observed. SQL injection, identifier quoting and the statement
cache serving one query's SQL for another are all in scope.

We reply in the advisory thread, agree on a fix and a disclosure date with the reporter, and
publish the advisory together with the patched release. Credit is given in the advisory unless
you ask otherwise.

## Scope notes

- Values passed to the query builder are always sent as bound parameters.
- Strings given as column names to `columns()`, `where()` and similar may contain SQL expressions
  (`COUNT(id)`, `a.b AS c`) by design and must not come from untrusted input. `orderBy()` and
  `groupBy()` only take plain column names. For a name that comes from user input, check it
  against an allowlist or quote it with `$driver->identifier()`, which always yields delimited
  identifiers.
- `Fragment` and `Expression` are raw SQL by definition.
