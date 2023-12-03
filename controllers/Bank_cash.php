<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_Cash extends Admin_controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Cash_model');
        $this->load->model('Department_model');
        $this->load->model('Staff_model'); // Assuming you have a model for staff
    }

    // List all cash
    public function index() {
        $data['cash'] = $this->Cash_model->get_cashs();
        $this->load->view('cash/list', $data);
    }

    // Show form for adding a new cash
    public function create() {
        $data['departments'] = $this->Department_model->get_departments();
        $this->load->view('cash/add', $data);
    }
    public function store() {
        $data = $this->input->post();

        $cash_id = $this->Cash_model->add_cash([
            'name' => $data['name'],
            'department_id' => $data['department_id']
        ]);

        // Handle permissions
        if (isset($data['permissions'])) {
            foreach ($data['permissions'] as $employee_id => $perms) {

                foreach ($perms as $perm_type => $perm_value) {
                    if ($perm_type == 'view_global' AND $perm_value == 'on') {
                        // Checkbox checked and days provided
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type,
                            'days' => $perms['view_global_days']
                        ]);
                    } elseif ($perm_type == 'view_own' AND $perm_value == 'on') {
                        // Checkbox checked and days provided
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type,
                            'days' => $perms['view_own_days']
                        ]);
                    } elseif ($perm_value) {
                        // Checkbox checked but no days
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type
                        ]);
                    }
                }
            }
        }

        redirect(admin_url('bank_module/bank_cash'));
    }

    private function formatPermissionsData($permissions) {
        $formattedPermissions = [];
        foreach ($permissions as $employee_id => $perms) {
            foreach ($perms as $type => $value) {
                if (is_array($value)) {
                    // For permissions with days
                    $formattedPermissions[] = [
                        'employee_id' => $employee_id,
                        'permission_type' => $type,
                        'days' => $value['days']
                    ];
                } else {
                    // For simple checkbox permissions
                    $formattedPermissions[] = [
                        'employee_id' => $employee_id,
                        'permission_type' => $type,
                        'days' => null
                    ];
                }
            }
        }
        return $formattedPermissions;
    }




    // Show form for editing a cash
    public function edit($id) {
        $data['cash'] = $this->Cash_model->get_cash($id);
        $data['departments'] = $this->Department_model->get_departments();
        $this->load->view('cash/edit', $data);
    }

    // Update a cash
    public function update($id) {
        $data = $this->input->post();
        $this->Cash_model->update_cash($id, $data);

        $this->db->where('cash_id', $id);
        $this->db->delete('cash_employee_permissions');
        $cash_id = $id;
        
        if (isset($data['permissions'])) {
            foreach ($data['permissions'] as $employee_id => $perms) {

                foreach ($perms as $perm_type => $perm_value) {
                    if ($perm_type == 'view_global' AND $perm_value == 'on') {
                        // Checkbox checked and days provided
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type,
                            'days' => $perms['view_global_days']
                        ]);
                    } elseif ($perm_type == 'view_own' AND $perm_value == 'on') {
                        // Checkbox checked and days provided
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type,
                            'days' => $perms['view_own_days']
                        ]);
                    } elseif ($perm_value) {
                        // Checkbox checked but no days
                        $this->Cash_model->insert_permission([
                            'cash_id' => $cash_id,
                            'employee_id' => $employee_id,
                            'permission_type' => $perm_type
                        ]);
                    }
                }
            }
        }

        redirect(admin_url('bank_module/bank_cash'));
    }
    public function update_employee_permissions($cash_id, $permissions) {
        // Assuming you have a table 'cash_employee_permissions' to store these permissions

        // Delete existing permissions for the cash
        $this->db->where('cash_id', $cash_id);
        $this->db->delete('cash_employee_permissions');

        // Insert new permissions
        foreach ($permissions as $perm) {
            $data = [
                'cash_id' => $cash_id,
                'employee_id' => $perm['employee_id'],
                'permission_type' => $perm['permission_type'],
                'days' => isset($perm['days']) ? $perm['days'] : null
                // Add other fields if necessary
            ];
            $this->db->insert('cash_employee_permissions', $data);
        }
    }



    public function insert_permissions($cash_id, $permissions) {
        foreach ($permissions as $employee_id => $perms) {
            foreach ($perms as $type => $values) {
                // You will need to adjust this part based on how your permissions data is structured
                $perm_data = [
                    'cash_id' => $cash_id,
                    'employee_id' => $employee_id,
                    'permission_type' => $type,
                    'days' => isset($values['days']) ? $values['days'] : null,
                    // other fields as necessary
                ];
                $this->db->insert('cash_employee_permissions', $perm_data);
            }
        }
    }


    // Delete a cash
    public function delete($id) {
        $this->Cash_model->delete_cash($id);
        redirect(admin_url('bank_module/bank_cash'));
    }

    public function get_employees_by_department() {
        $department_id = $this->input->get('department_id');
        
        // Fetch employees related to the department from the Cash_model
        $department_employees = $this->Cash_model->get_department_employees($department_id);
        $other_employees = $this->Cash_model->get_other_employees($department_id);

        $data = [
            'departmentEmployees' => $department_employees,
            'otherEmployees' => $other_employees
        ];

        echo json_encode($data);
    }

    public function get_current_permissions() {
        $cash_id = $this->input->get('cash_id');
        $department_id = $this->input->get('department_id');

        if (!$cash_id || !$department_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        // Fetch current permissions
        $currentPermissions = $this->Cash_model->get_current_permissions($cash_id, $department_id);

        // Fetch employees related to the department
        $department_employees = $this->Cash_model->get_department_employees($department_id);
        $other_employees = $this->Cash_model->get_other_employees($department_id);

        $data = [
            'departmentEmployees' => $department_employees,
            'otherEmployees' => $other_employees,
            'currentPermissions' => $currentPermissions
        ];

        echo json_encode($data);
    }

}
