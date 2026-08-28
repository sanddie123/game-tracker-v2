# Game Tracker

A sleek, responsive web application designed to manage and track your video game collection. Built with an iOS-inspired user interface, it features real-time search and data retrieval powered by the IGDB API.

## Features

* **IGDB Integration:** Instantly search and add games with accurate cover art, developer info, and IGDB ratings.
* **Dynamic Dashboard:** Track completion rates, average scores, and top platforms at a glance.
* **Live Filtering:** Sort your library instantly by progress status (Playing, Played, To Play) or search locally by title.
* **Native UI/UX:** Smooth animations, frosted-glass modals, and a responsive grid that works seamlessly on both desktop and mobile.
* **Dark Mode:** Fully integrated light and dark themes with local storage saving.

## Requirements

* PHP 7.4 or higher
* MySQL or MariaDB
* Twitch / IGDB API Credentials (Client ID and Secret)

## Installation

1. Clone this repository to your local local server environment (e.g., XAMPP, Laragon, or Docker).
2. Create a new MySQL database named `game_tracker_v2`.
3. Run the following SQL to generate the required table:
   ```sql
   CREATE TABLE games (
       id INT AUTO_INCREMENT PRIMARY KEY,
       igdb_id INT UNIQUE,
       name VARCHAR(255) NOT NULL,
       cover_image_url VARCHAR(255),
       platforms JSON,
       progress ENUM('Not played', 'To play', 'Playing', 'Played', 'Not finished') DEFAULT 'Not played',
       developer VARCHAR(255),
       igdb_rating DECIMAL(5,2),
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
