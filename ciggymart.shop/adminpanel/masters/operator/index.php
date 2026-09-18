<?php
include('../../../config.php'); 
require_once(PATH_LIBRARIES.'/classes/DBConn.php');
include(PATH_ADMIN_INCLUDE.'/header.php');
$db = new DBConn();

// get list of Operators
$operatorList=$db->ExecuteQuery("SELECT Operator_Id, Operator_name, Address, State_Name, Contact_No, Email, Password, CASE WHEN O.Is_Active=1 THEN 'Block' ELSE 'Unblock' END AS Status
FROM tbl_operators O
LEFT JOIN tbl_states S ON S.State_Id = O.State_Id
ORDER BY Operator_Id DESC"); 
?>

<div class="modern-dashboard">
    <div class="module-header">
        <div class="header-left">
            <h2>Users & Roles (Operators)</h2>
            <p>Manage system users, login credentials, and access roles.</p>
        </div>
        <div class="header-right">
            <a class="btn-primary" href="add_operator.php"><i class="fa fa-plus"></i> Add New User</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table modern-table" id="operatorTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact No</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($operatorList)): foreach($operatorList as $op): ?>
                    <tr>
                        <td>#<?php echo $op['Operator_Id']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($op['Operator_name']); ?></td>
                        <td><?php echo htmlspecialchars($op['Contact_No']); ?></td>
                        <td><?php echo htmlspecialchars($op['Email']); ?></td>
                        <td><?php echo htmlspecialchars($op['State_Name'] ? $op['State_Name'] : '-'); ?></td>
                        <td><?php echo htmlspecialchars($op['Address']); ?></td>
                        <td>
                            <span class="status-badge <?php echo ($op['Status'] == 'Block') ? 'status-delivered' : 'status-rto'; ?>">
                                <?php echo ($op['Status'] == 'Block') ? 'Active' : 'Blocked'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_operator.php?id=<?php echo $op['Operator_Id']; ?>" class="btn-secondary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted">No users found. Create one!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(PATH_ADMIN_INCLUDE.'/footer.php'); ?>


