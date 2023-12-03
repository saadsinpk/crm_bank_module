<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Transactions</h3>
                    </div>
                    <div class="panel-body">

                        <a href="<?php echo admin_url('bank_module/transaction/create'); ?>" class="btn btn-success mbot10">Add New Transaction</a>
                        <!-- Add Filter Form -->
                        <form action="<?php echo admin_url('bank_module/transaction'); ?>" method="get">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="department_id">Department</label>
                                        <select name="department_id" id="department_id" class="form-control">
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?php echo $department['departmentid']; ?>" <?php echo (isset($_GET['department_id']) && $_GET['department_id'] == $department['departmentid']) ? 'selected' : ''; ?>>
                                                    <?php echo $department['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_cash_id">Bank Cash</label>
                                        <select name="bank_cash_id" id="bank_cash_id" class="form-control">
                                            <!-- Bank cash options will be populated here -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_date">Filter by Date (MM-YYYY)</label>
                                        <input type="month" name="filter_date" id="filter_date" class="form-control" value="<?php echo isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Search</label>
                                        <input type="text" name="search" id="search" class="form-control" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Search">
                                    </div>
                                </div>
                            </div>

                        </form>

                        <!-- Existing table and other code... -->
                        <div class="table-responsive"> <!-- Add this div -->
                            <table class="table table-bordered" id="list_table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Cash</th>
                                        <th>Payment</th>
                                        <th>Paycheck</th>
                                        <th>Balance</th>
                                        <th>Other</th>
                                        <th>Actions</th>    
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php //$balance = 0;?>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('d-m-Y', strtotime($transaction['date'])); ?></td>
                                            <td><?php echo $transaction['name']; ?></td>
                                            <td><?php echo $transaction['cash_name']; ?></td>
                                            <?php if($transaction['transaction_type'] == 'Payment'){ ?>
                                                <?php //$balance = $balance + $transaction['amount'];?>
                                                <td><?php echo $transaction['amount']; ?></td>
                                                <td></td>
                                            <?php } else { ?>
                                                <?php // $balance = $balance - $transaction['amount'];?>
                                                <td></td>
                                                <td><?php echo $transaction['amount']; ?></td>
                                            <?php } ?>
                                            <td><?php echo $transaction['balance']; ?></td>
                                            <td></td>
                                            <td>
                                                <?php if($transaction['allow_edit'] == 1 || is_admin()) {?>
                                                    <a href="<?php echo admin_url('bank_module/transaction/edit/' . $transaction['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <?php }?>
                                                <?php if($transaction['allow_delete'] == 1 || is_admin()) {?>
                                                    <a href="<?php echo admin_url('bank_module/transaction/delete/' . $transaction['id']); ?>" class="btn btn-danger btn-sm _delete">Delete</a>
                                                <?php }?>
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
<script>


document.addEventListener('DOMContentLoaded', function() {
    var departmentSelect = document.getElementById('department_id');
    var bankCashSelect = document.getElementById('bank_cash_id');
    var urlParams = new URLSearchParams(window.location.search);
    
    var selectedDepartmentId = urlParams.get('department_id');
    var selectedBankCashId = urlParams.get('bank_cash_id');

    if (selectedDepartmentId) {
        // If department_id is in the URL, set it and update bank cash dropdown
        departmentSelect.value = selectedDepartmentId;
        updateBankCashDropdown(selectedDepartmentId, selectedBankCashId);
    } else if (departmentSelect.options.length > 1) {
        // If no department_id in URL, select the first department
        departmentSelect.selectedIndex = 1;
        selectedDepartmentId = departmentSelect.value;
        updateBankCashDropdown(selectedDepartmentId);
    }
});

function updateBankCashDropdown(departmentId, selectedBankCashId) {
    var bankCashSelect = document.getElementById('bank_cash_id');

    fetch('<?php echo admin_url('bank_module/transaction/get_bank_cash_by_department_view/'); ?>' + departmentId)
        .then(response => response.json())
        .then(bankCashes => {
            var isFirstOptionSet = false;

            bankCashes.forEach(function(bankCash, index) {
                var option = document.createElement('option');
                option.value = bankCash.id;
                option.textContent = bankCash.name;
                bankCashSelect.appendChild(option);

                if (selectedBankCashId && bankCash.id == selectedBankCashId) {
                    option.selected = true;
                    isFirstOptionSet = true;
                } else if (selectedBankCashId === undefined && index === 0) {
                    // Auto-select the first option only if no bank_cash_id is specified in the URL
                    option.selected = true;
                    isFirstOptionSet = true;
                    fetchFilteredTransactions();
                }
            });

            if (!isFirstOptionSet && selectedBankCashId === true) {
                // Fallback to select first option if it's not already set
                bankCashSelect.options[0].selected = true;
            }
        })
        .catch(error => console.error('Error:', error));
}



document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners to filters
    document.getElementById('department_id').addEventListener('change', fetchFilteredTransactions);
    document.getElementById('bank_cash_id').addEventListener('change', fetchFilteredTransactions);
    document.getElementById('filter_date').addEventListener('change', fetchFilteredTransactions);
    document.getElementById('search').addEventListener('keyup', debounce(fetchFilteredTransactions, 500));
});

