<?php
include '../components/connect.php';
session_start();

// Initialize message array and CSRF token
$message = [];
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug log file
$debug_log = 'debug_login.log';

if (isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message[] = "Invalid CSRF token!";
        file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] CSRF token mismatch\n", FILE_APPEND);
    } else {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);

        // Input validation
        if (empty($name) || empty($pass)) {
            $message[] = "Username and password are required!";
            file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Empty username or password\n", FILE_APPEND);
        } elseif (strlen($name) > 20 || strlen($pass) > 20) {
            $message[] = "Username or password too long!";
            file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Username or password too long\n", FILE_APPEND);
        } else {
            try {
                $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE name = ?");
                $select_admin->execute([$name]);
                
                file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Query executed for username: $name\n", FILE_APPEND);
                
                if ($select_admin->rowCount() > 0) {
                    $fetch_admin = $select_admin->fetch(PDO::FETCH_ASSOC);
                    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Found user: $name, stored pass: {$fetch_admin['password']}\n", FILE_APPEND);
                    
                    // ✅ Plain text password check
                    if ($pass === $fetch_admin['password']) {
                        $_SESSION['admin_id'] = $fetch_admin['id'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate CSRF token
                        file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Login successful for $name\n", FILE_APPEND);
                        header('location:dashboard.php');
                        exit;
                    } else {
                        $message[] = "Incorrect username or password!";
                        file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Password verification failed for $name\n", FILE_APPEND);
                    }
                } else {
                    $message[] = "Incorrect username or password!";
                    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] No user found for username: $name\n", FILE_APPEND);
                }
            } catch (PDOException $e) {
                $message[] = "Database error: " . htmlspecialchars($e->getMessage());
                file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Database error: " . $e->getMessage() . "\n", FILE_APPEND);
            }
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
    <title>Admin Login - SLFooD.LK</title>
    <link rel="icon" href="../images/content.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --secondary: #dc3545;
            --accent: #ffc107;
            --bg: #f8f9fa;
            --text: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Rubik', sans-serif; }

        /* Background image updated */
        body {
            background: url('../images/food-1024x683.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            border-radius: 25px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 900px;
        }
        .form-container { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(15px); padding: 40px; width: 450px; text-align: center; border-right: 1px solid rgba(255, 255, 255, 0.1); }
        .ad-section { width: 450px; height: 550px; background: url('../images/ad.jpg') center/cover; position: relative; display: flex; align-items: center; justify-content: center; }
        .ad-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(1px); }
        .ad-content { position: relative; z-index: 2; text-align: center; color: white; padding: 20px; }
        .ad-content h3 { font-size: 2.2rem; margin-bottom: 15px; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); background: linear-gradient(45deg, #ff0000, #00ff00, #0000ff); -webkit-background-clip: text; color: transparent; }
        .ad-content p { font-size: 1.1rem; margin: 5px 0; opacity: 0.9; }
        .ad-content .social-links { margin-top: 20px; }
        .ad-content .social-links a { color: white; margin: 0 10px; font-size: 1.5rem; transition: transform 0.3s ease; }
        .ad-content .social-links a:hover { transform: scale(1.2); }

        .logo-section { margin-bottom: 30px; display: flex; align-items: center; justify-content: center; }
        .logo-img { width: 90px; height: 90px; border-radius: 15px; object-fit: cover; border: 3px solid rgba(255, 255, 255, 0.5); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3); animation: logoFloat 3.5s ease-in-out infinite; background: linear-gradient(45deg, #ff0000, #00ff00, #0000ff); }
        @keyframes logoFloat { 0%, 100% { transform: translateY(0px) scale(1); } 50% { transform: translateY(-10px) scale(1.05); } }

        .form-container h3 { font-size: 2.5rem; margin-bottom: 10px; text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4); font-weight: 700; background: linear-gradient(45deg, #ff0000, #00ff00, #0000ff); -webkit-background-clip: text; color: transparent; }
        .brand-text { color: rgba(255, 255, 255, 0.9); font-size: 1.2rem; margin-bottom: 30px; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4); font-weight: 300; }

        .form-container .box { width: 100%; padding: 16px 20px; margin: 15px 0; border: none; border-radius: 15px; background: rgba(255, 255, 255, 0.2); color: #fff; font-size: 1.1rem; transition: all 0.4s; border: 2px solid transparent; }
        .form-container .box:focus { background: rgba(255, 255, 255, 0.3); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2); outline: none; border: 2px solid var(--accent); }
        .form-container .box::placeholder { color: rgba(255, 255, 255, 0.8); font-weight: 300; }

        .form-container .btn { width: 100%; padding: 18px; background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent)); border: none; border-radius: 15px; color: #fff; font-size: 1.2rem; font-weight: 600; cursor: pointer; transition: all 0.4s; margin-top: 25px; text-transform: uppercase; letter-spacing: 1px; position: relative; overflow: hidden; }
        .form-container .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); }
        .form-container .btn::after { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255, 255, 255, 0.3); border-radius: 50%; transform: translate(-50%, -50%); transition: width 0.4s ease, height 0.4s ease; }
        .form-container .btn:hover::after { width: 250px; height: 250px; }

        .message { background: linear-gradient(135deg, #f44336, #d32f2f); color: #fff; padding: 16px 20px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; animation: slideDown 0.5s ease; position: fixed; top: 30px; left: 50%; transform: translateX(-50%); z-index: 1000; min-width: 350px; box-shadow: 0 8px 25px rgba(244, 67, 54, 0.3); }
        @keyframes slideDown { from { opacity: 0; transform: translateX(-50%) translateY(-30px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
    </style>
</head>
<body>
<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<div class="container">
    <section class="form-container">
        <div class="logo-section">
            <img src="../images/content.jpg" alt="SLFooD.LK Logo" class="logo-img" onerror="this.style.display='none'">
        </div>
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <h3>Admin Panel</h3>
            <div class="brand-text">SLFooD.LK - Matara</div>
            <input type="text" name="name" maxlength="20" required placeholder="Enter your username" class="box" value="admin" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="pass" maxlength="20" required placeholder="Enter your password" class="box" value="12345" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="LOGIN NOW" name="submit" class="btn">
        </form>
    </section>
    <div class="ad-section">
        <div class="ad-content">
            <h3>SLFooD.LK</h3>
            <p>Fresh Food Delivery in Matara</p>
            <p><i class="fas fa-phone"></i> 0777831046</p>
            <p><i class="fab fa-whatsapp"></i> 0777831046</p>
            <p><i class="fas fa-envelope"></i> slfoodsrilankan@gmail.com</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="mailto:slfoodsrilankan@gmail.com"><i class="fas fa-envelope"></i></a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9rem;">Created by LYN NIMSHAN</p>
        </div>
    </div>
</div>

<script>
setTimeout(function() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(function(message) {
        message.style.animation = 'slideDown 0.3s ease reverse';
        setTimeout(function() { message.remove(); }, 300);
    });
}, 5000);
</script>
</body>
</html>
