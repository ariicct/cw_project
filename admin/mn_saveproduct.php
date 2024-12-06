<?php
// เรียกใช้ไฟล์ connection.php
include '../connect/connection.php';

session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php"); // เปลี่ยนกลับไปหน้าล็อกอิน
    exit();
}

// ประกาศตัวแปรเริ่มต้น
$product_list = [];
$error_message = '';
$success_message = '';

// ดึงรายชื่อสินค้า
$query_products = "SELECT product_id, product_name FROM products";
$result_products = mysqli_query($conn, $query_products);
while ($row = mysqli_fetch_assoc($result_products)) {
    $product_list[$row['product_id']] = $row['product_name'];
}

// บันทึกข้อมูลคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $customer_address = mysqli_real_escape_string($conn, $_POST['customer_address']);
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($customer_name) || empty($customer_phone) || empty($customer_address) || empty($product_id) || empty($quantity) || empty($delivery_date)) {
        $error_message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif ($quantity <= 0) {
        $error_message = "จำนวนสินค้าต้องมากกว่า 0";
    } else {
        // ตรวจสอบว่าลูกค้าใหม่หรือไม่
        $check_customer_query = "SELECT cus_id FROM customer WHERE cus_name = '$customer_name' AND cus_number = '$customer_phone'";
        $result_check = mysqli_query($conn, $check_customer_query);

        if (mysqli_num_rows($result_check) > 0) {
            $row = mysqli_fetch_assoc($result_check);
            $cus_id = $row['cus_id'];
        } else {
            // เพิ่มลูกค้าใหม่
            $get_new_cus_id_query = "SELECT LPAD(COALESCE(MAX(CAST(cus_id AS UNSIGNED)), 0) + 1, 3, '0') AS new_cus_id FROM customer";
            $result_new_cus_id = mysqli_query($conn, $get_new_cus_id_query);
            $row_new_cus_id = mysqli_fetch_assoc($result_new_cus_id);
            $cus_id = $row_new_cus_id['new_cus_id'];

            $insert_customer_query = "INSERT INTO customer (cus_id, cus_name, cus_number, cus_address) 
                                      VALUES ('$cus_id', '$customer_name', '$customer_phone', '$customer_address')";
            mysqli_query($conn, $insert_customer_query);
        }

        // เพิ่มคำสั่งซื้อใหม่
        $get_new_order_id_query = "SELECT CONCAT('P', LPAD(COALESCE(MAX(CAST(SUBSTRING(order_id, 2) AS UNSIGNED)), 0) + 1, 4, '0')) AS new_order_id FROM orders";
        $result_new_order_id = mysqli_query($conn, $get_new_order_id_query);
        $row_new_order_id = mysqli_fetch_assoc($result_new_order_id);
        $order_id = $row_new_order_id['new_order_id'];

        $insert_order_query = "INSERT INTO orders (order_id, product_id, quantity, delivery_date, cus_id, order_date, status) 
                               VALUES ('$order_id', '$product_id', '$quantity', '$delivery_date', '$cus_id', NOW(), 'บันทึกแล้ว')";

        if (mysqli_query($conn, $insert_order_query)) {
            $success_message = "บันทึกคำสั่งผลิตสำเร็จ";
        } else {
            $error_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
    }
}
// เตรียมข้อมูลสำหรับแก้ไขคำสั่งซื้อ
$edit_order = null;
if (isset($_GET['edit_order_id'])) {
    $edit_order_id = mysqli_real_escape_string($conn, $_GET['edit_order_id']);
    $query_edit_order = "SELECT o.order_id, c.cus_name, c.cus_number, c.cus_address, 
                                o.product_id, o.quantity, o.delivery_date
                         FROM orders o
                         JOIN customer c ON o.cus_id = c.cus_id
                         WHERE o.order_id = '$edit_order_id'";
    $result_edit_order = mysqli_query($conn, $query_edit_order);
    $edit_order = mysqli_fetch_assoc($result_edit_order);
}

// แก้ไขคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_order'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $delivery_date = mysqli_real_escape_string($conn, $_POST['delivery_date']);
    

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($product_id) || empty($quantity) || empty($delivery_date)) {
        $error_message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif ($quantity <= 0) {
        $error_message = "จำนวนสินค้าต้องมากกว่า 0";
    } else {
        // แก้ไขคำสั่งซื้อ
        $update_order_query = "UPDATE orders 
                               SET product_id = '$product_id', 
                                   quantity = '$quantity', 
                                   delivery_date = '$delivery_date',
                               WHERE order_id = '$order_id'";

        if (mysqli_query($conn, $update_order_query)) {
            $success_message = "แก้ไขคำสั่งผลิตสำเร็จ";
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . mysqli_error($conn);
        }
    }
}

// ลบคำสั่งซื้อ
if (isset($_GET['delete_order_id'])) {
    $order_id_to_delete = mysqli_real_escape_string($conn, $_GET['delete_order_id']);

    // ลบคำสั่งซื้อ
    $delete_order_query = "DELETE FROM orders WHERE order_id = '$order_id_to_delete'";
    
    if (mysqli_query($conn, $delete_order_query)) {
        $success_message = "ลบคำสั่งผลิตสำเร็จ";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการลบข้อมูล: " . mysqli_error($conn);
    }
}

