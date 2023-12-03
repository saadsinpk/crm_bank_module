<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add User</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/bank_users/store')); ?>
                            <div class="form-group">
                                <label for="name">User Name</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select class="form-control" name="department_id" id="department">
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?php echo $department['id']; ?>">
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
