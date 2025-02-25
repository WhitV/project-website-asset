<?php
session_start();
require '../models/database.php';
require '../models/logger.php';

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin_code = $_POST['pin_code'];
    $stmt = $conn->prepare("SELECT pin_code_hash FROM pin_codes WHERE id = 1"); // Adjust the query as needed
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && hash('sha256', $pin_code) === $result['pin_code_hash']) {
        $_SESSION['authenticated'] = true;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "รหัส PIN ไม่ถูกต้องกรุณาลองใหม่อีกครั้ง";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter PIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* ธีมดำ */
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

        /* กล่องใส่ PIN */
        .pin-container {
            position: relative;
            background: rgba(30, 30, 30, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            z-index: 2; /* กล่องอยู่หน้าสุด */
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

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
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
            background: #007bff;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        /* ปุ่ม Clear */
        .btn-dark {
            background-color: #222;
            color: white;
            border: none;
        }

        .btn-dark:hover {
            background-color: #444;
        }

        /* แสงเรืองด้านหลังกล่อง PIN */
        .glow-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 250px;
            z-index: 1; /* อยู่หลังกล่อง PIN */
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

    <!-- แสงด้านหลังกล่อง -->
    <div class="glow-wrapper">
        <div class="glow-effect" id="glow"></div>
    </div>

    <!-- กล่อง PIN (อยู่หน้าสุด) -->
    <div class="pin-container" id="pin-box">
        <h2>🔒 Enter PIN</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="pin_code" class="form-label">PIN Code</label>
                <input type="password" name="pin_code" id="pin_code" class="form-control" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
        <button class="btn btn-dark mt-3 w-100" onclick="clearInput()">Clear</button>
    </div>

    <script>
        function clearInput() {
            document.getElementById('pin_code').value = "";
        }

        // ให้แสงเรืองด้านหลังกล่อง PIN ขยับตามเมาส์
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
