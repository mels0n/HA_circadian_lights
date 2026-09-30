<?php
// Read-only proxy that publishes a fixed allow-list of Home Assistant
// entity states as JSON, for shields.io badges and other public pages.
//
// The token never reaches the browser, and callers cannot choose which
// entities are read: only the ids in $entities are ever fetched, and only
// their `state` is returned (no attributes).
//
// Configuration comes from the web server's environment, never this file:
//   HA_URL    base URL of Home Assistant as the proxy sees it
//             (default http://homeassistant:8123)
//   HA_TOKEN  a long-lived access token (Profile > Security in Home Assistant)

$ha_url = getenv('HA_URL') ?: 'http://homeassistant:8123';
$token  = getenv('HA_TOKEN');

$entities = [
    'sensor.circadian_brightness',
    'sensor.circadian_color_temp',
];

header('Content-Type: application/json');
header('Cache-Control: public, max-age=60');

if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'HA_TOKEN is not set']);
    exit;
}

function get_state($ha_url, $token, $entity_id) {
    $ch = curl_init("$ha_url/api/states/$entity_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        'Content-Type: application/json',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || $body === false) {
        return null;
    }
    $data = json_decode($body, true);
    return $data['state'] ?? null;
}

$output = [];
foreach ($entities as $id) {
    $state = get_state($ha_url, $token, $id);
    if ($state !== null) {
        $output[$id] = $state;
    }
}

echo json_encode($output);
