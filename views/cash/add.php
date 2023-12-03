<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add Cash</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/bank_cash/store')); ?>
                        <div class="form-group">
                            <label for="name">Cash Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select class="form-control" name="department_id" id="department">
                                <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['departmentid']; ?>">
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
                                    <!-- Add other columns as needed -->
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
                                    <!-- Add other columns as needed -->
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
    document.addEventListener('DOMContentLoaded', function() {
        var departmentSelect = document.getElementById('department');
        if (departmentSelect.options.length > 0) {
            departmentSelect.selectedIndex = 0;
            var selectedDepartmentId = departmentSelect.value;
            updateEmployeeLists(selectedDepartmentId);
        }
    });

    document.getElementById('department').addEventListener('change', function() {
        var departmentId = this.value;
        updateEmployeeLists(departmentId);
    });

    function updateEmployeeLists(departmentId) {
        // Clear tables
        document.getElementById('department-employee-list').querySelector('tbody').innerHTML = '';
        document.getElementById('other-employee-list').querySelector('tbody').innerHTML = '';

        fetch('<?php echo admin_url('bank_module/bank_cash/get_employees_by_department'); ?>?department_id=' + departmentId)
            .then(response => response.json())
            .then(data => {
                populateEmployeeTable('department-employee-list', data.departmentEmployees);
                populateEmployeeTable('other-employee-list', data.otherEmployees);
            })
            .catch(error => console.error('Error:', error));
    }

    function populateEmployeeTable(tableId, employees) {
        employees.forEach(employee => {
            var row = `
                <tr>
                    <td>${employee.firstname} ${employee.lastname}</td>
                    <td>
                        <input type="checkbox" name="permissions[${employee.staffid}][view_global]" onchange="toggleInput(this)"> 
                        <input type="number" name="permissions[${employee.staffid}][view_global_days]" min="0" style="width: 50px; display: none;" placeholder="Days">
                    </td>
                    <td>
                        <input type="checkbox" name="permissions[${employee.staffid}][view_own]" onchange="toggleInput(this)"> 
                        <input type="number" name="permissions[${employee.staffid}][view_own_days]" min="0" style="width: 50px; display: none;" placeholder="Days">
                    </td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][create]"></td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][edit]"></td>
                    <td><input type="checkbox" name="permissions[${employee.staffid}][delete]"></td>
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
