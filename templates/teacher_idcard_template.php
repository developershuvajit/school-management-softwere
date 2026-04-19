<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Teacher ID Cards - School Management Softwere</title>

<style>
@page { size: A4 portrait; margin: 8mm; }
* { box-sizing: border-box; }

html, body {
    margin: 0;
    padding: 0;
    background: #fff;
}

.a4-sheet {
    width: 210mm;
    height: 297mm;
    display: grid;
    grid-template-columns: repeat(3, 2.1in);
    grid-template-rows: repeat(3, 3.4in);
    justify-content: space-between;
    align-content: space-between;
    padding: 6mm;
    page-break-after: always;
}

.id-card {
    width: 2.1in;
    height: 3.4in;
    background: url("../public/images/id_bg.png") no-repeat center / cover;
    border-radius: 14px;
    border: 3px solid #ffd1e6;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 5px;
    overflow: hidden;
}

.school-title {
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    margin-top: 8px;
}

.photo-box {
    width: 1in;
    height: 1in;
    border-radius: 50%;
    border: 3px solid #fff;
    overflow: hidden;
}

.photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.teacher-name {
    font-size: 13px;
    font-weight: 800;
    color: #b80060;
    text-align: center;
}

.details {
    font-size: 9px;
    font-weight: 600;
    text-align: center;
}

.teacher-mobile {
    font-size: 11px;
    font-weight: 600;
}

.qr-box {
    width: 1.15in;
    height: 1.15in;
    background: #fff;
    border-radius: 10px;
    border: 2.5px solid #ff2b88;
    padding: 6px;
}

.qr-box img {
    width: 100%;
    height: 100%;
}

.footer-text {
    font-size: 7px;
    font-weight: bold;
    color: #fff;
}
</style>
</head>

<body>

<?php
$count = 0;
echo '<div class="a4-sheet">';

while ($teacher = $res->fetch_assoc()):

    $photoPath = (!empty($teacher['photo']) && file_exists("../".$teacher['photo']))
        ? "../".$teacher['photo']
        : "../assets/images/default-avatar.png";

    $qrFile = $qrDir.'/teacher_'.$teacher['teacher_code'].'.png';

    /* ====== FIXED QR DATA ====== */
    if (!file_exists($qrFile)) {

        $qrData = json_encode([
            'id'   => $teacher['teacher_code'],
            'type' => 'teacher'
        ]);

        Builder::create()
            ->writer(new PngWriter())
            ->data($qrData)
            ->size(250)
            ->margin(10)
            ->build()
            ->saveToFile($qrFile);
    }

    if ($count > 0 && $count % 9 == 0) {
        echo '</div><div class="a4-sheet">';
    }
?>

<div class="id-card">
    <div class="school-title">School Management Softwere</div>

    <div class="photo-box">
        <img src="<?= $photoPath ?>">
    </div>

    <div class="teacher-name"><?= htmlspecialchars($teacher['name']) ?></div>

    <div class="details">
        Subject: <?= htmlspecialchars($teacher['subject']) ?><br>
        Code: <?= htmlspecialchars($teacher['teacher_code']) ?>
    </div>

    <div class="teacher-mobile">
        Mobile: <?= htmlspecialchars($teacher['phone']) ?>
    </div>

    <div class="qr-box">
        <img src="<?= '../qrcodes/teacher_'.$teacher['teacher_code'].'.png' ?>">
    </div>

    <div class="footer-text">
        Join Date: <?= htmlspecialchars($teacher['join_date']) ?>
    </div>
</div>

<?php
$count++;
endwhile;

echo '</div>';
?>

</body>
</html>
