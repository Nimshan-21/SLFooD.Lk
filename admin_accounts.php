<?php
include '../components/connect.php';
session_start();

// Check if admin_id is set
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
   header('location:admin_login.php');
   exit;
}

// Initialize CSRF token
if (!isset($_SESSION['csrf_token'])) {
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check database connection
if (!$conn) {
   $message[] = 'Error: Database connection failed. Please check connect.php configuration.';
} else {
   // Verify admin table exists
   try {
      $conn->query("SELECT 1 FROM `admin` LIMIT 1");
   } catch (PDOException $e) {
      $message[] = 'Error: Admin table not found or inaccessible. Please verify database schema.';
      error_log("Table Check Error: " . $e->getMessage());
   }
}

// Handle admin deletion
if (isset($_POST['delete']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
   $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
   if ($delete_id) {
      try {
         $delete_admin = $conn->prepare("DELETE FROM `admin` WHERE id = ?");
         $delete_admin->execute([$delete_id]);
         $message[] = 'Admin account deleted!';
         header('location:admin_accounts.php');
         exit;
      } catch (PDOException $e) {
         error_log("Delete Error: " . $e->getMessage());
         $message[] = 'Error deleting admin account: ' . (error_reporting() ? $e->getMessage() : 'Please try again.');
      }
   } else {
      $message[] = 'Invalid admin ID for deletion.';
   }
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

try {
   $select_account = $conn->prepare("SELECT id, name FROM `admin` LIMIT :limit OFFSET :offset");
   $select_account->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
   $select_account->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
   $select_account->execute();

   $count_query = $conn->query("SELECT COUNT(*) FROM `admin`");
   $total_accounts = $count_query->fetchColumn();
} catch (PDOException $e) {
   error_log("Query Error: " . $e->getMessage());
   $message[] = 'Error loading admin accounts: ' . (error_reporting() ? $e->getMessage() : 'Please check database connection and try again.');
}

// Fetch profile
try {
   $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
   $select_profile->execute([$admin_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
   $profile_name = $fetch_profile['name'] ?? 'Admin';
} catch (PDOException $e) {
   error_log("Profile Query Error: " . $e->getMessage());
   $profile_name = 'Admin';
}
$total_pages = $total_accounts > 0 ? ceil($total_accounts / $per_page) : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admins Accounts</title>
   <link rel="icon" href="images/content.jpg" type="image/x-icon">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      body {
         background-image: url('images/food-1024x683.jpg');
         background-size: cover;
         background-position: center;
         background-repeat: no-repeat;
         margin: 0;
         font-family: Arial, sans-serif;
         position: relative;
      }
      body::before {
         content: '';
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0,0,0,0.4);
         z-index: 0;
      }
      header {
         background: #90ee90;
         padding: 10px 20px;
         display: flex;
         align-items: center;
         justify-content: space-between;
         position: relative;
         z-index: 1;
      }
      .nav-links a {
         margin: 0 5px;
         padding: 10px 15px;
         border-radius: 5px;
         text-decoration: none;
         color: white;
         transition: transform 0.3s ease;
      }
      .nav-links a:nth-child(1) { background: #00bfff; }
      .nav-links a:nth-child(2) { background: #ffd700; color: black; }
      .nav-links a:nth-child(3) { background: #ffa500; }
      .nav-links a:nth-child(4) { background: #ff0000; }
      .nav-links a:nth-child(5) { background: #800080; }
      .nav-links a:nth-child(6) { background: #808080; }
      .profile {
         display: flex;
         align-items: center;
         gap: 10px;
      }
      .profile span { font-weight: bold; color: #333; }
      .btn, .delete-btn {
         background: linear-gradient(to right, #4CAF50, #45a049);
         border-radius: 10px;
         padding: 10px 15px;
         color: #fff;
         border: none;
         cursor: pointer;
         text-decoration: none;
      }
      .delete-btn { background: linear-gradient(to right, #ff4b4b, #e63939); }
      .accounts {
         padding: 20px;
         position: relative;
         z-index: 1;
      }
      h1.heading { color: white; text-align: center; margin-bottom: 20px; }
      table {
         width: 90%;
         margin: 20px auto;
         border-collapse: collapse;
         background: rgba(255,255,255,0.9);
         border-radius: 15px;
         overflow: hidden;
         border: 1px solid #ddd;
      }
      th, td {
         padding: 12px 15px;
         text-align: center;
         border-bottom: 1px solid rgba(255,255,255,0.3);
      }
      th { background-color: #4CAF50; color: white; }
      .register-btn {
         display: block;
         width: 200px;
         margin: 20px auto;
         text-align: center;
         background: linear-gradient(to right, #4CAF50, #45a049);
         border-radius: 10px;
         padding: 10px;
         color: #fff;
         text-decoration: none;
      }
      .register-btn:hover { transform: translateY(-2px); }
      .empty { text-align: center; font-size: 18px; margin: 20px 0; }
   </style>
</head>
<body>

<header>
   <div style="display: flex; align-items: center; gap: 20px;">
      <img src="images/content.jpg" alt="SLFooD.LK Logo" style="height: 100px;">
      <div class="nav-links">
         <a href="dashboard.php">Home</a>
         <a href="products.php">Products</a>
         <a href="placed_orders.php">Orders</a>
         <a href="users_accounts.php">Users</a>
         <a href="messages.php">Messages</a>
         <a href="admin_accounts.php">Admins</a>
      </div>
   </div>
   <div class="profile">
      <a href="update_profile.php" class="btn">Update Profile</a>
      <a href="../components/admin_logout.php" class="btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
      <i class="fas fa-user-shield" style="font-size: 20px; color: #808080;"></i>
      <span><?= htmlspecialchars($profile_name); ?></span>
   </div>
</header>

<section class="accounts">
   <h1 class="heading">Admins Account</h1>
   <?php
   if (isset($message)) {
      foreach ($message as $msg) {
         $class = strpos($msg, 'Error') !== false ? 'error' : 'success';
         echo '<p class="empty ' . $class . '">' . htmlspecialchars($msg) . '</p>';
      }
   }
   ?>
   <a href="register_admin.php" class="register-btn">Register New Admin</a>

   <table>
      <thead>
         <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php
         if (isset($select_account) && $select_account->rowCount() > 0) {
            while ($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)) {
         ?>
         <tr>
            <td><?= htmlspecialchars($fetch_accounts['id']); ?></td>
            <td><?= htmlspecialchars($fetch_accounts['name']); ?></td>
            <td>
               <?php if ($fetch_accounts['id'] != $admin_id) { ?>
                  <form action="" method="POST" style="display: inline;">
                     <input type="hidden" name="delete_id" value="<?= $fetch_accounts['id']; ?>">
                     <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                     <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Delete this account?');">Delete</button>
                  </form>
               <?php } else { ?>
                  <a href="update_profile.php" class="btn">Update</a>
               <?php } ?>
            </td>
         </tr>
         <?php
            }
         } else {
            echo '<tr><td colspan="3" class="empty">No accounts available</td></tr>';
         }
         ?>
      </tbody>
   </table>

   <div style="margin-top: 20px; text-align: center;">
      <?php if ($page > 1): ?>
         <a href="?page=<?= $page - 1 ?>" class="btn">Previous</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
         <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
         <a href="?page=<?= $page + 1 ?>" class="btn">Next</a>
      <?php endif; ?>
   </div>
</section>

</body>
</html>
