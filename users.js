// users.js - FIXED VERSION

// Initialize user database
const usersDB = [
  {
    username: "shahid@example.com",
    password: "Shahid1234",
    email: "shahid@example.com",
    fullName: "Shahid Khanusiya",
    role: "user",
    bookedTrips: [],
    savedDestinations: [],
    rewardPoints: 1250,
  },
  {
    username: "admin@kindora.com",
    password: "Admin1234",
    email: "admin@kindora.com",
    fullName: "Kindora Admin",
    role: "admin",
    bookedTrips: [],
    savedDestinations: [],
    rewardPoints: 0,
  },
  {
    username: "kindora@gmail.com",
    password: "Kindora1234",
    email: "kindora@gmail.com",
    fullName: "Kindora Admin",
    role: "admin",
    bookedTrips: [],
    savedDestinations: [],
    rewardPoints: 0,
  },
];

// Debug function to check localStorage
function debugLocalStorage() {
  console.log("=== DEBUG: LocalStorage Status ===");
  const users = localStorage.getItem("users");
  if (users) {
    const parsedUsers = JSON.parse(users);
    console.log(`Found ${parsedUsers.length} users in localStorage:`);
    parsedUsers.forEach((user, index) => {
      console.log(`  ${index + 1}. ${user.username} (${user.role})`);
    });
  } else {
    console.log("No users found in localStorage");
  }
  console.log("=====================================");
}

// Initialize localStorage with proper error handling
function initializeUserDatabase() {
  try {
    if (!localStorage.getItem("users")) {
      localStorage.setItem("users", JSON.stringify(usersDB));
      console.log("✅ Initial user database created in localStorage");
    } else {
      console.log("✅ User database already exists in localStorage");
    }
    debugLocalStorage();
  } catch (error) {
    console.error("❌ Error initializing localStorage:", error);
  }
}

// Call initialization
initializeUserDatabase();

// FIXED Login function - NO IMMEDIATE REDIRECT
function login(email, password) {
  console.log("=== DEBUG: Login Attempt ===");
  console.log(`Email input: '${email}'`);
  console.log(`Password length: ${password.length}`);

  try {
    const all = JSON.parse(localStorage.getItem("users")) || [];
    console.log(`Loaded ${all.length} users from localStorage`);

    // Debug: Show all users for comparison
    all.forEach((user, index) => {
      console.log(
        `  User ${index + 1}: '${
          user.username
        }' -> '${user.username.toLowerCase()}' (${user.role})`
      );
    });

    // Find user with case-insensitive email comparison
    const user = all.find((u) => {
      const emailMatch = u.username.toLowerCase() === email.toLowerCase();
      const passwordMatch = u.password === password;
      console.log(
        `Checking ${u.username}: email=${emailMatch}, password=${passwordMatch}`
      );
      return emailMatch && passwordMatch;
    });

    if (user) {
      console.log(`✅ LOGIN SUCCESS: ${user.fullName} (${user.role})`);
      localStorage.setItem("currentUser", JSON.stringify(user));
      console.log("✅ Current user saved to localStorage");
      return { success: true, user: user };
    } else {
      console.log("❌ LOGIN FAILED: No matching user found");
      return { success: false, user: null };
    }
  } catch (error) {
    console.error("❌ Login error:", error);
    return { success: false, user: null };
  }
}

// Register function with improved error handling
function register({ fullName, email, password }) {
  try {
    const existing = JSON.parse(localStorage.getItem("users")) || [];
    if (
      existing.find((u) => u.username.toLowerCase() === email.toLowerCase())
    ) {
      return { success: false, message: "User already exists" };
    }

    existing.push({
      username: email,
      password,
      email,
      fullName,
      role: "user",
      bookedTrips: [],
      savedDestinations: [],
      rewardPoints: 0,
    });

    localStorage.setItem("users", JSON.stringify(existing));
    console.log(`✅ New user registered: ${fullName}`);
    return { success: true, message: "Registration successful" };
  } catch (error) {
    console.error("❌ Registration error:", error);
    return { success: false, message: "Registration failed" };
  }
}

// Logout function
function logoutUser() {
  try {
    localStorage.removeItem("currentUser");
    console.log("✅ User logged out successfully");
    return true;
  } catch (error) {
    console.error("❌ Logout error:", error);
    return false;
  }
}

// Get current user with error handling
function getCurrentUser() {
  try {
    const userStr = localStorage.getItem("currentUser");
    if (userStr) {
      const user = JSON.parse(userStr);
      console.log(`Current user: ${user.fullName} (${user.role})`);
      return user;
    }
    console.log("No current user found");
    return null;
  } catch (error) {
    console.error("❌ Error getting current user:", error);
    return null;
  }
}

// Session validation function
function validateSession() {
  const user = getCurrentUser();
  return user !== null;
}

// Admin validation function
function isAdmin() {
  const user = getCurrentUser();
  return user && user.role === "admin";
}

// Reset localStorage (for debugging)
function resetUserDatabase() {
  localStorage.removeItem("users");
  localStorage.removeItem("currentUser");
  initializeUserDatabase();
  console.log("🔄 User database reset");
}
