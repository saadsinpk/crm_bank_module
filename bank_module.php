<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Name: Bank Module
 * Description: Custom module for managing departments and users.
 * Version: 1.0.0
 * Author: Sid Techno
 * Author URI: sidtechno.com
 */

$module_version = '1.0.0';

define('BANK_MODULE_MODULE_NAME', 'bank_module');
$CI = &get_instance();

/**
 * Register activation module hook
 */
register_activation_hook(BANK_MODULE_MODULE_NAME, 'bank_module_activation_hook');

function bank_module_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(BANK_MODULE_MODULE_NAME, [BANK_MODULE_MODULE_NAME]);

hooks()->add_action('admin_init', 'bank_module_init_menu_items');

function bank_module_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('bank-module', [
        'slug' => 'bank-module', 
        'name' => 'Bank Module', 
        'icon' => 'fa fa-bank', 
        'href' => admin_url('bank_module'),
        'position' => 5,
    ]);
    $CI->app_menu->add_sidebar_children_item('bank-module', [
        'slug' => 'transaction', 
        'name' => 'Transaction', 
        'href' => admin_url('bank_module/transaction')
    ]);
    if (is_admin()) {

        if (!$CI->db->table_exists(db_prefix() . 'cash_employee_permissions')) {
            $query = 'CREATE TABLE `' . db_prefix() . 'cash_employee_permissions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `cash_id` int(11) NOT NULL,
                `employee_id` int(11) NOT NULL,
                `permission_type` varchar(50) NOT NULL,
                `days` int(11) DEFAULT NULL,
                `is_department_employee` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        }

        // Create bank_departments table
        if (!$CI->db->table_exists(db_prefix() . 'bank_departments')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'bank_departments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        // Create bank_users table
        if (!$CI->db->table_exists(db_prefix() . 'bank_users')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'bank_users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `department_id` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `department_id` (`department_id`),
              FOREIGN KEY (`department_id`) REFERENCES `' . db_prefix() . 'bank_departments` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        // Create bank_cash table
        if (!$CI->db->table_exists(db_prefix() . 'bank_cash_cashes')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'bank_cash_cashes` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `department_id` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }


        // Create bank_transactions table
        if (!$CI->db->table_exists(db_prefix() . 'bank_cash_transactions')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'bank_cash_transactions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `department_id` int(11) NOT NULL,
                `cash_id` int(11) NOT NULL,
                `name_id` int(11) NOT NULL,
                `date` date NOT NULL,
                `transaction_type` varchar(20) NOT NULL,
                `balance` varchar(20) NOT NULL,
                `amount` decimal(10,2) NOT NULL,
                PRIMARY KEY (`id`)
                -- Add foreign key for name_id if applicable
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        $CI->app_menu->add_sidebar_children_item('bank-module', [
            'slug' => 'bankcash', 
            'name' => 'Cash List', 
            'href' => admin_url('bank_module/bank_cash')
        ]);
    }
}
