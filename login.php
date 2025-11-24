<?php
require_once 'config.php';
 


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get return URL from parameter or default to index.php
// At top, after your require_once/config/session:
$return_url = isset($_GET['return']) ? urldecode($_GET['return']) : '/kindora/index.php';




// If already logged in, redirect
if (isUserLoggedIn()) {
    header('Location: ' . $return_url);
    exit();
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate
    if (empty($email) || empty($password)) {
        $error = '❌ Please fill in all fields';
    } else {
        // Verify login using config function
        $user = verifyUserLogin($email, $password);
         
        if ($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'] ?? $user['name'] ?? 'User';
    $_SESSION['role'] = $user['role'];


    error_log("DEBUG: user role is '" . ($user['role'] ?? 'undefined') . "'");

    if (isset($user['role']) && strtolower(trim($user['role'])) === 'admin') {
        header('Location: /kindora/adminkindora.php');
        exit();
    }
    header('Location: ' . $return_url);
    exit();
}
 else {
            $error = '❌ Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Kindora</title>
    <link rel="icon" type="image/png" href="kindora-logo.ico" />
    <link href="common_nav_footer.css" rel="stylesheet" />
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      body {
        width: 100%;
        min-height: 100vh;
        font-family: "Open Sans", sans-serif;
        background: linear-gradient(135deg, #1e2a38, #005fa3);
        display: flex;
        flex-direction: column;
      }
      .container {
        background-color: #ffffff;
        width: 100%;
        max-width: 420px;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        text-align: center;
        margin: 60px auto;
      }
      .container h1 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 26px;
      }
      input {
        width: 100%;
        padding: 14px;
        margin: 12px 0;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 15px;
        outline: none;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
      }
      input:focus {
        border-color: #007acc;
        box-shadow: 0 0 6px rgba(0, 122, 204, 0.5);
      }
      #but {
        width: 100%;
        background-color: #007acc;
        color: white;
        padding: 14px;
        margin-top: 12px;
        border-radius: 10px;
        border: none;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
      }
      #but:hover {
        background-color: #005fa3;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
      }
      #but:disabled {
        background-color: #ccc;
        cursor: not-allowed;
        transform: none;
      }
      .link {
        margin-top: 18px;
        font-size: 14px;
      }
      .link a {
        color: #007acc;
        text-decoration: none;
      }
      .link a:hover {
        text-decoration: underline;
      }
      .message {
        margin-top: 15px;
        font-size: 14px;
        font-weight: bold;
        display: none;
        padding: 10px;
        border-radius: 5px;
      }
      .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
      }
      .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
      }
      .debug-info {
        margin-top: 20px;
        font-size: 12px;
        color: #666;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        text-align: left;
      }
    </style>
  </head>
  <body>
    <!-- Navigation (unchanged) -->
    <div id="nav1">
      <a href="index.php" id="logo-link">
        <div id="logo">Kindora</div>
      </a>
      <div id="nav2">
        <div class="inspire-wrapper">
          <a class="a1 dropbtn" id="inspireBtn" href="#">Be Inspired</a>
          <div class="scroll-container1" id="inspireScroll">
            <a data-href="asia.html">
              <div class="image-wrapper1">
                <img src="web images/banner/asia.jpg" alt="Asia" />
                <div class="overlay-text">Asia</div>
              </div>
            </a>
            <a data-href="europe.html">
              <div class="image-wrapper1">
                <img src="web images/banner/europe.jpeg" alt="Europe" />
                <div class="overlay-text">Europe</div>
              </div>
            </a>
            <a data-href="africa.html">
              <div class="image-wrapper1">
                <img src="web images/banner/africa.jpg" alt="Africa" />
                <div class="overlay-text">Africa</div>
              </div>
            </a>
            <a data-href="north_america.html">
              <div class="image-wrapper1">
                <img
                  src="web images/banner/north america.jpg"
                  alt="North America"
                />
                <div class="overlay-text">North America</div>
              </div>
            </a>
            <a data-href="south_america.html">
              <div class="image-wrapper1">
                <img src="7 wonders/Machu Picchu.jpeg" alt="South America" />
                <div class="overlay-text">South America</div>
              </div>
            </a>
            <a data-href="australia.html">
              <div class="image-wrapper1">
                <img src="web images/banner/australia.jpg" alt="Australia" />
                <div class="overlay-text">Australia</div>
              </div>
            </a>
            <a data-href="antarctica.html">
              <div class="image-wrapper1">
                <img src="web images/banner/antarctica.jpeg" alt="Antarctica" />
                <div class="overlay-text">Antarctica</div>
              </div>
            </a>
          </div>
        </div>
        <a class="a1 dropbtn" href="explore.html">Places to go</a>
        <a class="a1 dropbtn" href="things_to_do.html">Things to do</a>
        <a class="a1 dropbtn" href="booking.html">Plan Your Trip</a>
      </div>
      <button class="menu" onclick="toggleMenu()">☰</button>
      <div id="sidebar" class="sidebar">
        <button class="closebtn" onclick="toggleMenu()">×</button>
        <a href="home.html">Home</a>
        <a href="login.html">Login/Register</a>
        <a href="booking.html">Book Your Trips</a>
        <a href="mytrips.html">My Trips</a>
        <a href="aboutus.html">About</a>
        <a href="contactus.html">Contact</a>
      </div>
    </div>

    <br /><br /><br /><br />
    <br /><br /><br /><br />

    <div class="container">
        <h1>🔐 Login</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?return=' . urlencode($return_url)); ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    placeholder="Enter your email" 
                    value="<?php echo htmlspecialchars($email); ?>"
                    required 
                />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    placeholder="Enter your password" 
                    required 
                />
            </div>

            <button type="submit" id="but">Login</button>

            <div class="link">
                Don't have an account? <a href="register.html">Register here</a>
            </div>
        </form>
    </div>
    
    

    <!-- Footer (unchanged) -->
    <footer>
      <div class="footer-container">
        <div class="footer-left">
          <h3>Kindora</h3>
          <p>
            Your gateway to dream destinations around the globe.<br />
            Explore, travel, and create unforgettable memories with us.
          </p>
        </div>
        <div class="footer-middle">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="#">Be Inspired</a></li>
            <li><a href="explore.html">Places to Go</a></li>
            <li><a href="things_to_do.html">Things to Do</a></li>
            <li><a href="booking.html">Plan Your Trip</a></li>
            <li><a href="contactus.html">Contact</a></li>
          </ul>
        </div>
        <div class="footer-right">
          <h4>Stay Connected</h4>
          <form class="subscribe-form">
            <input type="email" placeholder="Enter your email" required />
            <button type="submit">Subscribe</button>
          </form>
          <h4>Follow Us</h4>
          <div class="social-icons">
            <a href="#"><img src="icons/facebook.jpeg" alt="Facebook" /></a>
            <a href="#"><img src="icons/instagram.jpeg" alt="Instagram" /></a>
            <a href="#"><img src="icons/twitter.png" alt="Twitter" /></a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 Kindora. All rights reserved.</p>
      </div>
    </footer>

    
    <script src="common_nav_footer.js"></script>
  </body>
</html>
