<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_Departments extends Admin_controller {

    public function __construct() {
        parent::__construct();
        log_message('error', 'Bank_Departments constructor called');

        if (!is_admin()) {
            access_denied('Bank Module');
        }
        $this->load->model('Department_model');
    }

    // List all departments
    public function index() {
        $data['title'] = _l('Bank Department');
        $data['bank_departments'] = $this->Department_model->get_departments();
        $this->load->view('bank_departments/list', $data);
    }

    // Show form for adding a new department
    public function create() {
        $this->load->view('bank_departments/add');
    }

    // Store a new department
    public function store() {
        $data = $this->input->post();
        $this->Department_model->add_department($data);
        redirect(admin_url('bank_module/bank_departments'));
    }

    // Show form for editing a department
    public function edit($id) {
        $data['department'] = $this->Department_model->get_department($id);
        $this->load->view('bank_departments/edit', $data);
    }

    // Update a department
    public function update($id) {
        $data = $this->input->post();
        $this->Department_model->update_department($id, $data);
        redirect(admin_url('bank_module/bank_departments'));
    }

    // Delete a department
    public function delete($id) {
        $this->Department_model->delete_department($id);
        redirect(admin_url('bank_module/bank_departments'));
    }
}
