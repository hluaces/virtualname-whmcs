<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.2
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: Install registrars
// *************************************************************************

//INSTALL REQUIRES
require_once('../../../../../configuration.php');
require_once('../../../../../init.php');
require_once('../../../../../includes/functions.php');

//FILE VALIDATION
if (!defined('WHMCS') || !isset($_SESSION['adminid'])) die('This file cannot be accessed directly');

//INIT WHMCS
require_once('../../virtualname.php');
global $vname_admin, $vname_install, $vname_prices;
virtualname_init();

if(!$customadminpath || trim($customadminpath) == '')
    $customadminpath = 'admin';

//CALL INSTALL UNINSTALL
$action = $_GET['action'];
if($action == 'install'){
    $response = $vname_install->check_module_install($customadminpath);
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, $response['error']);
}
elseif($action == 'uninstall'){
    $response = $vname_install->check_uninstall_registrar($customadminpath);
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, $response['error']);
}
elseif($action == 'update'){
    $version = $_GET['version'];
    $whmcs   = $_GET['version'];
    $response = $vname_install->check_update($customadminpath, $version);
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, $response['error']);
}
elseif($action == 'updateprices'){
    $idprice       = $_GET['idprice'];
    $client_group  = $_GET['group'];
    $response = $vname_prices->check_update_price($customadminpath, $idprice, $client_group);
    //RETURN ADMIN CALL
    $vname_admin->return_tools_page($customadminpath, $action, $response['error']);
}
elseif($action == 'sendErrors'){
    $response = $vname_admin->create_file_log();
    $response = $vname_admin->send_error_logs($GLOBALS['CONFIG']['CompanyName'], $GLOBALS['CONFIG']['Email']);
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, false);
}
elseif($action == 'errorsClean'){
    $response = $vname_admin->clean_error_logs();
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, false);
}
elseif($action == 'installtables'){
    $response = $vname_install->install_tables();
    //RETURN ADMIN CALL
    $vname_admin->return_admin_page($customadminpath, $action, $response['error']);
}
elseif($action == 'saveErrors'){
    $response = $vname_admin->create_file_log();
    if($response){
        $return = false;
        $error_file = '../../logs/virtualname_errors_log.txt';
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=\'virtualname_errors_log.txt\'');
        header('Content-Length: ' . filesize($error_file));
        readfile($error_file);
        exit;
    }
    else
        $return = 'Cant\' download error file';
}
?>