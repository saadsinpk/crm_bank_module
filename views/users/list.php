<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url('modules/bank_module/assets/css/custom_style.css'); ?>">
<script src="<?php echo base_url('modules/bank_module/assets/js/custom_script.js'); ?>"></script>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Users List</h3>
                    </div>
                    <div class="panel-body">
                        <a href="<?php echo admin_url('bank_module/bank_users/create'); ?>" class="btn btn-success mbot10">Add New User</a>
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>Department</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo $user['name']; ?></td>
                                        <td><?php echo $user['department_name']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('bank_module/bank_users/edit/' . $user['id']); ?>" class="btn btn-primary">Edit</a>
                                            <a href="<?php echo admin_url('bank_module/bank_users/delete/' . $user['id']); ?>" class="btn btn-danger _delete">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>
</body>

</html>
