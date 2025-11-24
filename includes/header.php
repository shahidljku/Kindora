<?php
require_once __DIR__ . '/../config.php';

session_start();

// Load user data
$userData = null;
if (isset($_SESSION['user_id'])) {
    try {
        $result = KindoraDatabase::query("SELECT * FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
        $userData = $result[0] ?? null;
    } catch (Exception $e) {
        error_log("USER ERROR: " . $e->getMessage());
    }
}

// Fetch Continents
$continents = KindoraDatabase::query("
    SELECT name, image_url 
    FROM destinations 
    WHERE type = 'Continent'
    ORDER BY name ASC
");
?>

<link href="/Kindora/assets/css/styles.css" rel="stylesheet" />
<link href="/Kindora/assets/css/all.min.css" rel="stylesheet" />


<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="/Kindora/index.php" class="nav-logo">
            <i class="fas fa-globe-americas"></i>
            <span>Kindora</span>
        </a>

        <div class="nav-menu" id="nav-menu">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link" id="inspireBtn">
                    <i class="fas fa-compass"></i>
                    Be Inspired
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="dropdown-content" id="inspireDropdown">
                    <div class="dropdown-grid">
                        <?php foreach ($continents as $continent): ?>
                            <a href="/Kindora/pages/continents/<?php echo strtolower(str_replace(' ', '-', htmlspecialchars($continent['name']))); ?>.php" class="dropdown-item">
                                <img src="<?php echo htmlspecialchars($continent['image_url']); ?>" alt="">
                                <span><?php echo htmlspecialchars($continent['name']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <a href="/Kindora/pages/explore.php" class="nav-link"><i class="fas fa-map-marker-alt"></i> Places to Go</a>
            <a href="/Kindora/pages/home.php" class="nav-link"><i class="fas fa-list-ul"></i> Things to Do</a>
            <a href="/Kindora/booking.php" class="nav-link"><i class="fas fa-calendar-check"></i> Plan Your Trip</a>

            <?php if ($userData): ?>
                <a href="/Kindora/pages/mytrips.php" class="nav-link"><i class="fas fa-suitcase"></i> My Trips</a>
                <a href="/Kindora/pages/profile.php" class="nav-link"><i class="fas fa-user"></i> <?php echo htmlspecialchars(substr($userData['full_name'], 0, 15)); ?></a>
                <a href="/Kindora/pages/logout.php" class="nav-link cta-nav"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
               <a href="/Kindora/login.php?return=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="nav-link cta-nav"><i class="fas fa-user"></i> Login</a>

            <?php endif; ?>
        </div>

        <div class="hamburger" id="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>
</nav>
