# Home Assistant Circadian Lights

![Circadian Brightness](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fcr_lights.melson.us%2Fcr_light_stats.php&query=%24%5B%27sensor.circadian_brightness%27%5D&label=Brightness&color=blue)
![Color Temp](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fcr_lights.melson.us%2Fcr_light_stats.php&query=%24%5B%27sensor.circadian_color_temp%27%5D&label=Color%20Temp&color=orange)

*Badges above represent the live values of the lights currently in my house.*
*Scales: Brightness (0-255), Color Temp (Kelvin)*

This repository documents the circadian lighting setup for this Home Assistant instance.
It includes a custom circadian calculation engine and automations specifically designed for **LIFX lights**.

## Overview
The goal is to automatically adjust the color temperature (Kelvin) and brightness of lights throughout the day to match the natural circadian rhythm (based on sun position).

### Key Features
- **LIFX Optimized**: Uses `lifx.set_state` with `power: false` to update lights while they are OFF, ensuring they turn on at the correct settings immediately.
- **Manual Override Protection**: The system dynamically scans for lights. If a light is ON but set to a color/brightness significantly different from the circadian target (e.g. Blue, or dimmed low manually), it is **excluded** from updates. This prevents the system from overwriting your manual scenes.
- **Voice Restoration**: Includes a "Restore" command to force a light back to the circadian rhythm if you are done with your manual setting.
- **Solar Logic**: Calculates targets based on Dawn, Noon, and Dusk sensors for a natural progression.
- **External use**: The calculated Kelvin and Brightness values are exposed as `sensor.circadian_color_temp` and `sensor.circadian_brightness` for use in other projects.

## Hardware
- **LIFX Lights**: chosen for their reliable WiFi connectivity and ability to accept state commands while powered off.

## Configuration

### Packages
The core logic is contained in `packages/circadian_logic.yaml`.
It defines:
- **Input Numbers**: To configure Min/Max Kelvin and Brightness ranges.
- **Template Sensors**:
    - `sensor.circadian_color_temp`: Calculated K value.
    - `sensor.circadian_brightness`: Calculated brightness value (0-255).
    - `sensor.circadian_target_lights`: A dynamic list of light entities that need updating (excludes manually overridden lights).

### Automation
The automations are located in `automations/`.
- `circadian_lighting.yaml`:
    - **Triggers**: Changes in calculated Kelvin, Brightness, or the list of Target Lights.
    - **Action**: Sends `lifx.set_state` to the target lights.
- `restore_lights.yaml`:
    - **Trigger**: Voice command "(Fix|Restore) [the] {light_name} [light(s)]".
    - **Action**: Forces the specific light back to the current circadian values.


## Display & Usage

### Home Assistant Dashboard
You can display the current Circadian values on your dashboard using a simple Entities card or Gauge card.

**Entities Card Example:**
```yaml
type: entities
title: Circadian Rhythm
entities:
  - entity: sensor.circadian_color_temp
    name: Target Kelvin
  - entity: sensor.circadian_brightness
    name: Target Brightness
  - entity: sensor.circadian_target_lights
    name: Active Lights Updating
```

**Gauge Card Example:**
```yaml
type: horizontal-stack
cards:
  - type: gauge
    entity: sensor.circadian_color_temp
    min: 1500
    max: 9000
    name: Kelvin
    needle: true
  - type: gauge
    entity: sensor.circadian_brightness
    min: 0
    max: 255
    name: Brightness
    needle: true
```

### External Project Usage (API)
To use these numbers in your other project, you can query the Home Assistant REST API.

**Endpoint:** `GET /api/states/sensor.circadian_color_temp`
**Header:** `Authorization: Bearer YOUR_LONG_LIVED_ACCESS_TOKEN`

**Response:**
```json
{
    "entity_id": "sensor.circadian_color_temp",
    "state": "4500",
    "attributes": {
        "unit_of_measurement": "K",
        "friendly_name": "Circadian Color Temp"
    },
    ...
}
```
You can essentially "poll" this endpoint or use the WebSocket API to subscribe to changes.
