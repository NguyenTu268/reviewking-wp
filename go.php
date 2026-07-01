<?php
// go.php - Code tẩy nguồn miễn phí
error_reporting(0);

// Lấy link đích thật sự (Sailrite)
$target = $_GET['url']; 

if (!$target) { echo "Lỗi link"; exit; }

$target = urldecode($target);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <title>Loading...</title>
</head>
<body>
    <script>
        // Kỹ thuật Double Meta Refresh "thủ công"
        // Bước 1: Xóa sạch referrer
        var meta = document.createElement('meta');
        meta.name = "referrer";
        meta.content = "no-referrer";
        document.getElementsByTagName('head')[0].appendChild(meta);

        // Bước 2: Chuyển hướng
        window.location.replace("<?php echo $target; ?>");
    </script>
    <noscript>
        <!-- Phòng hờ nếu tắt JS thì dùng thẻ meta refresh -->
        <meta http-equiv="refresh" content="0;url=<?php echo $target; ?>">
    </noscript>
</body>
</html>