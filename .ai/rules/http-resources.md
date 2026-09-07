---
paths:
  - 'app/Http/Resources/**'
---

# Http Resources

## Keep Inertia resource props unwrapped
Resources passed to Inertia use the global JsonResource::withoutWrapping() setting. Keep single resources and non-paginated collections unwrapped so their payloads match the TypeScript contracts; paginated resources retain Laravel's standard data/links/meta structure.
