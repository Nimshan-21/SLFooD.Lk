<?php
include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

$message = [];
$debug_log = 'debug_accounts.log';

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
    $cpass = filter_var($_POST['cpass'], FILTER_SANITIZE_STRING);

    if (empty($name) || empty($pass) || empty($cpass)) {
        $message[] = 'All fields are required!';
    } elseif (strlen($name) > 20 || strlen($pass) > 20) {
        $message[] = 'Username or password too long!';
    } elseif ($pass !== $cpass) {
        $message[] = 'Confirm password does not match!';
    } else {
        $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ?");
        $select_admin->execute([$name]);
        if ($select_admin->rowCount() > 0) {
            $message[] = 'Username already exists!';
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $insert_admin = $conn->prepare("INSERT INTO `admin`(name, password) VALUES(?,?)");
            $insert_admin->execute([$name, $hashed_pass]);
            $message[] = 'New admin registered successfully!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin - SLFooD.LK</title>
    <link rel="icon" href="../images/content.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #aafaa5, #8df593, #6cdbeb);
            background-size: 200% 200%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        header {
            background-color: #90ee90;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            width: 100%;
        }
        header img {
            height: 80px;
            margin-right: 20px;
        }
        header a {
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 5px;
            color: #fff;
            transition: all 0.3s ease;
        }
        header a.home { background-color: #00bfff; color: white; }
        header a.products { background-color: #ffd700; color: black; }
        header a.orders { background-color: #ffa500; color: white; }
        header a.users { background-color: #ff0000; color: white; }
        header a.messages { background-color: #800080; color: white; }
        header a.admins { background-color: #808080; color: white; }
        header a.login { background-color: #32cd32; color: white; }
        header a.register { background-color: #ff69b4; color: white; }
        header a.logout { background-color: #ff0000; color: white; }
        header a:hover { opacity: 0.8; transform: scale(1.05); }
        .form-container {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            width: 400px;
            margin: 50px auto;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            text-align: center;
            animation: slideUp 0.7s ease forwards;
        }
        @keyframes slideUp {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .form-container h3 {
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 25px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .form-container .box {
            width: 100%;
            padding: 15px;
            margin: 12px 0;
            border: none;
            border-radius: 12px;
            background: rgba(255,255,255,0.25);
            color: #fff;
            font-size: 1.7rem;
        }
        .form-container .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #4CAF50, #45a049);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 1.9rem;
            cursor: pointer;
            transition: all 0.4s ease;
        }
        .form-container .btn:hover {
            background: linear-gradient(to right, #45a049, #4CAF50);
            transform: translateY(-3px);
        }
        .message {
            background: rgba(255,75,75,0.9);
            color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message i { cursor: pointer; }
    </style>
</head>
<body>

<header>
    <div style="display:flex;align-items:center;">
        <img src="images/content.jpg" alt="Logo">
        <a href="dashboard.php" class="home">HOME</a>
        <a href="products.php" class="products">PRODUCTS</a>
        <a href="orders.php" class="orders">ORDERS</a>
        <a href="users.php" class="users">USERS</a>
        <a href="messages.php" class="messages">MESSAGES</a>
        <a href="admin_accounts.php" class="admins">ADMINS</a>
    </div>
    <div style="display:flex;align-items:center;">
        <?php if(!$admin_id): ?>
            <a href="admin_login.php" class="login">LOGIN</a>
            <a href="register_admin.php" class="register">REGISTER</a>
        <?php else: ?>
            <a href="update_profile.php" class="register">UPDATE PROFILE</a>
            <a href="../components/admin_logout.php" class="logout">LOGOUT</a>
        <?php endif; ?>
    </div>
</header>

<section class="form-container">
    <?php if(!empty($message)): ?>
        <?php foreach($message as $msg): ?>
            <div class="message"><span><?= htmlspecialchars($msg) ?></span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>
        <?php endforeach; ?>
    <?php endif; ?>
    <form action="" method="POST">
        <h3>Register New Admin</h3>
        <input type="text" name="name" maxlength="20" required placeholder="Enter your username" class="box" oninput="this.value=this.value.replace(/\s/g,'')">
        <input type="password" name="pass" maxlength="20" required placeholder="Enter your password" class="box" oninput="this.value=this.value.replace(/\s/g,'')">
        <input type="password" name="cpass" maxlength="20" required placeholder="Confirm your password" class="box" oninput="this.value=this.value.replace(/\s/g,'')">
        <input type="submit" value="Register Now" name="submit" class="btn">
    </form>
</section>

<script>
    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => msg.remove());
    }, 5000);
</script>

</body>
</html>
