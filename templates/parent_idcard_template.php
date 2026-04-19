<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Parent ID Cards - SCHOOL INDIA JUNIOR</title>

<style>
@page {
    size: A4 portrait;
    margin: 8mm;
}

* { box-sizing: border-box; }

html, body {
    margin: 0;
    padding: 0;
    background: #ffffff;
}

/* ================= A4 GRID ================= */
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

/* ================= ID CARD ================= */
.id-card {
    width: 2.1in;
    height: 3.4in;

    background:
        
        url("../public/images/parent_bg.png") no-repeat center / cover;

    border-radius: 14px;
    border: 3px solid #2e7d32;

    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 5px 6px;
}

/* ================= HEADER ================= */
.school-title {
    font-size: 11px;
    font-weight: 800;
    color: #fff;
    margin-bottom:5px;
    text-align: center;
}

.card-type {
    font-size: 9px;
    font-weight: bold;
    color: #2e7d32;
    margin-bottom: 4px;
}

/* ================= PHOTO ================= */
.photo-box {
    width: 1in;
    height: 1in;
    border-radius: 50%;
    border: 3px solid #2e7d32;
    background: #ffffff;
    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ================= NAME ================= */
.student-name {
    font-size: 13px;
    font-weight: 800;
    color: #1b5e20;
    text-align: center;
    margin-top: 4px;
}

/* ================= DETAILS ================= */
.details {
    font-size: 9px;
    font-weight: 700;
    color: #2e7d32;
    text-align: center;
    line-height: 1.3;
    margin-top: 2px;
}

/* ================= MOBILE + ID ================= */
.student-mobile {
    font-size: 9px;
    font-weight: 700;
    color: #1b5e20;
    text-align: center;
    margin-top: 2px;
}

/* ================= QR ================= */
.qr-box {
    width: 0.95in;
    height: 0.95in;
    margin: 4px auto 6px;

    background: #ffffff;
    border-radius: 8px;
    border: 2px solid #2e7d32;
    padding: 4px;

    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-box img {
    width: 100%;
    height: 100%;
}

/* ================= FOOTER ================= */
.footer-text {
    font-size: 7px;
    font-weight: bold;
    color: #1b5e20;
    text-align: center;
}
</style>
</head>

<body>

<?php
$count = 0;
echo '<div class="a4-sheet">';

while ($student = $res->fetch_assoc()):

    $photoPath = (!empty($student['photo']) && file_exists("../" . $student['photo']))
        ? "../" . $student['photo']
        : "../assets/images/default-avatar.png";

    $qrFile = $qrDir . '/qr_' . $student['id'] . '.png';

    if (!file_exists($qrFile)) {
        $qrData = json_encode([
            'student_id' => $student['student_id'],
            'student_name' => $student['first_name'].' '.$student['last_name'],
            'class' => $student['class_name'],
            'section' => $student['section_name'],
            'roll' => $student['roll_number'],
            'parent_mobile' => $student['parent_phone'],
            'academic_year' => $student['academic_year']
        ]);

        $qr = Builder::create()
            ->writer(new PngWriter())
            ->data($qrData)
            ->size(260)
            ->build();

        $qr->saveToFile($qrFile);
    }

    if ($count > 0 && $count % 9 == 0) {
        echo '</div><div class="a4-sheet">';
    }
?>

<div class="id-card">
    <div class="school-title">SCHOOL INDIA JUNIOR</div>
   

    <div class="photo-box">
        <img src="<?= $photoPath ?>" alt="student">
    </div>

    <div class="student-name">
        <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
    </div>

    <!-- Class + Section + Roll (ONE LINE) -->
    <div class="details">
        Class <?= htmlspecialchars($student['class_name']) ?>
        <?= htmlspecialchars($student['section_name']) ?>
        | Roll <?= htmlspecialchars($student['roll_number']) ?>
    </div>

    <!-- Student ID + Mobile (ONE LINE) -->
    <div class="student-mobile">
        ID: <?= htmlspecialchars($student['student_id']) ?>
        | Mob: <?= htmlspecialchars($student['parent_phone']) ?>
    </div>

    <div class="qr-box">
        <img src="<?= '../qrcodes/qr_'.$student['id'].'.png' ?>" alt="QR">
    </div>

    <div class="footer-text">
        Academic Session <?= htmlspecialchars($student['academic_year']) ?>
    </div>
    
</div>

<?php
$count++;
endwhile;

echo '</div>';
?>

</body>
</html>
