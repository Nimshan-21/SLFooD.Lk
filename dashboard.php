<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard - SLFooD.LK</title>
   <link rel="icon" href="images/content.jpg" type="image/x-icon"> <

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Chart.js for advanced dashboard chart -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <!-- Custom CSS with button animations -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<!-- Updated Admin Header with colorful buttons and login/register or profile/logout based on session -->
<header style="background-color: #90ee90; padding: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
   <div style="display: flex; align-items: center;">
      <img src="images/content.jpg" alt=" " style="height: 100px; margin-right: 50px;"> <!-- Replace with your actual logo path -->
      <a href="dashboard.php" style="background-color: #00bfff; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Home (Dashboard)</a>
      <a href="products.php" style="background-color: #ffd700; color: black; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Products</a>
      <a href="placed_orders.php" style="background-color: #ffa500; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Orders</a>
      <a href="users_accounts.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Users</a>
      <a href="messages.php" style="background-color: #800080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Messages</a>
      <a href="admin_accounts.php" style="background-color: #808080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Admins</a>
   </div>
   <div style="display: flex; align-items: center;">
      <?php if (!$admin_id): ?>
         <a href="admin_login.php" style="background-color: #32cd32; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Login</a>
         <a href="register_admin.php" style="background-color: #ff69b4; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Register</a>
      <?php else: ?>
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <a href="update_profile.php" style="background-color: #32cd32; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Update Profile</a>
         <a href="../components/admin_logout.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none; transition: transform 0.3s ease, background-color 0.3s;">Logout</a>
         <i class="fas fa-user-shield" style="margin: 0 10px; font-size: 20px; color: #808080;"></i> <!-- Admin icon -->
         <span style="margin-left: 5px; font-weight: bold;"><?= htmlspecialchars($fetch_profile['name']); ?></span>
      <?php endif; ?>
      <i class="fas fa-search" style="margin: 0 10px; font-size: 20px;"></i>
      <i class="fas fa-shopping-cart" style="margin: 0 10px; font-size: 20px;">(0)</i>
   </div>
</header>

<style>
   /* Inline CSS for button hover animations (add to admin_style.css for production) */
   header a:hover {
      transform: scale(1.05);
      background-color: #555 !important;
      color: white !important;
   }
</style>

<!-- Admin Dashboard Section Starts -->
<section class="dashboard">
   <h1 class="heading">Dashboard</h1>

   <div class="box-container">
      <!-- Welcome Box -->
      <div class="box">
         <?php if ($admin_id): ?>
            <h3>welcome!</h3>
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
            <a href="update_profile.php" class="btn">update profile</a>
         <?php endif; ?>
      </div>

      <!-- Total Pendings -->
      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['pending']);
            while ($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)) {
               $total_pendings += $fetch_pendings['total_price'];
            }
         ?>
         <h3><span>RS.</span><?= number_format($total_pendings, 2); ?><span>/-</span></h3>
         <p>total pendings</p>
         <a href="placed_orders.php" class="btn">see orders</a>
      </div>

      <!-- Total Completed -->
      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            while ($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)) {
               $total_completes += $fetch_completes['total_price'];
            }
         ?>
         <h3><span>RS.</span><?= number_format($total_completes, 2); ?><span>/-</span></h3>
         <p>total completes</p>
         <a href="placed_orders.php" class="btn">see orders</a>
      </div>

      <!-- Total Orders -->
      <div class="box">
         <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $numbers_of_orders = $select_orders->rowCount();
         ?>
         <h3><?= $numbers_of_orders; ?></h3>
         <p>total orders</p>
         <a href="placed_orders.php" class="btn">see orders</a>
      </div>

      <!-- Total Products -->
      <div class="box">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $numbers_of_products = $select_products->rowCount();
         ?>
         <h3><?= $numbers_of_products; ?></h3>
         <p>products added</p>
         <a href="products.php" class="btn">see products</a>
      </div>

      <!-- Total Users -->
      <div class="box">
         <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $numbers_of_users = $select_users->rowCount();
         ?>
         <h3><?= $numbers_of_users; ?></h3>
         <p>users accounts</p>
         <a href="users_accounts.php" class="btn">see users</a>
      </div>

      <!-- Total Admins -->
      <div class="box">
         <?php
            $select_admins = $conn->prepare("SELECT * FROM `admin`");
            $select_admins->execute();
            $numbers_of_admins = $select_admins->rowCount();
         ?>
         <h3><?= $numbers_of_admins; ?></h3>
         <p>admins</p>
         <a href="admin_accounts.php" class="btn">see admins</a>
      </div>

      <!-- Total Messages -->
      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $numbers_of_messages = $select_messages->rowCount();
         ?>
         <h3><?= $numbers_of_messages; ?></h3>
         <p>new messages</p>
         <a href="messages.php" class="btn">see messages</a>
      </div>

      <!-- Innovative feature: Sales chart -->
      <div class="box" style="width: 100%;">
         <h3>Sales Overview</h3>
         <canvas id="salesChart" width="400" height="200" style="animation: fadeIn 1s;"></canvas>
      </div>
   </div>
</section>
<!-- Admin Dashboard Section Ends -->

<!-- Custom JS -->
<script src="../js/admin_script.js"></script>

<script>
   // Chart.js script for bar chart
   var ctx = document.getElementById('salesChart').getContext('2d');
   var chart = new Chart(ctx, {
       type: 'bar',
       data: {
           labels: ['Pendings', 'Completes'],
           datasets: [{
               label: 'Sales (RS.)',
               data: [<?= $total_pendings; ?>, <?= $total_completes; ?>],
               backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
               borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
               borderWidth: 1
           }]
       },
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           },
           animation: {
               duration: 2000 
           }
       }
   });
</script>

</body>
</html>