<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/x-icon">
    <script src="../assets/js/themeToggle.js"></script>
    <style>
        .modal-title {
            color: black;
        }
        #warrantyAlert {
            display: none; /* ซ่อนปุ่มแจ้งเตือนเริ่มต้น */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../views/dashboard.php"><img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">&nbspAsset Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php
                    $navItems = [
                        ['href' => '../views/dashboard.php', 'label' => 'Dashboard'],
                        ['href' => '../views/view_assets.php', 'label' => 'View Assets']
                    ];
                    foreach ($navItems as $item) {
                        echo '<li class="nav-item" style="margin-right: 10px;">';
                        echo '<a class="nav-link nav-link.dark-mode" href="' . $item['href'] . '">' . $item['label'] . '</a>';
                        echo '</li>';
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="notificationButton">
                        </a>
                    </li>
                </ul>
                <span id="themeToggle" class="ms-3" title="Toggle Theme">
                    <span id="themeIcon">🌙</span>
                </span>
            </div>
        </div>
    </nav>

    <div id="warrantyAlert" class="position-fixed bottom-0 end-0 m-4">
        <button id="alertButton" class="btn btn-danger rounded-circle p-3 position-relative">
            <span class="position-absolute top-50 start-50 translate-middle">!</span>
            <span id="alertCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                0
            </span>
        </button>
    </div>

    <div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">สินทรัพย์ใกล้หมดประกัน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <ul id="warrantyList" class="list-group"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('notificationButton').addEventListener('click', function() {
        fetch('../views/notification.php')
            .then(response => response.text())
            .then(html => {
                const popup = document.createElement('div');
                popup.innerHTML = html;
                document.body.appendChild(popup);
            });
    });

    document.addEventListener("DOMContentLoaded", function () {
        fetch("../views/get_warranty_alerts.php")
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    document.getElementById("alertCount").textContent = data.length;
                    let list = document.getElementById("warrantyList");
                    list.innerHTML = "";
                    data.forEach(asset => {
                        let item = document.createElement("li");
                        item.className = "list-group-item";
                        item.textContent = asset.name + " - หมดประกัน: " + asset.warranty_expiry_date;
                        list.appendChild(item);
                    });

                    document.getElementById("alertButton").addEventListener("click", function () {
                        new bootstrap.Modal(document.getElementById("warrantyModal")).show();
                    });

                    // แสดงปุ่มแจ้งเตือนเมื่อมีข้อมูล
                    document.getElementById("warrantyAlert").style.display = "block";
                }
            });
    });
    </script>
</body>
</html>

