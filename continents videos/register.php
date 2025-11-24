<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kindora â€“ Register</title>
    <link href="common_nav_footer.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="kindora-logo.ico" />
    <style>
      body {
        width: 100%;
        min-height: 100vh;
        font-family: "Open Sans", sans-serif;
        background: linear-gradient(135deg, #1e2a38, #005fa3);
        flex-direction: column;
      }
      .register-container {
        max-width: 400px;
        margin: 120px auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      }
      .register-container h2 {
        text-align: center;
        color: #003366;
        margin-bottom: 20px;
      }
      .register-form label {
        display: block;
        margin: 12px 0 6px;
        font-weight: 600;
        color: #333;
      }
      .register-form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
      }
      .register-form button {
        width: 100%;
        margin-top: 20px;
        padding: 12px;
        background: #003366;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s;
      }
      .register-form button:hover {
        background: #0055a5;
      }
      .login-link {
        text-align: center;
        margin-top: 15px;
      }
      .login-link a {
        color: #003366;
        text-decoration: none;
        font-weight: 600;
      }
      .error {
        color: #c00;
        margin-top: 10px;
        font-size: 0.9rem;
      }
    </style>
  </head>
  <body>
    <!-- âœ… Updated Navigation (from login.html) -->
    <div id="nav1">
      <a href="home.html" id="logo-link">
        <div id="logo">Kindora</div>
      </a>
      <div id="nav2">
        <div class="inspire-wrapper">
          <a class="a1 dropbtn" id="inspireBtn" href="#">Be Inspired</a>
          <div class="scroll-container1" id="inspireScroll">
            <a data-href="asia.html">
              <div class="image-wrapper1">
                <img src="web images/banner/asia.avif" alt="Asia" />
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
                <img src="web images/banner/africa.avif" alt="Africa" />
                <div class="overlay-text">Africa</div>
              </div>
            </a>
            <a data-href="north_america.html">
              <div class="image-wrapper1">
                <img
                  src="web images/banner/north america.avif"
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
                <img src="web images/banner/australia.avif" alt="Australia" />
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
      <button class="menu" onclick="toggleMenu()">â˜°</button>
      <div id="sidebar" class="sidebar">
        <button class="closebtn" onclick="toggleMenu()">Ã—</button>
        <a href="home.html">Home</a>
        <a href="login.html">Login/Register</a>
        <a href="booking.html">Book Your Trips</a>
        <a href="mytrips.html">My Trips</a>
        <a href="aboutus.html">About</a>
        <a href="contactus.html">Contact</a>
      </div>
    </div>
    <!-- âœ… End Updated Navigation -->

    <div class="register-container">
      <h2>Create Your Account</h2>
      <form id="registerForm" class="register-form" method="POST">
  <label for="fullName">Full Name</label>
  <input type="text" id="fullName" name="fullName" required />

  <label for="email">Email Address</label>
  <input type="email" id="email" name="email" required />

  <label for="password">Password</label>
  <input type="password" id="password" name="password" minlength="6" required />

  <label for="confirmPassword">Confirm Password</label>
  <input type="password" id="confirmPassword" name="confirmPassword" minlength="6" required />

  <div id="errorMsg" class="error"></div>
  <button type="submit">Register</button>
</form>

      <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("kindora_connect.php");

    $fullname = $_POST["fullName"];
    $email = $_POST["email"];
    $pass = $_POST["password"];
    $confirm_pass = $_POST["confirmPassword"];

    // Validate passwords match
    if ($pass !== $confirm_pass) {
        echo "Passwords do not match.";
        exit();
    }
    // Hash the password before storing it
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    // Insert into table specifying only the fields you want to provide
    $sql = "INSERT INTO users (username, password, email, full_name, role, reward_points) 
        VALUES ('$email', '$hashed_pass', '$email', '$fullname', 'user', 0)";


    if (mysqli_query($conn, $sql)) {
        echo "Registration successful!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
      <div class="login-link">
        Already have an account? <a href="login.html">Login</a>
      </div>
    </div>

    <footer>
      <div class="footer-container">
        <div class="footer-left">
          <h3>Kindora</h3>
          <p>Explore, travel, and create unforgettable memories with us.</p>
        </div>
        <div class="footer-middle">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="explore.html">Explore</a></li>
            <li><a href="booking.html">Book</a></li>
            <li><a href="contactus.html">Contact</a></li>
          </ul>
        </div>
        <div class="footer-right">
          <h4>Stay Connected</h4>
          <form class="subscribe-form">
            <input type="email" placeholder="Your email" required />
            <button type="submit">Subscribe</button>
          </form>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 Kindora. All rights reserved.</p>
      </div>
    </footer>

    <script src="common_nav_footer.js"></script>
    <script src="users.js"></script>
    <!--<script>
      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }

      document
        .getElementById("registerForm")
        .addEventListener("submit", function (e) {
          e.preventDefault();
          const fullName = document.getElementById("fullName").value.trim();
          const email = document.getElementById("email").value.trim();
          const password = document.getElementById("password").value;
          const confirm = document.getElementById("confirmPassword").value;
          const errorDiv = document.getElementById("errorMsg");
          errorDiv.textContent = "";

          if (password !== confirm) {
            errorDiv.textContent = "Passwords do not match.";
            return;
          }

          if (!register({ fullName, email, password })) {
            errorDiv.textContent = "User already exists. Please login.";
            return;
          }

          alert("ðŸŽ‰ Registration successful! Please login.");
          window.location.href = "login.html";
        });
    </script>-->
  </body>
</html>
