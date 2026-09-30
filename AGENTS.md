# AGENTS.md

Contributor guide for this repository.

## What it is

A how-to for sun-driven circadian lighting in Home Assistant. The deliverables are configuration files a reader copies into their own instance, plus an optional PHP proxy. There is no build step and no application code.

## Layout

- `custom_templates/`: Jinja macros, loaded from `/config/custom_templates/`
- `packages/`: Home Assistant packages (template sensors)
- `automations/`: one automation per file, in the format the automation editor's YAML mode accepts
- `web/`: the read-only stats proxy
- `docs/adr/`: architecture decision records

## Checks

- YAML must parse: `python -c "import yaml,sys; [yaml.safe_load(open(f)) for f in sys.argv[1:]]" packages/*.yaml automations/*.yaml`
- PHP must lint: `php -l web/cr_light_stats.php`
- Test template changes in Developer tools > Template against a real instance before committing.

## Conventions

- Keep examples generic: no real entity names from a specific house, no IP addresses, no tokens.
- Use `color_temp_kelvin`, never `color_temp` (mireds), in light actions.
- Prefer native triggers and conditions over templates where one exists.
- Explain non-obvious steps with a comment next to the step.
