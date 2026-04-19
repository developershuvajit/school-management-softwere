<?php
session_start();
include('../config/database.php');

// -------------- POST HANDLING --------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD NOTICE
    if ($action == 'add') {
        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);
        $type    = trim($_POST['notice_type']);
        $start   = !empty($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'])) : date('Y-m-d H:i:s');
        $end     = !empty($_POST['end_date'])   ? date('Y-m-d H:i:s', strtotime($_POST['end_date'])) : null;

        $stmt = $conn->prepare("INSERT INTO notices (title, content, start_date, end_date, notice_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $content, $start, $end, $type);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Notice added successfully";
        header("Location: notice_section.php");
        exit;
    }

    // UPDATE NOTICE
    if ($action == 'edit') {
        $id      = intval($_POST['notice_id']);
        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);
        $type    = trim($_POST['notice_type']);
        $start   = !empty($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'])) : date('Y-m-d H:i:s');
        $end     = !empty($_POST['end_date'])   ? date('Y-m-d H:i:s', strtotime($_POST['end_date'])) : null;

        $stmt = $conn->prepare("UPDATE notices SET title=?, content=?, start_date=?, end_date=?, notice_type=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $content, $start, $end, $type, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Notice updated successfully";
        header("Location: notice_section.php");
        exit;
    }

    // DELETE NOTICE
    if ($action == 'delete') {
        $id = intval($_POST['notice_id']);
        $stmt = $conn->prepare("DELETE FROM notices WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['msg'] = "Notice deleted successfully";
        header("Location: notice_section.php");
        exit;
    }
}

// -------------- FETCH ALL NOTICES --------------
$rows = [];
$result = $conn->query("SELECT * FROM notices ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Notices - School Management Softwere</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div id="main-wrapper">
    <?php include "../includes/navbar.php"; ?>
    <?php include('../includes/sidebar_logic.php'); ?>

    <div class="content-body">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="mb-0">Manage Notices</h3>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalAddNotice"><i class="fa fa-plus"></i> Add Notice</button>
            </div>

            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-success text-dark"><?= htmlspecialchars($_SESSION['msg']) ?></div>
                <?php unset($_SESSION['msg']); ?>
            <?php endif; ?>

            <!-- Add Notice Modal -->
            <div class="modal fade" id="modalAddNotice" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <form method="post" class="modal-content">
                        <input type="hidden" name="action" value="add">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Notice</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Content</label>
                                <textarea name="content" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label>Notice Type</label>
                                <select name="notice_type" class="form-control" required>
                                    <option value="general">📢 General</option>
                                    <option value="urgent">🔥 Urgent</option>
                                    <option value="important">⭐ Important</option>
                                    <option value="academic">📚 Academic</option>
                                    <option value="event">📅 Event</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 mb-3">
                                    <label>Start Date & Time</label>
                                    <input type="datetime-local" name="start_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label>End Date & Time (optional)</label>
                                    <input type="datetime-local" name="end_date" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Notice</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notices Grid -->
            <div class="row g-4">
                <?php if (empty($rows)): ?>
                    <div class="col-12">
                        <div class="alert alert-secondary">No notices found. Click "Add Notice" to create one.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($rows as $notice): ?>
                        <?php $nid = (int)$notice['id']; ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <h5 class="fw-bold"><?= htmlspecialchars($notice['title']) ?></h5>
                                <p class="mb-3 text-dark"><?= nl2br(htmlspecialchars($notice['content'])) ?></p>
                                <p class="text-muted small mb-2">Start: <?= date('d M Y, H:i', strtotime($notice['start_date'])) ?></p>
                                <?php if ($notice['end_date']): ?>
                                    <p class="text-muted small mb-2">End: <?= date('d M Y, H:i', strtotime($notice['end_date'])) ?></p>
                                <?php endif; ?>
                                <div class="d-lg-flex gap-2">
                                    <button class="btn btn-sm btn-secondary flex-fill" data-toggle="modal" data-target="#modalEditNotice_<?= $nid ?>"><i class="fa fa-edit"></i> Edit</button>
                                    <form method="post" class="flex-fill" onsubmit="return confirm('Delete this notice?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="notice_id" value="<?= $nid ?>">
                                        <button type="submit" class="btn btn-sm btn-danger w-100"><i class="fa fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="modalEditNotice_<?= $nid ?>" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <form method="post" class="modal-content">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="notice_id" value="<?= $nid ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Notice #<?= $nid ?></h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($notice['title']) ?>" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Content</label>
                                            <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($notice['content']) ?></textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label>Notice Type</label>
                                            <select name="notice_type" class="form-control" required>
                                                <option value="general" <?= ($notice['notice_type'] ?? 'general') == 'general' ? 'selected' : '' ?>>🛈 General</option>
                                                <option value="urgent" <?= ($notice['notice_type'] ?? 'general') == 'urgent' ? 'selected' : '' ?>>❗ Urgent</option>
                                                <option value="important" <?= ($notice['notice_type'] ?? 'general') == 'important' ? 'selected' : '' ?>>★ Important</option>
                                                <option value="academic" <?= ($notice['notice_type'] ?? 'general') == 'academic' ? 'selected' : '' ?>>🎓 Academic</option>
                                                <option value="event" <?= ($notice['notice_type'] ?? 'general') == 'event' ? 'selected' : '' ?>>🗓 Event</option>
                                            </select>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6 mb-3">
                                                <label>Start Date & Time</label>
                                                <input type="datetime-local" name="start_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($notice['start_date'])) ?>">
                                            </div>
                                            <div class="form-group col-md-6 mb-3">
                                                <label>End Date & Time (optional)</label>
                                                <input type="datetime-local" name="end_date" class="form-control" value="<?= $notice['end_date'] ? date('Y-m-d\TH:i', strtotime($notice['end_date'])) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Notice</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
</div>
<?php include "../includes/js_links.php"; ?>
</body>
</html>