// ดึงข้อมูลคำสั่งซื้อทั้งหมด
$query_orders = "SELECT o.order_id, c.cus_name, c.cus_number, p.product_name, o.quantity, o.delivery_date, o.status
                 FROM orders o
                 JOIN customer c ON o.cus_id = c.cus_id
                 JOIN products p ON o.product_id = p.product_id";
$result_orders = mysqli_query($conn, $query_orders);
require '../admin/sidebar_admin.php';

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกคำสั่งผลิต</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding-top: 50px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 4px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 1200px; /* เพิ่มขนาดสำหรับหน้าจอกว้าง */
            margin-right: 50px;
            margin-top: -15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .alert {
            margin-top: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }


        .custom-container {
    width: 79%; /* กำหนดความกว้างของคอนเทนเนอร์ */
    margin: 0 auto; /* ทำให้คอนเทนเนอร์อยู่กลางหน้า */
    padding: 20px; /* เพิ่มพื้นที่รอบๆ */
    background-color: #fff; /* พื้นหลังสีขาว */
    border-radius: 10px; /* ขอบมุมโค้ง */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* เพิ่มเงา */
    margin-top: 20px;
    margin-right: 50px;
}

h2 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    text-align: left;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
}

table th {
    background-color: #f8f9fa;
    font-size: 16px;
    color: #555;
}

table td {
    background-color: #fff;
    font-size: 14px;
    color: #555;
}

table tr:nth-child(even) td {
    background-color: #f2f2f2;
}

table tr:hover {
    background-color: #f1f1f1;
}

.btn {
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    margin: 2px;
    display: inline-block;
}
.modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

    </style>
</head>
<body>

<div class="container">
    <h1>📦 บันทึกคำสั่งผลิต</h1>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ฟอร์มเพิ่มคำสั่งซื้อใหม่ -->
    <form method="post" action="">
    <div class="row">
    <!-- แถวนอน 1: ชื่อลูกค้า เบอร์โทร ที่อยู่ -->
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">ชื่อลูกค้า</label>
            <input type="text" name="customer_name" class="form-control" style="border-radius: 30px; padding: 12px 20px;" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">เบอร์โทร</label>
            <input type="text" name="customer_phone" class="form-control" style="border-radius: 30px; padding: 12px 20px;" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">ที่อยู่</label>
            <input type="text" name="customer_address" class="form-control" style="border-radius: 30px; padding: 12px 20px;" required>
        </div>
    </div>

    <!-- แถวนอน 2: รายชื่อสินค้า จำนวนที่ต้องการ วันที่ต้องนำส่ง -->
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">รายชื่อสินค้า</label>
            <select name="product_id" class="form-control" style="border-radius: 30px; padding: 12px 20px;" required>
                <option value="">เลือกสินค้า</option>
                <?php foreach ($product_list as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">จำนวนที่ต้องการ</label>
            <input type="number" name="quantity" class="form-control" style="border-radius: 30px; padding: 12px 20px;" min="1" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="form-label">วันที่ต้องนำส่ง</label>
            <input type="date" name="delivery_date" class="form-control" style="border-radius: 30px; padding: 12px 20px;" required>
        </div>
    </div>
</div>
        <button type="submit" class="btn btn-primary w-100">บันทึกคำสั่งผลิต</button>
    </form>
</div>

<!-- Modal สำหรับแก้ไขคำสั่งซื้อ -->
<?php if ($edit_order): ?>
    <div class="modal fade show" id="editOrderModal" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขคำสั่งผลิต</h5>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-close"></a>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" value="<?php echo $edit_order['order_id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">ชื่อลูกค้า</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_order['cus_name']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">เบอร์โทร</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_order['cus_number']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รายชื่อสินค้า</label>
                            <select name="product_id" class="form-control" required>
                                <?php foreach ($product_list as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" 
                                        <?php echo ($id == $edit_order['product_id']) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">จำนวนที่ต้องการ</label>
                            <input type="number" name="quantity" class="form-control" 
                                   value="<?php echo $edit_order['quantity']; ?>" 
                                   min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">วันที่ต้องนำส่ง</label>
                            <input type="date" name="delivery_date" class="form-control" 
                                   value="<?php echo $edit_order['delivery_date']; ?>" 
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" name="edit_order" class="btn btn-primary">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<div class="custom-container">
    <h2>รายการคำสั่งผลิต</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>เลขที่คำสั่งผลิต</th>
                <th>ชื่อลูกค้า</th>
                <th>เบอร์โทร</th>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>วันที่ต้องนำส่ง</th>
                <th>สถานะ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['cus_name']; ?></td>
                    <td><?php echo $order['cus_number']; ?></td>
                    <td><?php echo $order['product_name']; ?></td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><?php echo $order['delivery_date']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>
                        <a href="?edit_order_id=<?php echo $order['order_id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                        <a href="?delete_order_id=<?php echo $order['order_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบคำสั่งผลิตนี้หรือไม่?')">ลบ</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>