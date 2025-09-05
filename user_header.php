<?php
// Display messages if any (e.g., success or error notifications)
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

<header class="header" style="background: linear-gradient(to right, #aafaa5, #8df593);">

   <section class="flex">

      <a href="home.php" class="logo">
         <img src="images/content.jpg" alt="" width="100" height="100">
      </a>

      <nav class="navbar">
         <a href="home.php" style="background: linear-gradient(to right, #4CAF50, #2E7D32); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">Home</a>
         <a href="about.php" style="background: linear-gradient(to right, #2196F3, #1565C0); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">About</a>
         <a href="menu.php" style="background: linear-gradient(to right, #FFC107, #FF9800); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">Menu</a>
         <a href="orders.php" style="background: linear-gradient(to right, #E91E63, #C2185B); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">Orders</a>
         <a href="contact.php" style="background: linear-gradient(to right, #9C27B0, #7B1FA2); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">Contact</a>
         <a href="admin/admin_login.php" style="background: linear-gradient(to right, #607D8B, #455A64); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin: 0 5px;">Admin Portal</a>
      </nav>

      <div class="icons">
         <?php
            // Count cart items for the current user
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <a href="search.php"><i class="fas fa-search"></i></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_items; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="menu-btn" class="fas fa-bars"></div>
      </div>

      <div class="profile">
         <?php
            // Fetch user profile if logged in
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if($select_profile->rowCount() > 0){
               $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p class="name"><?= $fetch_profile['name']; ?></p>
         <div class="flex">
            <a href="profile.php" class="btn">profile</a>
            <a href="components/user_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
         </div>
         <p class="account">
            <a href="login.php">login</a> or
            <a href="register.php">register</a>
         </p>  
         <?php
            }else{
         ?>
            <p class="name">please login first!</p>
            <a href="login.php" class="btn">login</a>
         <?php
          }
         ?>
      </div>

   </section>

</header>