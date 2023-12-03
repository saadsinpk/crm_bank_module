<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo base_url('modules/bank_module/assets/css/custom_style.css'); ?>">
<script src="<?php echo base_url('modules/bank_module/assets/js/custom_script.js'); ?>"></script>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add Department</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/bank_departments/store')); ?>
                            <div class="form-group">
                                <label for="name">Department Name</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?php echo admin_url('bank_module/bank_departments'); ?>" class="btn btn-default">Cancel</a>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>
</body>

</html>