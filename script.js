// ==========================================
// 1. DARK MODE TOGGLE
// ==========================================
const html = document.documentElement;
const themeSwitch = document.getElementById("themeSwitch");

const savedTheme = localStorage.getItem("theme") || "light";
html.setAttribute("data-theme", savedTheme);
if (themeSwitch && savedTheme === "dark") themeSwitch.checked = true;

window.toggleTheme = function () {
  const currentTheme = html.getAttribute("data-theme");
  const newTheme = currentTheme === "light" ? "dark" : "light";
  html.setAttribute("data-theme", newTheme);
  localStorage.setItem("theme", newTheme);
  if (themeSwitch) themeSwitch.checked = newTheme === "dark";
};

// ==========================================
// 2. GRID VIEW TOGGLE
// ==========================================
window.setGridView = function (view) {
  const gameGrid = document.getElementById("gameGrid");
  const btnDetailed = document.getElementById("btn-detailed");
  const btnCompact = document.getElementById("btn-compact");

  if (!gameGrid) return;

  if (view === "compact") {
    gameGrid.classList.remove("grid-detailed");
    gameGrid.classList.add("grid-compact");
  } else {
    gameGrid.classList.remove("grid-compact");
    gameGrid.classList.add("grid-detailed");
  }

  if (btnDetailed && btnCompact) {
    btnDetailed.classList.toggle("active", view === "detailed");
    btnCompact.classList.toggle("active", view === "compact");
  }

  localStorage.setItem("grid-view", view);
};

const savedView = localStorage.getItem("grid-view") || "detailed";
setGridView(savedView);

// ==========================================
// 3. IGDB SEARCH & ADD LOGIC
// ==========================================
const addModalEl = document.getElementById("addGameModal");
const addGameModal = addModalEl ? new bootstrap.Modal(addModalEl) : null;
const searchInput = document.getElementById("igdbSearchInput");
const searchResults = document.getElementById("searchResults");
const searchSpinner = document.getElementById("searchSpinner");

let searchTimeout = null;

window.openAddModal = function () {
  if (!addGameModal) return;
  if (searchInput) searchInput.value = "";
  if (searchResults) searchResults.innerHTML = "";
  addGameModal.show();
  setTimeout(() => {
    if (searchInput) searchInput.focus();
  }, 500);
};

if (searchInput) {
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    if (query.length < 3) {
      searchResults.innerHTML = "";
      return;
    }
    searchTimeout = setTimeout(() => {
      performSearch(query);
    }, 500);
  });
}

async function performSearch(query) {
  if (!searchResults || !searchSpinner) return;
  searchResults.innerHTML = "";
  searchSpinner.classList.remove("d-none");

  try {
    const response = await fetch(
      `api_search.php?q=${encodeURIComponent(query)}`,
    );
    const games = await response.json();
    searchSpinner.classList.add("d-none");

    if (games.error) {
      searchResults.innerHTML = `<div class="col-12 text-center text-danger">${games.error}</div>`;
      return;
    }
    if (games.length === 0) {
      searchResults.innerHTML = `<div class="col-12 text-center text-muted">No games found for "${query}"</div>`;
      return;
    }

    games.forEach((game) => {
      const coverUrl =
        game.cover && game.cover.image_id
          ? `https://images.igdb.com/igdb/image/upload/t_cover_big/${game.cover.image_id}.jpg`
          : "";
      const platforms = game.platforms ? game.platforms.map((p) => p.name) : [];
      const platformsJson = JSON.stringify(platforms).replace(/"/g, "&quot;");
      const year = game.first_release_date
        ? new Date(game.first_release_date * 1000).getFullYear()
        : "TBA";

      let developer = "Unknown Developer";
      if (game.involved_companies) {
        const dev = game.involved_companies.find((c) => c.developer);
        if (dev && dev.company) developer = dev.company.name;
      }
      let rating = game.rating ? game.rating.toFixed(1) : 0;

      const card = document.createElement("div");
      card.className = "col";
      card.innerHTML = `
                <div class="ios-game-card h-100 bg-body-tertiary rounded-3 p-2 shadow-sm text-center" 
                     onclick="submitGame(${game.id}, '${game.name.replace(/'/g, "\\'")}', '${coverUrl}', '${platformsJson}', '${developer.replace(/'/g, "\\'")}', '${rating}')">
                    ${coverUrl ? `<img src="${coverUrl}" class="ios-cover mb-2" style="border-radius: 8px;">` : `<div class="ios-cover mb-2 bg-secondary d-flex align-items-center justify-content-center text-white" style="border-radius: 8px;"><i class="fas fa-gamepad fs-3"></i></div>`}
                    <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.85rem;" title="${game.name}">${game.name}</h6>
                    <small class="text-muted">${year}</small>
                </div>
            `;
      searchResults.appendChild(card);
    });
  } catch (err) {
    searchSpinner.classList.add("d-none");
    searchResults.innerHTML = `<div class="col-12 text-center text-danger">Network error occurred.</div>`;
  }
}

window.submitGame = function (
  igdbId,
  name,
  coverUrl,
  platformsStr,
  developer,
  rating,
) {
  document.getElementById("form_igdb_id").value = igdbId;
  document.getElementById("form_name").value = name;
  document.getElementById("form_cover").value = coverUrl;
  document.getElementById("form_developer").value = developer;
  document.getElementById("form_rating").value = rating;

  const platformsContainer = document.getElementById(
    "form_platforms_container",
  );
  platformsContainer.innerHTML = "";
  try {
    const platforms = JSON.parse(platformsStr);
    platforms.forEach((platform) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "platforms[]";
      input.value = platform;
      platformsContainer.appendChild(input);
    });
  } catch (e) {
    console.error("Failed to parse platforms");
  }
  document.getElementById("addGameForm").submit();
};

