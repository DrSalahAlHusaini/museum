<?php
header('Content-Type: text/html; charset=utf-8');

// معلومات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "اسم_المستخدم";
$password = "كلمة_المرور";
$dbname = "اسم_قاعدة_البيانات";

// إنشاء اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تعيين مجموعة الأحرف لضبط اللغة العربية
$conn->set_charset("utf8mb4");

// معالجة بيانات النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استقبال البيانات النصية
    $branch = $_POST['branch'];
    $museum = $_POST['museum'];
    $artifact_type = $_POST['artifact_type'];
    $piece_number = $_POST['piece_number'];
    $record_number = $_POST['record_number'] ?? null;
    $discovery_place = $_POST['discovery_place'] ?? null;
    $source = $_POST['source'] ?? null;
    $acquisition_date = $_POST['acquisition_date'] ?? null;
    $material = $_POST['material'] ?? null;
    $color = $_POST['color'] ?? null;
    $time_period = $_POST['time_period'] ?? null;
    $height = $_POST['height'] ?? null;
    $width = $_POST['width'] ?? null;
    $length = $_POST['length'] ?? null;
    $diameter = $_POST['diameter'] ?? null;
    $depth = $_POST['depth'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $condition = $_POST['condition'] ?? null;
    $description = $_POST['description'] ?? null;
    $published_by = $_POST['published_by'] ?? null;
    $documenter = $_POST['documenter'];
    $documentation_date = $_POST['documentation_date'];
    
    // الحقول الاختيارية
    $catalog_number = $_POST['catalog_number'] ?? null;
    $card_number = $_POST['card_number'] ?? null;
    $guide_number = $_POST['guide_number'] ?? null;
    $previous_number = $_POST['previous_number'] ?? null;
    $storage_location = $_POST['storage_location'] ?? null;
    $room = $_POST['room'] ?? null;
    $safe = $_POST['safe'] ?? null;
    $storage = $_POST['storage'] ?? null;
    $shelf = $_POST['shelf'] ?? null;
    $previous_location = $_POST['previous_location'] ?? null;

    // معالجة رفع الصورة
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . $piece_number . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // التحقق من أن الملف صورة
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // التحقق من حجم الملف (2MB كحد أقصى)
            if ($_FILES["image"]["size"] <= 2 * 1024 * 1024) {
                // السماح بأنواع معينة من الملفات
                $allowed_types = ["jpg", "jpeg", "png", "gif"];
                if (in_array(strtolower($file_extension), $allowed_types)) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        echo "عذراً، حدث خطأ أثناء رفع الملف.";
                    }
                } else {
                    echo "عذراً، فقط ملفات JPG, JPEG, PNG & GIF مسموح بها.";
                }
            } else {
                echo "عذراً، حجم الملف كبير جداً (الحد الأقصى 2MB).";
            }
        } else {
            echo "الملف المرفوع ليس صورة.";
        }
    }

    // إعداد الاستعلام SQL
    $sql = "INSERT INTO artifacts (
        branch, museum, artifact_type, piece_number, record_number, discovery_place, 
        source, acquisition_date, material, color, time_period, height, width, 
        length, diameter, depth, weight, `condition`, description, published_by, 
        documenter, documentation_date, image_path, catalog_number, card_number, 
        guide_number, previous_number, storage_location, room, safe, storage, 
        shelf, previous_location
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // إعداد الاستعلام
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("خطأ في إعداد الاستعلام: " . $conn->error);
    }

    // ربط المعاملات
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssss",
        $branch, $museum, $artifact_type, $piece_number, $record_number, $discovery_place,
        $source, $acquisition_date, $material, $color, $time_period, $height, $width,
        $length, $diameter, $depth, $weight, $condition, $description, $published_by,
        $documenter, $documentation_date, $image_path, $catalog_number, $card_number,
        $guide_number, $previous_number, $storage_location, $room, $safe, $storage,
        $shelf, $previous_location
    );

    // تنفيذ الاستعلام
    if ($stmt->execute()) {
        // إعادة توجيه بعد الحفظ بنجاح
        header("Location: success.html");
        exit();
    } else {
        echo "خطأ: " . $stmt->error;
    }

    // إغلاق الاتصال
    $stmt->close();
    $conn->close();
} else {
    echo "طلب غير صالح";
}
?>