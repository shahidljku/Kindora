<?php
/**
 * KINDORA NAVBAR COMPONENT (UPDATED)
 * Navigation bar with all new page links
 * 
 * Add this as includes/navbar_updated.php
 * Or update your existing includes/navbar.php with these new links
 */
?>

<nav class="navbar" style="
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
">
    <!-- Logo Section -->
    <div class="navbar-brand" style="font-size: 1.5em; font-weight: 700;">
        🌍 Kindora
    </div>

    <!-- Navigation Links -->
    <div class="navbar-menu" style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
        
        <!-- Home -->
        <a href="index.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            🏠 Home
        </a>

        <!-- NEW: Search Destinations -->
        <a href="search.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            🔍 Search
        </a>

        <!-- NEW: Packages -->
        <a href="packages.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            🎒 Packages
        </a>

        <!-- NEW: Offers -->
        <a href="offers.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            🎁 Offers
        </a>

        <!-- NEW: Reviews -->
        <a href="reviews.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            ⭐ Reviews
        </a>

        <!-- Existing Links -->
        <a href="contact.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
           onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
            📧 Contact
        </a>

        <!-- Auth Section -->
        <div class="navbar-auth" style="border-left: 1px solid rgba(255,255,255,0.3); padding-left: 30px; display: flex; gap: 15px;">
            <?php if (isUserLoggedIn()): ?>
                <!-- Logged In User -->
                <span style="color: #ff6b35; font-weight: 600;">
                    👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                </span>
                <a href="my-bookings.php" style="color: white; text-decoration: none; font-weight: 500; transition: all 0.3s ease;" 
                   onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
                    🎫 My Bookings
                </a>
                <a href="logout.php" style="
                    color: white; 
                    text-decoration: none; 
                    font-weight: 600; 
                    background: #ff6b35; 
                    padding: 8px 16px; 
                    border-radius: 5px;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#ff5722'" onmouseout="this.style.background='#ff6b35'">
                    🚪 Logout
                </a>
            <?php else: ?>
                <!-- Not Logged In -->
                <a href="login.php" style="
                    color: white; 
                    text-decoration: none; 
                    font-weight: 600; 
                    transition: all 0.3s ease;
                " onmouseover="this.style.color='#ff6b35'" onmouseout="this.style.color='white'">
                    🔐 Login
                </a>
                <a href="register.php" style="
                    color: white; 
                    text-decoration: none; 
                    font-weight: 600; 
                    background: #ff6b35; 
                    padding: 8px 16px; 
                    border-radius: 5px;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#ff5722'" onmouseout="this.style.background='#ff6b35'">
                    ✍️ Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- CSS for responsive mobile menu (optional) -->
<style>
    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            gap: 20px !important;
        }
        
        .navbar-menu {
            flex-direction: column;
            width: 100%;
            gap: 10px !important;
            text-align: center;
        }
        
        .navbar-auth {
            border-left: none !important;
            border-top: 1px solid rgba(255,255,255,0.3) !important;
            padding-left: 0 !important;
            padding-top: 15px !important;
            justify-content: center;
            width: 100%;
        }
    }
</style>