<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaction extends Admin_controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cash_model');
        $this->load->model('User_model');
        $this->load->model('Department_model');
        $this->load->model('Transaction_model');
        $this->load->library('form_validation');

        // Load other necessary models for departments, cash, etc.
    }

    // List all transactions
    public function index() {
        // Fetch transactions
        $department_id = $this->input->get('department_id');
        $selected_department_id = $this->input->get('department_id');
        $selected_bank_cash_id = $this->input->get('bank_cash_id');
        $filter_date = $this->input->get('filter_date');
        $search = $this->input->get('search');

        $data['transactions'] = $this->Transaction_model->get_all_transactions($department_id, $selected_bank_cash_id, $filter_date, $search);
        // print_r($data['transactions']);
        $data['departments'] = $this->Department_model->get_departments_view();
        $data['users'] = $this->User_model->get_users();
        $data['selected_department_id'] = $selected_department_id;
        $data['selected_bank_cash_id'] = $selected_bank_cash_id;


        $this->load->view('bank_module/transaction/list', $data);
    }

    // Show form for adding a new transaction
    public function create() {

        // Fetch data for dropdowns (departments, cash, etc.)
        $data['departments'] = $this->Department_model->get_departments();
        $data['cash'] = $this->Cash_model->get_cashs(); // Assuming you have a Cash_model
        $data['users'] = $this->User_model->get_users(); // Assuming you have a Cash_model
        // Similarly fetch other necessary data
        $this->load->view('bank_module/transaction/add', $data);
    }

    // Store a new transaction
    public function store() {
        $this->form_validation->set_rules('department_id', 'Department', 'required');
        $this->form_validation->set_rules('cash_id', 'Cash', 'required');
        $this->form_validation->set_rules('name_id', 'User', 'required');
        $this->form_validation->set_rules('date', 'Date', 'required');
        $this->form_validation->set_rules('transaction_type', 'Transaction Type', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            // Validation failed
            $this->session->set_flashdata('error', validation_errors());
            redirect(admin_url('bank_module/transaction/create'));
        } else {
            $data = $this->input->post();
            $this->Transaction_model->add_transaction($data);
            redirect(admin_url('bank_module/transaction'));
        }
    }
    
    public function fetch_filtered_transactions() {
        $postData = json_decode($this->input->raw_input_stream, true);

        $departmentId = $_GET['department_id'] ?? null;
        $bankCashId = $_GET['bank_cash_id'] ?? null;
        $filterDate = $_GET['filter_date'] ?? null;
        $search = $_GET['search'] ?? '';
        // Implement your logic to fetch filtered transactions based on these parameters
        $transactions = $this->Transaction_model->get_all_transactions($departmentId, $bankCashId, $filterDate, $search);

        // Return the transactions as JSON
        echo json_encode($transactions);
    }

    // Show form for editing a transaction
    public function edit($id) {
        // Fetch transaction data and data for dropdowns
        $data['transaction'] = $this->Transaction_model->get_transaction_by_id($id);
        $data['departments'] = $this->Department_model->get_departments();
        $data['cash_item'] = $this->Cash_model->get_cashs(); // Assuming you have a Cash_model
        $data['users'] = $this->User_model->get_users(); // Assuming you have a Cash_model
        // ... and other necessary data
        $this->load->view('bank_module/transaction/edit', $data);
    }

    // Update a transaction
    public function update($id) {
        // Validate and update data
        // Redirect to the transaction list after updating
        $data = $this->input->post();
        $this->Transaction_model->update_transaction($id, $data);
        redirect(admin_url('bank_module/transaction'));
    }

    // Delete a transaction
    public function delete($id) {
        // Delete the transaction
        // Redirect to the transaction list after deletion
        $this->Transaction_model->delete_transaction($id);
        redirect(admin_url('bank_module/transaction'));
    }
    public function get_users_by_department($department_id) {
        $this->load->model('User_model'); // Load the model, if not already loaded
        $users = $this->User_model->get_users_by_department($department_id);

        echo json_encode($users);
    }
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

    public function get_bank_cash_by_department_view($department_id) {
        // Assuming BankCash_model is loaded and has a method get_bank_cash_by_department
        if (is_admin()) {
            $bankCashes = $this->Cash_model->get_bank_cashes_by_department($department_id);
            echo json_encode($bankCashes);
        } else {
            $current_staff_id = get_staff_user_id(); // Assumed function to get current staff ID
            $permissions = $this->get_cash_employee_permissions($current_staff_id);
            $check_create_exist = array();

            foreach ($permissions as $permissions_key => $permissions_value) {
                if($permissions_value['permission_type'] == 'view_own_days') {
                    $check_create_exist[$permissions_value['cash_id']]['view_own_days'] = 1;
                } elseif($permissions_value['permission_type'] == 'view_global_days') {
                    $check_create_exist[$permissions_value['cash_id']]['view_global_days'] = 1;
                } elseif($permissions_value['permission_type'] == 'create') {
                    $check_create_exist[$permissions_value['cash_id']]['create'] = 1;
                }
            }


            $bankCashes = $this->Cash_model->get_bank_cashes_by_department($department_id);
            foreach ($bankCashes as $bankCashes_key => $bankCashes_value) {
                if (isset($check_create_exist[$bankCashes_value['id']])) {
                    if ($check_create_exist[$bankCashes_value['id']]['view_own_days'] == 1) {
                        // Some logic here if needed
                    } elseif ($check_create_exist[$bankCashes_value['id']]['view_global_days'] == 1) {
                        // Some logic here if needed
                    } else {
                        unset($bankCashes[$bankCashes_key]);
                    }
                } else {
                    unset($bankCashes[$bankCashes_key]);
                }
            }
            echo json_encode($bankCashes);
        }
    }

    public function get_bank_cash_by_department($department_id) {
        // Assuming BankCash_model is loaded and has a method get_bank_cash_by_department
        if (is_admin()) {
            $bankCashes = $this->Cash_model->get_bank_cashes_by_department($department_id);
            echo json_encode($bankCashes);
        } else {
            $current_staff_id = get_staff_user_id(); // Assumed function to get current staff ID
            $permissions = $this->get_cash_employee_permissions($current_staff_id);
            $check_create_exist = array();

            foreach ($permissions as $permissions_key => $permissions_value) {
                if($permissions_value['permission_type'] == 'view_own_days') {
                    $check_create_exist[$permissions_value['cash_id']]['view_own_days'] = 1;
                } elseif($permissions_value['permission_type'] == 'view_global_days') {
                    $check_create_exist[$permissions_value['cash_id']]['view_global_days'] = 1;
                } elseif($permissions_value['permission_type'] == 'create') {
                    $check_create_exist[$permissions_value['cash_id']]['create'] = 1;
                }
            }


            $bankCashes = $this->Cash_model->get_bank_cashes_by_department($department_id);
            foreach ($bankCashes as $bankCashes_key => $bankCashes_value) {
                if (isset($check_create_exist[$bankCashes_value['id']])) {
                    if ($check_create_exist[$bankCashes_value['id']]['view_own_days'] == 1 && $check_create_exist[$bankCashes_value['id']]['create'] == 1) {
                        // Some logic here if needed
                    } elseif ($check_create_exist[$bankCashes_value['id']]['view_global_days'] == 1 && $check_create_exist[$bankCashes_value['id']]['create'] == 1) {
                        // Some logic here if needed
                    } else {
                        unset($bankCashes[$bankCashes_key]);
                    }
                } else {
                    unset($bankCashes[$bankCashes_key]);
                }
            }
            echo json_encode($bankCashes);
        }
    }

}
