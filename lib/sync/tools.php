<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE TOOLS
// * PLUGIN Api v1
// * WHMCS version 8.1.X
// * @copyright Copyright (c) 2021, Virtualname
// * @version 1.1.9
// * @link http://www.virtualname.es
// * @package WHMCSModule
// * @subpackage TCpanel
// * File description: Tools Extra Module
// *************************************************************************

//INSTALL REQUIRES
require_once(realpath(dirname(__FILE__).'/../../../../..').'/configuration.php');
require_once(realpath(dirname(__FILE__).'/../../../../..').'/init.php');
require_once(realpath(dirname(__FILE__).'/../../../../..').'/includes/functions.php');


//INIT WHMCS
require_once(realpath(dirname(__FILE__).'/../../../../../modules/registrars/virtualname/').'/virtualname.php');
global $vname_domains;
virtualname_init();

if($_GET)
    $action = $_GET['action'];
if($_POST)
    $action = $_POST['action'];
if($_SERVER['argv'][0])
    $action = $_SERVER['argv'][1];

if(!$customadminpath || trim($customadminpath) == '')
    $customadminpath = 'admin';

if($action == 'initsync'){
    $limit  = $_POST['limit'];
    $offset = $_POST['offset'];
    $until  = $_POST['until'];
    $status = $_POST['status'];
    $init   = $_POST['startInit'];
    if($init == 1)
        $initSync = $vname_domains->tools_list($limit, $offset, $until, $status);
    else
        $initSync = $vname_domains->tools_total_count();

    if($initSync['total_count'] == 0)
        $initSync = $vname_domains->tools_list($limit, $offset, $until, $status);
    print_r(json_encode($initSync));
}
elseif($action == 'domainsync'){
    $client_id = $_POST['client'];
    $syncExpire = $_POST['syncExpire'];
    $syncCancel = $_POST['syncCancel'];
    $updateSync = 0;
    if(isset($_POST['updateSync']))
        $updateSync = $_POST['updateSync'];
    $domainsync = $vname_domains->tools_domainsync($client_id, $updateSync, $syncExpire, $syncCancel);
    print_r(json_encode($domainsync));
}
elseif($action == 'contactsync'){
    $client = $_POST['client'];
    $updateSync = 0;
    if(isset($_POST['updateSync']))
        $updateSync = $_POST['updateSync'];
    $contactsync = $vname_domains->tools_contactsync($client, $updateSync);
    print_r(json_encode($contactsync));
}
elseif($action == 'syncDoms'){
    $syncExpire = $_POST['syncExpire'];
    $syncCancel = $_POST['syncCancel'];
    $nloop      = -1;
    $updateSync = 0;
    if(isset($_POST['nloop']))
        $nloop  = $_POST['nloop'];
    if(isset($_POST['updateSync']))
        $updateSync = $_POST['updateSync'];
    $domainsync = $vname_domains->tools_all_domainsync($nloop, $updateSync, $syncExpire, $syncCancel);
    print_r(json_encode($domainsync));
}
elseif($action == 'syncContacts'){
    $nloop = -1;
    if(isset($_POST['nloop']))
        $nloop = $_POST['nloop'];
    $updateSync = 0;
    if(isset($_POST['updateSync']))
        $updateSync = $_POST['updateSync'];
    $contactsync = $vname_domains->tools_all_contactsync($nloop, $updateSync);
    print_r(json_encode($contactsync));
}
elseif($action == 'updateprices'){
    require_once(realpath(dirname(__FILE__).'/../../../../../modules/addons/virtualname_tools/lib/classes/').'/class.prices_tools.php');
    $idprice       = $_GET['idprice'];
    $client_group  = $_GET['group'];
    $response = tools_update_whmcs_prices($idprice, $client_group);
    //RETURN ADMIN CALL
    tools_return_page($customadminpath, $action, $response['error']);
}
elseif($action == 'init_transfer'){
    require_once(realpath(dirname(__FILE__).'/../../../../../modules/addons/virtualname_tools/lib/classes/').'/class.domains_tools.php');
    $response = tools_transfers_list();
    print_r(json_encode($response));
}
elseif($action == 'launch_transfer'){
    require_once(realpath(dirname(__FILE__).'/../../../../../modules/addons/virtualname_tools/lib/classes/').'/class.domains_tools.php');
    $transfer_on_renewal = array();
    $transfer_on_renewal['domainid'] = $_POST['domain'];
    $transfer_on_renewal['type'] = $_POST['type'];
    $transfer_on_renewal['value'] = $_POST['value'];
    $response = tools_domain_transfer($transfer_on_renewal);
    print_r(json_encode($response));
}
?>
