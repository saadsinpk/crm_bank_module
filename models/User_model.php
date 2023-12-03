<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends App_Model {

    public function __construct() {
        parent::__construct();
    }

    // Add a new user with department
    public function add_user($data) {
        // Assuming 'users' is your user table and it has a 'department_id' column
        $this->db->insert('staff', $data);
        return $this->db->insert_id();
    }

    // Get a single user by ID
    public function get_user($id) {
        $this->db->where('id', $id);
        return $this->db->get('staff')->row();
    }

    // Get all users with department information
    public function get_users() {
        $this->db->select('staff.*');
        $this->db->from('staff');
        return $this->db->get()->result_array();
    }

    // Update a user's information, including department
    public function update_user($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('staff', $data);
        return $this->db->affected_rows();
    }

    // Delete a user
    public function delete_user($id) {
        $this->db->where('id', $id);
        $this->db->delete('staff');
        return $this->db->affected_rows();
    }
    
    public function get_users_by_department($department_id) {
        $this->db->select('tblstaff.staffid, tblstaff.firstname, tblstaff.lastname, tblstaff.email');
        $this->db->from('tblstaff');
        $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'inner');
        $this->db->where('tblstaff_departments.departmentid', $department_id);
        $query = $this->db->get();

        return $query->result_array();
    }
    public function has_permission($permission_type) {
        // Get user ID from session or authentication system
        $user_id = $this->session->userdata('user_id');

        // Query the database to check if the user has the specified permission
        // This is a simplified example; adjust according to your database schema
        $this->db->where('user_id', $user_id);
        $this->db->where('permission', $permission_type);
        $query = $this->db->get('user_permissions');

        return $query->num_rows() > 0;
    }

}
