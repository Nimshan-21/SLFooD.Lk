<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

// Valid payment statuses
$valid_statuses = ['None', 'Pay', 'Pending', 'Paid', 'Packing', 'Delivery', 'Completed'];

// Update payment status
if(isset($_POST['order_id']) && isset($_POST['payment_status'])){
    $status = $_POST['payment_status'];
    if(in_array($status, $valid_statuses)){
        $update = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
        $update->execute([$status, $_POST['order_id']]);
    }
    header('location: placed_orders.php');
    exit;
}

// Delete order
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_order->execute([$delete_id]);
    header('location:placed_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Placed Orders</title>
<link rel="icon" href="images/content.jpg" type="image/x-icon">

<!-- font awesome cdn link -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<!-- custom css file link -->
<link rel="stylesheet" href="../css/admin_style.css">

<style>
header a:hover { transform: scale(1.05); background-color: #555 !important; color: white !important; }

.orders { padding: 20px; }

.heading { text-align: center; font-size: 2.5rem; color: #fff; margin-bottom: 20px; text-shadow: 2px 2px 5px rgba(0,0,0,0.5); }

.box-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding: 10px; }

.box {
    background-color: #fff;
    border: 2px solid #90ee90;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    text-align: left;
    transition: transform 0.2s;
    max-width: 320px;
}

.box:hover { transform: scale(1.03); }

.box p { margin: 8px 0; font-size: 1rem; color: #333; }

.box p strong { color: #444; }

.button-group { display: flex; justify-content: space-between; margin-top: 15px; }

.receipt-btn, .delete-btn { flex: 1; margin: 5px; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 0.95rem; text-align: center; cursor: pointer; transition: background-color 0.3s; }

.receipt-btn { background-color: #00bfff; color: white; }
.receipt-btn:hover { background-color: #009acd; }

.delete-btn { background-color: #ff0000; color: white; }
.delete-btn:hover { background-color: #cc0000; }

.empty { text-align: center; font-size: 1.5rem; color: #fff; }

.payment-form select { width: 100%; padding: 5px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
</style>
</head>

<body style="background-image: url('images/food-1024x683.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

<header style="background-color: #90ee90; padding: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
   <div style="display: flex; align-items: center;">
      <img src="images/content.jpg" alt="SLFooD.LK Logo" style="height: 100px; margin-right: 50px;">
      <a href="dashboard.php" style="background-color: #00bfff; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Dashboard</a>
      <a href="products.php" style="background-color: #ffd700; color: black; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Products</a>
      <a href="placed_orders.php" style="background-color: #ffa500; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Orders</a>
      <a href="users_accounts.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Users</a>
      <a href="messages.php" style="background-color: #800080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Messages</a>
      <a href="admin_accounts.php" style="background-color: #808080; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Admins</a>
   </div>
   <div style="display: flex; align-items: center;">
      <?php
      $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
      $select_profile->execute([$admin_id]);
      $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
      ?>
      <a href="update_profile.php" style="background-color: #32cd32; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Update Profile</a>
      <a href="../components/admin_logout.php" style="background-color: #ff0000; color: white; padding: 10px 15px; margin: 5px; border-radius: 5px; text-decoration: none;">Logout</a>
      <i class="fas fa-user-shield" style="margin: 0 10px; font-size: 20px; color: #808080;"></i>
      <span style="margin-left: 5px; font-weight: bold;"><?= htmlspecialchars($fetch_profile['name']); ?></span>
   </div>
</header>

<section class="orders">
   <h1 class="heading">Placed Orders</h1>

   <div class="box-container">
      <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders` ORDER BY placed_on DESC");
      $select_orders->execute();
      if ($select_orders->rowCount() > 0) {
         while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            $placed_on = new DateTime($fetch_orders['placed_on']);
            $date = $placed_on->format('Y-m-d');
            $time = $placed_on->format('H:i:s');

            // Total calculation with discount, COD, tax
            $original_total = $fetch_orders['total_price'];
            $discount = $original_total * 0.11; // 11% discount
            $cod_charge = 250;
            $tax = 45;
            $total_payment = ($original_total - $discount) + $cod_charge + $tax;
      ?>
      <div class="box">
         <p><strong>User ID:</strong> <?= $fetch_orders['user_id']; ?></p>
         <p><strong>Name:</strong> <?= htmlspecialchars($fetch_orders['name']); ?></p>
         <p><strong>Email:</strong> <?= htmlspecialchars($fetch_orders['email']); ?></p>
         <p><strong>Address:</strong> <?= htmlspecialchars($fetch_orders['address']); ?></p>
         <p><strong>Total Pay:</strong> RS.<?= number_format($total_payment, 2); ?>/-</p>
         <p style="font-size: 0.85rem; color: #555;">
             (Original: RS.<?= $original_total; ?>, Discount 11%: -RS.<?= number_format($discount,2); ?>, COD: +RS.<?= $cod_charge; ?>, Tax: +RS.<?= $tax; ?>)
         </p>
         <p><strong>Payment Method:</strong> <?= htmlspecialchars($fetch_orders['method']); ?></p>
         <p><strong>Payment Status:</strong>
            <form method="post" class="payment-form">
                <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                <select name="payment_status" onchange="this.form.submit()">
                    <?php foreach($valid_statuses as $status): ?>
                        <option value="<?= $status; ?>" <?= $fetch_orders['payment_status']==$status?'selected':''; ?>><?= $status; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
         </p>
         <p><strong>Date:</strong> <?= $date; ?></p>
         <p><strong>Time:</strong> <?= $time; ?></p>

         <div class="button-group">
            <a href="generate_receipt.php?id=<?= $fetch_orders['id']; ?>" class="receipt-btn">Generate Receipt</a>
            <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
         </div>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
      ?>
   </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
