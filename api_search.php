<?php
// api_search.php - Bridges the frontend AJAX calls to the IGDB backend
require_once 'igdb.php';

// Ensure the user actually searched for something
if (isset($_GET['q'])) {
    $query = trim($_GET['q']);
    
    // Only search if the query is at least 3 characters long to save API calls
    if (strlen($query) > 2) {
        $results = search_igdb_games($query);
        
        // Return the results as standard JSON for your Javascript to read
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}

// Return an empty array if no valid query was provided
header('Content-Type: application/json');
echo json_encode([]);
?>