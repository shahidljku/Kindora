


<?php

require_once 'includes/header.php';
// Get search parameters
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$difficulty = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : '';
$season = isset($_GET['season']) ? trim($_GET['season']) : '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$results = array();
$totalResults = 0;
$totalPages = 1;

// BUILD QUERY - EXCLUDE Continents and 7 wonders
$where = array("is_active = 1", "type NOT IN ('Continent')");
$params = array();

if (!empty($q)) {
    $where[] = "(name LIKE :q OR description LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

if (!empty($difficulty)) {
    $where[] = "difficulty_level = :difficulty";
    $params[':difficulty'] = strtolower($difficulty);
}

if (!empty($season)) {
    $where[] = "best_season = :season";
    $params[':season'] = $season;
}

$whereClause = implode(" AND ", $where);

// COUNT TOTAL RESULTS
try {
    $countQuery = "SELECT COUNT(*) as cnt FROM destinations WHERE " . $whereClause;
    $countResult = KindoraDatabase::query($countQuery, $params);
    
    if ($countResult && isset($countResult[0]['cnt'])) {
        $totalResults = intval($countResult[0]['cnt']);
    }
    
    $totalPages = $totalResults > 0 ? ceil($totalResults / $perPage) : 1;
    
    // FETCH RESULTS
    if ($totalResults > 0) {
        $searchQuery = "SELECT * FROM destinations WHERE " . $whereClause . " ORDER BY name ASC LIMIT " . $offset . ", " . $perPage;
        $results = KindoraDatabase::query($searchQuery, $params) ?: array();
    }
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
}

// HANDLE AJAX AUTOCOMPLETE
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $suggestions = array();
    if (!empty($q)) {
        try {
            $ajaxQuery = "SELECT destination_id, name FROM destinations WHERE (name LIKE :q OR description LIKE :q) AND is_active = 1 AND type NOT IN ('Continent', '7 wonders') LIMIT 10";
            $results_ajax = KindoraDatabase::query($ajaxQuery, array(':q' => '%' . $q . '%')) ?: array();
            
            foreach ($results_ajax as $item) {
                $suggestions[] = array(
                    'id' => intval($item['destination_id']),
                    'name' => htmlspecialchars($item['name'])
                );
            }
        } catch (Exception $e) {
            error_log("AJAX error: " . $e->getMessage());
        }
    }
    
    echo json_encode(['success' => true, 'suggestions' => $suggestions]);
    exit;
}

// NORMAL PAGE OUTPUT (NON-AJAX)
$pageTitle = 'Search Destinations - Kindora'; // if header.php uses it

?>

