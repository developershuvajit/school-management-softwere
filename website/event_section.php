<?php
// event_section_fixed.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Debug toggle
$DEBUG = true; // set false in production

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php'); // expects $conn (mysqli)

// flash helper
function flash($key, $value = null)
{
    if ($value === null) {
        if (!empty($_SESSION[$key])) {
            $v = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $v;
        }
        return null;
    }
    $_SESSION[$key] = $value;
    return true;
}

// debug helper
function dbg($label, $var)
{
    global $DEBUG;
    if (!$DEBUG) return;
    echo "<pre style='background:#111;color:#b2f5ff;padding:10px;border-radius:6px;margin:8px 0;'>";
    echo "<strong>$label</strong>\n";
    var_dump($var);
    echo "</pre>";
}

// upload dir (relative stored in DB)
$uploadDirRel = 'uploads/events/';
$uploadDirAbs = __DIR__ . '/../' . $uploadDirRel;
if (!is_dir($uploadDirAbs)) @mkdir($uploadDirAbs, 0755, true);

// Simple sanitiser (we use prepared statements)
$safe = function ($v) {
    if ($v === null) return null;
    $v = trim((string)$v);
    return $v === '' ? null : $v;
};

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $errors = [];
    $success = '';

    // ADD
    if ($action === 'add') {
        $title = $safe($_POST['title'] ?? '');
        $description = $safe($_POST['description'] ?? '');
        $location = $safe($_POST['location'] ?? '');
        $event_date = $safe($_POST['event_date'] ?? '');

        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if (!$title || !$description) {
            $errors[] = "Title and description are required.";
        }

        // handle optional image
        $imageRel = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['image'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Image upload error code: " . $f['error'];
            } else {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed)) {
                    $errors[] = "Invalid image type. Allowed: " . implode(', ', $allowed);
                } else {
                    $filename = 'event_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadDirAbs . $filename;
                    if (!move_uploaded_file($f['tmp_name'], $dest)) {
                        $errors[] = "Failed to move uploaded file.";
                    } else {
                        $imageRel = $uploadDirRel . $filename;
                    }
                }
            }
        }

        if (empty($errors)) {
            $sql = "INSERT INTO events (title, description, image, location, event_date, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $errors[] = "Prepare failed: " . $conn->error;
            } else {
                $b_title = $title;
                $b_description = $description;
                $b_image = $imageRel;
                $b_location = $location;
                $b_event_date = $event_date;
                $b_status = $status;

                if (!$stmt->bind_param(
                    "sssssi",
                    $b_title,
                    $b_description,
                    $b_image,
                    $b_location,
                    $b_event_date,
                    $b_status
                )) {
                    $errors[] = "bind_param failed: " . $stmt->error;
                } else {
                    if (!$stmt->execute()) {
                        $errors[] = "Execute failed: " . $stmt->error;
                    } else {
                        $success = "Event added. ID: " . $stmt->insert_id;
                    }
                }
                $stmt->close();
            }
        }

        dbg('ADD errors', $errors);
        dbg('ADD success', $success);
    }

    // EDIT
    if ($action === 'edit') {
        $id = (int)($_POST['event_id'] ?? 0);
        $title = $safe($_POST['title'] ?? '');
        $description = $safe($_POST['description'] ?? '');
        $location = $safe($_POST['location'] ?? '');
        $event_date = $safe($_POST['event_date'] ?? '');
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

        if ($id <= 0) $errors[] = "Invalid event id.";
        if (!$title || !$description) $errors[] = "Title and description are required.";

        // optional image field named image_<id> as in your edit forms
        $fileField = 'image_' . $id;
        $imageRel = null;
        if (isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES[$fileField];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Image upload error code: " . $f['error'];
            } else {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed)) {
                    $errors[] = "Invalid image type. Allowed: " . implode(', ', $allowed);
                } else {
                    $filename = 'event_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $uploadDirAbs . $filename;
                    if (!move_uploaded_file($f['tmp_name'], $dest)) {
                        $errors[] = "Failed to move uploaded file.";
                    } else {
                        $imageRel = $uploadDirRel . $filename;
                    }
                }
            }
        }

        if (empty($errors)) {
            if ($imageRel) {
                // UPDATE including image: placeholders = 10 (9 cols to set + WHERE id)
                $sql = "UPDATE events SET title = ?, description = ?, image = ?, location = ?, event_date = ?, status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $errors[] = "Prepare failed: " . $conn->error;
                } else {
                    // types: 8 strings + 2 ints => "ssssssssii"
                    $b_title = $title;
                    $b_description = $description;
                    $b_image = $imageRel;
                    $b_location = $location;
                    $b_event_date = $event_date;

                    $b_status = $status;
                    $b_id = $id;

                    if (!$stmt->bind_param(
                        "sssssii",
                        $b_title,
                        $b_description,
                        $b_image,
                        $b_location,
                        $b_event_date,

                        $b_status,
                        $b_id
                    )) {
                        $errors[] = "bind_param failed: " . $stmt->error;
                    } else {
                        if (!$stmt->execute()) $errors[] = "Execute failed: " . $stmt->error;
                        else $success = "Event updated (with new image).";
                    }
                    $stmt->close();
                }
            } else {
                // UPDATE without changing image: placeholders = 9 (8 columns + WHERE id)
                $sql = "UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $errors[] = "Prepare failed: " . $conn->error;
                } else {
                    // types: 7 strings + 2 ints => "sssssssii"
                    $b_title = $title;
                    $b_description = $description;
                    $b_location = $location;
                    $b_event_date = $event_date;
                    $b_status = $status;
                    $b_id = $id;

                    if (!$stmt->bind_param(
                        "ssssii",
                        $b_title,
                        $b_description,
                        $b_location,
                        $b_event_date,
                        $b_status,
                        $b_id
                    )) {
                        $errors[] = "bind_param failed: " . $stmt->error;
                    } else {
                        if (!$stmt->execute()) $errors[] = "Execute failed: " . $stmt->error;
                        else $success = "Event updated.";
                    }
                    $stmt->close();
                }
            }
        }

        dbg('EDIT errors', $errors);
        dbg('EDIT success', $success);
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int)($_POST['event_id'] ?? 0);
        if ($id <= 0) {
            $errors[] = "Invalid id for delete.";
        } else {
            // remove image if exists
            $qr = $conn->prepare("SELECT image FROM events WHERE id = ? LIMIT 1");
            if ($qr) {
                $qr->bind_param('i', $id);
                $qr->execute();
                $rres = $qr->get_result();
                $row = $rres->fetch_assoc();
                $qr->close();
                if (!empty($row['image'])) {
                    $filePath = __DIR__ . '/../' . ltrim($row['image'], '/');
                    if (is_file($filePath)) @unlink($filePath);
                }
            }

            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            if (!$stmt) {
                $errors[] = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param('i', $id);
                if (!$stmt->execute()) $errors[] = "Execute failed: " . $stmt->error;
                else $success = "Event deleted.";
                $stmt->close();
            }
        }
        dbg('DELETE errors', $errors);
        dbg('DELETE success', $success);
    }

    // store flashes and redirect
    if (!empty($errors)) flash('events_errors', $errors);
    if ($success !== '') flash('events_success', $success);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch events