function fetchFilteredTransactions() {
    var departmentId = document.getElementById('department_id').value;
    var bankCashId = document.getElementById('bank_cash_id').value;
    var filterDate = document.getElementById('filter_date').value;
    var search = document.getElementById('search').value;

    var url = new URL('<?php echo admin_url('bank_module/transaction/fetch_filtered_transactions'); ?>');
    var params = { department_id: departmentId, bank_cash_id: bankCashId, filter_date: filterDate, search: search };
    url.search = new URLSearchParams(params).toString();

    fetch(url)
    .then(response => response.json())
    .then(updateTransactionTable)
    .catch(error => console.error('Error:', error));
}

function updateTransactionTable(transactions) {
    var tbody = document.querySelector('#list_table tbody');
    tbody.innerHTML = ''; // Clear current table rows

    transactions.forEach(function(transaction) {
        var row = tbody.insertRow();

        // Date column
        var cellDate = row.insertCell();
        cellDate.textContent = transaction.date; // Replace 'date' with the actual property name

        // Name column
        var cellName = row.insertCell();
        cellName.textContent = transaction.name; // Replace 'name' with the actual property name

        // Cash column
        var cellCash = row.insertCell();
        cellCash.textContent = transaction.cash_name; // Replace 'cash_name' with the actual property name

        // Payment column
        var cellPayment = row.insertCell();
        if (transaction.transaction_type === 'Payment') {
            cellPayment.textContent = transaction.amount;
        } else {
            cellPayment.textContent = '';
        }

        // Paycheck column
        var cellPaycheck = row.insertCell();
        if (transaction.transaction_type === 'Paycheck') {
            cellPaycheck.textContent = transaction.amount;
        } else {
            cellPaycheck.textContent = '';
        }

        // Balance column
        var cellBalance = row.insertCell();
        cellBalance.textContent = transaction.balance; // Replace 'balance' with the actual property name

        // Other column (if you have other data to show)
        var cellOther = row.insertCell();
        // cellOther.textContent = transaction.otherData; // Replace 'otherData' with the actual property name

        // Actions column
        var cellActions = row.insertCell();
        if (transaction.allow_edit || is_admin) {
            var editLink = document.createElement('a');
            editLink.href = admin_url + 'bank_module/transaction/edit/' + transaction.id; // Construct the edit URL
            editLink.textContent = 'Edit';
            editLink.className = 'btn btn-primary btn-sm';
            cellActions.appendChild(editLink);
        }
        if (transaction.allow_delete || is_admin) {
            var deleteLink = document.createElement('a');
            deleteLink.href = admin_url + 'bank_module/transaction/delete/' + transaction.id; // Construct the delete URL
            deleteLink.textContent = 'Delete';
            deleteLink.className = 'btn btn-danger btn-sm _delete';
            cellActions.appendChild(deleteLink);
        }
    });
}

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

</script>
<?php init_tail(); ?>
</body>

</html>
