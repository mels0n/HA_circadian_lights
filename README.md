# Home Assistant Circadian Lights

![Circadian Brightness](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fcr_lights.melson.us%2Fcr_light_stats.php&query=%24%5B%27sensor.circadian_brightness%27%5D&label=Brightness&color=blue)
![Color Temp](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fcr_lights.melson.us%2Fcr_light_stats.php&query=%24%5B%27sensor.circadian_color_temp%27%5D&label=Color%20Temp&color=orange)

*The badges show the live values driving the lights in my house right now. Brightness is 0-255; colour temperature is Kelvin.*

## What this is

A how-to for sun-driven lighting in Home Assistant: bulbs shift from warm and dim before dawn, to cool and bright at solar noon, and back to warm through the evening, without overriding any light you have set by hand.

This is a cleaned-up, generic version of the setup running in my home, not a copy of my live configuration. Entity names are generic and anything specific to my house has been removed.

**How it works**

- **One curve, two outputs.** `custom_templates/circadian.jinja` defines the curve once: rise from dawn to solar noon, fall from noon to two hours after dusk, hold overnight. Two template sensors call it with different ranges: colour temperature (2500 K to 9000 K) and brightness (150 to 255).
- **Manual override protection.** `sensor.circadian_target_lights` lists the lights that should follow the curve: lights that are off (and support colour temperature), plus lights that are on and still within 10% of the current targets. A light you turned blue, or dimmed for a film, falls outside that window and is left alone until you restore it.
- **Update lights while they are off.** LIFX accepts `lifx.set_state` with the light powered off, so a bulb always turns on at the right values instead of flashing the old ones first. This is why the automations are LIFX-specific.
- **Recovery.** When a bulb comes back from unavailable (power cut, WiFi drop, wall switch), it is resynced immediately.
- **Voice restore.** "Restore the kitchen lights" in Assist puts overridden lights back on the curve.

| File | Purpose |
|---|---|
| `custom_templates/circadian.jinja` | The curve, as a reusable macro |
| `packages/circadian_lighting.yaml` | Colour temp, brightness, and target-lights sensors |
| `automations/circadian_lighting.yaml` | Pushes the values to eligible lights |
| `automations/light_available.yaml` | Resyncs a bulb the moment it comes back online |
| `automations/restore_lights.yaml` | Assist sentence to undo a manual override |
| `web/cr_light_stats.php` | Optional read-only proxy for public badges |

## Install

**Requirements:** Home Assistant 2024.10 or later, the LIFX integration, and the built-in Sun integration (on by default). No custom components.

1. **Macro.** Copy `custom_templates/circadian.jinja` to `/config/custom_templates/`. Then run the action `homeassistant.reload_custom_templates` (Developer tools > Actions).
2. **Sensors.** Enable packages if you have not already, by adding this to `configuration.yaml`:
   ```yaml
   homeassistant:
     packages: !include_dir_named packages
   ```
   Copy `packages/circadian_lighting.yaml` to `/config/packages/` and restart Home Assistant. Check that `sensor.circadian_color_temp` and `sensor.circadian_brightness` have numbers and `sensor.circadian_target_lights` lists your bulbs.
3. **Automations.** For each file in `automations/`: Settings > Automations > Create automation > three-dot menu > Edit in YAML, paste the file, save.

**Tuning.** The ranges are the numbers passed to `circadian()` in the package. To tune them from the UI instead, create four Number helpers (Settings > Devices & services > Helpers) and pass them in:

```jinja
{{ circadian(states('input_number.circadian_min_kelvin') | int(2500),
             states('input_number.circadian_max_kelvin') | int(9000),
             this.state) }}
```

**Dashboard.** A quick view of what the system is doing:

```yaml
type: entities
title: Circadian Rhythm
entities:
  - entity: sensor.circadian_color_temp
    name: Target Kelvin
  - entity: sensor.circadian_brightness
    name: Target Brightness
  - entity: sensor.circadian_target_lights
    name: Lights following the curve
```

## Publish live values (optional)

`web/cr_light_stats.php` returns the current state of a fixed list of entities as JSON, which is what the badges above read. Put it behind any PHP-capable web server or reverse proxy that can reach Home Assistant. It only ever reads the entity ids hard-coded in `$entities` and only returns their `state`, so a caller cannot use it to read anything else.

Point a shields.io [dynamic JSON badge](https://shields.io/badges/dynamic-json-badge) at it with a query like `$['sensor.circadian_brightness']`.

## Secrets

The proxy needs one secret: a Home Assistant long-lived access token (Profile > Security > Long-lived access tokens). Set it as the `HA_TOKEN` environment variable on the web server (and `HA_URL` if Home Assistant is not at `http://homeassistant:8123`). Never paste the token into the PHP file or commit it. Nothing else in this repository needs credentials.

## License

MIT. See [LICENSE](LICENSE).
