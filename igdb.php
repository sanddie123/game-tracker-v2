<?php
// igdb.php - Handles Twitch OAuth and IGDB API requests
require_once __DIR__ . '/config.php';

// 1. Get or Refresh the OAuth Token
function get_igdb_token() {
    $token_file = __DIR__ . '/igdb_token.json';
    
    // Check if we have a valid cached token to avoid spamming the Twitch Auth server
    if (file_exists($token_file)) {
        $token_data = json_decode(file_get_contents($token_file), true);
        if ($token_data['expires_at'] > time()) {
            return $token_data['access_token'];
        }
    }
    
    // Request a new token
    $ch = curl_init('https://id.twitch.tv/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'grant_type' => 'client_credentials'
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        $data['expires_at'] = time() + $data['expires_in'] - 300; 
        file_put_contents($token_file, json_encode($data));
        return $data['access_token'];
    }
    
    return false;
}

// 2. Search IGDB for Games
function search_igdb_games($query) {
    $token = get_igdb_token();
    if (!$token) return ['error' => 'Authentication failed. Check your Client ID and Secret.'];

    // IGDB uses 'Apicalypse' syntax.
    $apicalypse = 'search "' . addslashes($query) . '"; ';
    // NEW: Added rating and involved_companies fields
    $apicalypse .= 'fields name, cover.image_id, platforms.name, first_release_date, rating, involved_companies.company.name, involved_companies.developer; ';
    $apicalypse .= 'where version_parent = null; '; 
    $apicalypse .= 'limit 12;';

    $ch = curl_init('https://api.igdb.com/v4/games');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $apicalypse);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Client-ID: ' . TWITCH_CLIENT_ID,
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    return ['error' => 'API Request failed with code: ' . $httpCode];
}
?>