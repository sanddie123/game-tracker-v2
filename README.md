# Game Tracker v2

A personal game library and progress tracker built with PHP and MySQL, with game metadata pulled live from the **IGDB (Twitch) API**. Browse your collection in an iOS-inspired dashboard, track what you're playing, and see stats on your library at a glance.

## Features

- **IGDB-powered search** — search the IGDB catalog and add games to your library with cover art, platforms, developer, and rating auto-filled.
- **Progress tracking** — categorize each game as `Not played`, `To play`, `Playing`, `Played`, or `Not finished`.
- **iOS-style dashboard** — sidebar widgets, filters, and a responsive grid with detailed/compact view toggles.
- **Library statistics** — completion rate, average IGDB rating, total games, and top platforms breakdown.
- **Search & filter** — instantly filter your library by progress status or search by name.
- **Light/dark mode** toggle.

## Tech Stack

- PHP (procedural, PDO for MySQL)
- MySQL / MariaDB
- Vanilla JavaScript
- Bootstrap 5 + Font Awesome

## Project Structure

```
├── index.php           # Main dashboard UI (library + statistics views)
├── actions.php         # Handles add / update / delete game requests
├── api_search.php      # Server-side proxy for IGDB search queries
├── igdb.php            # IGDB/Twitch API auth + request helpers
├── db.php              # Database connection (PDO)
├── config.example.php  # Template for local configuration
├── script.js           # Front-end interactivity (modals, filters, search)
└── style.css           # Custom styling on top of Bootstrap
```

## Getting Started

### Prerequisites

- PHP 7.4+ with the PDO MySQL extension
- MySQL or MariaDB
- A [Twitch Developer](https://dev.twitch.tv/console/apps) application (used for IGDB API access)

### Setup

1. **Clone the repo**
   ```bash
   git clone https://github.com/sanddie123/game-tracker-v2.git
   cd game-tracker-v2
   ```

2. **Create the database**
   ```sql
   CREATE DATABASE game_tracker_v2;
   ```
   Create a `games` table matching the fields used in the app (`name`, `progress`, `cover_image_url`, `platforms`, `developer`, `igdb_rating`, `created_at`, etc.).

3. **Configure credentials**
   ```bash
   cp config.example.php config.php
   ```
   Edit `config.php` with your database credentials and Twitch/IGDB `TWITCH_CLIENT_ID` and `TWITCH_CLIENT_SECRET`.

4. **Serve the app**
   ```bash
   php -S localhost:8000
   ```
   Then open `http://localhost:8000` in your browser.

## Usage

- Click the **+** button to search IGDB and add a game to your library.
- Click any game card to view details and update its progress or delete it.
- Use the sidebar to filter by progress status, or the **Statistics** widget to see library insights.

## Notes

- `config.php` is gitignored — never commit real API credentials.
- This is a personal-use project; there's no authentication layer, so it's intended to run locally or behind your own access controls.
