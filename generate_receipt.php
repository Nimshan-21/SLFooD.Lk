<?php
include '../components/connect.php';

if(!isset($_GET['id'])){
    die("Order ID not provided!");
}

$order_id = $_GET['id'];

// get order
$select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
$select_order->execute([$order_id]);
if($select_order->rowCount() == 0){
    die("Order not found!");
}
$order = $select_order->fetch(PDO::FETCH_ASSOC);

// calculate total with discount, COD, tax
$original_total = $order['total_price'];
$discount = $original_total * 0.11; // 11% discount
$cod_charge = 250;
$tax = 45;
$total_payment = ($original_total - $discount) + $cod_charge + $tax;
?>
<!DOCTYPE html>
<html>
<head>
<title>Receipt - Order #<?= $order['id']; ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9f9f9; }
.receipt { max-width: 600px; margin: auto; padding: 20px; border: 2px dashed #333; background: #fff; }
h2 { text-align: center; margin-bottom: 20px; }
.logo { display: block; margin: 0 auto 15px; max-width: 150px; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
table, th, td { border: 1px solid black; }
th, td { padding: 8px; text-align: center; }
.button-container { text-align: center; margin-top: 20px; }
button { padding: 10px 20px; font-size: 16px; cursor: pointer; margin: 5px; border-radius: 5px; }
</style>
</head>
<body>
<div class="receipt">
   <img src="../images/content.jpg" alt="SLFooD.LK Logo" class="logo">
   <h2>SLFooD.LK - Receipt</h2>
   <p><strong>Order ID:</strong> <?= $order['id']; ?></p>
   <p><strong>Name:</strong> <?= htmlspecialchars($order['name']); ?></p>
   <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
   <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
   <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['method']); ?></p>
   <p><strong>Status:</strong> <?= htmlspecialchars($order['payment_status']); ?></p>
   
   <p><strong>Total Payment:</strong> Rs. <?= number_format($total_payment, 2); ?> /-</p>
   <p style="font-size: 0.85rem; color: #555;">
       (Original: Rs.<?= $original_total; ?>, Discount 11%: -Rs.<?= number_format($discount,2); ?>, COD: +Rs.<?= $cod_charge; ?>, Tax: +Rs.<?= $tax; ?>)
   </p>

   <div class="button-container">
      <button onclick="window.print()">🖨 Print Receipt</button>
      <button onclick="window.location.href='place_order.php'">❌ Close</button>
   </div>
</div>
</body>
</html>
