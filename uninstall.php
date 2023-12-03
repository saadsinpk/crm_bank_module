<?php
defined('BASEPATH') or exit('No direct script access allowed');

function bank_module_uninstall() {
    $CI = &get_instance();

    // Include migration file
    include_once(__DIR__ . '/migrations/001_Add_bank_departments_table.php');
    include_once(__DIR__ . '/migrations/002_Modify_bank_users_table.php');

    // Revert migrations
    $CI->load->library('migration');

    $migration = new Migration_Modify_users_table();
    $migration->down();

    $migration = new Migration_Add_departments_table();
    $migration->down();

    return true;
}
