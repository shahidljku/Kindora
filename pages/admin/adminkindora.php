<?php
session_start();
require '../../config.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$current_admin = $_SESSION['full_name'];

// Handle DELETE requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $dest_id = (int)$_POST['dest_id'];
    
    $stmt = $conn->prepare("DELETE FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $dest_id);
    
    if ($stmt->execute()) {
        header("Location: adminkindora.php?deleted=1");
        exit();
    } else {
        echo "<script>alert('❌ Error deleting destination: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle UPDATE requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $dest_id = (int)$_POST['dest_id'];
    $dest_name = trim($_POST['dest_name']);
    $dest_type = trim($_POST['dest_type']);
    $dest_description = trim($_POST['dest_description']);
    $img_url = !empty(trim($_POST['img_url'])) ? trim($_POST['img_url']) : NULL;
    $video_url = !empty(trim($_POST['video_url'])) ? trim($_POST['video_url']) : NULL;
    
    $check = $conn->prepare("SELECT destination_id FROM destinations WHERE name = ? AND destination_id != ?");
    $check->bind_param("si", $dest_name, $dest_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Destination name already exists!');</script>";
    } else {
        $stmt = $conn->prepare(
            "UPDATE destinations SET name = ?, type = ?, description = ?, image_url = ?, video_url = ? WHERE destination_id = ?"
        );
        $stmt->bind_param("sssssi", $dest_name, $dest_type, $dest_description, $img_url, $video_url, $dest_id);
        
        if ($stmt->execute()) {
            header("Location: adminkindora.php?updated=1");
            exit();
        } else {
            echo "<script>alert('❌ Error updating destination: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    $check->close();
}

// Handle CREATE requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && (!isset($_POST['action']) || $_POST['action'] == 'create')) {
    $dest_name = trim($_POST['dest_name']);
    $dest_type = trim($_POST['dest_type']);
    $dest_description = trim($_POST['dest_description']);
    
    $img_url = !empty(trim($_POST['img_url'])) ? trim($_POST['img_url']) : NULL;
    $video_url = !empty(trim($_POST['video_url'])) ? trim($_POST['video_url']) : NULL;

    $check = $conn->prepare("SELECT destination_id FROM destinations WHERE name = ?");
    $check->bind_param("s", $dest_name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Destination already exists!');</script>";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO destinations (name, type, description, image_url, video_url) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $dest_name, $dest_type, $dest_description, $img_url, $video_url);

        if ($stmt->execute()) {
            header("Location: adminkindora.php?success=1");
            exit();
        } else {
            echo "<script>alert('❌ Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    $check->close();
}

// Handle admin registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_admin') {
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);
    
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $admin_email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Email already exists!');</script>";
    } else {
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (username, password, email, full_name, role, reward_points) VALUES (?, ?, ?, ?, 'admin', 0)"
        );
        $stmt->bind_param("ssss", $admin_email, $hashed_password, $admin_email, $admin_name);
        
        if ($stmt->execute()) {
            header("Location: adminkindora.php?admin_added=1");
            exit();
        } else {
            echo "<script>alert('❌ Error adding admin: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    $check->close();
}

// Get dashboard statistics
$stats = [];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as total FROM bookings");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as revenue FROM bookings WHERE status = 'confirmed'");
$stats['total_revenue'] = $result->fetch_assoc()['revenue'] ?? 0;

// Active destinations
$result = $conn->query("SELECT COUNT(*) as total FROM destinations WHERE type != 'temp'");
$stats['active_destinations'] = $result->fetch_assoc()['total'];

// Average rating
$result = $conn->query("SELECT AVG(rating) as avg_rating FROM reviews WHERE status = 'approved'");
$stats['avg_rating'] = round($result->fetch_assoc()['avg_rating'] ?? 4.8, 1);

// Unread notifications count
$result = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE is_read = 0 AND (admin_id IS NULL OR admin_id = " . $_SESSION['user_id'] . ")");
$stats['unread_notifications'] = $result->fetch_assoc()['unread'];

// Show success/error messages
if (isset($_GET['success'])) {
    echo "<script>alert('✅ Destination added successfully!');</script>";
}
if (isset($_GET['updated'])) {
    echo "<script>alert('✅ Destination updated successfully!');</script>";
}
if (isset($_GET['deleted'])) {
    echo "<script>alert('✅ Destination deleted successfully!');</script>";
}
if (isset($_GET['admin_added'])) {
    echo "<script>alert('✅ New admin added successfully!');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kindora Admin Dashboard</title>
    <link rel="icon" type="image/png" href="kindora-logo.ico" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
      }
      .fade-in {
        animation: fadeIn 0.28s ease-in;
      }
      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(8px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .stat-card {
        transition: transform 0.18s ease, box-shadow 0.18s ease;
      }
      .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      }
      .modal-backdrop {
        backdrop-filter: blur(4px);
      }
      .table-responsive {
        -webkit-overflow-scrolling: touch;
        overflow-x: auto;
      }
      .search-highlight {
        background-color: #fef08a !important;
        color: #92400e !important;
        font-weight: 600;
        border-radius: 3px;
        padding: 1px 3px;
      }
    </style>
  </head>
  <body class="bg-gray-50 font-sans min-h-screen flex">
    <!-- Mobile Topbar -->
    <header class="w-full md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-40">
      <div class="flex items-center space-x-3">
        <button id="mobile-menu-toggle" class="text-gray-700 p-2 rounded-md hover:bg-gray-100">☰</button>
        <div class="flex items-center space-x-2">
          <span class="text-2xl">🏖️</span>
          <h1 class="text-lg font-semibold">Kindora Admin</h1>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <button id="mobile-search-toggle" class="text-gray-600 p-2 rounded-md hover:bg-gray-100">🔍</button>
        <button id="mobile-notif-toggle" class="text-gray-600 p-2 rounded-md relative hover:bg-gray-100">
          🔔
          <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $stats['unread_notifications'] ?></span>
        </button>
      </div>
    </header>

    <div class="flex flex-1 min-h-screen">
      <!-- Sidebar -->
      <aside id="sidebar" class="hidden md:block md:w-64 lg:w-64 bg-blue-900 text-white flex-shrink-0">
        <div class="p-6">
          <div class="flex items-center">
            <span class="text-2xl mr-2">🏖️</span>
            <div>
              <div class="text-lg font-bold">Kindora Admin</div>
              <div class="text-xs text-blue-200">Dashboard</div>
            </div>
          </div>
        </div>
        <nav class="mt-6">
          <a href="#" data-section="dashboard" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">📊 <span class="ml-3">Dashboard</span></a>
          <a href="#" data-section="destinations" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">🏝️ <span class="ml-3">Destinations</span></a>
          <a href="#" data-section="bookings" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">📅 <span class="ml-3">Bookings</span></a>
          <a href="#" data-section="customers" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">👥 <span class="ml-3">Customers</span></a>
          <a href="#" data-section="reviews" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">⭐ <span class="ml-3">Reviews</span></a>
          <a href="#" data-section="analytics" class="nav-item flex items-center px-6 py-3 text-blue-100 hover:bg-blue-800 hover:text-white transition-colors">📈 <span class="ml-3">Analytics</span></a>
        </nav>
      </aside>

      <!-- Mobile Sidebar -->
      <div id="mobile-sidebar" class="fixed inset-0 z-50 hidden">
        <div id="mobile-sidebar-backdrop" class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="absolute left-0 top-0 bottom-0 w-64 bg-blue-900 text-white p-6 overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <span class="text-2xl mr-2">🏖️</span>
              <div>
                <div class="text-lg font-bold">Kindora Admin</div>
              </div>
            </div>
            <button id="mobile-sidebar-close" class="text-white p-1 rounded hover:bg-blue-800">✕</button>
          </div>
          <nav class="space-y-1">
            <a href="#" data-section="dashboard" class="block px-3 py-2 rounded hover:bg-blue-800">📊 Dashboard</a>
            <a href="#" data-section="destinations" class="block px-3 py-2 rounded hover:bg-blue-800">🏝️ Destinations</a>
            <a href="#" data-section="bookings" class="block px-3 py-2 rounded hover:bg-blue-800">📅 Bookings</a>
            <a href="#" data-section="customers" class="block px-3 py-2 rounded hover:bg-blue-800">👥 Customers</a>
            <a href="#" data-section="reviews" class="block px-3 py-2 rounded hover:bg-blue-800">⭐ Reviews</a>
            <a href="#" data-section="analytics" class="block px-3 py-2 rounded hover:bg-blue-800">📈 Analytics</a>
          </nav>
        </div>
      </div>

      <!-- Main content area -->
      <div class="flex-1 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="hidden md:flex items-center justify-between bg-white shadow-sm border-b border-gray-200 px-6 py-4 sticky top-0 z-30">
          <div class="flex items-center space-x-4">
            <button id="menu-toggle" class="hidden md:inline-block text-gray-600 hover:bg-gray-100 p-2 rounded">☰</button>
            <h2 id="page-title" class="text-2xl font-semibold text-gray-800">Dashboard</h2>
            
            <!-- SEARCH BAR -->
            <div id="search-container" class="hidden">
              <div class="relative">
                <input
                  id="global-search"
                  type="text"
                  placeholder="Search destinations, bookings, customers..."
                  class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-80"
                />
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <!-- SEARCH TOGGLE BUTTON -->
            <button
              id="toggle-search"
              class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
              🔍
            </button>
            
            <div class="relative">
              <button id="notif-btn" class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                🔔
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $stats['unread_notifications'] ?></span>
              </button>
              <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <div class="p-4 border-b border-gray-200">
                  <h3 class="font-semibold text-gray-800">Notifications</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <?php
                  $notif_query = "SELECT * FROM notifications WHERE (admin_id IS NULL OR admin_id = " . $_SESSION['user_id'] . ") ORDER BY created_at DESC LIMIT 5";
                  $notif_result = $conn->query($notif_query);
                  
                  if ($notif_result && $notif_result->num_rows > 0) {
                      while ($notification = $notif_result->fetch_assoc()) {
                          echo '<div class="p-3 border-b border-gray-100 hover:bg-gray-50' . ($notification['is_read'] ? '' : ' bg-blue-50') . '">
                            <p class="text-sm font-medium text-gray-800">' . htmlspecialchars($notification['title']) . '</p>
                            <p class="text-xs text-gray-600">' . htmlspecialchars($notification['message']) . ' - ' . date('M j, g:i A', strtotime($notification['created_at'])) . '</p>
                          </div>';
                      }
                  } else {
                      echo '<div class="p-3 text-center text-gray-500">No notifications</div>';
                  }
                  ?>
                </div>
              </div>
            </div>

            <button id="add-new-btn" onclick="showAddNewModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">+ Add New</button>

            <div class="relative">
              <button id="user-menu-btn" class="flex items-center space-x-2 hover:bg-gray-100 rounded-lg p-2">
                <span class="text-gray-600"><?= htmlspecialchars($current_admin) ?></span>
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                  <?= strtoupper(substr($current_admin, 0, 2)) ?>
                </div>
              </button>
              <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile Settings</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Account</a>
                <hr class="my-1" />
                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Sign Out</a>
              </div>
            </div>
          </div>
        </header>

        <!-- Main content -->
        <main class="flex-1 p-4 md:p-6 overflow-y-auto">
          <!-- Dashboard Section -->
          <section id="dashboard-section" class="section-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-500 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_bookings']) ?></p>
                    <p class="text-green-600 text-sm">+12% from last month</p>
                  </div>
                  <div class="text-4xl">📅</div>
                </div>
              </div>

              <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-500 text-sm">Revenue</p>
                    <p class="text-3xl font-bold text-gray-800">$<?= number_format($stats['total_revenue'], 0) ?></p>
                    <p class="text-green-600 text-sm">+8% from last month</p>
                  </div>
                  <div class="text-4xl">💰</div>
                </div>
              </div>

              <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-500 text-sm">Active Destinations</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $stats['active_destinations'] ?></p>
                    <p class="text-blue-600 text-sm">2 new this month</p>
                  </div>
                  <div class="text-4xl">🏝️</div>
                </div>
              </div>

              <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-500 text-sm">Customer Rating</p>
                    <p class="text-3xl font-bold text-gray-800"><?= $stats['avg_rating'] ?></p>
                    <p class="text-yellow-600 text-sm">⭐⭐⭐⭐⭐</p>
                  </div>
                  <div class="text-4xl">⭐</div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Recent Bookings -->
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Bookings</h3>
                <div class="space-y-4">
                  <?php
                  $recent_bookings = $conn->query("
                    SELECT b.*, u.full_name, d.name as destination_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.user_id 
                    JOIN destinations d ON b.destination_id = d.destination_id 
                    ORDER BY b.created_at DESC 
                    LIMIT 3
                  ");
                  
                  if ($recent_bookings && $recent_bookings->num_rows > 0) {
                      while ($booking = $recent_bookings->fetch_assoc()) {
                          $status_class = $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                          echo '
                          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                              <p class="font-medium text-gray-800">' . htmlspecialchars($booking['destination_name']) . '</p>
                              <p class="text-sm text-gray-500">' . htmlspecialchars($booking['full_name']) . ' - $' . number_format($booking['total_amount']) . '</p>
                            </div>
                            <span class="' . $status_class . ' px-2 py-1 rounded-full text-xs">' . ucfirst($booking['status']) . '</span>
                          </div>';
                      }
                  }
                  ?>
                </div>
              </div>

              <!-- Popular Destinations -->
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Popular Destinations</h3>
                <div class="space-y-4">
                  <?php
                  $popular_destinations = $conn->query("
                    SELECT d.name, COUNT(b.booking_id) as booking_count 
                    FROM destinations d 
                    LEFT JOIN bookings b ON d.destination_id = b.destination_id 
                    WHERE d.type != 'temp'
                    GROUP BY d.destination_id 
                    ORDER BY booking_count DESC 
                    LIMIT 3
                  ");
                  
                  if ($popular_destinations && $popular_destinations->num_rows > 0) {
                      $icons = ['🏝️', '🗼', '🏯'];
                      $i = 0;
                      while ($dest = $popular_destinations->fetch_assoc()) {
                          echo '
                          <div class="flex items-center justify-between">
                            <div class="flex items-center">
                              <span class="text-2xl mr-3">' . $icons[$i] . '</span>
                              <div>
                                <p class="font-medium text-gray-800">' . htmlspecialchars($dest['name']) . '</p>
                                <p class="text-sm text-gray-500">' . $dest['booking_count'] . ' bookings this month</p>
                              </div>
                            </div>
                            <div class="text-right">
                              <p class="text-sm font-medium text-green-600">+' . (23 - $i * 5) . '%</p>
                            </div>
                          </div>';
                          $i++;
                      }
                  }
                  ?>
                </div>
              </div>
            </div>
          </section>

          <!-- Destinations Section -->
          <section id="destinations-section" class="section-content hidden mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
              <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Manage Destinations</h3>
                <button onclick="showAddDestinationForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Destination</button>
              </div>

              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <?php
                  $sql = "SELECT * FROM destinations WHERE type != 'temp' ORDER BY name";
                  $result = $conn->query($sql);

                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo '
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                          ' . (!empty($row['image_url'])
                            ? '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '" class="w-full h-48 object-cover rounded-lg mb-3" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';" />
                               <div class="text-4xl mb-3 text-center" style="display: none;">🏝️</div>'
                            : '<div class="text-4xl mb-3 text-center">🏝️</div>') . '
                          <h4 class="font-semibold text-gray-800 mb-2">' . htmlspecialchars($row['name']) . '</h4>
                          <p class="text-gray-600 text-sm mb-2"><strong>Type:</strong> ' . htmlspecialchars($row['type']) . '</p>
                          <p class="text-gray-600 text-sm mb-3">' . htmlspecialchars(substr($row['description'], 0, 100)) . '...</p>
                          <div class="flex space-x-2">
                            <button 
                              onclick="editDestination(' . $row['destination_id'] . ', \'' . addslashes($row['name']) . '\', \'' . addslashes($row['type']) . '\', \'' . addslashes($row['description']) . '\', \'' . addslashes($row['image_url']) . '\', \'' . addslashes($row['video_url']) . '\')"
                              class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm hover:bg-blue-200 transition-colors">Edit</button>
                            <button 
                              onclick="deleteDestination(' . $row['destination_id'] . ', \'' . addslashes($row['name']) . '\')"
                              class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm hover:bg-red-200 transition-colors">Delete</button>
                          </div>
                        </div>';
                    }
                  } else {
                    echo "<p class='text-gray-500 col-span-full text-center py-8'>No destinations found.</p>";
                  }
                  ?>
                </div>
              </div>
            </div>
          </section>

          <!-- Bookings Section -->
          <section id="bookings-section" class="section-content hidden mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
              <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Booking Management</h3>
              </div>

              <div class="table-responsive p-4">
                <table class="w-full">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking ID</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Travel Date</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <?php
                    $bookings = $conn->query("
                      SELECT b.*, u.full_name, u.email, d.name as destination_name 
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.user_id 
                      JOIN destinations d ON b.destination_id = d.destination_id 
                      ORDER BY b.created_at DESC
                    ");
                    
                    if ($bookings && $bookings->num_rows > 0) {
                        while ($booking = $bookings->fetch_assoc()) {
                            $status_class = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-blue-100 text-blue-800'
                            ];
                            
                            echo '
                            <tr class="hover:bg-gray-50">
                              <td class="px-4 py-4 text-sm font-medium text-gray-900">#BK' . str_pad($booking['booking_id'], 3, '0', STR_PAD_LEFT) . '</td>
                              <td class="px-4 py-4 text-sm text-gray-900">
                                <div>
                                  <div class="font-medium">' . htmlspecialchars($booking['full_name']) . '</div>
                                  <div class="text-gray-500 text-xs">' . htmlspecialchars($booking['email']) . '</div>
                                </div>
                              </td>
                              <td class="px-4 py-4 text-sm text-gray-900">' . htmlspecialchars($booking['destination_name']) . '</td>
                              <td class="px-4 py-4 text-sm text-gray-900">' . date('M j, Y', strtotime($booking['travel_date'])) . '</td>
                              <td class="px-4 py-4 text-sm text-gray-900 font-semibold">$' . number_format($booking['total_amount']) . '</td>
                              <td class="px-4 py-4">
                                <span class="' . $status_class[$booking['status']] . ' px-2 py-1 rounded-full text-xs">' . ucfirst($booking['status']) . '</span>
                              </td>
                            </tr>';
                        }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- Customers Section -->
          <section id="customers-section" class="section-content hidden mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Management</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $customers = $conn->query("
                  SELECT u.*, 
                    COUNT(b.booking_id) as total_bookings, 
                    IFNULL(SUM(b.total_amount), 0) as total_spent 
                  FROM users u 
                  LEFT JOIN bookings b ON u.user_id = b.user_id 
                  WHERE u.role = 'user' 
                  GROUP BY u.user_id 
                  ORDER BY total_spent DESC
                ");
                
                if ($customers && $customers->num_rows > 0) {
                    while ($customer = $customers->fetch_assoc()) {
                        $initials = strtoupper(substr($customer['full_name'], 0, 2));
                        $colors = ['bg-blue-500', 'bg-pink-500', 'bg-purple-500', 'bg-green-500', 'bg-red-500'];
                        $color = $colors[$customer['user_id'] % 5];
                        
                        echo '
                        <div class="border border-gray-200 rounded-lg p-4">
                          <div class="flex items-center mb-3">
                            <div class="w-12 h-12 ' . $color . ' rounded-full flex items-center justify-center text-white font-semibold mr-3">' . $initials . '</div>
                            <div>
                              <h4 class="font-semibold text-gray-800">' . htmlspecialchars($customer['full_name']) . '</h4>
                              <p class="text-sm text-gray-600">' . htmlspecialchars($customer['email']) . '</p>
                            </div>
                          </div>
                          <div class="text-sm text-gray-600 space-y-1">
                            <p>📅 ' . $customer['total_bookings'] . ' bookings</p>
                            <p>💰 Total spent: $' . number_format($customer['total_spent']) . '</p>
                          </div>
                        </div>';
                    }
                }
                ?>
              </div>
            </div>
          </section>

          <!-- Reviews Section -->
          <section id="reviews-section" class="section-content hidden mt-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
              <h3 class="text-lg font-semibold text-gray-800">Customer Reviews</h3>

              <?php
              $reviews = $conn->query("
                SELECT r.*, u.full_name, d.name as destination_name 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                JOIN destinations d ON r.destination_id = d.destination_id 
                ORDER BY r.created_at DESC
              ");
              
              if ($reviews && $reviews->num_rows > 0) {
                  while ($review = $reviews->fetch_assoc()) {
                      $stars = str_repeat('⭐', $review['rating']);
                      $initials = strtoupper(substr($review['full_name'], 0, 2));
                      $status_class = $review['status'] == 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                      
                      echo '
                      <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                          <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">' . $initials . '</div>
                            <div>
                              <h4 class="font-semibold text-gray-800">' . htmlspecialchars($review['full_name']) . '</h4>
                              <p class="text-sm text-gray-600">' . htmlspecialchars($review['destination_name']) . '</p>
                            </div>
                          </div>
                          <div class="flex items-center space-x-2">
                            <span class="text-yellow-400">' . $stars . '</span>
                            <span class="ml-2 text-sm text-gray-600">' . $review['rating'] . '.0</span>
                            <span class="' . $status_class . ' px-2 py-1 rounded-full text-xs">' . ucfirst($review['status']) . '</span>
                          </div>
                        </div>
                        ' . (!empty($review['title']) ? '<h5 class="font-medium text-gray-800 mb-2">' . htmlspecialchars($review['title']) . '</h5>' : '') . '
                        <p class="text-gray-700 mb-3">' . htmlspecialchars($review['review_text']) . '</p>
                        <div class="text-sm text-gray-500">' . date('M j, Y g:i A', strtotime($review['created_at'])) . '</div>
                      </div>';
                  }
              }
              ?>
            </div>
          </section>

          <!-- Analytics Section -->
          <section id="analytics-section" class="section-content hidden mt-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Revenue</h3>
                <div class="space-y-4">
                  <?php
                  $monthly_revenue = $conn->query("
                    SELECT 
                      MONTH(created_at) as month, 
                      MONTHNAME(created_at) as month_name,
                      SUM(total_amount) as revenue 
                    FROM bookings 
                    WHERE YEAR(created_at) = YEAR(CURDATE()) AND status = 'confirmed'
                    GROUP BY MONTH(created_at), MONTHNAME(created_at)
                    ORDER BY MONTH(created_at)
                  ");
                  
                  $max_revenue = 0;
                  $revenue_data = [];
                  
                  if ($monthly_revenue && $monthly_revenue->num_rows > 0) {
                      while ($month = $monthly_revenue->fetch_assoc()) {
                          $revenue_data[] = $month;
                          if ($month['revenue'] > $max_revenue) {
                              $max_revenue = $month['revenue'];
                          }
                      }
                  }
                  
                  foreach ($revenue_data as $month) {
                      $percentage = $max_revenue > 0 ? ($month['revenue'] / $max_revenue) * 100 : 0;
                      echo '
                      <div class="flex justify-between items-center">
                        <span class="text-gray-600">' . $month['month_name'] . '</span>
                        <div class="flex items-center">
                          <div class="w-32 bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: ' . $percentage . '%"></div>
                          </div>
                          <span class="ml-2 text-gray-800 font-semibold">$' . number_format($month['revenue']) . '</span>
                        </div>
                      </div>';
                  }
                  ?>
                </div>
              </div>

              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance Metrics</h3>
                <div class="space-y-6">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Conversion Rate</span>
                    <div class="flex items-center">
                      <div class="w-24 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 68%"></div>
                      </div>
                      <span class="ml-2 text-green-600 font-semibold">68%</span>
                    </div>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Customer Satisfaction</span>
                    <div class="flex items-center">
                      <div class="w-24 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 94%"></div>
                      </div>
                      <span class="ml-2 text-blue-600 font-semibold">94%</span>
                    </div>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Repeat Bookings</span>
                    <div class="flex items-center">
                      <div class="w-24 bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: 32%"></div>
                      </div>
                      <span class="ml-2 text-purple-600 font-semibold">32%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>

    <!-- Add New Modal (Admin Registration) -->
    <div id="add-new-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal-backdrop">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 transform transition-all">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-800">Add New Admin</h3>
          <button onclick="hideAddNewModal()" class="text-gray-400 hover:text-gray-600 transition-colors">✕</button>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="add_admin">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name *</label>
              <input name="admin_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
              <input name="admin_email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
              <input name="admin_password" type="password" minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required />
            </div>
            <div class="bg-blue-50 p-3 rounded-lg">
              <div class="flex items-center space-x-2">
                <span class="text-blue-600">💡</span>
                <span class="text-sm text-blue-800">New admin will have full access to the dashboard</span>
              </div>
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="hideAddNewModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Add Admin</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Destination Modal -->
    <div id="add-destination-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal-backdrop">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-800">Add New Destination</h3>
          <button onclick="hideAddDestinationForm()" class="text-gray-400 hover:text-gray-600 transition-colors">✕</button>
        </div>
        <form method="post" enctype="multipart/form-data">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Destination Name *</label>
              <input name="dest_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Destination Type *</label>
              <select name="dest_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
                <option value="">Select Type</option>
                <option value="asia">Asia</option>
                <option value="europe">Europe</option>
                <option value="africa">Africa</option>
                <option value="northamerica">North America</option>
                <option value="southamerica">South America</option>
                <option value="australia">Australia</option>
                <option value="antarctica">Antarctica</option>
                <option value="7 wonders">7 Wonders</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
              <textarea name="dest_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Describe this amazing destination..." required></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
              <input name="img_url" type="text" placeholder="/Kindora/path/to/image.jpg" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
              <input name="video_url" type="text" placeholder="/Kindora/path/to/video.mp4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="hideAddDestinationForm()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Add Destination</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Destination Modal -->
    <div id="edit-destination-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal-backdrop">
      <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-800">Edit Destination</h3>
          <button onclick="hideEditDestinationForm()" class="text-gray-400 hover:text-gray-600 transition-colors">✕</button>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="update" />
          <input type="hidden" id="edit-dest-id" name="dest_id" />
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Destination Name *</label>
              <input id="edit-dest-name" name="dest_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Destination Type *</label>
              <select id="edit-dest-type" name="dest_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
                <option value="asia">Asia</option>
                <option value="europe">Europe</option>
                <option value="africa">Africa</option>
                <option value="northamerica">North America</option>
                <option value="southamerica">South America</option>
                <option value="australia">Australia</option>
                <option value="antarctica">Antarctica</option>
                <option value="7 wonders">7 Wonders</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
              <textarea id="edit-dest-description" name="dest_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
              <input id="edit-img-url" name="img_url" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
              <input id="edit-video-url" name="video_url" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="hideEditDestinationForm()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">Cancel</button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Update Destination</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      let currentSection = "dashboard";
      let searchTimeout;

      // SEARCH FUNCTIONALITY
      function initializeSearch() {
        const searchInput = document.getElementById("global-search");
        const searchContainer = document.getElementById("search-container");
        const toggleBtn = document.getElementById("toggle-search");

        // Search toggle
        toggleBtn?.addEventListener("click", () => {
          if (searchContainer.classList.contains("hidden")) {
            searchContainer.classList.remove("hidden");
            searchInput.focus();
          } else {
            searchContainer.classList.add("hidden");
            searchInput.value = "";
            clearSearchResults();
          }
        });

        // Real-time search
        searchInput?.addEventListener("input", (e) => {
          clearTimeout(searchTimeout);
          const query = e.target.value.toLowerCase().trim();
          
          if (query.length >= 2) {
            searchTimeout = setTimeout(() => performSearch(query), 300);
          } else {
            clearSearchResults();
          }
        });
      }

      function performSearch(query) {
        if (!query || query.length < 2) {
          clearSearchResults();
          return;
        }

        const currentSectionEl = document.getElementById(currentSection + "-section");
        if (!currentSectionEl) return;

        let searchableElements = [];

        // Get searchable elements based on current section
        switch (currentSection) {
          case 'destinations':
            searchableElements = currentSectionEl.querySelectorAll('[class*="border border-gray-200 rounded-lg"]');
            break;
          case 'customers':
            searchableElements = currentSectionEl.querySelectorAll('[class*="border border-gray-200 rounded-lg"]');
            break;
          case 'reviews':
            searchableElements = currentSectionEl.querySelectorAll('[class*="border border-gray-200 rounded-lg"]');
            break;
          case 'bookings':
            searchableElements = currentSectionEl.querySelectorAll('tbody tr');
            break;
          default:
            searchableElements = currentSectionEl.querySelectorAll('h3, h4, h5, p, td');
        }

        let foundCount = 0;

        searchableElements.forEach(element => {
          const text = element.textContent.toLowerCase();
          const matches = text.includes(query);
          
          if (matches) {
            element.style.display = '';
            element.classList.remove('search-hidden');
            highlightText(element, query);
            foundCount++;
          } else {
            element.style.display = 'none';
            element.classList.add('search-hidden');
          }
        });

        console.log(`Found ${foundCount} results for "${query}"`);
      }

      function highlightText(element, query) {
        // Remove previous highlights
        element.querySelectorAll('.search-highlight').forEach(el => {
          const parent = el.parentNode;
          parent.replaceChild(document.createTextNode(el.textContent), el);
          parent.normalize();
        });

        // Add new highlights
        const walker = document.createTreeWalker(
          element,
          NodeFilter.SHOW_TEXT,
          null,
          false
        );

        const textNodes = [];
        let node;
        while (node = walker.nextNode()) {
          if (node.textContent.toLowerCase().includes(query)) {
            textNodes.push(node);
          }
        }

        textNodes.forEach(textNode => {
          const text = textNode.textContent;
          const regex = new RegExp(`(${query})`, 'gi');
          const parts = text.split(regex);
          
          if (parts.length > 1) {
            const fragment = document.createDocumentFragment();
            parts.forEach(part => {
              if (part.toLowerCase() === query.toLowerCase()) {
                const span = document.createElement('span');
                span.className = 'search-highlight';
                span.textContent = part;
                fragment.appendChild(span);
              } else if (part) {
                fragment.appendChild(document.createTextNode(part));
              }
            });
            textNode.parentNode.replaceChild(fragment, textNode);
          }
        });
      }

      function clearSearchResults() {
        // Clear highlights
        document.querySelectorAll('.search-highlight').forEach(el => {
          const parent = el.parentNode;
          parent.replaceChild(document.createTextNode(el.textContent), el);
          parent.normalize();
        });

        // Show all elements
        document.querySelectorAll('.search-hidden').forEach(el => {
          el.style.display = '';
          el.classList.remove('search-hidden');
        });
      }

      // SECTION MANAGEMENT
      const sections = document.querySelectorAll(".section-content");
      
      function showSection(name) {
        // Clear search when switching sections
        clearSearchResults();
        document.getElementById("search-container")?.classList.add("hidden");
        const searchInput = document.getElementById("global-search");
        if (searchInput) searchInput.value = "";
        
        sections.forEach((s) => s.classList.add("hidden"));
        const target = document.getElementById(name + "-section");
        if (target) {
          target.classList.remove("hidden");
          target.classList.add("fade-in");
          document.getElementById("page-title").textContent = name.charAt(0).toUpperCase() + name.slice(1);
        }
        currentSection = name;
        hideMobileSidebar();
      }

      // MOBILE SIDEBAR MANAGEMENT
      document.getElementById("mobile-menu-toggle")?.addEventListener("click", () => {
        document.getElementById("mobile-sidebar").classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
      });

      document.getElementById("mobile-sidebar-close")?.addEventListener("click", hideMobileSidebar);
      document.getElementById("mobile-sidebar-backdrop")?.addEventListener("click", hideMobileSidebar);

      function hideMobileSidebar() {
        const ms = document.getElementById("mobile-sidebar");
        if (ms) ms.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
      }

      // NAVIGATION EVENT LISTENERS
      document.querySelectorAll("aside a[data-section], #mobile-sidebar a[data-section]").forEach((a) => {
        a.addEventListener("click", (e) => {
          e.preventDefault();
          const sec = a.getAttribute("data-section");
          if (sec) showSection(sec);
        });
      });

      // DROPDOWN MANAGEMENT
      document.getElementById("notif-btn")?.addEventListener("click", () => {
        document.getElementById("notifications-dropdown").classList.toggle("hidden");
      });

      document.getElementById("user-menu-btn")?.addEventListener("click", () => {
        document.getElementById("user-menu").classList.toggle("hidden");
      });

      document.addEventListener("click", (e) => {
        // Close dropdowns when clicking outside
        if (!e.target.closest("#notifications-dropdown") && !e.target.closest("#notif-btn")) {
          document.getElementById("notifications-dropdown")?.classList.add("hidden");
        }
        if (!e.target.closest("#user-menu") && !e.target.closest("#user-menu-btn")) {
          document.getElementById("user-menu")?.classList.add("hidden");
        }
      });

      // MODAL FUNCTIONS
      function showAddNewModal() {
        document.getElementById("add-new-modal").classList.remove("hidden");
        document.getElementById("add-new-modal").classList.add("flex");
      }

      function hideAddNewModal() {
        document.getElementById("add-new-modal").classList.add("hidden");
        document.getElementById("add-new-modal").classList.remove("flex");
      }

      function showAddDestinationForm() {
        document.getElementById("add-destination-modal").classList.remove("hidden");
        document.getElementById("add-destination-modal").classList.add("flex");
      }

      function hideAddDestinationForm() {
        document.getElementById("add-destination-modal").classList.add("hidden");
        document.getElementById("add-destination-modal").classList.remove("flex");
      }

      function showEditDestinationForm() {
        document.getElementById("edit-destination-modal").classList.remove("hidden");
        document.getElementById("edit-destination-modal").classList.add("flex");
      }

      function hideEditDestinationForm() {
        document.getElementById("edit-destination-modal").classList.add("hidden");
        document.getElementById("edit-destination-modal").classList.remove("flex");
      }

      function editDestination(id, name, type, description, imageUrl, videoUrl) {
        document.getElementById("edit-dest-id").value = id;
        document.getElementById("edit-dest-name").value = name;
        document.getElementById("edit-dest-type").value = type;
        document.getElementById("edit-dest-description").value = description;
        document.getElementById("edit-img-url").value = imageUrl || '';
        document.getElementById("edit-video-url").value = videoUrl || '';
        showEditDestinationForm();
      }

      function deleteDestination(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="dest_id" value="${id}">
          `;
          document.body.appendChild(form);
          form.submit();
        }
      }

      // INITIALIZATION
      document.addEventListener("DOMContentLoaded", () => {
        showSection("dashboard");
        initializeSearch();
      });

      // KEYBOARD SHORTCUTS
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          hideMobileSidebar();
          hideAddNewModal();
          hideAddDestinationForm();
          hideEditDestinationForm();
        }
        
        // Ctrl+F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
          e.preventDefault();
          document.getElementById("toggle-search")?.click();
        }
      });
    </script>
  </body>
</html>
