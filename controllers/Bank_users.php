<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_Users extends Admin_controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Department_model');
    }

    // List all users
    public function index() {
        $data['users'] = $this->User_model->get_users();
        $this->load->view('users/list', $data);
    }

    // Show form for adding a new user
    public function create() {
        $data['departments'] = $this->Department_model->get_departments();
        $this->load->view('users/add', $data);
    }

    // Store a new user
    public function store() {
        $data = $this->input->post();
        $this->User_model->add_user($data);
        redirect(admin_url('bank_module/bank_users'));
    }

    // Show form for editing a user
    public function edit($id) {
        $data['user'] = $this->User_model->get_user($id);
        $data['departments'] = $this->Department_model->get_departments();
        $this->load->view('users/edit', $data);
    }

    // Update a user
    public function update($id) {
        $data = $this->input->post();
        $this->User_model->update_user($id, $data);
        redirect(admin_url('bank_module/bank_users'));
    }

    // Delete a user
    public function delete($id) {
        $this->User_model->delete_user($id);
        redirect(admin_url('bank_module/bank_users'));
    }
}
