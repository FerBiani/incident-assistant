---
paths:
  - 'resources/**'
---

# Resources

## Follow the incident-management interface conventions
Build the Inertia/Vue/Tailwind interface as a simple, clean, neutral administrative UI. Use `slate-50` for the app background, `white` surfaces, `slate-900` primary text, `slate-500`/`slate-600` secondary text, `slate-200` borders, `blue-600` primary actions, `red-600` destructive actions, emerald success states, and `rounded-lg` by default. Avoid decorative gradients, glass effects, excessive shadows, unnecessary animation, and visual complexity.
Use light badges with darker text. Map incident status colors as open/blue, investigating/amber, resolved/emerald; map severity as low/slate, medium/blue, high/orange, critical/red. Keep the configured sans font; use approximately `text-2xl font-semibold` for page titles, `text-lg font-semibold` for section titles, `text-sm`/`text-base` for content, `text-sm text-slate-500` for secondary information, and `text-sm font-medium` for labels. Use monospace for technical output.
Use a consistent layout with primary navigation (prefer a simple desktop sidebar) and main content ordered as title/description, primary actions, then content. Keep section spacing consistent, forms reasonably narrow, and data-heavy tables, incident details, and logs wider when useful. Keep layouts usable on small screens while prioritizing desktop; add sophisticated mobile interactions only when required.
Compose Inertia pages from page-level behavior and reusable Vue components. Extract components for repeated visual patterns, meaningful behavior, or clearly improved readability, never for speculative future reuse. Forms follow label → control → adjacent validation feedback; backend validation is authoritative, and primary, secondary, and destructive actions must be visually distinct.
Separate logs, stack traces, code, and similar technical content from normal content using a dark monospace container, horizontal scrolling for long lines, and preserved significant whitespace. Provide simple, consistent loading, empty, validation-error, operation-failure, and operation-success feedback when applicable. Prioritize clarity, consistency, readability, and reuse; add no UI or design-system dependency unless a concrete feature requires it.
