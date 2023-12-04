<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaction_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }
    public function get_all_transactions($department_id = null, $selected_bank_cash_id = null, $filter_date = null, $search = null) {
        $current_staff_id = get_staff_user_id();
        $permissions = $this->get_cash_employee_permissions($current_staff_id);
        $this->db->select('tblbank_cash_transactions.*, tbldepartments.name as department_name, tblbank_cash_cashes.name as cash_name, CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name');
        $this->db->from('tblbank_cash_transactions');
        $this->db->join('tbldepartments', 'tblbank_cash_transactions.department_id = tbldepartments.departmentid', 'left');
        $this->db->join('tblbank_cash_cashes', 'tblbank_cash_transactions.cash_id = tblbank_cash_cashes.id', 'left');
        $this->db->join('tblstaff', 'tblbank_cash_transactions.name_id = tblstaff.staffid', 'left');
        if($department_id != '') {
            $this->db->where('tbldepartments.departmentid', $department_id);
        }
        if($selected_bank_cash_id != '') {
            $this->db->where('tblbank_cash_transactions.cash_id', $selected_bank_cash_id);
        }

        // Handle filter_date
        if ($filter_date) {
            $dateParts = explode('-', $filter_date);
            if (count($dateParts) == 2) {
                $year = $dateParts[0];
                $month = $dateParts[1];
                $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
                $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
                $this->db->where('tblbank_cash_transactions.date >=', $startDate);
                $this->db->where('tblbank_cash_transactions.date <=', $endDate);
            }
        }

        // Handle search
        if ($search) {
            $this->db->group_start();
            $this->db->like('LOWER(CONCAT(tblstaff.firstname, " ", tblstaff.lastname))', strtolower($search));
            $this->db->or_like('LOWER(tbldepartments.name)', strtolower($search));
            $this->db->or_like('LOWER(tblbank_cash_cashes.name)', strtolower($search));
            $this->db->or_like('tblbank_cash_transactions.amount', $search);
            $this->db->or_like('tblbank_cash_transactions.balance', $search);
            $this->db->or_like('CAST(tblbank_cash_transactions.date AS CHAR)', $search);
            $this->db->group_end();
        }




        // Add this line to order by date
        $this->db->order_by('tblbank_cash_transactions.date', 'DESC');
        $this->db->order_by('tblbank_cash_transactions.id', 'DESC');

        // Fetch the transactions
        $transactions = $this->db->get()->result_array();
        if (!is_admin()) {
            $new_permissions = array();
            foreach ($permissions as $permission) {
                $new_permissions[$permission['cash_id']][] = $permission;
            }
            foreach ($transactions as $transaction_key => &$transaction) {
                if(isset($new_permissions[$transaction['cash_id']])) {
                    foreach ($new_permissions[$transaction['cash_id']] as $new_permission_key => $new_permission_value) {
                        if (isset($new_permission_value['permission_type']) && $new_permission_value['permission_type'] == 'view_global') {
                            $days_to_check = $new_permission_value['days'];
                            if ($days_to_check != '' AND $days_to_check != 0) {
                                $transactionDate = new DateTime($transaction['date']);
                                $transactionDate->modify("+$days_to_check days");
                                $currentDate = new DateTime();

                                if ($transactionDate < $currentDate) {
                                    unset($transactions[$transaction_key]);
                                    break;
                                }
                            }
                        }
                        if (isset($new_permission_value['permission_type']) && $new_permission_value['permission_type'] == 'view_own') {
                            $days_to_check = $new_permission_value['days'];
                            if ($days_to_check != '' AND $days_to_check != 0) {
                                $transactionDate = new DateTime($transaction['date']);
                                $transactionDate->modify("+$days_to_check days");
                                $currentDate = new DateTime();

                                if ($transactionDate < $currentDate) {
                                    unset($transactions[$transaction_key]);
                                    break;
                                }
                            }
                        }
                        if(isset($new_permission_value['permission_type']) AND $new_permission_value['permission_type'] == 'view_own_days') {
                            if($transaction['name_id'] != $current_staff_id) {
                                unset($transactions[$transaction_key]);
                                break;
                            }
                        }
                        if(isset($new_permission_value['permission_type']) AND $new_permission_value['permission_type'] == 'edit') {
                            $transactions[$transaction_key]['allow_edit'] = 1;
                        }
                        if(isset($new_permission_value['permission_type']) AND $new_permission_value['permission_type'] == 'delete') {
                            $transactions[$transaction_key]['allow_delete'] = 1;
                        }
                    }
                } else {
                    unset($transactions[$transaction_key]);
                }
            }

        }
        return $transactions;
    }

    // Function to get cash_employee_permissions for a given staff
    private function get_cash_employee_permissions($staff_id, $cash_id = 0, $permission_type = '') {
        // Query to retrieve permissions
        $this->db->select('*');
        $this->db->from('cash_employee_permissions');
        if($cash_id != 0) {
            $this->db->where('cash_id', $cash_id);
        }
        if($permission_type != '') {
            $this->db->where('permission_type', $permission_type);
        }
        $this->db->where('employee_id', $staff_id);
        return $this->db->get()->result_array();
    }



    // Get a single transaction by ID
    public function get_transaction_by_id($id) {
        return $this->db->get_where(db_prefix() . 'bank_cash_transactions', ['id' => $id])->row_array();
    }
    // Add a new transaction
    // Add a new transaction
    public function add_transaction($data) {
        // Insert the transaction without balance
        $this->db->insert(db_prefix() . 'bank_cash_transactions', $data);

        // Recalculate balances for all transactions in the same department and cash
        $this->recalculate_all_balances($data['department_id'], $data['cash_id']);

        return $this->db->insert_id();
    }



    private function calculate_balance_before_date($date, $department_id) {
        $this->db->select('SUM(CASE WHEN transaction_type = "Payment" THEN amount ELSE -amount END) as balance');
        $this->db->from(db_prefix() . 'bank_cash_transactions');
        $this->db->where('department_id', $department_id);
        $this->db->where('date <=', $date);
        $query = $this->db->get();

        if ($query->row()) {
            return (float) $query->row()->balance;
        }
        return 0.0;
    }


    // Update a transaction
    public function update_transaction($id, $data) {
        $this->db->select('department_id, cash_id');
        $this->db->from(db_prefix() . 'bank_cash_transactions');
        $this->db->where('id', $id);
        $current_transaction = $this->db->get()->row_array();

        if (!$current_transaction) {
            // Handle the case where the transaction does not exist
            return 0; // or any appropriate response
        }

        // Extract the department_id and cash_id from the current transaction
        $current_department_id = $current_transaction['department_id'];
        $current_cash_id = $current_transaction['cash_id'];

        // Update the transaction
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'bank_cash_transactions', $data);

        // Check if department_id or cash_id has changed, and recalculate balances accordingly
        if ($data['department_id'] != $current_department_id || $data['cash_id'] != $current_cash_id) {
            $this->recalculate_all_balances($current_department_id, $current_cash_id);
        }

        // Recalculate balances for all transactions in the updated department and cash
        $this->recalculate_all_balances($data['department_id'], $data['cash_id']);

        return $this->db->affected_rows();

    }
    // Recalculate all balances for a specific department and cash
    private function recalculate_all_balances($department_id, $cash_id) {
        // Fetch all transactions for the specified department and cash, ordered by date
        $this->db->select('*');
        $this->db->from(db_prefix() . 'bank_cash_transactions');
        $this->db->where('department_id', $department_id);
        $this->db->where('cash_id', $cash_id);
        $this->db->order_by('date', 'ASC');
        $transactions = $this->db->get()->result_array();

        $balance = 0.0;

        // Recalculate balance for each transaction
        foreach ($transactions as $transaction) {
            $balance += ($transaction['transaction_type'] == 'Payment') ? $transaction['amount'] : -$transaction['amount'];

            // Update the balance of the transaction
            $this->db->where('id', $transaction['id']);
            $this->db->update(db_prefix() . 'bank_cash_transactions', ['balance' => $balance]);
        }
    }


    private function recalculate_balances($startDate, $department_id, $cash_id) {
        // Fetch all subsequent transactions
        $this->db->select('*');
        $this->db->from(db_prefix() . 'bank_cash_transactions');
        $this->db->where('department_id', $department_id);
        $this->db->where('cash_id', $cash_id);
        $this->db->where('date >=', $startDate);
        $this->db->order_by('date', 'ASC');
        $subsequent_transactions = $this->db->get()->result_array();

        // Recalculate balance starting from the start date
        $balance = $this->calculate_balance_before_date($startDate, $department_id, $cash_id);

        foreach ($subsequent_transactions as $transaction) {
            $balance += ($transaction['transaction_type'] == 'Payment') ? $transaction['amount'] : -$transaction['amount'];

            // Update the balance of the transaction
            $this->db->where('id', $transaction['id']);
            $this->db->update(db_prefix() . 'bank_cash_transactions', ['balance' => $balance]);
        }
    }

    // Delete a transaction
    public function delete_transaction($id) {
        $skip_delete = 1;
        $this->db->select('cash_id, name_id, department_id');
        $this->db->from(db_prefix() . 'bank_cash_transactions');
        $this->db->where('id', $id);
        $bank_transaction = $this->db->get()->row_array();

        if(!is_admin()) {
            $current_staff_id = get_staff_user_id();
            if(isset($bank_transaction['cash_id'])) {
                $permissions = $this->get_cash_employee_permissions($current_staff_id, $bank_transaction['cash_id']);
            }
            foreach ($permissions as $permissions_key => $permissions_value) {
                if(isset($permissions_value['permission_type'])) {
                    if($permissions_value['permission_type'] == 'view_own_days') {
                        if($bank_transaction['name_id'] == $permissions_value['employee_id']) {
                            $skip_delete = 0;
                        }
                    }
                    if($permissions_value['permission_type'] == 'delete') {
                        $skip_delete = 0;
                    }
                    if($permissions_value['permission_type'] == 'view_global_days') {
                        $skip_delete = 0;
                    }
                }
            }
        } else {
         $skip_delete = 0;   
        }
        if($skip_delete == 0) {
            $this->recalculate_all_balances($bank_transaction['department_id'], $bank_transaction['cash_id']);
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'bank_cash_transactions');
            return $this->db->affected_rows();
        }
    }
 
}
