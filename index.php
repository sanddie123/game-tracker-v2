<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM games ORDER BY created_at DESC");
$games = $stmt->fetchAll(PDO::FETCH_OBJ);

// Calculate dynamic category counts & statistics
$progress_counts = [
    'Not played' => 0,
    'To play' => 0,
    'Playing' => 0,
    'Played' => 0,
    'Not finished' => 0
];
$total_rating = 0;
$rating_count = 0;
$platform_counts = [];

foreach ($games as $g) {
    if (isset($progress_counts[$g->progress])) {
        $progress_counts[$g->progress]++;
    }
    if ($g->igdb_rating > 0) {
        $total_rating += $g->igdb_rating;
        $rating_count++;
    }
    $plats = json_decode($g->platforms ?? '[]');
    if (is_array($plats)) {
        foreach ($plats as $p) {
            $platform_counts[$p] = ($platform_counts[$p] ?? 0) + 1;
        }
    }
}

// Final Stats Math
$total_games = count($games);
$total_played = $progress_counts['Played'];
$completion_rate = $total_games > 0 ? round(($total_played / $total_games) * 100) : 0;
$avg_rating = $rating_count > 0 ? round($total_rating / $rating_count, 1) : 0;
arsort($platform_counts);
$top_platforms = array_slice($platform_counts, 0, 5, true);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Game Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="d-flex flex-column flex-md-row min-vh-100">

        <!-- iOS STYLE SIDEBAR -->
        <aside class="ios-sidebar d-none d-md-flex flex-column p-4 flex-shrink-0" style="width: 300px; height: 100vh; position: sticky; top: 0; overflow-y: auto;">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0 tracking-tight" style="color: var(--text-main);">Game Tracker</h3>
                
            </div>

            <!-- Widget Grid -->
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="ios-widget bg-accent-blue text-white h-100 p-3 d-flex flex-column justify-content-between active-widget filter-btn" onclick="filterGames('All', this, true)">
                        <div class="d-flex justify-content-between align-items-start">
                            <i class="fas fa-inbox fs-5 opacity-75"></i>
                            <span class="fs-4 fw-bold lh-1"><?= $total_games ?></span>
                        </div>
                        <span class="fw-semibold mt-3 d-block">All</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="ios-widget ios-widget-standard h-100 p-3 d-flex flex-column justify-content-between filter-btn" onclick="showStatistics(this)">
                        <div class="d-flex justify-content-between align-items-start">
                            <i class="fas fa-chart-simple fs-5 text-accent-green opacity-75"></i>
                        </div>
                        <span class="fw-semibold mt-3 d-block">Statistics</span>
                    </div>
                </div>
            </div>

            <!-- List Section (Progress) -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <h6 class="text-muted fw-bold m-0" style="font-size: 0.85rem;">Progress</h6>
                <i class="fas fa-chevron-down text-muted" style="font-size: 0.75rem;"></i>
            </div>

            <ul class="nav flex-column ios-nav-list mb-auto" id="sidebarFilters">
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex justify-content-between align-items-center filter-btn" onclick="filterGames('Not played', this, false)">
                        <span><i class="far fa-circle text-muted me-2" style="width: 20px; text-align: center;"></i> Not played</span>
                        <span class="text-muted"><?= $progress_counts['Not played'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex justify-content-between align-items-center filter-btn" onclick="filterGames('To play', this, false)">
                        <span><i class="fas fa-list text-accent-blue me-2" style="width: 20px; text-align: center;"></i> To play</span>
                        <span class="text-muted"><?= $progress_counts['To play'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex justify-content-between align-items-center filter-btn" onclick="filterGames('Playing', this, false)">
                        <span><i class="fas fa-adjust text-accent-blue me-2" style="width: 20px; text-align: center;"></i> Playing</span>
                        <span class="text-muted"><?= $progress_counts['Playing'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex justify-content-between align-items-center filter-btn" onclick="filterGames('Played', this, false)">
                        <span><i class="fas fa-check-circle text-accent-blue me-2" style="width: 20px; text-align: center;"></i> Played</span>
                        <span class="text-muted"><?= $progress_counts['Played'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link d-flex justify-content-between align-items-center filter-btn" onclick="filterGames('Not finished', this, false)">
                        <span><i class="fas fa-ban text-muted me-2" style="width: 20px; text-align: center;"></i> Not finished</span>
                        <span class="text-muted"><?= $progress_counts['Not finished'] ?></span>
                    </a>
                </li>
            </ul>

            <!-- Dark Mode Toggle -->
            <hr class="my-4 opacity-10">
            <button class="btn w-100 d-flex justify-content-between align-items-center p-2 rounded-3 text-start" onclick="toggleTheme()" style="background: rgba(142, 142, 147, 0.12); color: var(--text-main);">
                <span><i class="fas fa-moon me-2"></i> Dark Mode</span>
                <div class="form-check form-switch m-0" style="pointer-events: none;">
                    <input class="form-check-input" type="checkbox" id="themeSwitch">
                </div>
            </button>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="ios-main flex-grow-1 p-4 p-md-5 overflow-auto pb-5" style="height: 100vh;">

            <!-- Wrapper for the Grid View -->
            <div id="mainLibraryView">
                <!-- Top Toolbar -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-3">
                    <h2 class="fw-bold m-0 tracking-tight" id="categoryTitle">All</h2>

                    <div class="d-flex gap-3 align-items-center ms-auto">
                        <div class="ios-segmented-control">
                            <button id="btn-detailed" class="active" onclick="setGridView('detailed')"><i class="fas fa-list"></i></button>
                            <button id="btn-compact" onclick="setGridView('compact')"><i class="fas fa-th"></i></button>
                        </div>

                        <button class="btn btn-link text-accent-blue p-0 fs-4" onclick="openAddModal()"><i class="fas fa-plus"></i></button>

                        <div class="ios-search position-relative ms-2 d-none d-sm-block">
                            <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                            <input type="text" id="librarySearchInput" class="form-control" placeholder="Search" style="padding-left: 35px; width: 200px;">
                        </div>
                    </div>
                </div>
                <p class="text-muted fw-semibold mb-4" id="categoryCount"><?= $total_games ?> games</p>

                <!-- Dynamic Game Grid -->
                <div id="gameGrid" class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4 grid-detailed">
                    <?php if (count($games) > 0): ?>
                        <?php foreach ($games as $game): ?>
                            <div class="col game-item" data-progress="<?= htmlspecialchars($game->progress, ENT_QUOTES) ?>">
                                <div class="ios-game-card" onclick="openDetailModal(<?= $game->id ?>, '<?= htmlspecialchars(addslashes($game->name), ENT_QUOTES) ?>', '<?= $game->progress ?>', '<?= htmlspecialchars($game->cover_image_url, ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($game->platforms), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($game->developer ?? 'Unknown Developer'), ENT_QUOTES) ?>', '<?= number_format((float)($game->igdb_rating ?? 0), 1) ?>')">
                                    <div class="position-relative">
                                        <?php if (!empty($game->cover_image_url)): ?>
                                            <img src="<?= htmlspecialchars($game->cover_image_url, ENT_QUOTES) ?>" class="ios-cover" alt="<?= htmlspecialchars($game->name, ENT_QUOTES) ?>">
                                        <?php else: ?>
                                            <div class="ios-cover bg-light d-flex align-items-center justify-content-center border" style="color: #ccc;">
                                                <i class="fas fa-image fs-1"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="compact-badges position-absolute top-0 end-0 p-2 gap-1 flex-column d-none">
                                            <span class="badge bg-accent-blue text-white rounded-pill shadow-sm" title="<?= htmlspecialchars($game->progress, ENT_QUOTES) ?>">
                                                <i class="fas fa-gamepad"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="details-block mt-2">
                                        <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.9rem;" title="<?= htmlspecialchars($game->name, ENT_QUOTES) ?>">
                                            <?= htmlspecialchars($game->name, ENT_QUOTES) ?>
                                        </h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php
                                            $platforms = json_decode($game->platforms ?? '[]');
                                            if (is_array($platforms) && !empty($platforms)) {
                                                foreach (array_slice($platforms, 0, 3) as $platform) {
                                                    echo '<span class="badge bg-accent-blue text-white rounded-pill" style="font-size: 0.6rem;">' . htmlspecialchars($platform, ENT_QUOTES) . '</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-secondary text-white rounded-pill" style="font-size: 0.6rem;">Unknown</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-ghost fs-1 text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted fw-semibold">No games in your library yet.</h5>
                            <p class="text-muted small">Click the + button to search and add games.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wrapper for the Statistics View -->
            <div id="statisticsView" class="d-none">
                <div class="mb-4">
                    <h2 class="fw-bold m-0 tracking-tight" style="color: var(--text-main);">Insights</h2>
                    <p class="text-muted fw-semibold mb-0">Your library at a glance</p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="ios-widget ios-widget-standard p-4 text-center h-100">
                            <i class="fas fa-trophy fs-1 text-accent-blue mb-3"></i>
                            <h2 class="fw-bold m-0 tracking-tight"><?= $completion_rate ?>%</h2>
                            <span class="text-muted small fw-semibold">Completion Rate</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="ios-widget ios-widget-standard p-4 text-center h-100">
                            <i class="fas fa-star fs-1 text-accent-red mb-3"></i>
                            <h2 class="fw-bold m-0 tracking-tight"><?= $avg_rating ?></h2>
                            <span class="text-muted small fw-semibold">Avg. IGDB Rating</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="ios-widget ios-widget-standard p-4 text-center h-100">
                            <i class="fas fa-gamepad fs-1 text-accent-green mb-3"></i>
                            <h2 class="fw-bold m-0 tracking-tight"><?= $total_games ?></h2>
                            <span class="text-muted small fw-semibold">Total Games</span>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold tracking-tight mb-3 mt-4" style="color: var(--text-main);">Top Platforms</h5>
                <div class="ios-widget ios-widget-standard p-0 overflow-hidden">
                    <ul class="list-group list-group-flush ios-list-group">
                        <?php if (!empty($top_platforms)): ?>
                            <?php foreach ($top_platforms as $plat => $count): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-3 px-4">
                                    <span class="fw-semibold" style="color: var(--text-main);"><?= htmlspecialchars($plat) ?></span>
                                    <span class="badge bg-accent-blue rounded-pill fs-6"><?= $count ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item bg-transparent text-muted text-center py-4 border-0">No platform data yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>

        <!-- MOBILE NAV -->
        <nav class="ios-bottom-nav d-md-none d-flex justify-content-around py-2">
            <a href="#" class="active"><i class="fas fa-inbox"></i><br><span>Library</span></a>
            <a href="#"><i class="fas fa-search"></i><br><span>Search</span></a>
            <a href="#" onclick="toggleTheme()"><i class="fas fa-moon"></i><br><span>Theme</span></a>
        </nav>
    </div>

    <!-- IGDB Search Modal -->
    <div class="modal fade" id="addGameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg" style="border-radius: var(--radius-widget); border: none; background-color: var(--bg-main);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold tracking-tight">Add Game</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(var(--close-invert, 0));"></button>
                </div>
                <div class="modal-body">
                    <div class="ios-search position-relative mb-3">
                        <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                        <input type="text" id="igdbSearchInput" class="form-control form-control-lg text-body" placeholder="Search IGDB..." style="padding-left: 45px; border-radius: 12px; background-color: var(--bg-sidebar); border: none;">
                    </div>
                    <div id="searchSpinner" class="text-center d-none my-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="searchResults" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 overflow-auto" style="max-height: 50vh; overflow-x: hidden;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Submission -->
    <form id="addGameForm" action="actions.php" method="POST" class="d-none">
        <input type="hidden" name="igdb_id" id="form_igdb_id">
        <input type="hidden" name="name" id="form_name">
        <input type="hidden" name="cover_image_url" id="form_cover">
        <div id="form_platforms_container"></div>
        <input type="hidden" name="progress" value="Not played">
        <input type="hidden" name="developer" id="form_developer">
        <input type="hidden" name="igdb_rating" id="form_rating">
    </form>

    <!-- iOS Detail & Action Modal -->
    <div class="modal fade" id="detailGameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg ios-detail-modal">
                <div class="modal-header-blurred position-relative" id="detail_header_bg">
                    <div class="d-flex justify-content-between p-3 position-relative z-2">
                        <button type="button" class="btn btn-sm btn-light rounded-circle shadow-sm fw-bold text-muted d-flex align-items-center justify-content-center" data-bs-dismiss="modal" style="width: 32px; height: 32px; filter: invert(var(--close-invert, 0));">&times;</button>
                    </div>
                    <div class="text-center position-relative z-2 mt-1">
                        <button class="btn rounded-pill text-white px-5 shadow-sm fw-semibold" onclick="toggleEditActions()" style="background-color: #2bc1c4; border: none; font-size: 0.95rem;">
                            <i class="fas fa-arrow-circle-right me-1"></i> Actions...
                        </button>
                    </div>
                </div>
                <div class="modal-body text-center pt-0 position-relative pb-4" style="background-color: var(--bg-main);">
                    <img src="" id="detail_game_cover" class="ios-cover shadow-lg mx-auto" style="width: 130px; margin-top: -65px; border: 4px solid var(--bg-main); position: relative; z-index: 3;" alt="Cover">
                    <h4 class="fw-bold tracking-tight mt-3 mb-1" id="detail_game_title" style="color: var(--text-main);">Game Title</h4>
                    <p class="text-muted small fw-medium mb-1" id="detail_game_devs">Developer / Publisher</p>
                    <p class="text-muted small mb-3" id="detail_game_platforms" style="font-size: 0.8rem;">Platforms...</p>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
                        <div class="text-accent-red fw-bold">
                            <i class="fas fa-heart"></i> <span id="detail_game_rating">85.2</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">IGDB</span>
                    </div>

                    <div id="detail_actions_panel" class="d-none mt-4 text-start border-top pt-4">
                        <form action="actions.php" method="POST">
                            <input type="hidden" name="edit_id" id="detail_game_id">
                            <label class="form-label fw-semibold text-muted small">Update Progress</label>
                            <select name="progress" id="detail_progress" class="form-select form-select-lg mb-3 ios-input">
                                <option value="Not played">Not played</option>
                                <option value="To play">To play</option>
                                <option value="Playing">Playing</option>
                                <option value="Played">Played</option>
                                <option value="Not finished">Not finished</option>
                            </select>
                            <button type="submit" class="btn bg-accent-blue text-white w-100 rounded-pill fw-bold py-2 mb-2 shadow-sm">Save Changes</button>
                        </form>
                        <form action="actions.php" method="POST" class="mt-2">
                            <input type="hidden" name="delete_id" id="delete_game_id2">
                            <button type="submit" class="btn w-100 rounded-pill fw-bold py-2 text-danger" style="background-color: rgba(255, 59, 48, 0.1); border: none;">
                                Delete Game
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js?v=5"></script>
</body>

</html>