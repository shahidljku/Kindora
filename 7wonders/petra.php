<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Petra - Visitor Guide</title>
    <link href="7wonders.css" rel="stylesheet" />
       
    <style>
        body {
            padding-left: 1px;
            padding-right: 1px;
        }
      #nav1 {
        height: 16%;
      }
      .image-heading {
        text-align: center;
        font-size: 2rem;
        padding: 20px;
        background: #003366;
        color: white;
        margin-top: 4%;
      }
      img {
        width: 100%;
        height: auto;
      }
      section {
        padding: 20px;
        max-width: 900px;
        margin: auto;
      }
      h3 {
        color: #1e40af;
        margin-bottom: 10px;
      }
      .book-btn {
        display: inline-block;
        margin-top: 15px;
        padding: 12px 28px;
        background: linear-gradient(135deg, #ffcc00, #ff9900);
        color: #003366;
        font-size: 1.1rem;
        font-weight: bold;
        text-decoration: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
      }
      .book-btn:hover {
        background: linear-gradient(135deg, #ffd633, #ffb84d);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
      }
      .book-btn:active {
        transform: scale(0.97);
      }
    </style>
  </head>
  <body>
    <?php require_once '../includes/header.php';?>
    <div class="image-heading">PETRA - Ma'an Governorate, Jordan</div>
    <img
      src="7_wonders/Petra (Jordan).avif"
      alt="Petra in Jordan - The Treasury (Al-Khazneh)"
    />

    <section>
      <h3>Introduction:</h3>
      <p>
        Petra is an ancient archaeological city in southern Jordan, famous for
        its rock-cut architecture and water conduit system. Known as the "Rose
        City" due to the color of the stone, Petra is one of the most iconic
        archaeological sites in the world.
      </p>
    </section>

    <section>
      <h3>Overview / History:</h3>
      <p>
        Petra was the capital of the Nabataean Kingdom as early as 400 B.C. It
        flourished as a major trading hub until it declined after Roman
        annexation and a series of earthquakes. It was rediscovered by Swiss
        explorer Johann Ludwig Burckhardt in 1812.
      </p>
    </section>

    <section>
      <h3>Architecture & Design:</h3>
      <p>
        The city's architecture is carved directly into rose-colored sandstone
        cliffs. Notable structures include the Treasury (Al-Khazneh), the
        Monastery (Ad Deir), and the Royal Tombs. Petra also includes temples, a
        Roman-style theatre, and a complex water system.
      </p>
    </section>

    <section>
      <h3>Cultural Significance:</h3>
      <p>
        A UNESCO World Heritage Site and one of the New Seven Wonders of the
        World, Petra reflects the architectural and engineering brilliance of
        the Nabataeans and remains a symbol of Jordan's rich cultural heritage.
      </p>
    </section>

    <section>
      <h3>Things to do:</h3>
      <ul>
        <li>Walk through the Siq, the narrow gorge leading to the Treasury.</li>
        <li>Climb to the Monastery for incredible views.</li>
        <li>Explore the Royal Tombs and Street of Facades.</li>
        <li>
          Visit Petra by Night for a candle-lit experience at the Treasury.
        </li>
      </ul>
    </section>

    <section>
      <h3>How to reach:</h3>
      <p>
        Nearest airport: Queen Alia International Airport in Amman.<br />
        From Amman, travel by bus, taxi, or private tour (approximately 3–4
        hours to Petra). Wadi Musa is the nearest town and base for visitors.
      </p>
    </section>

    <section>
      <h3>Tourist Information:</h3>
      <p><strong>Location:</strong> Ma'an Governorate, Jordan</p>
      <p>
        <strong>Best time to visit:</strong> March to May, and September to
        November (mild temperatures)
      </p>
      <p>
        <strong>Entry fee:</strong> Around 50 JOD (Jordanian Dinar) for a
        one-day ticket
      </p>
      <p>
        <strong>Nearby attractions:</strong> Little Petra (Siq al-Barid), Wadi
        Rum, Dana Biosphere Reserve
      </p>
      <a href="../booking.php?destination_id=10" class="book-btn">Book Now</a>
    </section>

    <section>
      <h3>Visitor Tips:</h3>
      <p>
        Wear sturdy shoes and sun protection. Petra involves extensive walking,
        often on uneven terrain. Bring water, and start early to avoid heat.
        Hiring a guide adds great historical context to your visit.
      </p>
    </section>

    <section>
      <h3>360° View of Petra – Jordan</h3>
      <iframe
        src="https://www.google.com/maps/embed?pb=!4v1690287020000!6m8!1m7!1sCAoSLEFGMVFpcE5oQUxZQy1ZWFZTTFJ5R2xxeFJHS3FfblB5djdOV1l3cU5NZG4w!2m2!1d30.3222356!2d35.4519603!3f0!4f0!5f0.7820865974627469"
        width="100%"
        height="500"
        style="border: 0"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </section>

    <script>
      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }
    </script>
    
    <?php require_once '../includes/footer.php';?>
    </body>
</html>

?>