// ==========================================
// 4. DETAIL & EDIT GAME LOGIC
// ==========================================
const detailModalEl = document.getElementById("detailGameModal");
const detailGameModal = detailModalEl
  ? new bootstrap.Modal(detailModalEl)
  : null;
const actionsPanel = document.getElementById("detail_actions_panel");

window.openDetailModal = function (
  id,
  name,
  progress,
  coverUrl,
  platformsStr,
  developer,
  rating,
) {
  if (!detailGameModal) return;
  if (actionsPanel) actionsPanel.classList.add("d-none");

  document.getElementById("detail_game_id").value = id;
  document.getElementById("delete_game_id2").value = id;
  document.getElementById("detail_game_title").innerText = name;
  document.getElementById("detail_progress").value = progress;
  document.getElementById("detail_game_devs").innerText = developer;
  document.getElementById("detail_game_rating").innerText =
    rating > 0 ? rating : "--";

  try {
    const platforms = platformsStr ? JSON.parse(platformsStr) : [];
    document.getElementById("detail_game_platforms").innerText =
      platforms.length > 0 ? platforms.join(", ") : "Unknown Platform";
  } catch (e) {
    document.getElementById("detail_game_platforms").innerText =
      "Unknown Platform";
  }

  const coverEl = document.getElementById("detail_game_cover");
  const bgEl = document.getElementById("detail_header_bg");

  if (coverUrl) {
    coverEl.src = coverUrl;
    coverEl.style.display = "block";
    bgEl.style.backgroundImage = `url('${coverUrl}')`;
    bgEl.style.backgroundColor = "transparent";
  } else {
    coverEl.style.display = "none";
    bgEl.style.backgroundImage = "none";
    bgEl.style.backgroundColor = "var(--bg-sidebar)";
  }
  detailGameModal.show();
};

window.toggleEditActions = function () {
  if (actionsPanel) actionsPanel.classList.toggle("d-none");
};

// ==========================================
// 5. SIDEBAR & SEARCH FILTER LOGIC
// ==========================================
const librarySearch = document.getElementById("librarySearchInput");

window.filterGames = function (category, element, isWidget) {
  document.getElementById("mainLibraryView").classList.remove("d-none");
  const statsView = document.getElementById("statisticsView");
  if (statsView) statsView.classList.add("d-none");

  const titleEl = document.getElementById("categoryTitle");
  if (titleEl) titleEl.innerText = category;

  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active-widget", "active-filter-link");
  });

  const allWidget = document.querySelector(".ios-widget.filter-btn");
  if (element) {
    if (isWidget) {
      element.classList.add("active-widget");
      if (allWidget && element !== allWidget) {
        allWidget.classList.remove("bg-accent-blue", "text-white");
        allWidget.classList.add("ios-widget-standard");
      }
    } else {
      element.classList.add("active-filter-link");
      if (allWidget) {
        allWidget.classList.remove("bg-accent-blue", "text-white");
        allWidget.classList.add("ios-widget-standard");
      }
    }
  }
  applyFilters();
};

window.showStatistics = function (element) {
  document.getElementById("mainLibraryView").classList.add("d-none");
  document.getElementById("statisticsView").classList.remove("d-none");

  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active-widget", "active-filter-link");
  });

  const allWidget = document.querySelector(".ios-widget.filter-btn");
  if (allWidget) {
    allWidget.classList.remove("bg-accent-blue", "text-white");
    allWidget.classList.add("ios-widget-standard");
  }

  if (element) {
    element.classList.add("active-widget");
  }
};

if (librarySearch) {
  librarySearch.addEventListener("input", applyFilters);
}

function applyFilters() {
  const searchTerm = librarySearch
    ? librarySearch.value.toLowerCase().trim()
    : "";
  const activeCategory = document.getElementById("categoryTitle")
    ? document.getElementById("categoryTitle").innerText
    : "All";
  const games = document.querySelectorAll(".game-item");
  let visibleCount = 0;

  games.forEach((game) => {
    const title = game.querySelector("h6").innerText.toLowerCase();
    const matchesSearch = title.includes(searchTerm);
    const matchesCategory =
      activeCategory === "All" || game.dataset.progress === activeCategory;

    if (matchesSearch && matchesCategory) {
      game.style.display = "";
      visibleCount++;
    } else {
      game.style.display = "none";
    }
  });

  const countEl = document.getElementById("categoryCount");
  if (countEl) countEl.innerText = `${visibleCount} games`;
}
