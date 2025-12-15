<?php
// --- CONFIGURATION ---
// How Reverse Proxy talks to HA 
$ha_url = "http://homeassistant:8123"; 

// PASTE YOUR LONG-LIVED TOKEN BELOW
$token = "YOUR_LONG_LIVED_ACCESS_TOKEN_HERE";

// The standard sensors you always want
$entities = [
    'sensor.circadian_brightness',
    'sensor.circadian_color_temp'
];

// --- HELPER FUNCTION ---
function get_entity($entity_id) {
    global $ha_url, $token;
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$ha_url/api/states/$entity_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // Set headers (This is where the secret token lives safely)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    
    // Execute and close
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Basic error handling
    if ($http_code != 200) {
        return null; 
    }
    
    return json_decode($result, true);
}

// --- MAIN LOGIC ---
$output = [];

// Fetch the default required sensors
foreach ($entities as $id) {
    $data = get_entity($id);
    if ($data) {
        // We only expose the state to keep it clean (and safe)
        $output[$id] = $data['state']; 
    }
}


// --- OUTPUT ---
// Send as JSON so your external app can easily read it
header('Content-Type: application/json');
echo json_encode($output);
?>
