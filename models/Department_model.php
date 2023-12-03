<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Department_model extends App_Model {

    public function __construct() {
        parent::__construct();
    }

    // Add a new department
    public function add_department($data) {
        $this->db->insert('departments', $data);
        return $this->db->insert_id();
    }

    // Get a single department by ID
    public function get_department($id) {
        $this->db->where('id', $id);
        return $this->db->get('departments')->row();
    }

    // Get all departments
    public function get_departments_view() {
        if (is_admin()) {
            return $this->db->get('departments')->result_array();
        } else {
            $current_staff_id = get_staff_user_id(); // Assumed function to get current staff ID

            // Get the departments associated with the current staff
            $this->db->select('departmentid');
            $this->db->from('tblstaff_departments');
            $this->db->where('staffid', $current_staff_id);
            $staff_departments = $this->db->get()->result_array();

            $staff_departments_sec = $this->get_all_department_base_permission_view($current_staff_id);

            if (empty($staff_departments) AND empty($staff_departments_sec)) {
                // Handle case where no departments are associated with the staff
                return []; // or any appropriate response
            }
            $department_ids = array_column($staff_departments, 'departmentid');
            $merged_departments = array_merge($staff_departments_sec, $department_ids);

            // Extract department IDs

            // Now get the departments that the staff member is allowed to see
            $this->db->select('*');
            $this->db->from('tbldepartments');
            $this->db->where_in('departmentid', $merged_departments); // Update this line with the correct column name
            return $this->db->get()->result_array();
        }
    }

    public function get_departments() {
        if (is_admin()) {
            return $this->db->get('departments')->result_array();
        } else {
            $current_staff_id = get_staff_user_id(); // Assumed function to get current staff ID

            // Get the departments associated with the current staff
            $this->db->select('departmentid');
            $this->db->from('tblstaff_departments');
            $this->db->where('staffid', $current_staff_id);
            $staff_departments = $this->db->get()->result_array();

            $staff_departments_sec = $this->get_all_department_base_permission($current_staff_id);

            if (empty($staff_departments) AND empty($staff_departments_sec)) {
                // Handle case where no departments are associated with the staff
                return []; // or any appropriate response
            }
            $department_ids = array_column($staff_departments, 'departmentid');
            $merged_departments = array_merge($staff_departments_sec, $department_ids);

            // Extract department IDs

            // Now get the departments that the staff member is allowed to see
            $this->db->select('*');
            $this->db->from('tbldepartments');
            $this->db->where_in('departmentid', $merged_departments); // Update this line with the correct column name
            return $this->db->get()->result_array();
        }
    }

    public function get_all_department_base_permission_view($current_staff_id) {
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
        $return_kets = array();
        foreach ($check_create_exist as $check_create_exist_key => $check_create_exist_value) {
            if($check_create_exist_value['view_own_days'] == 1) {
                $return_kets[] = $check_create_exist_key;
            } elseif($check_create_exist_value['view_global_days'] == 1) {
                $return_kets[] = $check_create_exist_key;
            }
        }
        $return_department_id = array();
        foreach ($return_kets as $return_kets_key => $return_kets_value) {
            $return_department_id = array_merge($return_department_id, $this->get_department_ids_by_cash_id($return_kets_value));
        }

        // Remove duplicates and reindex
        $return_department_id = array_values(array_unique($return_department_id));
        return $return_department_id;
    }

    public function get_all_department_base_permission($current_staff_id) {
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
        $return_kets = array();
        foreach ($check_create_exist as $check_create_exist_key => $check_create_exist_value) {
            if($check_create_exist_value['view_own_days'] == 1 AND $check_create_exist_value['create'] == 1) {
                $return_kets[] = $check_create_exist_key;
            } elseif($check_create_exist_value['view_global_days'] == 1 AND $check_create_exist_value['create'] == 1) {
                $return_kets[] = $check_create_exist_key;
            }
        }
        $return_department_id = array();
        foreach ($return_kets as $return_kets_key => $return_kets_value) {
            $return_department_id = array_merge($return_department_id, $this->get_department_ids_by_cash_id($return_kets_value));
        }

        // Remove duplicates and reindex
        $return_department_id = array_values(array_unique($return_department_id));
        return $return_department_id;
    }

    public function get_department_ids_by_cash_id($cash_id) {
        $this->db->select('department_id');
        $this->db->from('tblbank_cash_cashes');
        $this->db->where('id', $cash_id);
        $result = $this->db->get()->result_array();

        // Extract department IDs
        return array_column($result, 'department_id');
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

    // Update a department
    public function update_department($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('departments', $data);
        return $this->db->affected_rows();
    }

    // Delete a department
    public function delete_department($id) {
        $this->db->where('id', $id);
        $this->db->delete('departments');
        return $this->db->affected_rows();
    }
}
