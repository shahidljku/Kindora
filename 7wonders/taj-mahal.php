<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Taj Mahal - Visitor Guide</title>
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
    <div class="image-heading">Taj Mahal - Agra, India</div>
    <img src="7_wonders/Taj-Mahal.avif" alt="Taj Mahal" />

    <section>
      <h3>Introduction</h3>
      <p>
        The Taj Mahal, located in Agra, India, is one of the world’s most iconic
        monuments and a UNESCO World Heritage Site.
      </p>
    </section>

    <section>
      <h3>Overview / History</h3>
      <p>
        Commissioned by Mughal emperor Shah Jahan for his wife Mumtaz Mahal, the
        Taj Mahal blends Persian, Islamic, and Indian styles.
      </p>
    </section>

    <section>
      <h3>Architecture & Design</h3>
      <p>
        A masterpiece of Mughal architecture made from white marble with
        intricate inlay work of precious stones.
      </p>
    </section>

    <section>
      <h3>Cultural Significance</h3>
      <p>
        The Taj Mahal represents eternal love and is one of India’s most visited
        tourist attractions.
      </p>
    </section>

    <section>
      <h3>Things to Do</h3>
      <ul>
        <li>See the mausoleum at sunrise or sunset.</li>
        <li>Walk through the Charbagh gardens.</li>
        <li>Visit Agra Fort nearby.</li>
      </ul>
    </section>

    <section>
      <h3>Tourist Information</h3>
      <p><strong>Location:</strong> Agra, Ut  tar Pradesh, India</p>
      <a href="../booking.php?destination_id=9" class="book-btn">Book Now</a>
    </section>

    <section>
      <h3>360° View of Taj Mahal</h3>
      <iframe
        width="100%"
        height="500"
        style="border: 0"
        src="https://www.google.com/maps/embed?pb=!4v1691234567890!6m8!1m7!1sCAoSLEFGMVFpcE45Y2VRY3M0SHhVbGhXbFhEdHoxcEpJSjY3Y3ppNElrdlhKTWJm!2m2!1d27.175015!2d78.042155!3f90!4f0!5f0.7820865974627469"
        allowfullscreen
        loading="lazy"
      >
      </iframe>
    </section>


    <script>
      function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("open");
      }
    </script>
    <?php require_once '../includes/footer.php';?>
  </body>
</html>
