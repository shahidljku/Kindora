    <?php
    require_once __DIR__ . '/../config.php';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Machu Picchu - Visitor Guide</title>
        <link href="7wonders.css" rel="stylesheet" />
        <link rel="icon" type="image/png" href="../icons/kindora-logo.ico" />
    
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
            padding: 20px ;
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
        <div class="image-heading">MACHU PICCHU - Peru</div>
        <img src="7_wonders/Machu Picchu.avif" alt="Machu Picchu, Peru" />

        <section>
        <h3>Introduction:</h3>
        <p>
            Machu Picchu is a 15th-century Incan citadel located in the Andes
            Mountains of Peru. It is one of the most famous archaeological sites in
            the world and a UNESCO World Heritage Site.
        </p>
        </section>

        <section>
        <h3>Overview / History:</h3>
        <p>
            Believed to have been built by the Inca emperor Pachacuti around 1450,
            Machu Picchu was abandoned in the 16th century. It remained largely
            unknown to the outside world until it was rediscovered in 1911 by Hiram
            Bingham.
        </p>
        </section>

        <section>
        <h3>Architecture & Design:</h3>
        <p>
            Machu Picchu is known for its dry-stone walls, terraced fields, and
            sophisticated urban layout. Key structures include the Temple of the
            Sun, Intihuatana stone, and the Room of the Three Windows.
        </p>
        </section>

        <section>
        <h3>Cultural Significance:</h3>
        <p>
            As a sacred site of the Inca civilization, Machu Picchu reflects the
            ingenuity of ancient Andean culture. It is a symbol of Peru’s heritage
            and attracts visitors from all over the world.
        </p>
        </section>

        <section>
        <h3>Things to do:</h3>
        <ul>
            <li>Explore the Inca ruins and temples.</li>
            <li>Hike the Inca Trail or Huayna Picchu for panoramic views.</li>
            <li>Visit the Sun Gate (Inti Punku).</li>
            <li>Take guided tours to learn about Inca history and culture.</li>
        </ul>
        </section>

        <section>
        <h3>How to reach:</h3>
        <p>
            Nearest airport: Alejandro Velasco Astete Airport in Cusco.<br />
            From Cusco, take a train to Aguas Calientes and then a bus or hike to
            Machu Picchu. Advance booking is highly recommended.
        </p>
        </section>

        <section>
        <h3>Tourist Information:</h3>
        <p><strong>Location:</strong> Andes Mountains, Cusco Region, Peru</p>
        <p><strong>Best time to visit:</strong> April to October (dry season)</p>
        <p>
            <strong>Entry fee:</strong> Required; varies based on trail access and
            citizenship
        </p>
        <p>
            <strong>Nearby attractions:</strong> Sacred Valley, Cusco, Ollantaytambo
        </p>
        <a href="../booking.php?destination_id=11" class="book-btn">Book Now</a>
        </section>

        <section>
        <h3>Visitor Tips:</h3>
        <p>
            Book tickets and train rides in advance. Acclimate to high altitude in
            Cusco before visiting. Carry passport and be prepared for changing
            weather. Limited daily entries apply.
        </p>
        </section>

        <section>
        <h3>360° View of Machu Picchu – Peru</h3>
        <iframe
            src="https://www.google.com/maps/embed?pb=!4v1698765432100!6m8!1m7!1sCAoSLEFGMVFpcE5oWmVRY1JrWGR4cVZVRjVEMHVoMmRyc3Q4Q0lOOG5jdWlWQ0hn!2m2!1d-13.163141!2d-72.544963!3f0!4f0!5f0.7820865974627469"
            width="100%"
            height="500"
            style="border: 0"
            allowfullscreen
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

