<?php
include '../components/connect.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
}
if(isset($_POST['update'])){
   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, price = ? WHERE id = ?");
   $update_product->execute([$name, $category, $price, $pid]);
   $message[] = 'Product updated!';
   $old_image = $_POST['old_image'];
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_img/'.$image;
   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'Images size is too large!';
      }else{
         $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $pid]);
         move_uploaded_file($image_tmp_name, $image_folder);
         unlink('../uploaded_img/'.$old_image);
         $message[] = 'Image updated!';
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>
   <link rel="icon" href="images/LYgjKqzpQb.ico" type="image/x-icon">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      body {
         background: linear-gradient(135deg, #aafaa5, #8df593, #6cdbeb);
         background-size: 200% 200%;
         animation: gradient 15s ease infinite;
      }
      @keyframes gradient {
         0% { background-position: 0% 50%; }
         50% { background-position: 100% 50%; }
         100% { background-position: 0% 50%; }
      }
      .update-product form {
         background: rgba(255, 255, 255, 0.2);
         backdrop-filter: blur(15px);
         border-radius: 20px;
         padding: 30px;
         box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
         border: 1px solid rgba(255, 255, 255, 0.3);
         transition: transform 0.3s ease;
      }
      .update-product form:hover {
         transform: translateY(-5px);
      }
      .update-product .box, .update-product select {
         background: rgba(255, 255, 255, 0.3);
         border-radius: 10px;
         padding: 12px;
         margin: 10px 0;
         color: #fff;
         transition: all 0.3s ease;
      }
      .update-product .box:focus, .update-product select:focus {
         background: rgba(255, 255, 255, 0.5);
         transform: translateY(-2px);
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }
      .update-product .box::placeholder {
         color: rgba(255, 255, 255, 0.7);
      }
      .btn, .option-btn {
         background: linear-gradient(to right, #4CAF50, #45a049);
         border-radius: 10px;
         padding: 10px 15px;
         color: #fff;
         transition: all 0.3s ease;
      }
      .btn:hover, .option-btn:hover {
         background: linear-gradient(to right, #45a049, #4CAF50);
         transform: translateY(-2px);
      }
      .message {
         background: rgba(255, 75, 75, 0.9);
         color: #fff;
         padding: 15px;
         margin-bottom: 20px;
         border-radius: 10px;
         display: flex;
         justify-content: space-between;
         align-items: center;
         animation: slideIn 0.5s ease;
      }
      @keyframes slideIn {
         from { transform: translateY(-20px); opacity: 0; }
         to { transform: translateY(0); opacity: 1; }
      }
      .message i {
         cursor: pointer;
         font-size: 1.8rem;
      }
   </style>
</head>
<body>
<?php include '../components/admin_header.php' ?>
<section class="update-product">
   <h1 class="heading">Update Product</h1>
   <?php
   $update_id = $_GET['update'];
   $show_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $show_products->execute([$update_id]);
   if($show_products->rowCount() > 0){
      while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
      <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <span>Update Name</span>
      <input type="text" required placeholder="Enter product name" name="name" maxlength="100" class="box" value="<?= $fetch_products['name']; ?>">
      <span>Update Price</span>
      <input type="number" min="0" max="9999999999" required placeholder="Enter product price" name="price" onkeypress="if(this.value.length == 10) return false;" class="box" value="<?= $fetch_products['price']; ?>">
      <span>Update Category</span>
      <select name="category" class="box" required>
         <option selected value="<?= $fetch_products['category']; ?>"><?= $fetch_products['category']; ?></option>
         <option value="main dish">Main Dish</option>
         <option value="fast food">Fast Food</option>
         <option value="drinks">Drinks</option>
         <option value="desserts">Desserts</option>
      </select>
      <span>Update Image</span>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
      <div class="flex-btn">
         <input type="submit" value="Update" class="btn" name="update">
         <a href="products.php" class="option-btn">Go Back</a>
      </div>
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">No products added yet!</p>';
   }
   ?>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>