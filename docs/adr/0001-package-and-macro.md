# ADR-0001: Ship as a package plus a shared Jinja macro

Status: accepted (2026-09-30)

## Context

The colour temperature and brightness sensors follow the same sun-driven curve with different ranges. The first version of this repository duplicated the curve in both templates, and declared tuning helpers that the templates never read, so the published tuning had no effect.

## Decision

Define the curve once in `custom_templates/circadian.jinja` and call it from both sensors with their ranges as arguments. Ship the sensors as a Home Assistant package, because the target-lights sensor needs an `attributes:` block, which the UI template helper does not offer.

## Alternatives considered

- **UI template helpers only.** Fully UI-editable, but cannot express the target-lights attribute, and would still duplicate the curve.
- **A blueprint.** Blueprints cover automations and scripts, not template sensors, so the core of this setup would still live elsewhere.
- **`input_number` helpers declared in the package.** Without `initial:` they start at their minimum on first load (a max-Kelvin of 1500); with `initial:` they reset on every restart. Documented instead as an optional UI-created helper.

## Consequences

Installing takes three steps (macro, package, automations) instead of one, and changing the curve means reloading custom templates. In return the curve has a single definition and every tunable number is visible where it is used.
