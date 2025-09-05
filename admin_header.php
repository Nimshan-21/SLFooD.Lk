<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <section class="flex">
      <a href="dashboard.php" class="logo">
         <span style="background: linear-gradient(90deg, #FF0000, #FFA500, #FFFF00, #008000, #0000FF, #4B0082, #EE82EE); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2.5rem; animation: rainbow 5s infinite;"></span>
         <!-- Placeholder for AI-generated logo image -->
         <img src="images/content.jpg" alt="" width="100" height="100" class="">
      </a>

      <nav class="navbar">
         <a href="dashboard.php">HOME</a>
         <a href="products.php">PRODUCTS</a>
         <a href="placed_orders.php">ORDERS</a>
         <a href="admin_accounts.php">ADMINS</a>
         <a href="users_accounts.php">USERS</a>
         <a href="messages.php">MESSAGES</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update_profile.php" class="btn">UPDATE PROFILE</a>
         <div class="flex-btn">
            <a href="admin_login.php" class="option-btn">LOGIN</a>
            <a href="register_admin.php" class="option-btn">REGISTER</a>
         </div>
         <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">LOGOUT</a>
      </div>
   </section>
</header>