$events = [];
$res = $conn->query("SELECT * FROM events ORDER BY event_date DESC, created_at DESC");
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) $events[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Events</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .event-card img {
            max-height: 200px;
            object-fit: cover;
            width: 100%;
            border-radius: 6px;
        }

        .event-card .meta {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 6px;
        }

        .badge-status {
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .badge-active {
            background: #E6FFFA;
            color: #007A6D;
        }

        .badge-inactive {
            background: #FFF5F5;
            color: #973232;
        }

        pre.dbg {
            background: #111;
            color: #b2f5ff;
            padding: 12px;
            border-radius: 6px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <?php include "../includes/preloader.php"; ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="mb-0">Manage Events</h3>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddEvent"><i class="fa fa-plus"></i> Add Event</button>
                </div>

                <?php
                if ($errs = flash('events_errors')) {
                    echo '<div class="alert alert-danger">';
                    foreach ($errs as $e) echo '<div>' . htmlspecialchars($e) . '</div>';
                    echo '</div>';
                }
                if ($msg = flash('events_success')) {
                    echo '<div class="alert alert-success text-dark">' . htmlspecialchars($msg) . '</div>';
                }
                ?>

                <!-- Add Event Modal -->
                <div class="modal fade" id="modalAddEvent" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <form method="post" class="modal-content" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Event</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 mb-3">
                                        <label>Event Date</label>
                                        <input type="date" name="event_date" class="form-control">
                                    </div>

                                </div>
                                <div class="form-group mb-3">
                                    <label>Image (optional)</label>
                                    <input type="file" name="image" accept="image/*" class="form-control">
                                    <small class="text-muted">Recommended: 1200x700</small>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Event</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Events grid -->
                <div class="row g-4">
                    <?php if (empty($events)): ?>
                        <div class="col-12">
                            <div class="alert alert-secondary text-white">No events found.</div>
                        </div>
                        <?php else: foreach ($events as $ev):
                            $eid = (int)$ev['id'];
                            $title = htmlspecialchars($ev['title']);
                            $desc = nl2br(htmlspecialchars($ev['description']));
                            $date = $ev['event_date'] ? date('d M Y', strtotime($ev['event_date'])) : '';

                            $img = !empty($ev['image']) ? '../' . htmlspecialchars($ev['image']) : '../public/images/no-image.png';
                            $statusBadge = ((int)$ev['status'] === 1) ? '<span class="badge-status badge-active">Active</span>' : '<span class="badge-status badge-inactive">Inactive</span>';
                        ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="border rounded p-2 h-100 event-card">
                                    <img src="<?= $img ?>" alt="Event <?= $eid ?>">
                                    <h5 class="fw-bold mt-2"><?= $title ?> <?= $statusBadge ?></h5>
                                    <?php if ($date): ?><div class="meta">Date: <?= $date ?></div><?php endif; ?>
                                    <p class="text-muted mb-2"><?= $desc ?></p>

                                    <div class="d-lg-flex gap-2">
                                        <button class="btn btn-sm btn-secondary flex-fill" data-toggle="modal" data-target="#modalEditEvent_<?= $eid ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>

                                        <form method="post" class="flex-fill" onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?= $eid ?>">
                                            <button type="submit" class="btn btn-sm btn-danger w-100"><i class="fa fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit modal -->
                            <div class="modal fade" id="modalEditEvent_<?= $eid ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <form method="post" class="modal-content" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="event_id" value="<?= $eid ?>">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Event #<?= $eid ?></h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-group mb-3">
                                                <label>Title</label>
                                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ev['title']) ?>" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($ev['description']) ?></textarea>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6 mb-3">
                                                    <label>Event Date</label>
                                                    <input type="date" name="event_date" class="form-control" value="<?= $ev['event_date'] ? date('Y-m-d', strtotime($ev['event_date'])) : '' ?>">
                                                </div>

                                            </div>
                                            <div class="form-group mb-3">
                                                <label>Change Image (optional)</label>
                                                <input type="file" name="image_<?= $eid ?>" accept="image/*" class="form-control">
                                                <small class="text-muted">Leave empty to keep existing image.</small>
                                                <?php if (!empty($ev['image'])): ?>
                                                    <div class="mt-2">
                                                        <img src="<?= $img ?>" alt="Current image" style="max-height:140px;" class="img-fluid rounded">
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= (int)$ev['status'] === 1 ? 'selected' : '' ?>>Active</option>
                                                    <option value="0" <?= (int)$ev['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Event</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                    <?php endforeach;
                    endif; ?>
                </div>


            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
    </div>

    <?php include "../includes/js_links.php"; ?>
</body>

</html>