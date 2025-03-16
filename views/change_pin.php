<?php
session_start();
require '../models/database.php';
require '../models/logger.php';

$db = new Database();
$conn = $db->getConnection();
$logger = new Logger();
$logger->log('User accessed change_pin.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pin = $_POST['current_pin'];
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];

    $stmt = $conn->prepare("SELECT pin_code_hash FROM pin_codes WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && hash('sha256', $current_pin) === $result['pin_code_hash']) {
        if ($new_pin === $confirm_pin) {
            $new_pin_hash = hash('sha256', $new_pin);
            $update_stmt = $conn->prepare("UPDATE pin_codes SET pin_code_hash = :new_pin_hash WHERE id = 1");
            $update_stmt->bindParam(':new_pin_hash', $new_pin_hash);
            $update_stmt->execute();
            $success = "เปลี่ยนรหัส PIN สำเร็จ";
            $logger->log('PIN changed successfully');
        } else {
            $error = "รหัส PIN ใหม่และการยืนยันไม่ตรงกัน";
            $logger->log('PIN change failed: PINs do not match');
        }
    } else {
        $error = "รหัส PIN ปัจจุบันไม่ถูกต้อง";
        $logger->log('PIN change failed: Incorrect current PIN');
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัส PIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            overflow: hidden;
            position: relative;
        }

        .pin-container {
            position: relative;
            background: rgba(30, 30, 30, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            z-index: 2;
        }

        .pin-container h2 {
            margin-bottom: 20px;
            font-weight: bold;
            color: #ffffff;
        }

        .error {
            color: #ff4d4d;
            font-size: 14px;
        }

        .success {
            color: #4dff4d;
            font-size: 14px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 0px 0;
            border: 2px solid #333;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
            background-color: #1e1e1e;
            color: white;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(135deg, #0056b3, #007bff);
        }

        .btn-dark {
            background-color: #222;
            color: white;
            border: none;
            margin-top: 10px;
        }

        .btn-dark:hover {
            background-color: #444;
        }

        .back-link {
            color: #f8f9fa;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #e2e6ea;
        }

        .glow-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 250px;
            z-index: 1;
        }

        .glow-effect {
            position: absolute;
            width: 150%;
            height: 150%;
            top: 50%;
            left: 50%;
            background: radial-gradient(circle, rgba(0, 132, 255, 0.6), rgba(0, 0, 0, 0));
            border-radius: 50%;
            filter: blur(50px);
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease-out;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="glow-wrapper">
        <div class="glow-effect" id="glow"></div>
    </div>

    <div class="pin-container" id="pin-box">
        <a href="pin.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
            </svg>
            กลับไปหน้า PIN
        </a>
        <h2>🔒 เปลี่ยนรหัส PIN</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="current_pin" class="form-label">รหัส PIN ปัจจุบัน</label>
                <input type="password" name="current_pin" id="current_pin"  maxlength="6" required>
            </div>
            <div class="mb-3">
                <label for="new_pin" class="form-label">รหัส PIN ใหม่</label>
                <input type="password" name="new_pin" id="new_pin" class="form-control" maxlength="6" required>
            </div>
            <div class="mb-3">
                <label for="confirm_pin" class="form-label">ยืนยันรหัส PIN ใหม่</label>
                <input type="password" name="confirm_pin" id="confirm_pin" class="form-control" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">เปลี่ยนรหัส PIN</button>
        </form>
    </div>

    <script>
        document.addEventListener('mousemove', (event) => {
            const glow = document.getElementById('glow');
            const glowWrapper = document.querySelector('.glow-wrapper');
            const boxRect = glowWrapper.getBoundingClientRect();
            const x = event.clientX - boxRect.left - boxRect.width / 2;
            const y = event.clientY - boxRect.top - boxRect.height / 2;

            glow.style.transform = `translate(-50%, -50%) translate(${x / 5}px, ${y / 5}px)`;
        });
    </script>
</body>
</html>
