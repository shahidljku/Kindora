<?php
/**
 * KINDORA - DATABASE PATH VERIFICATION TOOL
 * 
 * This script checks whether:
 * 1. Files/images referenced in database actually exist
 * 2. Database paths match the new organized folder structure
 * 3. Shows which paths are correct and which need fixing
 * 
 * Location: E:\xampp\htdocs\Kindora\verify_paths.php
 * Access: http://localhost/Kindora/verify_paths.php
 */

require_once 'config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = dirname(__FILE__);
$errors = [];
$warnings = [];
$success = [];
$totalChecked = 0;
$totalMissing = 0;

// Colors for output
$green = '<span style="color: green; font-weight: bold;">✓</span>';
$red = '<span style="color: red; font-weight: bold;">✗</span>';
$yellow = '<span style="color: orange; font-weight: bold;">!</span>';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Kindora - Path Verification Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        .success {
            color: green;
            background: #d4edda;
            border-left-color: green;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
        }
        .error {
            color: red;
            background: #f8d7da;
            border-left-color: red;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
        }
        .warning {
            color: orange;
            background: #fff3cd;
            border-left-color: orange;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .summary {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Kindora - Path Verification Tool</h1>
    <p>This tool checks if all database paths point to actual files in your organized folder structure.</p>

    <?php
    
    // ============================================================
    // STEP 1: CHECK ASSETS FOLDERS
    // ============================================================
    
    echo "<h2>1. Folder Structure Verification</h2>";
    
    $requiredFolders = [
        'assets' => 'assets/',
        'assets/images' => 'assets/images/',
        'assets/images/banner' => 'assets/images/banner/',
        'assets/images/7wonders' => 'assets/images/7wonders/',
        'assets/images/destinations' => 'assets/images/destinations/',
        'assets/css' => 'assets/css/',
        'assets/js' => 'assets/js/',
        'assets/videos' => 'assets/videos/',
        'pages' => 'pages/',
        'pages/continents' => 'pages/continents/',
        'pages/packages' => 'pages/packages/',
        'pages/wonders' => 'pages/wonders/',
    ];
    
    echo "<table>";
    echo "<tr><th>Folder</th><th>Status</th><th>Path</th></tr>";
    
    foreach ($requiredFolders as $name => $path) {
        $fullPath = $baseDir . '/' . $path;
        if (is_dir($fullPath)) {
            echo "<tr><td>$name</td><td>$green OK</td><td><code>$path</code></td></tr>";
            $success[] = "Folder exists: $path";
        } else {
            echo "<tr><td>$name</td><td>$red MISSING</td><td><code>$path</code></td></tr>";
            $errors[] = "Folder missing: $path";
        }
    }
    
    echo "</table>";
    
    // ============================================================
    // STEP 2: CHECK DATABASE PATHS
    // ============================================================
    
    echo "<h2>2. Database Image Paths Verification</h2>";
    
    try {
        $query = "SELECT destination_id, name, image_url, video_url FROM destinations LIMIT 30";
        $result = KindoraDatabase::query($query);
        
        if ($result && count($result) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Destination</th><th>Image Status</th><th>Image Path in DB</th><th>Actual File</th></tr>";
            
            foreach ($result as $row) {
                $totalChecked++;
                $destination_id = $row['destination_id'];
                $name = $row['name'];
                $imagePath = $row['image_url'];
                
                // Check if file exists
                $fullImagePath = $baseDir . '/' . $imagePath;
                
                if ($imagePath) {
                    if (file_exists($fullImagePath)) {
                        echo "<tr>";
                        echo "<td>$destination_id</td>";
                        echo "<td>$name</td>";
                        echo "<td>$green EXISTS</td>";
                        echo "<td><code>$imagePath</code></td>";
                        echo "<td>✓</td>";
                        echo "</tr>";
                        $success[] = "Image found: $imagePath";
                    } else {
                        $totalMissing++;
                        echo "<tr style='background: #ffebee;'>";
                        echo "<td>$destination_id</td>";
                        echo "<td>$name</td>";
                        echo "<td>$red MISSING</td>";
                        echo "<td><code>$imagePath</code></td>";
                        echo "<td>✗ File not found</td>";
                        echo "</tr>";
                        $errors[] = "Image missing: $imagePath (Destination ID: $destination_id - $name)";
                    }
                } else {
                    echo "<tr style='background: #fff3cd;'>";
                    echo "<td>$destination_id</td>";
                    echo "<td>$name</td>";
                    echo "<td>$yellow EMPTY</td>";
                    echo "<td>-</td>";
                    echo "<td>No path in database</td>";
                    echo "</tr>";
                    $warnings[] = "No image path for: $name (ID: $destination_id)";
                }
            }
            
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>Database error: " . $e->getMessage() . "</div>";
    }
    
    // ============================================================
    // STEP 3: CHECK CSS/JS FILES
    // ============================================================
    
    echo "<h2>3. CSS and JavaScript Files Verification</h2>";
    
    $cssFiles = [
        'assets/css/homepage.css',
        'assets/css/common_nav_footer.css',
        'assets/css/7wonders.css',
    ];
    
    $jsFiles = [
        'assets/js/common_nav_footer.js',
        'assets/js/users.js',
        'assets/js/data.js',
    ];
    
    echo "<h3>CSS Files:</h3>";
    echo "<table>";
    echo "<tr><th>File</th><th>Status</th><th>Full Path</th></tr>";
    
    foreach ($cssFiles as $file) {
        $fullPath = $baseDir . '/' . $file;
        $totalChecked++;
        if (file_exists($fullPath)) {
            echo "<tr><td>$file</td><td>$green EXISTS</td><td><code>$fullPath</code></td></tr>";
            $success[] = "CSS file found: $file";
        } else {
            $totalMissing++;
            echo "<tr style='background: #ffebee;'><td>$file</td><td>$red MISSING</td><td><code>$fullPath</code></td></tr>";
            $errors[] = "CSS file missing: $file";
        }
    }
    
    echo "</table>";
    
    echo "<h3>JavaScript Files:</h3>";
    echo "<table>";
    echo "<tr><th>File</th><th>Status</th><th>Full Path</th></tr>";
    
    foreach ($jsFiles as $file) {
        $fullPath = $baseDir . '/' . $file;
        $totalChecked++;
        if (file_exists($fullPath)) {
            echo "<tr><td>$file</td><td>$green EXISTS</td><td><code>$fullPath</code></td></tr>";
            $success[] = "JS file found: $file";
        } else {
            $totalMissing++;
            echo "<tr style='background: #ffebee;'><td>$file</td><td>$red MISSING</td><td><code>$fullPath</code></td></tr>";
            $errors[] = "JS file missing: $file";
        }
    }
    
    echo "</table>";
    
    // ============================================================
    // SUMMARY
    // ============================================================
    
    echo "<h2>Summary Report</h2>";
    
    echo "<div class='summary'>";
    echo "<strong>Total Checks:</strong> $totalChecked<br>";
    echo "<strong>Missing Files:</strong> <span style='color: red;'>$totalMissing</span><br>";
    echo "<strong>Success Rate:</strong> " . round((($totalChecked - $totalMissing) / $totalChecked * 100), 1) . "%<br>";
    echo "</div>";
    
    if (count($errors) > 0) {
        echo "<h3 style='color: red;'>❌ Errors Found:</h3>";
        foreach ($errors as $error) {
            echo "<div class='error'>$error</div>";
        }
    }
    
    if (count($warnings) > 0) {
        echo "<h3 style='color: orange;'>⚠️ Warnings:</h3>";
        foreach ($warnings as $warning) {
            echo "<div class='warning'>$warning</div>";
        }
    }
    
    if (count($errors) == 0 && count($warnings) == 0) {
        echo "<div style='background: #d4edda; border: 2px solid green; padding: 15px; border-radius: 5px; text-align: center;'>";
        echo "<h3 style='color: green; margin: 0;'>✅ All Paths Verified Successfully!</h3>";
        echo "<p>All files and database paths are correct and match your organized folder structure.</p>";
        echo "</div>";
    }
    
    // ============================================================
    // INSTRUCTIONS FOR FIXING
    // ============================================================
    
    if (count($errors) > 0) {
        echo "<h2>How to Fix Missing Files:</h2>";
        echo "<div class='warning' style='background: #fff3cd; padding: 15px;'>";
        echo "<p><strong>For each missing image:</strong></p>";
        echo "<ol>";
        echo "<li>Check if the image file exists in your project (search in Windows Explorer)</li>";
        echo "<li>If it exists, move it to the correct folder path shown above</li>";
        echo "<li>If it doesn't exist, you need to add the image file</li>";
        echo "<li>Run this verification script again to confirm</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    ?>

</div>

</body>
</html>
