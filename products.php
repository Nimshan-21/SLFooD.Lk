<?php
// Admin Products Management for SLFooD.LK
// Handles adding, deleting, and displaying products

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
   exit;
}

if (isset($_POST['add_product'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_img/' . $image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if ($select_products->rowCount() > 0) {
      $message[] = 'Product name already exists!';
   } else {
      if ($image_size > 2000000) {
         $message[] = 'Image size is too large';
      } else {
         move_uploaded_file($image_tmp_name, $image_folder);

         $insert_product = $conn->prepare("INSERT INTO `products`(name, category, price, image) VALUES(?,?,?,?)");
         $insert_product->execute([$name, $category, $price, $image]);

         $message[] = 'New product added!';
      }
   }
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   if ($fetch_delete_image && file_exists('../uploaded_img/' . $fetch_delete_image['image'])) {
      unlink('../uploaded_img/' . $fetch_delete_image['image']);
   }
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:products.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>
   <link rel="icon" href="images/content.jpg" type="image/x-icon">

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      /* Header Button Hover */
      header a:hover {
         transform: scale(1.05);
         background-color: #555 !important;
         color: white !important;
      }

      /* Add Products Form Center */
      .add-products {
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 80vh;
      }
      .add-products form {
         background: rgba(255, 255, 255, 0.95);
         padding: 30px;
         border-radius: 10px;
         box-shadow: 0 4px 15px rgba(0,0,0,0.2);
         width: 500px;
         text-align: center;
      }
      .add-products form h1 {
         margin-bottom: 20px;
         font-size: 26px;
         color: #333;
      }
      .add-products form .box {
         width: 100%;
         padding: 15px;
         margin: 10px 0;
         background-color: #ffffff;
         border: 1px solid #ccc;
         border-radius: 5px;
         font-size: 16px;
         height: 50px;
      }
      .add-products form .btn {
         background-color: #ba55d3;
         color: white;
         padding: 12px 20px;
         font-size: 16px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         transition: 0.3s;
         width: 100%;
      }
      .add-products form .btn:hover {
         background-color: #9932cc;
         transform: scale(1.03);
      }
   </style>
</head>
<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; animation: none;">

<!-- Header -->
<header style="background-color: #90ee90; padding: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
   <div style="display: flex; align-items: center;">
      <img src="images/content.jpg" alt="" style="height: 100px; margin-right: 50px;">
      <a href="dashboard.php" style="background-color: #00bfff; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Dashboard</a>
      <a href="products.php" style="background-color: #ffd700; color: black; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Home (Products)</a>
      <a href="placed_orders.php" style="background-color: #ffa500; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Orders</a>
      <a href="users_accounts.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Users</a>
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
      <i class="fas fa-search" style="margin: 0 10px; font-size: 20px;"></i>
      <i class="fas fa-shopping-cart" style="margin: 0 10px; font-size: 20px;">(0)</i>
   </div>
</header>

<!-- add products section starts -->
<section class="add-products">
   <form action="" method="POST" enctype="multipart/form-data">
      <h1>Add Product</h1>
      <input type="text" required placeholder="Enter product name" name="name" maxlength="100" class="box">
      <input type="number" min="0" max="9999999999" required placeholder="Enter product price" 
         name="price" onkeypress="if(this.value.length == 10) return false;" class="box">
      <select name="category" class="box" required>
         <option value="" disabled selected>Select category --</option>
         <option value="main dish">Main Dish</option>
         <option value="fast food">Fast Food</option>
         <option value="drinks">Drinks</option>
         <option value="desserts">Desserts</option>
      </select>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
      <input type="submit" value="Add Product" name="add_product" class="btn">
   </form>
</section>
<!-- add products section ends -->

<!-- show products section starts -->
<section class="show-products" style="padding-top: 0;">
   <div class="box-container">
      <?php
      $show_products = $conn->prepare("SELECT * FROM `products`");
      $show_products->execute();
      if ($show_products->rowCount() > 0) {
         while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="box">
         <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="" style="height: 200px; width: 150px; object-fit: cover;">
         <div class="flex">
            <div class="price"><span>$</span><?= $fetch_products['price']; ?><span>/-</span></div>
            <div class="category"><?= $fetch_products['category']; ?></div>
         </div>
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="flex-btn">
            <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
            <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
         </div>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No products added yet!</p>';
      }
      ?>
   </div>
</section>
<!-- show products section ends -->

<script src="../js/admin_script.js"></script>
</body>
</html>
