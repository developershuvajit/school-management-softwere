<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Student ID Cards - School Management Softwere</title>

    <style>
     /* ================= PAGE SETUP ================= */
@page {
    size: A4 portrait;
    margin: 8mm;
}

* {
    box-sizing: border-box;
}

html, body {
    margin: 0;
    padding: 0;
    background: #ffffff;
}

/* ================= A4 SHEET (3 × 3 GRID) ================= */
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

/* ================= ID CARD (REAL SIZE — DO NOT CHANGE) ================= */
.id-card {
    width: 2.1in;
    height: 3.4in;

    background: url("../public/images/id_bg.png") no-repeat center / cover;
    border-radius: 14px;
    border: 3px solid #ffd1e6;

    display: flex;
    flex-direction: column;
    align-items: center;

    overflow: hidden;
    padding: 10px 5px;
}

/* ================= HEADER ================= */
.school-title {
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    color: #ffffff;
    margin-top: 8px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* ================= PHOTO (FIXED CIRCULAR) ================= */
.photo-box {
    width: 1in;
    height: 1in;
    /*margin: 6px auto;*/

    border-radius: 50%;
    border: 3px solid #ffffff;
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
    /*object-position: center;*/

    /*display: block;*/
}
/* ================= TEXT ================= */
.student-name {
    text-align: center;
    font-size: 13px;
    font-weight: 800;
    color: #b80060;
    margin-top: 3px;
    line-height: 1.1;
}

.details {
    text-align: center;
    font-size: 9px;
    font-weight: 600;
    color: #3b0e28;
    margin-top: 2px;
    padding: 0 4px;
    line-height: 1.2;
}

.student-mobile {
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: #000;
    margin-top: 3px;
    margin-bottom:2px;
    line-height: 1.1;
}

/* ================= QR ================= */
.qr-box {
    width: 1.15in;
    height: 1.15in;

    margin: 2px auto 15px;

    background: #ffffff;
    border-radius: 10px;
    border: 2.5px solid #ff2b88;

    padding: 6px;

    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* ================= FOOTER ================= */
.footer-text {
    text-align: center;
    font-size: 7px;
    font-weight: bold;
    color: #ffffff;
    margin-bottom: 4px;

    line-height: 1;
}

/* ================= PRINT SAFETY ================= */
@media print {
    body {
        margin: 0;
    }

    .a4-sheet {
        page-break-after: always;
    }
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
                'id' => $student['student_id'],
                'name' => $student['first_name'] . ' ' . $student['last_name'],
                'class' => $student['class_name'],
                'mobile' => $student['parent_phone'],
                'academic_year' => $student['academic_year'],
                'section' => $student['section_name']
            ]);

            $qr = Builder::create()
                ->writer(new PngWriter())
                ->data($qrData)
                ->size(300)
                ->build();

            $qr->saveToFile($qrFile);
        }

     
        if ($count > 0 && $count % 9 == 0) {
            echo '</div><div class="a4-sheet">';
        }
    ?>

        <div class="id-card">
            <div class="school-title">School Management Softwere</div>

            <div class="photo-box">
                <img class="img-fluid" src="<?= $photoPath ?>" alt="student">
            </div>

            <div class="student-name"><?= htmlspecialchars($student['first_name'] . "  " . $student['last_name']) ?></div>

            <div class="details">
                <?= htmlspecialchars($student['class_name'] . " " . $student['section_name']) ?>
                | ID: <?= htmlspecialchars($student['student_id']) ?>
                | Roll: <?= htmlspecialchars($student['roll_number']) ?>
            </div>

            <div class="student-mobile"><span>Mobile :</span> <?= htmlspecialchars($student['parent_phone']) ?></div>

            <div class="qr-box">
                <img src="<?= '../qrcodes/qr_' . $student['id'] . '.png' ?>" alt="QR Code">
            </div>

            <div class="footer-text">Current Academic Session: <?= htmlspecialchars($student['academic_year']) ?></div>
        </div>

    <?php
        $count++;
    endwhile;


    echo '</div>'; 
    ?>

</body>

</html>
