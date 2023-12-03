<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add transaction</h3>
                    </div>
                    <div class="panel-body">
                        <?php if ($this->session->flashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?php echo $this->session->flashdata('error'); ?>
                            </div>
                        <?php endif; ?>
                        

                        <?php echo form_open(admin_url('bank_module/transaction/store')); ?>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department_id" id="department" class="form-control">
                                    <option>--Select Department--</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?php echo $department['departmentid']; ?>">
                                            <?php echo $department['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cash">Cash</label>
                                <select name="cash_id" id="cash" class="form-control">
                                </select>
                            </div>
                            <!-- Assuming 'name' is a dropdown, adjust as per your requirement -->
                            <div class="form-group">
                                <?php
                                // Assuming get_staff_user_id() is the function to get the logged-in staff ID
                                $logged_in_staff_id = get_staff_user_id();
                                if ($logged_in_staff_id) {
                                    // If staff is logged in, use a hidden field to pass the staff ID
                                    echo '<input type="hidden" name="name_id" value="' . $logged_in_staff_id . '">';
                                } else { ?>
                                    <label for="name">User</label>
                                    <select name="name_id" id="name" class="form-control">
                                        <?php foreach ($users as $users_item): ?>
                                            <option value="<?php echo $users_item['staffid']; ?>">
                                                <?php echo '(' . $users_item['email'] . ') ' . $users_item['firstname'] . ' ' . $users_item['lastname']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" name="date" id="date" class="form-control" value="<?php echo date("Y-m-d");?>">
                            </div>

                            <div class="form-group">
                                <label>Transaction Type</label>
                                <div>
                                    <input type="radio" name="transaction_type" id="payment" value="Payment">
                                    <label for="payment">Payment</label>
                                </div>
                                <div>
                                    <input type="radio" name="transaction_type" id="paycheck" value="Paycheck">
                                    <label for="paycheck">Paycheck</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" step="any">
                            </div>

                            <button type="submit" class="btn btn-success">Save</button>
                            <a href="<?php echo admin_url('bank_module/transaction'); ?>" class="btn btn-default">Cancel</a>
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
    var cashSelect = document.getElementById('cash');

    function updateCashDropdown(departmentId) {
        cashSelect.innerHTML = '<option value="">-- Select Cash --</option>';

        if (departmentId) {
            fetch('<?php echo admin_url('bank_module/transaction/get_bank_cash_by_department/'); ?>' + departmentId)
                .then(response => response.json())
                .then(cashOptions => {
                    console.log('Cash Options:', cashOptions); // Debugging

                    cashOptions.forEach(function(cash, index) {
                        var option = document.createElement('option');
                        option.value = cash.id;
                        option.textContent = cash.name;
                        cashSelect.appendChild(option);

                        if (index === 0) {
                            cashSelect.selectedIndex = 1; // Auto-select the first cash option
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }

    // Auto-select the first department if available
    if (departmentSelect.options.length > 1) {
        departmentSelect.selectedIndex = 1; // Assuming the first option is a placeholder
        updateCashDropdown(departmentSelect.options[departmentSelect.selectedIndex].value);
    }

    // Listener for department changes
    departmentSelect.addEventListener('change', function() {
        updateCashDropdown(this.value);
    });
});


</script>

<?php init_tail(); ?>
</body>

</html>
