<?php
// actions.php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ADD / UPDATE GAME ---
    if (isset($_POST['name'])) {
        $igdb_id = !empty($_POST['igdb_id']) ? (int)$_POST['igdb_id'] : null;
        $name = trim(strip_tags($_POST['name']));
        $cover_image_url = filter_var($_POST['cover_image_url'] ?? '', FILTER_SANITIZE_URL);
        $progress = in_array($_POST['progress'], ['Not played', 'To play', 'Playing', 'Played', 'Not finished']) ? $_POST['progress'] : 'Not played';
        $platforms = isset($_POST['platforms']) && is_array($_POST['platforms']) ? json_encode($_POST['platforms']) : null;
        
        // NEW: Capture Developer and Rating
        $developer = trim(strip_tags($_POST['developer'] ?? 'Unknown Developer'));
        $igdb_rating = is_numeric($_POST['igdb_rating']) ? (float)$_POST['igdb_rating'] : null;

        try {
            // NEW: Added developer and igdb_rating to the SQL
            $sql = "INSERT INTO games (igdb_id, name, cover_image_url, platforms, progress, developer, igdb_rating) 
                    VALUES (?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    name=VALUES(name), cover_image_url=VALUES(cover_image_url), platforms=VALUES(platforms), progress=VALUES(progress), developer=VALUES(developer), igdb_rating=VALUES(igdb_rating)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$igdb_id, $name, $cover_image_url, $platforms, $progress, $developer, $igdb_rating]);
            
            $_SESSION['message'] = "Game saved successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Database Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "danger";
        }
        
        header("Location: index.php");
        exit();
    }
    
    // --- DELETE GAME ---
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['message'] = "Game deleted successfully!";
        $_SESSION['msg_type'] = "danger";
        
        header("Location: index.php");
        exit();
    }

    // --- EDIT GAME PROGRESS ---
    if (isset($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $progress = in_array($_POST['progress'], ['Not played', 'To play', 'Playing', 'Played', 'Not finished']) ? $_POST['progress'] : 'Not played';

        try {
            $stmt = $pdo->prepare("UPDATE games SET progress = ? WHERE id = ?");
            $stmt->execute([$progress, $id]);
            
            $_SESSION['message'] = "Game updated!";
            $_SESSION['msg_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Database Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "danger";
        }
        
        header("Location: index.php");
        exit();
    }
}

// Redirect if accessed directly
header("Location: index.php");
exit();
?>