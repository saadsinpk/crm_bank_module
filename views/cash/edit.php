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
                        <h3 class="panel-title">Edit Cash</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/bank_cash/update/' . $cash->id)); ?>
                            <div class="form-group">
                                <label for="name">Cash Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="<?php echo set_value('name', $cash->name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select class="form-control" name="department_id" id="department">
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?php echo $department['departmentid']; ?>" <?php echo ($department['departmentid'] == $cash->department_id) ? 'selected' : ''; ?>>
                                            <?php echo $department['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Department Employee List Table -->
                            <h4>Department Employee List</h4>
                            <table class="table" id="department-employee-list">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>View (Global)</th>
                                        <th>View (Own)</th>
                                        <th>Create</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Content populated by JavaScript -->
                                </tbody>
                            </table>

                            <!-- Other Employee List Table -->
                            <h4>Other Employee List</h4>
                            <table class="table" id="other-employee-list">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>View (Global)</th>
                                        <th>View (Own)</th>
                                        <th>Create</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Content populated by JavaScript -->
                                </tbody>
                            </table>

                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?php echo admin_url('bank_module/bank_cash'); ?>" class="btn btn-default">Cancel</a>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript to handle department change and populate tables with current permissions
    document.addEventListener('DOMContentLoaded', function() {
        var departmentSelect = document.getElementById('department');
        var selectedDepartmentId = departmentSelect.value;
        updateEmployeeLists(selectedDepartmentId);
    });

    document.getElementById('department').addEventListener('change', function() {
        var departmentId = this.value;
        updateEmployeeLists(departmentId);
    });

    function updateEmployeeLists(departmentId) {
        // Clear tables
        document.getElementById('department-employee-list').querySelector('tbody').innerHTML = '';
        document.getElementById('other-employee-list').querySelector('tbody').innerHTML = '';

        // Fetch current permissions and populate tables
        fetchCurrentPermissions(departmentId);
    }

    function fetchCurrentPermissions(departmentId) {
        fetch('<?php echo admin_url('bank_module/bank_cash/get_current_permissions'); ?>?cash_id=<?php echo $cash->id; ?>&department_id=' + departmentId)
            .then(response => response.json())
            .then(data => {
                populateEmployeeTable('department-employee-list', data.departmentEmployees, data.currentPermissions);
                populateEmployeeTable('other-employee-list', data.otherEmployees, data.currentPermissions);
            })
            .catch(error => console.error('Error:', error));
    }
    function populateEmployeeTable(tableId, employees, currentPermissionsArray) {
        // Transform currentPermissionsArray into a structured object

        let currentPermissions = {};
        currentPermissionsArray.forEach(perm => {
            if (!currentPermissions[perm.employee_id]) {
                currentPermissions[perm.employee_id] = {};
            }
            currentPermissions[perm.employee_id][perm.permission_type] = perm.days ? perm.days : true;
        });

        employees.forEach(employee => {
            // Initialize permissions to empty if not set
            var employeePermissions = currentPermissions[employee.staffid] || {};
            console.log(employeePermissions);
            var viewGlobalChecked = employeePermissions['view_global'] ? 'checked' : '';
            var viewOwnChecked = employeePermissions['view_own'] ? 'checked' : '';
            var createChecked = employeePermissions['create'] ? 'checked' : '';
            var editChecked = employeePermissions['edit'] ? 'checked' : '';
            var deleteChecked = employeePermissions['delete'] ? 'checked' : '';

            var viewGlobalDays = employeePermissions['view_global'] || '';
            var viewOwnDays = employeePermissions['view_own'] || '';

            var row = `
                <tr>
                    <td>${employee.firstname} ${employee.lastname}</td>
                    <td>
                        <input type="checkbox" name="permissions[${employee.staffid}][view_global]" ${viewGlobalChecked} onchange="toggleInput(this)"> 
                        <input type="number" name="permissions[${employee.staffid}][view_global_days]" min="0" style="width: 50px; display: ${viewGlobalChecked ? 'inline-block' : 'none'};" placeholder="Days" value="${viewGlobalDays}">
                    </td>
                    <td>
                        <input type="checkbox" name="permissions[${employee.staffid}][view_own]" ${viewOwnChecked} onchange="toggleInput(this)"> 
                        <input type="number" name="permissions[${employee.staffid}][view_own_days]" min="0" style="width: 50px; display: ${viewOwnChecked ? 'inline-block' : 'none'};" placeholder="Days" value="${viewOwnDays}">
                    </td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][create]" ${createChecked}></td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][edit]" ${editChecked}></td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][delete]" ${deleteChecked}></td>
                </tr>`;
            document.getElementById(tableId).querySelector('tbody').insertAdjacentHTML('beforeend', row);
        });
    }


    function toggleInput(checkbox) {
        var input = checkbox.nextElementSibling;
        input.style.display = checkbox.checked ? 'inline-block' : 'none';
    }
</script>

<?php init_tail(); ?>
</body>
</html>
