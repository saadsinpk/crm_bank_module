<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url('modules/bank_module/assets/css/custom_style.css'); ?>">
<script src="<?php echo base_url('modules/bank_module/assets/js/custom_script.js'); ?>"></script>

<?php init_head(); ?>
<style type="text/css">
    .table-responsive {
        overflow-x: auto; /* Allows horizontal scrolling */
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Cash List</h3>
                    </div>
                    <div class="panel-body">
                        <a href="<?php echo admin_url('bank_module/bank_cash/create'); ?>" class="btn btn-success mbot10">Add New Cash</a>
                        <div class="table-responsive"> <!-- Add this div -->
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>Cash Name</th>
                                        <th>Department</th>
                                        <th>Employee with access to this cash</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cash as $cashs): ?>
                                        <tr>
                                            <td><?php echo $cashs['name']; ?></td>
                                            <td><?php echo $cashs['department_name']; ?></td>
                                            <td><?php echo $cashs['staff_names']; ?></td> <!-- Display staff name -->
                                            <td>
                                                <a href="<?php echo admin_url('bank_module/bank_cash/edit/' . $cashs['id']); ?>" class="btn btn-primary">Edit</a>
                                                <a href="<?php echo admin_url('bank_module/bank_cash/delete/' . $cashs['id']); ?>" class="btn btn-danger _delete">Delete</a>
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
</div>


<?php init_tail(); ?>
</body>

</html>
