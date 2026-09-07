---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Use focused Action classes for business use cases
Centralize meaningful business logic in focused Action classes under `app/Actions`, keeping controllers responsible for request handling, delegation, and responses. Each Action represents one specific use case (for example `CreateProject`, `ResolveIncident`, or `AddIncidentNote`) and exposes exactly one public operation named `handle`; use private methods to organize internal logic.
Do not create Actions for trivial `find`, `findOrFail`, or equivalent model queries that can remain in controllers, and do not create generic Actions that combine responsibilities. When logic must be shared between Actions, prefer an appropriate Trait or global helper.