<!-- PAGE-SPECIFIC STYLES (allowed inside body; simpler than fighting header.php) -->
<style>
    * { box-sizing: border-box; }
    body { background: #f8f9fa; color: #333; }

    .search-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 40px 20px;
        text-align: center;
    }
    
    .search-header h1 { font-size: 2em; margin-bottom: 15px; font-weight: 700; }
    
    .search-box-wrapper {
        max-width: 600px;
        margin: 0 auto;
        position: relative;
    }
    
    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .search-box input {
        flex: 1;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        font-size: 1em;
    }
    
    .search-box button {
        padding: 12px 30px;
        background: #ff6b35;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .search-box button:hover { background: #ff5722; }
    
    .autocomplete-suggestions {
        position: absolute;
        top: 50px;
        left: 0;
        right: 70px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 100;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .autocomplete-suggestions.active { display: block; }
    
    .suggestion-item {
        padding: 12px 20px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        color: #333;
        background: white;
        font-size: 0.95em;
        transition: all 0.2s ease;
    }
    
    .suggestion-item:hover {
        background: #f0f0f0;
        padding-left: 25px;
    }
    
    .suggestion-item:last-child { border-bottom: none; }
    
    .search-container {
        max-width: 1400px;
        margin: 40px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
    }
    
    .sidebar {
        background: white;
        padding: 20px;
        border-radius: 8px;
        height: fit-content;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 20px;
    }
    
    .filter-section {
        margin-bottom: 22px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .filter-section:last-child { border-bottom: none; }
    
    .filter-section h4 {
        color: #1e3c72;
        margin-bottom: 12px;
        font-weight: 600;
        font-size: 0.95em;
    }
    
    .filter-option {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 5px 0;
    }
    
    .filter-option input[type="radio"] {
        margin-right: 10px;
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
    
    .filter-option label {
        cursor: pointer;
        flex: 1;
        font-size: 0.9em;
        color: #333;
        user-select: none;
    }
    
    .applied-filters {
        background: #fffbf0;
        border: 2px solid #ffe0cc;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .applied-filters h5 {
        color: #1e3c72;
        font-size: 0.85em;
        margin-bottom: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .filter-tag {
        display: inline-block;
        background: #ff6b35;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 0.85em;
        font-weight: 500;
    }
    
    .filter-tag .close {
        margin-left: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .filter-tag .close:hover { text-decoration: underline; }
    
    .clear-all-btn {
        width: 100%;
        padding: 12px;
        background: #1e3c72;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        margin-top: 20px;
        transition: all 0.3s ease;
        font-size: 0.9em;
        display: block;
        text-align: center;
        text-decoration: none;
    }
    
    .clear-all-btn:hover { background: #2a5298; }
    
    .search-main-content {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .results-info {
        margin-bottom: 25px;
        color: #666;
        font-size: 0.95em;
    }
    
    /* RESPONSIVE GRID – SAME AS YOUR ORIGINAL WORKING VERSION */
    .search-results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 35px;
    }
    
    .search-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid #eee;
        display: flex;
        flex-direction: column;
    }
    
    .search-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        border-color: #ff6b35;
    }
    
    .search-card-image {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.5);
        font-size: 3em;
        overflow: hidden;
    }
    
    .search-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .search-card:hover .search-card-image img { transform: scale(1.1); }
    
    .search-card-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .search-card-title {
        font-size: 1.15em;
        color: #1e3c72;
        margin-bottom: 10px;
        font-weight: 700;
        line-height: 1.3;
    }
    
    .search-card-text {
        font-size: 0.9em;
        color: #666;
        line-height: 1.5;
        margin-bottom: 15px;
        flex: 1;
    }
    
    .search-card-button {
        display: inline-block;
        padding: 10px 18px;
        background: #1e3c72;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9em;
        transition: all 0.3s ease;
        text-align: center;
        border: 2px solid #1e3c72;
    }
    
    .search-card-button:hover {
        background: white;
        color: #1e3c72;
    }
    
    .no-results {
        text-align: center;
        padding: 80px 40px;
        color: #666;
    }
    
    .no-results h2 {
        color: #1e3c72;
        margin-bottom: 15px;
        font-size: 1.8em;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #eee;
    }
    
    .pagination a, .pagination span {
        padding: 10px 14px;
        border: 1px solid #ddd;
        border-radius: 6px;
        text-decoration: none;
        color: #1e3c72;
        cursor: pointer;
        font-size: 0.9em;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .pagination a:hover {
        background: #1e3c72;
        color: white;
        border-color: #1e3c72;
    }
    
    .pagination .current {
        background: #1e3c72;
        color: white;
        border-color: #1e3c72;
    }
    
    @media (max-width: 768px) {
        .search-container { grid-template-columns: 1fr; }
        .sidebar { position: static; }
        .search-results-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
    }
</style>
<br><br><br>
<!-- Search Header -->
<div class="search-header">
    <h1>🌍 Search Destinations</h1>
    
    <div class="search-box-wrapper">
        <form method="GET" action="search.php" id="mainSearchForm" class="search-box">
            <input type="text" id="searchInput" name="q" placeholder="Search destinations..." value="<?php echo htmlspecialchars($q); ?>" autocomplete="off">
            <button type="submit">🔍 Search</button>
        </form>
        <div id="suggestions" class="autocomplete-suggestions"></div>
    </div>
</div>

<!-- Main Content -->
<div class="search-container">
    <!-- Sidebar Filters -->
    <aside class="sidebar">
        <h3 style="color: #1e3c72; margin-bottom: 20px; font-weight: 700; font-size: 1.1em;">🔍 Filters</h3>
        
        <!-- Applied Filters -->
        <?php 
        $hasFilters = !empty($q) || !empty($difficulty) || !empty($season);
        if ($hasFilters): 
        ?>
        <div class="applied-filters">
            <h5>Active Filters</h5>
            <?php if (!empty($q)): ?>
                <span class="filter-tag">
                    🔎 <?php echo htmlspecialchars($q); ?>
                    <span class="close" onclick="removeFilter('q')">×</span>
                </span>
            <?php endif; ?>
            <?php if (!empty($difficulty)): ?>
                <span class="filter-tag">
                    🏔️ <?php echo ucfirst($difficulty); ?>
                    <span class="close" onclick="removeFilter('difficulty')">×</span>
                </span>
            <?php endif; ?>
            <?php if (!empty($season)): ?>
                <span class="filter-tag">
                    🌤️ <?php echo htmlspecialchars($season); ?>
                    <span class="close" onclick="removeFilter('season')">×</span>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form method="GET" action="search.php" id="filterForm">
            <!-- MUST KEEP search query when filtering -->
            <input type="hidden" name="q" id="filterQ" value="<?php echo htmlspecialchars($q); ?>">

            <!-- Difficulty Filter -->
            <div class="filter-section">
                <h4>Difficulty Level</h4>
                <div class="filter-option">
                    <input type="radio" name="difficulty" id="diff-all" value="" <?php echo empty($difficulty) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="diff-all">All Levels</label>
                </div>
                <?php foreach (array('easy', 'moderate', 'challenging', 'extreme') as $level): ?>
                <div class="filter-option">
                    <input type="radio" name="difficulty" id="diff-<?php echo $level; ?>" value="<?php echo $level; ?>" <?php echo $difficulty === $level ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="diff-<?php echo $level; ?>"><?php echo ucfirst($level); ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Season Filter -->
            <div class="filter-section">
                <h4>Best Season</h4>
                <div class="filter-option">
                    <input type="radio" name="season" id="season-all" value="" <?php echo empty($season) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="season-all">Any Time</label>
                </div>
                <?php foreach (array('Spring', 'Summer', 'Fall', 'Winter', 'All Year') as $s): ?>
                <div class="filter-option">
                    <input type="radio" name="season" id="season-<?php echo strtolower(str_replace(' ', '', $s)); ?>" value="<?php echo $s; ?>" <?php echo $season === $s ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label for="season-<?php echo strtolower(str_replace(' ', '', $s)); ?>"><?php echo $s; ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Clear All -->
            <a href="search.php" class="clear-all-btn">🗑️ Clear All Filters</a>
        </form>
    </aside>

    <!-- Results -->
    <main class="search-main-content">
        <div class="results-info">
            Found <strong><?php echo $totalResults; ?></strong> destinations
            <?php if (!empty($q)): ?>
                for "<strong><?php echo htmlspecialchars($q); ?></strong>"
            <?php endif; ?>
        </div>

        <?php if (count($results) > 0): ?>
            <div class="search-results-grid">
                <?php foreach ($results as $dest): ?>
                    <div class="search-card">
                        <div class="search-card-image">
                            <?php if (!empty($dest['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($dest['image_url']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" loading="lazy">
                            <?php else: ?>
                                📍
                            <?php endif; ?>
                        </div>
                        <div class="search-card-body">
                            <h3 class="search-card-title"><?php echo htmlspecialchars($dest['name']); ?></h3>
                            <p class="search-card-text"><?php echo substr(htmlspecialchars($dest['description'] ?? ''), 0, 100); ?>...</p>
                            <a href="destination.php?id=<?php echo intval($dest['destination_id']); ?>" class="search-card-button">View Details →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="search.php?q=<?php echo urlencode($q); ?>&difficulty=<?php echo urlencode($difficulty); ?>&season=<?php echo urlencode($season); ?>&page=1">« First</a>
                        <a href="search.php?q=<?php echo urlencode($q); ?>&difficulty=<?php echo urlencode($difficulty); ?>&season=<?php echo urlencode($season); ?>&page=<?php echo $page - 1; ?>">‹ Prev</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="search.php?q=<?php echo urlencode($q); ?>&difficulty=<?php echo urlencode($difficulty); ?>&season=<?php echo urlencode($season); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="search.php?q=<?php echo urlencode($q); ?>&difficulty=<?php echo urlencode($difficulty); ?>&season=<?php echo urlencode($season); ?>&page=<?php echo $page + 1; ?>">Next ›</a>
                        <a href="search.php?q=<?php echo urlencode($q); ?>&difficulty=<?php echo urlencode($difficulty); ?>&season=<?php echo urlencode($season); ?>&page=<?php echo $totalPages; ?>">Last »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <h2>🔍 No Results Found</h2>
                <p>Try different keywords or adjust filters</p>
                <a href="search.php" style="color: #ff6b35; text-decoration: none; margin-top: 15px; display: inline-block;">← Clear All</a>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    const filterQ = document.getElementById('filterQ');
    let timeout;

    // Update hidden input when search changes
    searchInput.addEventListener('input', function() {
        filterQ.value = this.value;
        
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsBox.classList.remove('active');
            return;
        }

        timeout = setTimeout(() => {
            fetch('search.php?ajax=1&q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    if (data.suggestions && data.suggestions.length > 0) {
                        suggestionsBox.innerHTML = data.suggestions.map(item =>
                            '<div class="suggestion-item" onclick="selectSuggestion(\'' + item.name.replace(/'/g, "\\'") + '\')">' + item.name + '</div>'
                        ).join('');
                        suggestionsBox.classList.add('active');
                    } else {
                        suggestionsBox.classList.remove('active');
                    }
                })
                .catch(err => console.error('Error:', err));
        }, 300);
    });

    function selectSuggestion(name) {
        searchInput.value = name;
        filterQ.value = name;
        suggestionsBox.classList.remove('active');
        document.getElementById('mainSearchForm').submit();
    }

    function removeFilter(type) {
        const url = new URL(window.location);
        url.searchParams.delete(type);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    document.addEventListener('click', function(e) {
        if (e.target !== searchInput) {
            suggestionsBox.classList.remove('active');
        }
    });
</script>

<?php require_once __DIR__ . '\includes/footer.php'; ?>
