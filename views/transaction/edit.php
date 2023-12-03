<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Edit Transaction</h3>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open(admin_url('bank_module/transaction/update/' . $transaction['id'])); ?>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department_id" id="department" class="form-control">
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?php echo $department['departmentid']; ?>" <?php echo ($department['departmentid'] == $transaction['department_id']) ? 'selected' : ''; ?>>
                                            <?php echo $department['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cash">Cash</label>
                                <select name="cash_id" id="cash" class="form-control">
                                    <?php foreach ($cash_item as $cash): ?>
                                        <option value="<?php echo $cash['id']; ?>" <?php echo ($cash['id'] == $transaction['cash_id']) ? 'selected' : ''; ?>>
                                            <?php echo $cash['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <?php
                                // Assuming is_admin() is the function to check if the user is an admin
                                if (is_admin()) {
                                    // Admin users can see and change the user selection
                                ?>
                                    <label for="name">User</label>
                                    <select name="name_id" id="name" class="form-control">
                                        <?php foreach ($users as $users_item): ?>
                                            <option value="<?php echo $users_item['staffid']; ?>" <?php echo ($users_item['staffid'] == $transaction['name_id']) ? 'selected' : ''; ?>>
                                                <?php echo '('.$users_item['email'].') '.$users_item['firstname'].' '.$users_item['lastname']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php
                                } else {
                                    // For staff users, pass the selected user ID as a hidden input
                                    echo '<input type="hidden" name="name_id" value="' . $transaction['name_id'] . '">';
                                }
                                ?>
                            </div>



                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" name="date" id="date" class="form-control" value="<?php echo $transaction['date']; ?>">
                            </div>

                            <div class="form-group">
                                <label>Transaction Type</label>
                                <div>
                                    <input type="radio" name="transaction_type" id="payment" value="Payment" <?php echo ($transaction['transaction_type'] == 'Payment') ? 'checked' : ''; ?>>
                                    <label for="payment">Payment</label>
                                </div>
                                <div>
                                    <input type="radio" name="transaction_type" id="paycheck" value="Paycheck" <?php echo ($transaction['transaction_type'] == 'Paycheck') ? 'checked' : ''; ?>>
                                    <label for="paycheck">Paycheck</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" name="amount" id="amount" class="form-control" value="<?php echo $transaction['amount']; ?>" step="any">
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
    var selectedDepartmentId = document.getElementById('department').value;
    updateCashDropdown(selectedDepartmentId);
});

document.getElementById('department').addEventListener('change', function() {
    var departmentId = this.value;
    updateCashDropdown(departmentId);
});

function updateCashDropdown(departmentId) {
    var cashSelect = document.getElementById('cash');
    var selectedCashId = '<?php echo $transaction['cash_id']; ?>';

    // Clear existing options in cash dropdown
    cashSelect.innerHTML = '<option value="">-- Select Cash --</option>';

    if (departmentId) {
        fetch('<?php echo admin_url('bank_module/transaction/get_bank_cash_by_department/'); ?>' + departmentId)
            .then(response => response.json())
            .then(cashOptions => {
                cashOptions.forEach(function(cash) {
                    var option = document.createElement('option');
                    option.value = cash.id;
                    option.textContent = cash.name;
                    option.selected = (cash.id == selectedCashId);
                    cashSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
}
</script>

<?php init_tail(); ?>
</body>

</html>