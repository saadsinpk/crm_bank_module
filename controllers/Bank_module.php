<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Bank_module extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    log_message('error', 'Bank Modules constructor called');

        if (!is_admin()) {
            access_denied('Bank Module');
        }
    }
    public function index()
    {
        $data['title'] = _l('Bank');
        $this->load->view('bank_module/main_view', $data);
    }

    public function reset()
    {
        update_option('bank_module', '[]');
        redirect(admin_url('bank_module'));
    }

    public function save()
    {
        hooks()->do_action('before_save_bank_module');

        update_option('bank_module', $this->input->post('data'));

        foreach(['admin_area','clients_area','clients_and_admin'] as $css_area) {
            // Also created the variables
            $$css_area = $this->input->post($css_area);
            $$css_area = trim($$css_area);
            $$css_area = nl2br($$css_area);
        }

        update_option('bank_module_custom_admin_area', $admin_area);
        update_option('bank_module_custom_clients_area', $clients_area);
        update_option('bank_module_custom_clients_and_admin_area', $clients_and_admin);
    }
}
