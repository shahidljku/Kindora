<?php
/**
 * KINDORA AUTO-FIXER SCRIPT
 * Run this once to automatically generate ALL PHP wrappers with correct paths
 * Usage: php auto-fix-kindora.php
 */

// Base directory - change this to your Kindora path if different
$baseDir = __DIR__;

// File mappings: [php_file] => [html_file, folder, needs_form_handling]
$files = [
    // Main files
    'home.php' => ['home.php', '', false],
    'aboutus.php' => ['aboutus.php', '', false],
    'contactus.php' => ['contactus.php', '', true],
    'explore.php' => ['explore.php', '', false],
    'thingstodo.php' => ['things_to_do.php', '', false],
    'booking.php' => ['booking.php', '', true],
    'confirmation.php' => ['confirmation.php', '', 'booking'],
    'login.php' => ['login.php', '', 'login'],
    'register.php' => ['register.php', '', 'register'],
    'admin.php' => ['admin.php', '', false],
    'faq.php' => ['faq.php', '', false],
    'volunteer.php' => ['volunteer.php', '', 'volunteer'],
    'guide.php' => ['guide.php', '', false],
    
    // Continental files
    'continents/asia.php' => ['asia.php', '..', false],
    'continents/europe.php' => ['europe.php', '..', false],
    'continents/africa.php' => ['africa.php', '..', false],
    'continents/north-america.php' => ['north_america.php', '..', false],
    'continents/south-america.php' => ['south_america.php', '..', false],
    'continents/australia.php' => ['australia.php', '..', false],
    'continents/antarctica.php' => ['antarctica.php', '..', false],
    
    // Seven Wonders files
    '7wonders/taj-mahal.php' => ['Taj_Mahal.php', '.', false],
    '7wonders/great-wall.php' => ['Great Wall of China.php', '.', false],
    '7wonders/christ-redeemer.php' => ['christ the redeemer.php', '.', false],
    '7wonders/petra.php' => ['Petra.php', '.', false],
    '7wonders/machu-picchu.php' => ['Machu Picchu.php', '.', false],
    '7wonders/colosseum.php' => ['Colosseum.php', '.', false],
    '7wonders/chichen-itza.php' => ['Chichen Itza.php', '.', false],
];

// API files
$apiFiles = [
    'api/live-prices.php' => 'livePrices',
    'api/newsletter.php' => 'newsletter', 
    'api/track-interaction.php' => 'trackInteraction',
    'api/update-preferences.php' => 'updatePreferences'
];

echo "🚀 KINDORA AUTO-FIXER STARTING...\n";

// Create directories
$dirs = ['api', 'continents'];
foreach ($dirs as $dir) {
    $path = $baseDir . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "📁 Created directory: $dir\n";
    }
}

// Generate main PHP files
foreach ($files as $phpFile => $config) {
    list($htmlFile, $htmlFolder, $formType) = $config;
    
    $fullPath = $baseDir . '/' . $phpFile;
    $dir = dirname($fullPath);
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Determine config path based on file location
    $configPath = strpos($phpFile, '/') !== false ? '../PHP/config.php' : 'PHP/config.php';
    $htmlPath = $htmlFolder ? $htmlFolder . '/' . $htmlFile : $htmlFile;
    
    $content = "<?php\nrequire_once __DIR__ . '/{$configPath}';\n\n";
    
    // Add form handling if needed
    if ($formType === true) {
        // Generic contact form
        $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $content .= "    \$data = [\n";
        $content .= "        'name' => \$_POST['name'] ?? '',\n";
        $content .= "        'email' => \$_POST['email'] ?? '',\n";
        $content .= "        'message' => \$_POST['message'] ?? '',\n";
        $content .= "        'created_at' => date('Y-m-d H:i:s')\n";
        $content .= "    ];\n";
        $content .= "    KindoraDatabase::insert('contact_messages', \$data);\n";
        $content .= "    header('Location: {$htmlFile}?sent=1');\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";
    } elseif ($formType === 'login') {
        $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $content .= "    \$row = KindoraDatabase::query('SELECT * FROM users WHERE email = ? LIMIT 1', [\$_POST['email'] ?? ''])[0] ?? null;\n";
        $content .= "    if (\$row && password_verify(\$_POST['password'] ?? '', \$row['password_hash'])) {\n";
        $content .= "        \$_SESSION['user_id'] = \$row['id'];\n";
        $content .= "        KindoraDatabase::trackUserActivity(\$row['id'], 'login_success', 'login');\n";
        $content .= "        header('Location: PHP/index.php');\n";
        $content .= "        exit;\n";
        $content .= "    }\n";
        $content .= "    header('Location: login.php?error=1');\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";
    } elseif ($formType === 'register') {
        $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $content .= "    \$data = [\n";
        $content .= "        'first_name' => \$_POST['first_name'] ?? '',\n";
        $content .= "        'last_name' => \$_POST['last_name'] ?? '',\n";
        $content .= "        'email' => \$_POST['email'] ?? '',\n";
        $content .= "        'password_hash' => password_hash(\$_POST['password'] ?? '', PASSWORD_DEFAULT),\n";
        $content .= "        'preferred_locale' => 'en_US',\n";
        $content .= "        'preferred_currency' => 'USD',\n";
        $content .= "        'status' => 'active',\n";
        $content .= "        'created_at' => date('Y-m-d H:i:s')\n";
        $content .= "    ];\n";
        $content .= "    \$userId = KindoraDatabase::insert('users', \$data);\n";
        $content .= "    \$_SESSION['user_id'] = \$userId;\n";
        $content .= "    header('Location: PHP/index.php');\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";
    } elseif ($formType === 'booking') {
        $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $content .= "    \$data = [\n";
        $content .= "        'user_id' => \$_SESSION['user_id'] ?? null,\n";
        $content .= "        'package_id' => \$_POST['package_id'] ?? null,\n";
        $content .= "        'start_date' => \$_POST['start_date'] ?? null,\n";
        $content .= "        'end_date' => \$_POST['end_date'] ?? null,\n";
        $content .= "        'people' => \$_POST['people'] ?? 1,\n";
        $content .= "        'status' => 'pending',\n";
        $content .= "        'created_at' => date('Y-m-d H:i:s')\n";
        $content .= "    ];\n";
        $content .= "    \$bookingId = KindoraDatabase::insert('bookings', \$data);\n";
        $content .= "    header('Location: confirmation.php?id=' . \$bookingId);\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";
    } elseif ($formType === 'volunteer') {
        $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
        $content .= "    \$data = [\n";
        $content .= "        'full_name' => \$_POST['full_name'] ?? '',\n";
        $content .= "        'email' => \$_POST['email'] ?? '',\n";
        $content .= "        'phone' => \$_POST['phone'] ?? '',\n";
        $content .= "        'message' => \$_POST['message'] ?? '',\n";
        $content .= "        'created_at' => date('Y-m-d H:i:s')\n";
        $content .= "    ];\n";
        $content .= "    KindoraDatabase::insert('volunteer_applications', \$data);\n";
        $content .= "    header('Location: volunteer.php?submitted=1');\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";
    }
    
    $content .= "readfile(__DIR__ . '/{$htmlPath}');\n?>";
    
    file_put_contents($fullPath, $content);
    echo "✅ Created: $phpFile\n";
}

