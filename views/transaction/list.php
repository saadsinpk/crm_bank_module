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
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="department_id">Department</label>
                                        <select name="department_id" id="department_id" class="form-control">
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?php echo $department['departmentid']; ?>">
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

    // Function to update bank cash dropdown
    function updateBankCashDropdown(departmentId, selectedBankCashId = null) {
        bankCashSelect.innerHTML = '';
        fetch('<?php echo admin_url('bank_module/transaction/get_bank_cash_by_department_view/'); ?>' + departmentId)
            .then(response => response.json())
            .then(bankCashes => {
                bankCashes.forEach(function(bankCash, index) {
                    var option = document.createElement('option');
                    option.value = bankCash.id;
                    option.textContent = bankCash.name;
                    bankCashSelect.appendChild(option);

                    if ((selectedBankCashId && bankCash.id == selectedBankCashId) || (!selectedBankCashId && index === 0)) {
                        option.selected = true;
                    }
                });
                if (bankCashes.length > 0 && selectedBankCashId === null) {
                    fetchFilteredTransactions(); // Fetch transactions when auto-selecting the first bank cash
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Select the first department and update bank cash options if no department is selected
    if (!selectedDepartmentId && departmentSelect.options.length > 1) {
        departmentSelect.selectedIndex = 0; // Auto-select the first department
        updateBankCashDropdown(departmentSelect.value);
    } else if (selectedDepartmentId) {
        departmentSelect.value = selectedDepartmentId;
        updateBankCashDropdown(selectedDepartmentId, selectedBankCashId);
    }

    // Event listener for department change
    departmentSelect.addEventListener('change', function() {
        updateBankCashDropdown(this.value);
    });
});




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
    console.log("bankCashId");
    console.log(bankCashId);

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
