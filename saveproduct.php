<?php
// เรียกใช้ไฟล์ connection.php
include 'connect/connection.php';

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
                               VALUES ('$order_id', '$product_id', '$quantity', '$delivery_date', '$cus_id', NOW(), 'pending')";

        if (mysqli_query($conn, $insert_order_query)) {
            $success_message = "บันทึกคำสั่งซื้อสำเร็จ";
        } else {
            $error_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding-top: 50px;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 500px;
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
    </style>
</head>
<body>

    <div class="container">
        <h1>📦 บันทึกคำสั่งซื้อ</h1>
        
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

        <form method="post" action="">
            <div class="form-group">
                <label class="form-label">ชื่อลูกค้า</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">เบอร์โทร</label>
                <input type="text" name="customer_phone" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">ที่อยู่</label>
                <input type="text" name="customer_address" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">รายชื่อสินค้า</label>
                <select name="product_id" class="form-control" required>
                    <option value="">เลือกสินค้า</option>
                    <?php foreach ($product_list as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">จำนวนที่ต้องการ</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label">วันที่ต้องนำส่ง</label>
                <input type="date" name="delivery_date" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">บันทึกคำสั่งซื้อ</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
