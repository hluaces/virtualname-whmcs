<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.8.X
// * @copyright Copyright (c) 2019, Virtualname
// * @version 1.1.19
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: General CALLBACK returns
// *************************************************************************

//INSTALL REQUIRES
require_once(realpath(dirname(__FILE__).'/../../../../..').'/configuration.php');
require_once(realpath(dirname(__FILE__).'/../../../../..').'/init.php');
require_once(realpath(dirname(__FILE__).'/../../../../..').'/includes/functions.php');

//INIT WHMCS
require_once(realpath(dirname(__FILE__).'/../..').'/virtualname.php');
global $vname_domains;
virtualname_init();

if($_GET)
    $action = $_GET['action'];
if($_POST)
    $action = $_POST['action'];
if($_SERVER['argv'][0])
    $action = $_SERVER['argv'][1];

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
elseif($action == 'outboundTransfer'){
    $vname_domains->tools_list(0, 0, 0, 'outbound_transfer');
    $vname_domains->outbounds_mailing();
    $domainsync = $vname_domains->tools_domainsync_status('', 'mod_virtualname_outbounds');
    print_r(json_encode($domainsync));
}
elseif($action == 'pendingDomains'){
    $vname_domains->tools_list(0, 0, 0, 'pending_domains');
    $domainsync = $vname_domains->tools_domainsync_status('pending', 'mod_virtualname_pendings');
    print_r(json_encode($domainsync));
}
?>