// Generate API files
foreach ($apiFiles as $apiFile => $type) {
    $fullPath = $baseDir . '/' . $apiFile;
    $content = "<?php\nrequire_once __DIR__ . '/../PHP/config.php';\n\n";
    
    switch ($type) {
        case 'livePrices':
            $content .= "\$prices = KindoraDatabase::query('SELECT pp.price_usd, d.slug FROM package_prices pp JOIN packages p ON p.id = pp.package_id JOIN destinations d ON d.id = p.destination_id ORDER BY d.id LIMIT 10');\necho json_encode(\$prices);\n";
            break;
        case 'newsletter':
            $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
            $content .= "    \$data = ['email' => \$_POST['email'] ?? '', 'subscribed_at' => date('Y-m-d H:i:s')];\n";
            $content .= "    KindoraDatabase::insert('newsletter_subscribers', \$data);\n";
            $content .= "    header('Location: ../home.php?subscribed=1');\n";
            $content .= "    exit;\n";
            $content .= "}\n";
            break;
        case 'trackInteraction':
            $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
            $content .= "    \$input = json_decode(file_get_contents('php://input'), true);\n";
            $content .= "    \$data = ['user_id' => \$_SESSION['user_id'] ?? null, 'action' => \$input['action'] ?? 'unknown', 'element' => \$input['element'] ?? '', 'created_at' => date('Y-m-d H:i:s')];\n";
            $content .= "    KindoraDatabase::insert('user_interactions', \$data);\n";
            $content .= "    echo json_encode(['success' => true]);\n";
            $content .= "}\n";
            break;
        case 'updatePreferences':
            $content .= "if (\$_SERVER['REQUEST_METHOD'] === 'POST') {\n";
            $content .= "    \$input = json_decode(file_get_contents('php://input'), true);\n";
            $content .= "    if (isset(\$_SESSION['user_id']) && isset(\$input['currency'])) {\n";
            $content .= "        KindoraDatabase::update('users', ['preferred_currency' => \$input['currency']], 'id = ?', [\$_SESSION['user_id']]);\n";
            $content .= "    }\n";
            $content .= "    echo json_encode(['success' => true]);\n";
            $content .= "}\n";
            break;
    }
    
    $content .= "?>";
    file_put_contents($fullPath, $content);
    echo "🔧 Created API: $apiFile\n";
}

// Generate SQL for missing tables
$sql = "-- Missing tables for Kindora API support\n";
$sql .= "CREATE TABLE IF NOT EXISTS user_interactions (id INT PRIMARY KEY AUTO_INCREMENT, user_id INT, action VARCHAR(100), element VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);\n";
$sql .= "CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INT PRIMARY KEY AUTO_INCREMENT, email VARCHAR(255) UNIQUE, subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);\n";
$sql .= "CREATE TABLE IF NOT EXISTS contact_messages (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), email VARCHAR(255), message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);\n";
$sql .= "CREATE TABLE IF NOT EXISTS volunteer_applications (id INT PRIMARY KEY AUTO_INCREMENT, full_name VARCHAR(255), email VARCHAR(255), phone VARCHAR(20), message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);\n";

file_put_contents($baseDir . '/missing-tables.sql', $sql);
echo "📊 Created missing-tables.sql\n";

echo "\n🎉 AUTO-FIX COMPLETE!\n";
echo "📋 NEXT STEPS:\n";
echo "1. Run: mysql -u root -p kindora_ultimate < missing-tables.sql\n";
echo "2. Test any PHP file: http://localhost/Kindora/home.php\n";
echo "3. All files now have correct paths automatically!\n";
?>