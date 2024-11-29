<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตามการผลิต</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap');
        
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: transform 0.3s ease, visibility 0.3s ease;
            z-index: 1000;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            visibility: visible; /* ใช้ควบคุมการแสดงผล */
            transform: translateX(0); /* แสดง Sidebar */
        }
        .sidebar-collapsed {
            visibility: hidden; /* ซ่อน Sidebar */
            transform: translateX(-250px); /* ย้าย Sidebar ออก */
        }

        .toggle-btn {
            position: fixed;
            left: 0;
            top: 5px;
            z-index: 1001;
            background-color: #667eea;
            color: white;
            border: none;
            padding: 10px 15px;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .menu-list {
            list-style-type: none;
            padding: 0;
            margin-top: 50px;
        }
        .menu-list li {
            padding: 15px 20px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        .menu-list li:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .menu-list li.active {
            background-color: rgba(255,255,255,0.2);
        }
        .menu-list li i {
            margin-right: 10px;
        }

        .main-content {
            padding: 20px;
            margin-left: 250px; /* ระยะห่างเมื่อ Sidebar เปิด */
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .main-content-full {
            margin-left: 0; /* ระยะห่างเมื่อ Sidebar ปิด */
        }

        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <ul class="menu-list">
            <li class="active">
                <i class="bi bi-house"></i>แดชบอร์ด
            </li>
            <li onclick="location.href='saveproduct.php'">
                <i class="bi bi-clipboard-plus"></i>บันทึกคำสั่งผลิต
            </li>
            <li>
                <i class="bi bi-cart-plus"></i>สั่งผลิต
            </li>
            <li>
                <i class="bi bi-box"></i>สถานีแผนกดิบ
            </li>
            <li>
                <i class="bi bi-fire"></i>สถานีแผนกอบ
            </li>
            <li>
                <i class="bi bi-egg"></i>สถานีแผนกสุก
            </li>
            <li onclick="location.href='logout.php'" class="text-danger">
                <i class="bi bi-box-arrow-right"></i>ออกจากระบบ
            </li>
        </ul>
    </div>

    <div class="main-content" id="mainContent">
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>คำสั่งผลิตทั้งหมด</h5>
                    <h2 class="text-primary">45</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>กำลังดำเนินการ</h5>
                    <h2 class="text-warning">12</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>เสร็จสิ้น</h5>
                    <h2 class="text-success">33</h2>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            // สลับสถานะของ Sidebar
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-full');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
