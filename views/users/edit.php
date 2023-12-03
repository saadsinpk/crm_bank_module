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
                        <h3 class="panel-title">Edit User</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/bank_users/update/' . $user->id)); ?>
                            <div class="form-group">
                                <label for="name">User Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="<?php echo set_value('name', $user->name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select class="form-control" name="department_id" id="department">
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?php echo $department['id']; ?>" <?php echo ($department['id'] == $user->department_id) ? 'selected' : ''; ?>>
                                            <?php echo $department['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?php echo admin_url('bank_module/bank_users'); ?>" class="btn btn-default">Cancel</a>
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