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
                        <h3 class="panel-title">Departments List</h3>
                    </div>
                    <div class="panel-body">
                        <a href="<?php echo admin_url('bank_module/bank_departments/create'); ?>" class="btn btn-success mbot10">Add New Department</a>
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <th>Department ID</th>
                                    <th>Department Name</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bank_departments as $department) : ?>
                                    <tr>
                                        <td><?php echo $department['id']; ?></td>
                                        <td><?php echo $department['name']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('bank_module/bank_departments/edit/' . $department['id']); ?>" class="btn btn-primary">Edit</a>
                                            <a href="<?php echo admin_url('bank_module/bank_departments/delete/' . $department['id']); ?>" class="btn btn-danger _delete">Delete</a>
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