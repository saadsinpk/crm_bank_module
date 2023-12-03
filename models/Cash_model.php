<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cash_model extends App_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add_cash($data) {
        $this->db->insert('bank_cash_cashes', $data);
        $cash_id = $this->db->insert_id();
        return $cash_id;
    }

    private function insert_permissions($cash_id, $permissions) {
        foreach ($permissions as $permission) {
            $permission_data = [
                'cash_id' => $cash_id,
                'employee_id' => $permission['employee_id'],
                'permission_type' => $permission['permission_type'],
                'days' => isset($permission['days']) ? $permission['days'] : null,
                'is_department_employee' => $permission['is_department_employee']
            ];
            $this->db->insert('cash_employee_permissions', $permission_data);
        }
    }
    public function insert_permission($data) {
        $this->db->insert('cash_employee_permissions', $data);
    }
    
    // Get a single cash by ID
    public function get_cash($id) {
        $this->db->where('id', $id);
        return $this->db->get('bank_cash_cashes')->row();
    }

    public function get_cashs() {
        $this->db->select('tblbank_cash_cashes.*, tbldepartments.name as department_name');
        $this->db->from('tblbank_cash_cashes');
        $this->db->join('tbldepartments', 'tblbank_cash_cashes.department_id = tbldepartments.departmentid', 'left');
        $cashs = $this->db->get()->result_array();

        foreach ($cashs as $key => $cash) {
            // Get the staff names for each cash
            $cashs[$key]['staff_names'] = $this->get_staff_names_by_cash($cash['id']);
        }

        return $cashs;
    }

    private function get_staff_names_by_cash($cash_id) {
        $this->db->select('CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as staff_name');
        $this->db->from('tblstaff');
        $this->db->join('cash_employee_permissions', 'tblstaff.staffid = cash_employee_permissions.employee_id');
        $this->db->where('cash_employee_permissions.cash_id', $cash_id);
        $this->db->where_in('cash_employee_permissions.permission_type', ['view_global', 'view_own']);
        $staff = $this->db->get()->result_array();

        // Concatenate staff names into a single string
        $staff_names = array_map(function($item) {
            return $item['staff_name'];
        }, $staff);

        return implode(', ', $staff_names);
    }


    private function get_staff_names_by_department($department_id) {
        $this->db->select('CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as staff_name');
        $this->db->from('tblstaff');
        $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'inner');
        $this->db->where('tblstaff_departments.departmentid', $department_id);
        $result = $this->db->get()->result_array();

        // Concatenate staff names into a single string
        $staff_names = array_map(function($item) {
            return $item['staff_name'];
        }, $result);

        return implode(', ', $staff_names);
    }

    // Update a cash's information, including department
    public function update_cash($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('bank_cash_cashes', $data);
        return $this->db->affected_rows();
    }

    // Delete a cash
    public function delete_cash($id) {
        $this->db->where('id', $id);
        $this->db->delete('bank_cash_cashes');

        $this->db->where('cash_id', $id);
        $this->db->delete('cash_employee_permissions');
        return $this->db->affected_rows();
    }

    // Method to get bank cashes by department ID
    public function get_bank_cashes_by_department($department_id) {
        $this->db->select('*');
        $this->db->from('tblbank_cash_cashes');
        $this->db->where('department_id', $department_id);
        $query = $this->db->get();

        return $query->result_array();
    }

    public function update_employee_permissions($cash_id, $department_employees) {
        // Assuming you have a table 'cash_employee_permissions' to store these permissions
        // You need to define the structure of your $department_employees and $other_employees
        // and how they are processed. Below is a simplified example.

        // Delete existing permissions for the cash
        $this->db->where('cash_id', $cash_id);
        $this->db->delete('cash_employee_permissions');
        
        // Process department employees
        foreach ($department_employees as $employee_id => $permissions) {
            $data = [
                'cash_id' => $cash_id,
                'employee_id' => $employee_id,
                // Add other permission fields here based on your form structure
            ];
            $this->db->insert('cash_employee_permissions', $data);
        }

        // Process other employees
        // foreach ($other_employees as $employee_id => $permissions) {
        //     $data = [
        //         'cash_id' => $cash_id,
        //         'employee_id' => $employee_id,
        //     ];
        //     $this->db->insert('cash_employee_permissions', $data);
        // }
    }

    // Method to fetch employees who are part of a specific department
    public function get_department_employees($department_id) {
        $this->db->select('tblstaff.staffid, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tblstaff');
        $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'inner');
        $this->db->where('tblstaff_departments.departmentid', $department_id);
        return $this->db->get()->result_array();
    }

    // Method to fetch employees who are not part of a specific department
    public function get_other_employees($department_id) {
        $this->db->select('tblstaff.staffid, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tblstaff');
        $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'left outer');
        $this->db->where('tblstaff_departments.departmentid !=', $department_id);
        $this->db->or_where('tblstaff_departments.departmentid IS NULL', null, false);
        return $this->db->get()->result_array();
    }    
    public function get_current_permissions($cash_id, $department_id) {
        // This method should return an array of permissions for the given cash and department.
        // The exact implementation will depend on your database schema.

        // Example query (you'll need to adjust this based on your database structure):
        $this->db->select('employee_id, permission_type, days, is_department_employee');
        $this->db->from('cash_employee_permissions');
        $this->db->where('cash_id', $cash_id);
        $this->db->join('tblstaff', 'cash_employee_permissions.employee_id = tblstaff.staffid', 'inner');
        $this->db->join('tblstaff_departments', 'tblstaff.staffid = tblstaff_departments.staffid', 'inner');
        // $this->db->where('tblstaff_departments.departmentid', $department_id);

        $query = $this->db->get();

        if ($query) {
            return $query->result_array();
        } else {
            // Handle the error appropriately
            log_message('error', 'Error fetching current permissions: ' . $this->db->last_query());
            return [];
        }
    }
    public function user_has_permission($cash_id, $permission_type = '') {
        $user_id = $this->session->userdata('user_id');

        // Adjust the query according to your database schema
        $this->db->where('cash_id', $cash_id);
        // $this->db->where('user_id', $user_id);
        // $this->db->where('permission_type', $permission_type);
        $query = $this->db->get('cash_employee_permissions');

        return $query->result_array();
        return $query->num_rows() > 0;
    }    
}
