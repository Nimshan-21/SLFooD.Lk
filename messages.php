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
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:messages.php');
   exit;
}

// Handle reply submission
if (isset($_POST['add_reply'])) {
   $message_id = $_POST['message_id'];
   $reply = filter_var($_POST['reply'], FILTER_SANITIZE_STRING);

   try {
      $conn->query("SELECT reply FROM `messages` LIMIT 1"); // Check if reply column exists
      $update_reply = $conn->prepare("UPDATE `messages` SET reply = ? WHERE id = ?");
      $update_reply->execute([$reply, $message_id]);
   } catch (PDOException $e) {
      error_log("Database Error: " . $e->getMessage());
      header('location:messages.php?error=column_missing');
      exit;
   }

   $select_message = $conn->prepare("SELECT email FROM `messages` WHERE id = ?");
   $select_message->execute([$message_id]);
   $fetch_message = $select_message->fetch(PDO::FETCH_ASSOC);
   $user_email = $fetch_message['email'];

   $to = $user_email;
   $subject = "Reply to Your Message - SLFooD.LK";
   $message = "Dear User,\n\nYour message has been reviewed. Here is the reply:\n\n" . $reply . "\n\nBest regards,\nSLFooD.LK Admin Team";
   $headers = "From: foods11608@gmail.com"; // Replace with your email address
   mail($to, $subject, $message, $headers);

   header('location:messages.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>
   <link rel="icon" href="images/content.jpg" type="image/x-icon">

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      /* Table styling */
      .messages-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 20px;
         background-color: rgba(255, 255, 255, 0.9);
      }
      .messages-table th, .messages-table td {
         border: 1px solid #ddd;
         padding: 10px;
         text-align: left;
      }
      .messages-table th {
         background-color: #90ee90;
         color: white;
      }
      .messages-table tr {
         animation: fadeIn 0.5s ease-in;
      }
      @keyframes fadeIn {
         from { opacity: 0; transform: translateY(10px); }
         to { opacity: 1; transform: translateY(0); }
      }
      .reply-box {
         background-color: #f0f0f0;
         padding: 10px;
         border-radius: 5px;
         margin-top: 5px;
      }
      .btn {
         background-color: #32cd32;
         color: white;
         padding: 5px 10px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
      }
      .btn:hover {
         background-color: #28a745;
      }
      .delete-btn {
         background-color: #ff0000;
         color: white;
         padding: 5px 10px;
         border-radius: 5px;
         text-decoration: none;
      }
      .delete-btn:hover {
         background-color: #cc0000;
      }
   </style>
</head>
<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<!-- Header from dashboard.php -->
<header style="background-color: #90ee90; padding: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
   <div style="display: flex; align-items: center;">
      <img src="images/content.jpg" alt="SLFooD.LK Logo" style="height: 100px; margin-right: 50px;">
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
         <i class="fas fa-user-shield" style="margin: 0 10px; font-size: 20px; color: #808080;"></i>
         <span style="margin-left: 5px; font-weight: bold;"><?= htmlspecialchars($fetch_profile['name']); ?></span>
      <?php endif; ?>
      <i class="fas fa-search" style="margin: 0 10px; font-size: 20px;"></i>
      <i class="fas fa-shopping-cart" style="margin: 0 10px; font-size: 20px;">(0)</i>
   </div>
</header>

<!-- messages section starts -->
<section class="messages">
   <h1 class="heading">Messages</h1>

   <table class="messages-table">
      <thead>
         <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Number</th>
            <th>Reply</th>
            <th>Action</th>
         </tr>
      </thead>
      <tbody>
         <?php
         $select_messages = $conn->prepare("SELECT * FROM `messages`");
         $select_messages->execute();
         if ($select_messages->rowCount() > 0) {
            while ($fetch_messages = $select_messages->fetch(PDO::FETCH_ASSOC)) {
         ?>
         <tr>
            <td><?= htmlspecialchars($fetch_messages['id']); ?></td>
            <td><?= htmlspecialchars($fetch_messages['name']); ?></td>
            <td><?= htmlspecialchars($fetch_messages['email']); ?></td>
            <td><?= htmlspecialchars($fetch_messages['message']); ?></td>
            <td class="increment-number" data-number="<?= htmlspecialchars($fetch_messages['number']); ?>">0</td>
            <td>
               <?php if (isset($fetch_messages['reply']) && !empty($fetch_messages['reply'])): ?>
                  <div class="reply-box">
                     <strong>Reply:</strong> <?= htmlspecialchars($fetch_messages['reply']); ?>
                  </div>
               <?php else: ?>
                  <form action="" method="POST" style="margin-top: 5px;">
                     <input type="hidden" name="message_id" value="<?= $fetch_messages['id']; ?>">
                     <textarea name="reply" placeholder="Enter reply" required class="box" style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 5px; min-height: 40px;"></textarea>
                     <input type="submit" name="add_reply" value="Send Reply" class="btn" style="margin-top: 5px;">
                  </form>
               <?php endif; ?>
            </td>
            <td><a href="messages.php?delete=<?= $fetch_messages['id']; ?>" class="delete-btn" onclick="return confirm('delete this message?');">delete</a></td>
         </tr>
         <?php
            }
         } else {
            echo '<tr><td colspan="7"><p class="empty" style="text-align: center;">you have no messages</p></td></tr>';
         }
         ?>
      </tbody>
   </table>
</section>
<!-- messages section ends -->

<!-- custom js file link -->
<script src="../js/admin_script.js"></script>
<script>
   // Number increment animation
   document.addEventListener('DOMContentLoaded', function() {
      const incrementElements = document.querySelectorAll('.increment-number');
      incrementElements.forEach(element => {
         const targetNumber = parseInt(element.getAttribute('data-number'));
         let currentNumber = 0;
         const duration = 2000; // 2 seconds
         const step = Math.ceil(targetNumber / (duration / 16)); // Approx. 60 FPS

         function increment() {
            if (currentNumber < targetNumber) {
               currentNumber += step;
               if (currentNumber > targetNumber) currentNumber = targetNumber;
               element.textContent = currentNumber;
               requestAnimationFrame(increment);
            }
         }
         increment();
      });
   });
</script>
</body>
</html>