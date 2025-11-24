<?php
require_once '../../config.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch all Europe destinations from database
$europeDestinations = array();
try {
    $europeDestinations = KindoraDatabase::query(
        "SELECT destination_id, name, description, image_url, best_season, difficulty_level
         FROM destinations 
         WHERE type = 'europe' AND is_active = 1 
         ORDER BY name ASC"
    ) ?: array();

    // Log for debugging
    error_log("Europe destinations query result: " . count($europeDestinations) . " records found");
} catch (Exception $e) {
    error_log("Europe destinations fetch error: " . $e->getMessage());
}

// Create an associative array for easy lookup by name (if needed)
$destData = array();
foreach ($europeDestinations as $dest) {
    $destData[$dest['name']] = $dest;
}

require_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Europe - Kindora</title>
    <link href="asia.css" rel="stylesheet" />
    <link href="../../assets/css/common_nav_footer.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="kindora-logo.ico">
    <meta name="description" content="Explore the wild beauty of Europe with Kindora - Safari adventures and cultural experiences">
  </head>
  <body>
    <br><br><br>
    <!-- Background Video -->
    <div class="video-container">
      <video autoplay muted loop id="bg-video">
        <source src="/Kindora/continents videos/europe_intro.webm" type="video/webm" />
      </video>
    </div>

    <!-- Dynamically Loaded Destinations Grid -->
    <div class="card-grid">
      <?php if (!empty($europeDestinations)): ?>
        <?php foreach ($europeDestinations as $destination): ?>
          <!-- <?php echo htmlspecialchars($destination['name']); ?> -->
          <a class="card-link" href="#" onclick="showDetails(event, '<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $destination['name']))); ?>')">
            <div class="video-card" data-video="<?php echo htmlspecialchars($destination['name']); ?>.webm">
              <img
                src="<?php echo htmlspecialchars($destination['image_url']); ?>"
                alt="<?php echo htmlspecialchars($destination['name']); ?>"
              />
              <div class="card-content">
                <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                <p>
                  <?php echo htmlspecialchars($destination['description'] ?? ''); ?>
                </p>
                <?php if (!empty($destination['best_season'])): ?>
                <p style="font-size: 0.9em; color:white; margin-top: 8px;">
                  <strong>Best Season:</strong> <?php echo htmlspecialchars($destination['best_season']); ?>
                </p>
                <?php endif; ?>
                <div class="card-tags">
                  <span class="tag adventure">Adventure</span>
                  <span class="tag culture">Culture</span>
                </div>
                <div class="card-actions">
                  <button
                    class="save-btn"
                    onclick="addToItinerary(event, '<?php echo htmlspecialchars($destination['name']); ?>')"
                  >
                    ❤️ Add to Trip
                  </button>
                  <button
                    class="info-btn"
                    onclick="handleBookNow(event, <?php echo (int)$destination['destination_id']; ?>)"
                  >
                    ✈️ Book Now
                  </button>
                </div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666; font-size: 18px;">
          <p>No destinations available at the moment. Please check back soon!</p>
        </div>
      <?php endif; ?>
    </div>

    <script>
      // Store selected destinations for trip planning
      let itinerary = [];

      // Handle Book Now button click (same logic as asia.php)
      function handleBookNow(event, destinationId) {
        event.preventDefault();
        event.stopPropagation();
        const isLoggedIn = <?php echo isUserLoggedIn() ? 'true' : 'false'; ?>;

        const bookingUrl = '/kindora/booking.php?destination_id=' + destinationId; // Absolute path

        if (isLoggedIn) {
          window.location.href = bookingUrl; // Go directly
        } else {
          window.location.href = '/kindora/login.php?return=' + encodeURIComponent(bookingUrl); // Pass abs path as return
        }
      }

      function showDetails(event, destinationId) {
        event.preventDefault();
        event.stopPropagation();
        console.log("Show details for: " + destinationId);
      }

      function displayItinerary() {
        if (itinerary.length === 0) {
          alert("📋 Your Europen Adventure Plan:\n\nNo destinations selected yet. Click '❤️ Add to Trip' to start planning!");
          return;
        }

        let tripList = itinerary
          .map((destination, index) => `${index + 1}. ${destination}`)
          .join("\n");

        let message = `✈️ Your Europen Adventure Plan:\n\n${tripList}\n\n🌟 ${itinerary.length} amazing destinations selected!`;

        alert(message);
      }

      function addToItinerary(event, destination) {
        event.preventDefault();
        event.stopPropagation();
        
        if (itinerary.indexOf(destination) === -1) {
          itinerary.push(destination);
          alert(`✅ ${destination} added to your trip!`);
        } else {
          alert(`❌ ${destination} is already in your trip.`);
        }
      }
    </script>

    <script>
      // Same scroll + sidebar behavior as asia.php
      document.querySelectorAll('.scroll-container1 a').forEach((a) => {
        a.addEventListener('dblclick', (e) => {
          const href = a.getAttribute('data-href');
          if (href) {
            window.location.href = href;
          }
        });
        
        a.addEventListener('click', (e) => {
          e.preventDefault();
        });
      });

      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }
    </script>
    <script src="../../assets/js/common_nav_footer.js"></script>

  </body>
</html>
<?php
require_once '../../includes/footer.php';
?>
