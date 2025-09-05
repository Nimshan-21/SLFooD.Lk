<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_users->execute([$delete_id]);
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_order->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   header('location:users_accounts.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users Accounts</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      body {
         background-image: url('images/food-1024x683.jpg');
         background-size: cover;
         background-position: center;
         background-repeat: no-repeat;
         margin: 0;
         font-family: Arial, sans-serif;
      }

      header a:hover {
         transform: scale(1.05);
         background-color: #555 !important;
         color: white !important;
      }

      section.accounts {
         padding: 30px;
         background: rgba(255, 255, 255, 0.85);
         margin: 20px;
         border-radius: 15px;
      }

      section.accounts h1.heading {
         text-align: center;
         font-size: 2rem;
         margin-bottom: 20px;
         color: #333;
         text-transform: uppercase;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
         gap: 20px;
      }

      .box {
         background: white;
         border-radius: 12px;
         box-shadow: 0 4px 8px rgba(0,0,0,0.2);
         padding: 20px;
         text-align: center;
         transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .box:hover {
         transform: translateY(-5px);
         box-shadow: 0 8px 16px rgba(0,0,0,0.3);
      }

      .box p {
         margin: 10px 0;
         font-size: 16px;
         color: #333;
      }

      .delete-btn {
         display: inline-block;
         padding: 8px 12px;
         background: #ff4d4d;
         color: #fff;
         border-radius: 5px;
         text-decoration: none;
         font-weight: bold;
         transition: background 0.3s ease;
      }

      .delete-btn:hover {
         background: #cc0000;
      }

      .empty {
         text-align: center;
         font-size: 18px;
         color: #666;
         grid-column: 1 / -1;
      }
   </style>
</head>
<body>

<!-- Header -->
<header style="background-color: #90ee90; padding: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
   <div style="display: flex; align-items: center;">
      <img src="images/content.jpg" alt="SLFooD.LK Logo" style="height: 100px; margin-right: 50px;">
      <a href="dashboard.php" style="background-color: #00bfff; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Dashboard</a>
      <a href="products.php" style="background-color: #ffd700; color: black; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Products</a>
      <a href="placed_orders.php" style="background-color: #ffa500; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Orders</a>
      <a href="users_accounts.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Home(Users)</a>
      <a href="messages.php" style="background-color: #800080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Messages</a>
      <a href="admin_accounts.php" style="background-color: #808080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Admins</a>
   </div>
   <div style="display: flex; align-items: center;">
      <?php if (!$admin_id): ?>
         <a href="admin_login.php" style="background-color: #32cd32; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Login</a>
         <a href="register_admin.php" style="background-color: #ff69b4; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Register</a>
      <?php else: ?>
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <a href="update_profile.php" style="background-color: #32cd32; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Update Profile</a>
         <a href="../components/admin_logout.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Logout</a>
         <i class="fas fa-user-shield" style="margin: 0 10px; font-size: 20px; color: #808080;"></i>
         <span style="margin-left: 5px; font-weight: bold;"><?= htmlspecialchars($fetch_profile['name']); ?></span>
      <?php endif; ?>
   </div>
</header>

<!-- user accounts section starts -->
<section class="accounts">
   <h1 class="heading">Users Accounts</h1>

   <div class="box-container">
      <?php
      $select_account = $conn->prepare("SELECT * FROM `users`");
      $select_account->execute();
      if ($select_account->rowCount() > 0) {
         while ($fetch_accounts = $select_account->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="box">
         <p><strong>User ID:</strong> <?= $fetch_accounts['id']; ?></p>
         <p><strong>Name:</strong> <?= $fetch_accounts['name']; ?></p>
         <p><strong>Email:</strong> <?= $fetch_accounts['email']; ?></p>
         <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" class="delete-btn" onclick="return confirm('Delete this account?');">Delete</a>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No accounts available</p>';
      }
      ?>
   </div>
</section>
<!-- user accounts section ends -->

<script src="../js/admin_script.js"></script>
</body>
</html>
