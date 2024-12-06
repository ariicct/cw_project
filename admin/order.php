<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "cw_project");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการส่งคำสั่งอัปเดตสถานะหรือไม่
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $action = $_GET['action'];
    $order_id = $_GET['order_id'];

    // อัปเดตสถานะตามการกระทำ
    if ($action == 'start') {
        $sql = "UPDATE orders SET status='สั่งผลิตแล้ว' WHERE order_id='$order_id'";
    } elseif ($action == 'cancel') {
        $sql = "UPDATE orders SET status='ยกเลิกแล้ว' WHERE order_id='$order_id'";
    }

    if ($conn->query($sql) === TRUE) {
        $message = "อัปเดตสถานะสำเร็จ";
    } else {
        $message = "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
    }
}

// ดึงข้อมูลคำสั่งผลิตจากตาราง orders
$sql = "SELECT order_id, product_id, quantity, cus_id, delivery_date, status FROM orders";
$result = $conn->query($sql);
require '../admin/sidebar_admin.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งผลิต</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding-bottom: 20%;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: auto;
            margin-top: 40px;
            margin-right: 40px;
        }

        h2 {
            font-weight: bold;
            color: #343a40;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            background-color: #343a40;
            color: white;
        }

        .badge {
            font-size: 14px;
        }

        .btn-sm {
            margin: 2px;
        }

        .alert {
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">รายการคำสั่งผลิต</h2>

        <!-- แสดงข้อความแจ้งเตือน -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>รหัสคำสั่ง</th>
                    <th>สินค้า</th>
                    <th>จำนวน</th>
                    <th>ลูกค้า</th>
                    <th>วันที่ส่ง</th>
                    <th>สถานะ</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['order_id'] ?></td>
                            <td><?= $row['product_id'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= $row['cus_id'] ?></td>
                            <td><?= $row['delivery_date'] ?></td>
                            <td>
                                <?php if ($row['status'] == 'บันทึกแล้ว'): ?>
                                    <span class="badge bg-warning text-dark">รอการผลิต</span>
                                <?php elseif ($row['status'] == 'สั่งผลิตแล้ว'): ?>
                                    <span class="badge bg-success">สั่งผลิตแล้ว</span>
                                <?php elseif ($row['status'] == 'ยกเลิกแล้ว'): ?>
                                    <span class="badge bg-danger">ยกเลิกแล้ว</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'บันทึกแล้ว'): ?>
                                    <a href="?action=start&order_id=<?= $row['order_id'] ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-circle"></i> เริ่มผลิต
                                    </a>
                                    <a href="?action=cancel&order_id=<?= $row['order_id'] ?>" class="btn btn-danger btn-sm">
                                        <i class="bi bi-x-circle"></i> ยกเลิก
                                    </a>
                                <?php elseif ($row['status'] == 'สั่งผลิตแล้ว' || $row['status'] == 'ยกเลิกแล้ว'): ?>
                                    <span class="text-muted">ไม่สามารถทำรายการได้</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">ไม่มีคำสั่งผลิต</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
