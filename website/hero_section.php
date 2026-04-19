<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<?php include('../config/database.php'); ?>

<body>
    <?php include "../includes/preloader.php" ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>

        <div class="content-body">
            <div class="container-fluid">

                <?php
                include "../config/database.php";
                $sql = "SELECT * FROM hero_sliders ORDER BY id ASC";
                $result = $conn->query($sql);
                $sliders = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $sliders[] = $row;
                    }
                }
                ?>
                <?php foreach ($sliders as $slide):
                    $id = (int)$slide['id'];
                    $imgPath = !empty($slide['image']) ?  '../' . htmlspecialchars($slide['image']) : 'assets/img/no-image.png';
                ?>
                    <div class="modal fade" id="Modal_slider_<?= $id; ?>" tabindex="-1" role="dialog" aria-labelledby="ModalLabel<?= $id; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="ModalLabel<?= $id; ?>">Edit Slider #<?= $id; ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <form id="editSliderForm<?= $id; ?>" method="post" action="../actions/slider_edit.php" enctype="multipart/form-data">
                                    <div class="modal-body">

                                        <input type="hidden" name="slider_id" value="<?= $id; ?>">

                                        <div class="mb-3">
                                            <label class="text-dark">Current Image</label>
                                            <div>
                                                <img src="<?= $imgPath; ?>" class="img-fluid rounded d-block mb-2" alt="Current slide image" style="max-height:180px;">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text-dark">Change Image (optional)</label>
                                            <input type="file" class="form-control" name="slider_image" accept="image/*">
                                            <small class="text-muted">Leave empty to keep existing image.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text-dark">Heading</label>
                                            <input type="text" class="form-control" name="slider_heading" value="<?= htmlspecialchars($slide['title'] ?? '') ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="text-dark">Description</label>
                                            <textarea class="form-control" name="slider_description" rows="4" required><?= htmlspecialchars($slide['description'] ?? '') ?></textarea>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Slider
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- now render cards for each slider, using the same $sliders array -->
                <div class="row g-4">
                    <?php if (empty($sliders)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No sliders found.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sliders as $slide):
                            $id = (int)$slide['id'];
                            $imgPath = !empty($slide['image']) ?  '../' . htmlspecialchars($slide['image']) : 'assets/img/no-image.png';
                        ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="border rounded p-2 h-100">
                                    <img src="<?= $imgPath; ?>" class="img-fluid rounded mb-2" alt="Slide <?= $id ?>" style="max-height:200px; width:100%; object-fit:cover;">
                                    <h5 class="fw-bold"><?= htmlspecialchars($slide['title'] ?? '') ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($slide['description'] ?? '') ?></p>

                                    <!-- edit button to open modal for THIS slider -->
                                    <button class="btn btn-sm btn-secondary w-100" data-toggle="modal" data-target="#Modal_slider_<?= $id; ?>">
                                        <i class="fa fa-edit"></i> Edit Slide
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>
</body>

</html>