<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

$message = [];

// Handle profile update
if (isset($_POST['submit'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   
   if (!empty($name)) {
      $select_name = $conn->prepare("SELECT * FROM `admin` WHERE name = ? AND id != ?");
      $select_name->execute([$name, $admin_id]);
      if ($select_name->rowCount() > 0) {
         $message[] = 'Username already taken!';
      } else {
         $update_name = $conn->prepare("UPDATE `admin` SET name = ? WHERE id = ?");
         $update_name->execute([$name, $admin_id]);
         $message[] = 'Username updated successfully!';
      }
   }

   if (!empty($_POST['old_pass'])) {
      $select_old_pass = $conn->prepare("SELECT password FROM `admin` WHERE id = ?");
      $select_old_pass->execute([$admin_id]);
      $prev_pass = $select_old_pass->fetch(PDO::FETCH_ASSOC)['password'];

      if (!password_verify($_POST['old_pass'], $prev_pass)) {
         $message[] = 'Old password not matched!';
      } elseif ($_POST['new_pass'] !== $_POST['confirm_pass']) {
         $message[] = 'Confirm password not matched!';
      } elseif (empty($_POST['new_pass'])) {
         $message[] = 'Please enter a new password!';
      } else {
         $update_pass = $conn->prepare("UPDATE `admin` SET password = ? WHERE id = ?");
         $update_pass->execute([password_hash($_POST['new_pass'], PASSWORD_DEFAULT), $admin_id]);
         $message[] = 'Password updated successfully!';
      }
   } elseif (!empty($_POST['new_pass']) || !empty($_POST['confirm_pass'])) {
      $message[] = 'Please enter your old password!';
   }
}

// Fetch profile
$select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Profile - SLFooD.LK</title>
<link rel="icon" href="images/content.jpg" type="image/x-icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="../css/admin_style.css">
<style>
body {
    background-image: url('images/food-1024x683.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    font-family: Arial, sans-serif;
}

/* Header like user_accounts.php */
header {
    background-color: rgba(144,238,144,0.9);
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}
header .left a {
    margin: 0 5px;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: transform 0.3s ease, background-color 0.3s;
}
header .left a:hover {
    transform: scale(1.05);
    background-color: #555 !important;
    color: white !important;
}
header img {
    height: 100px;
    margin-right: 50px;
}

/* Profile form glass effect */
.form-container {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 30px;
    width: 350px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    text-align: center;
    margin: 40px auto;
}
.form-container h3 {
    color: #fff;
    font-size: 2.2rem;
    margin-bottom: 20px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.form-container .box {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
    font-size: 1.6rem;
}
.form-container .btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(to right, #4CAF50, #45a049);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 1.8rem;
    cursor: pointer;
}
.form-container .btn:hover {
    background: linear-gradient(to right, #45a049, #4CAF50);
}

/* Message box */
.message {
    background: rgba(255, 75, 75, 0.9);
    color: #fff;
    padding: 15px;
    margin: 20px auto;
    width: 350px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.message i {
    cursor: pointer;
}
</style>
</head>
<body>

<!-- Header -->
<header>
   <div class="left" style="display:flex; align-items:center;">
      <img src="images/content.jpg" alt="SLFooD.LK Logo">
      <a href="dashboard.php" style="background-color:#00bfff;color:white;">Dashboard</a>
      <a href="products.php" style="background-color:#ffd700;color:black;">Products</a>
      <a href="placed_orders.php" style="background-color:#ffa500;color:white;">Orders</a>
      <a href="users_accounts.php" style="background-color:#ff0000;color:white;">Users</a>
      <a href="messages.php" style="background-color:#800080;color:white;">Messages</a>
      <a href="admin_accounts.php" style="background-color:#808080;color:white;">Admins</a>
   </div>
   <div class="right" style="display:flex; align-items:center;">
      <a href="update_profile.php" style="background-color:#32cd32;color:white;padding:10px 15px;border-radius:5px;">Update Profile</a>
      <a href="../components/admin_logout.php" style="background-color:#ff0000;color:white;padding:10px 15px;border-radius:5px;">Logout</a>
   </div>
</header>

<!-- Messages -->
<?php
if (!empty($message)) {
   foreach ($message as $msg) {
      echo '<div class="message"><span>'.htmlspecialchars($msg).'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<!-- Update Profile Form -->
<section class="form-container">
   <form action="" method="POST">
      <h3>Update Profile</h3>
      <input type="text" name="name" maxlength="20" class="box" placeholder="<?= htmlspecialchars($fetch_profile['name']); ?>">
      <input type="password" name="old_pass" maxlength="20" placeholder="Enter your old password" class="box">
      <input type="password" name="new_pass" maxlength="20" placeholder="Enter your new password" class="box">
      <input type="password" name="confirm_pass" maxlength="20" placeholder="Confirm your new password" class="box">
      <input type="submit" value="Update Now" name="submit" class="btn">
   </form>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
