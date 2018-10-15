<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.6.X
// * @copyright Copyright (c) 2018, Virtualname
// * @version 1.1.16
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: VIRTUALNAME INIT module
// *************************************************************************

if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

#REQUIRES-INCLUDES#
require_once dirname(__FILE__) . '/lib/classes/class.admin.php';
require_once dirname(__FILE__) . '/lib/classes/class.api.php';
require_once dirname(__FILE__) . '/lib/classes/class.install.php';
require_once dirname(__FILE__) . '/lib/classes/class.domains.php';
require_once dirname(__FILE__) . '/lib/classes/class.contacts.php';
require_once dirname(__FILE__) . '/lib/classes/class.nameservers.php';
require_once dirname(__FILE__) . '/lib/classes/class.prices.php';
#INSTANCES#
$vname_admin = new Virtualname_admin();
$vname_install = new Virtualname_install();
$vname_api = new Virtualname_api();
$vname_domains = new Virtualname_domains();
$vname_contacts = new Virtualname_contacts();
$vname_nameservers = new Virtualname_nameservers();
$vname_prices = new Virtualname_prices();
?>