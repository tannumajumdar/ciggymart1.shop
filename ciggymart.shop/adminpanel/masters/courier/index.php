<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->Execute("DELETE FROM tbl_courier_partners WHERE Partner_Id=$id");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$partners = $db->ExecuteQuery("SELECT * FROM tbl_courier_partners ORDER BY Partner_Id DESC");
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Courier Partners</h2>
            <p>Manage third-party logistics and delivery partners.</p>
        </div>
        <div class="header-right">
            <a class="btn-primary" href="add.php"><i class="fa fa-plus"></i> Add Partner</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Partner Name</th>
                        <th>Contact No</th>
                        <th>Tracking URL</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($partners)): foreach($partners as $p): ?>
                    <tr>
                        <td>#<?php echo $p['Partner_Id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['Partner_Name']); ?></td>
                        <td><?php echo htmlspecialchars($p['Contact_No'] ? $p['Contact_No'] : '-'); ?></td>
                        <td>
                            <?php if(!empty($p['Tracking_URL'])): ?>
                                <a href="<?php echo htmlspecialchars($p['Tracking_URL']); ?>" target="_blank" class="text-primary"><i class="fa fa-external-link"></i> Link</a>
                            <?php else: echo '-'; endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo ($p['Status'] == 1) ? 'status-delivered' : 'status-rto'; ?>">
                                <?php echo ($p['Status'] == 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?delete=<?php echo $p['Partner_Id']; ?>" onclick="return confirm('Are you sure you want to delete this partner?');" class="btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No partners added yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>

