<?php
// Validation
if (!isset($students) || !is_array($students) || count($students) === 0) {
    echo "<h3 style='text-align:center;color:red;margin-top:50px;'>No students found.</h3>";
    exit;
}
if (!isset($copies) || $copies < 1) $copies = 1;

// Photo handler
function student_photo_path($student)
{
    if (!empty($student['photo'])) {
        $path = __DIR__ . '/../' . $student['photo'];
        if (file_exists($path)) {
            return '../' . $student['photo'];
        }
    }
    return '../assets/images/default-avatar.png';
}

// Cute images for decoration
$cuteImages = [
    "../public/images/stickers/sticker1.png",
    "../public/images/stickers/sticker2.png",
    "../public/images/stickers/sticker3.png",
    "../public/images/stickers/sticker4.png"
];
$cuteIndex = 0;

// Color palette
$colorSchemes = [
    ['primary' => '#3B82F6', 'secondary' => '#60A5FA', 'light' => '#DBEAFE'],
    ['primary' => '#8B5CF6', 'secondary' => '#A78BFA', 'light' => '#EDE9FE'],
    ['primary' => '#10B981', 'secondary' => '#34D399', 'light' => '#D1FAE5'],
    ['primary' => '#EC4899', 'secondary' => '#F472B6', 'light' => '#FCE7F3'],
    ['primary' => '#F59E0B', 'secondary' => '#FBBF24', 'light' => '#FEF3C7'],
    ['primary' => '#14B8A6', 'secondary' => '#2DD4BF', 'light' => '#CCFBF1']
];
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Student Stickers - School Management Softwere</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        @page {
            size: 18in 12in;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* ================= PAGE (18×12 INCH) ================= */
        .page {
            width: 18in;
            height: 12in;
            padding: 0.4in;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(4, 1fr);
            gap: 0.2in;
            page-break-after: always;
            background: white;
            margin: 0 auto;
        }

        /* ================= STICKER ================= */
        .sticker {
            width: 4in;
            height: 2.5in;
            border-radius: 10px;
            position: relative;
            display: flex;
            overflow: hidden;
            border: 3px solid;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            background: white;
        }

        /* ================= HEADER ================= */
        .sticker-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            z-index: 10;
            border-bottom: 2px solid;
        }

        .school-name {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* ================= CONTENT ================= */
        .sticker-content {
            margin-top: 25px;
            height: calc(100% - 25px);
            display: flex;
            width: 100%;
        }

        /* ================= LEFT SECTION ================= */
        .left-section {
            width: 45%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* important */
            padding: 12px 10px 10px;
            position: relative;
            background: rgba(255, 255, 255, 0.2);
        }

        /* ================= STUDENT PHOTO (UPDATED) ================= */
        .photo-container {
            width: 145px;
            height: 145px;
            border-radius: 50%;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;

            margin-top: -10px; /* move up */
            margin-bottom: 16px;

            border: 4px solid;
            z-index: 3;
            background: white;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.18);
        }

        .photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        /* ================= ANIMAL STICKER (FIXED) ================= */
        .cute-image {
            position: absolute;
            bottom: 6px;
            left: 6px;
            width: 60px;
            height: 60px;

            z-index: 1;
            pointer-events: none;

            opacity: 0.95;
            filter: drop-shadow(1px 2px 3px rgba(0, 0, 0, 0.2));
        }

        /* ================= RIGHT SECTION ================= */
        .right-section {
            width: 55%;
            display: flex;
            flex-direction: column;
            padding: 15px 12px;
            position: relative;
        }

        /* ================= STUDENT ID BADGE ================= */
        .student-id {
            position: absolute;
            top: -32px;
            right: 10px;
            background: rgba(255, 255, 255, 0.95);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 800;
            border: 2px solid;
            z-index: 11;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* ================= NAME ================= */
        .student-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
            line-height: 1.2;
            padding-bottom: 8px;
            position: relative;
        }

        .student-name::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 2px;
            border-radius: 2px;
        }

        /* ================= DETAILS ================= */
        .details-grid {
            /* flex: 1; */  /* Remove flex:1 to avoid pushing footer down */
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            margin-bottom: 18px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-weight: 800;
            font-size: 10px;
            margin-bottom: 6px;
            color: #4b5563;
        }

        .detail-value {
            font-weight: 400;
            font-size: 11px;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 8px;
            border-radius: 6px;
            min-height: 22px;
            text-align: start;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* ================= SUBJECT FIELD ================= */
        .subject-field {
            text-align: start;
            font-weight: 600;
            color: inherit; /* use parent's color */
            margin-bottom: 12px;
            user-select: none;
            margin-top:5px;
        }

        .subject-field span.subject-label {
            font-size: 12px;
        }

        .subject-field span.subject-underline {
            display: inline-block;
            width: 185px; /* underline length */
            border-bottom: 2px solid currentColor;
            vertical-align: middle;
            margin-left: 0px;
        }

        /* ================= FOOTER ================= */
        .sticker-footer {
            text-align: right;
            font-size: 9px;
            font-weight: 600;
            margin-top: auto; /* push footer down */
            color: #000;
        }

        /* ================= PRINT ================= */
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
                width: 18in;
                height: 12in;
            }

            .page {
                padding: 0.4in;
                box-shadow: none;
            }

            .sticker {
                box-shadow: none;
                border-width: 2px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* ================= SCREEN PREVIEW ================= */
        @media screen {
            body {
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }

            .page {
                transform: scale(0.5);
                transform-origin: top center;
                border: 1px solid #e5e7eb;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>

<body>

    <?php
    $counter = 0;
    $stickerCount = 0;
    $colorIndex = 0;

    foreach ($students as $student):
        for ($i = 0; $i < $copies; $i++):
            // Start new page after every 16 stickers (4×4 grid)
            if ($stickerCount % 16 === 0) {
                if ($stickerCount > 0) echo '</div>';
                echo '<div class="page">';
            }

            $fullName = htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name']));
            $photoPath = htmlspecialchars(student_photo_path($student));
            $className = htmlspecialchars($student['class_name'] ?? '');
            $academicYear = htmlspecialchars($student['academic_year'] ?? '');
            $sectionName = htmlspecialchars($student['section_name'] ?? '');
            $rollNumber = htmlspecialchars($student['roll_number'] ?? '');
            $studentId = htmlspecialchars($student['student_id'] ?? '');

            // Get color scheme
            $colors = $colorSchemes[$colorIndex % count($colorSchemes)];
            $colorIndex++;

            // Get cute image
            $cuteImg = htmlspecialchars($cuteImages[$cuteIndex % count($cuteImages)]);
            $cuteIndex++;
    ?>

            <div class="sticker" style="
                border-color: <?= $colors['primary'] ?>;
                background: linear-gradient(135deg, <?= $colors['light'] ?> 0%, white 100%);
                color: <?= $colors['primary'] ?>;
            ">
                <!-- Top Header with School Name -->
                <div class="sticker-header" style="
                    background: <?= $colors['primary'] ?>;
                    border-bottom-color: <?= $colors['secondary'] ?>;
                ">
                    <div class="school-name">School Management Softwere</div>
                </div>

                <!-- Content Area -->
                <div class="sticker-content">
                    <!-- Left section: Photo + Cute image -->
                    <div class="left-section">
                        <!-- Cute image -->
                        <img src="<?= $cuteImg ?>" class="cute-image" alt="Decoration">

                        <!-- Photo -->
                        <div class="photo-container" style="border-color: <?= $colors['primary'] ?>;">
                            <img src="<?= $photoPath ?>" class="photo" alt="Student Photo">
                        </div>
                    </div>

                    <!-- Right section: Details -->
                    <div class="right-section">
                        <!-- Student Name -->
                        <div class="student-name" style="color: <?= $colors['primary'] ?>;">
                            <?= $fullName ?>
                            <div style="
                                content: '';
                                position: absolute;
                                bottom: 0;
                                left: 25%;
                                width: 50%;
                                height: 3px;
                                background: <?= $colors['primary'] ?>;
                                border-radius: 2px;
                            "></div>
                        </div>

                        <!-- Details Grid (2 columns) -->
                        <div class="details-grid">
                            <?php if (!empty($className) && !empty($sectionName)): ?>
                                <div class="detail-item">
                                    <span class="detail-label" style="color: <?= $colors['primary'] ?>;">Class</span>
                                    <span class="detail-value"><?= $className ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <span class="detail-label" style="color:<?= $colors['primary'] ?>">Roll</span>
                                <span class="detail-value"><?= $rollNumber ?></span>
                            </div>

                            <?php if (!empty($studentId)): ?>
                                <div class="detail-item">
                                    <span class="detail-label" style="color: <?= $colors['primary'] ?>;">Student ID</span>
                                    <span class="detail-value"><?= $studentId ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <span class="detail-label" style="color: <?= $colors['primary'] ?>;">Academic Year</span>
                                <span class="detail-value"><?= $academicYear ?></span>
                            </div>
                        </div>

                        <!-- Subject Input Placeholder -->
                        <div class="subject-field" style="color: <?= $colors['primary'] ?>;">
                            <!--<span class="subject-label">Subject</span>-->
                            <span class="subject-underline"></span>
                        </div>

                        <!-- Footer -->
                        
                    </div>
                </div>
            </div>

    <?php
            $counter++;
            $stickerCount++;
        endfor;
    endforeach;

    // Close the last page
    if ($stickerCount > 0) echo '</div>';
    ?>

</body>

</html>
