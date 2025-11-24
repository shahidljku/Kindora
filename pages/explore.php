
 <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Explore – Kindora</title>
    <link rel="stylesheet" href="homepage.css" />
    <link rel="stylesheet" href="explore.css" />
    >
    <link rel="icon" type="image/png" href="kindora-logo.ico" />
   <link href="../assets/css/styles.css" rel="stylesheet" />
<link href="../assets/css/all.min.css" rel="stylesheet" />
  </head>
  
  <body>
   <!--  -->
    <?php   require_once '../includes/header.php'; ?><br><br><br><br>
      <!-- Hero Section with Image Background -->
    <!-- <center>
      <div class="hero-content">
        <h1>Explore the World 🌍</h1>
        <p>Find your next adventure with Kindora</p>
        <div class="filters">
          <select>
            <option>All Continents</option>
            <option>Asia</option>
            <option>Europe</option>
            <option>Africa</option>
            <option>North America</option>
            <option>South America</option>
            <option>Australia</option>
          </select>
          <select>
            <option>All Categories</option>
            <option>Beaches</option>
            <option>Mountains</option>
            <option>Historical</option>
            <option>Modern Cities</option>
          </select>
          <input type="text" placeholder="Search destinations..." />
          <button class="search-btn">Search</button>
        </div>
      </div>
    </section>
</center>
    -->
    <section id="seven-wonders"> 
      <h2>7 Wonders of the World</h2>
      <div class="wonders-container">
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Great Wall of China.avif" />
          <h3>Great Wall of China</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Petra.avif" />
          <h3>Petra, Jordan</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Christ-Redeemer.avif" />
          <h3>Christ the Redeemer</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Machu-Picchu.avif" />
          <h3>Machu Picchu</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Chichen Itza (Mexico).avif" />
          <h3>Chichen Itza</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Colosseum.avif" />
          <h3>Colosseum</h3>
        </div>
        <div class="wonder-card">
          <img src="../assets/images/7wonders/Taj-Mahal.avif" />
          <h3>Taj Mahal</h3>
        </div>
      </div>
    </section>

    <section id="destinations">
      <h2>Popular Destinations</h2>
      <div class="grid-gallery">
        <img src="../places/paris.avif" alt="Paris" />
        <img src="../places/rome.avif" alt="Rome" />
        <img src="../places/bali.avif" alt="Bali" />
        <img src="../places/dubai.avif" alt="Dubai" />
        <img src="../places/tokyo.avif" alt="Tokyo" />
        <img src="../places/cape-town.avif" alt="Cape Town" />
      </div>
    </section>

    <section id="packages">
      <h2>Special Travel Packages</h2>
      <div class="packages">
        <div class="package-card">
          <img src="../our_packages/summer.avif" alt="Summer" />
          <h3>Summer Escape</h3>
          <p>Sunny beaches, islands & adventures.</p>
         <button class="package-btn"class="info-btn" onclick="handleBookNow(event, 301)"    <!-- <-- use the actual destination id -->✈️ Book Now</button>
        </div>
        <div class="package-card">
          <img src="../our_packages/winter.avif" alt="Winter" />
          <h3>Winter Wonderland</h3>
          <p>Snowy mountains & cozy retreats.</p>
           <button class="package-btn"class="info-btn" onclick="handleBookNow(event, 302)"    <!-- <-- use the actual destination id -->✈️ Book Now</button>
        </div>
        <div class="package-card">
          <img src="../our_packages/monsoon.avif" alt="Monsoon" />
          <h3>Monsoon Magic</h3>
          <p>Lush greenery & waterfalls.</p>
           <button class="package-btn"class="info-btn" onclick="handleBookNow(event, 303)"    <!-- <-- use the actual destination id -->✈️ Book Now</button>
        </div>
      </div>
    </section>
    <!-- <footer>
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
            <li><a href="home.php">Home</a></li>
            <li><a href="#">Be Inspired</a></li>
            <li><a href="explore.php">Places to Go</a></li>
            <li><a href="things_to_do.php">Things to Do</a></li>
            <li><a href="booking.php">Plan Your Trip</a></li>
            <li><a href="contactus.php">Contact</a></li>
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
            <a href="#"><img src="icons/twitter.avif" alt="Twitter" /></a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2025 Kindora. All rights reserved.</p>
      </div>
    </footer> -->

    <script>
      const inspireBtn = document.getElementById("inspireBtn");
      const scrollContainer = document.getElementById("inspireScroll");

      inspireBtn.addEventListener("click", function (e) {
        e.preventDefault();
        if (scrollContainer.style.display === "flex") {
          scrollContainer.style.display = "none";
        } else {
          scrollContainer.style.display = "flex";
        }
      });

      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }

      document.querySelectorAll(".scroll-container1 a").forEach((a) => {
        a.addEventListener("dblclick", (e) => {
          const href = a.getAttribute("data-href");
          if (href) {
            window.location.href = href;
          }
        });

        a.addEventListener("click", (e) => {
          e.preventDefault();
        });
      });
    </script>
    <script>
  // inject server-side login status into JS
  const IS_LOGGED_IN = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;

  function handleBookNow(event, destinationId) {
    event.preventDefault();
    event.stopPropagation();

    const bookingUrl = '/Kindora/booking.php?destination_id=' + encodeURIComponent(destinationId);

    if (IS_LOGGED_IN) {
      window.location.href = bookingUrl;
    } else {
      // redirect to login with return URL so user comes back prefilled
      window.location.href = '/Kindora/login.php?return=' + encodeURIComponent(bookingUrl);
    }
  }
</script>

  </body>
</html>
 <?php   require_once '../includes/footer.php'; ?>