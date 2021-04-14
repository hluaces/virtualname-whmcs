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

if (!defined('WHMCS'))
	die('This file cannot be accessed directly');

//FORCE DISABLE WARNINGS
$disable_warnings = false;
if($disable_warnings)
	ini_set('error_reporting', 0 );

#############################################
######## INIT FUNCTIONS ####################
#############################################
//INIT
function virtualname_tools_init(){
	#REQUIRES-INCLUDES#
	require_once dirname(__FILE__) . '/lib/classes/class.admin_tools.php';
	require_once dirname(__FILE__) . '/lib/classes/class.domains_tools.php';
	require_once dirname(__FILE__) . '/lib/classes/class.contacts_tools.php';
	require_once dirname(__FILE__) . '/lib/classes/class.invoices_tools.php';
	require_once dirname(__FILE__) . '/lib/classes/class.prices_tools.php';
	require_once dirname(__FILE__) . '/../../../includes/registrarfunctions.php';
}
//CONFIGURATION
function virtualname_tools_config(){
	//INIT
	virtualname_tools_init();
	// REQUIRES
	require_once('../modules/addons/virtualname_tools/config/locales/langs.php');
	$adminID 	= $_SESSION['adminid'];
	$admin_lang = tools_get_config_lang($adminID);
	$_LANG_VN   = tools_langs($admin_lang);
	$configarray = array(
		'name' => $_LANG_VN['config']['name'],
		'description' 	 => $_LANG_VN['config']['description'].' <a target=\'_blank\' href=\'http://whmcs.virtualname.net\'>whmcs.virtualname.net</a>',
		'version' 		 => '1.1.9',
		'author' 		 => 'Virtualname TCpanel',
		'language' 		 => 'spanish',
		'fields' => array(
			'syncData'  	   => array ('FriendlyName' => $_LANG_VN['sync']['real'], 				'Type' => 'yesno', 'Description' => $_LANG_VN['sync']['realdesc'], 'Default'=>'yes'),
			'updateDomains'    => array ('FriendlyName' => $_LANG_VN['sync']['updatedomain'], 		'Type' => 'yesno', 'Description' => $_LANG_VN['sync']['updatedomaindesc']),
			'updateContacts'   => array ('FriendlyName' => $_LANG_VN['sync']['updatecontact'], 		'Type' => 'yesno', 'Description' => $_LANG_VN['sync']['updatecontactdesc']),
			'updateExpireDoms' => array ('FriendlyName' => $_LANG_VN['sync']['updateexpiredoms'], 	'Type' => 'yesno', 'Description' => $_LANG_VN['sync']['updateexpiredomsdesc']),
			'updateCancelDoms' => array ('FriendlyName' => $_LANG_VN['sync']['updatecanceldoms'], 	'Type' => 'yesno', 'Description' => $_LANG_VN['sync']['updatecanceldomsdesc']),
		)
	);
	return $configarray;
}
//LOAD ALL FUNCTIONS
function virtualname_tools_load($vars, $action){
	global $tb_virtualname_tools, $_LANG_VN, $module_config, $vname_domains;
	//INIT
	virtualname_tools_init();
	// SET CONFIG VALUES
	$module_config = $vars;
	// REQUIRES
	require_once('../modules/addons/virtualname_tools/config/locales/langs.php');
	$adminID 	= $_SESSION['adminid'];
	$admin_lang = tools_get_config_lang($adminID);
	$_LANG_VN   = tools_langs($admin_lang);

	// REQUIRES
	require_once('../modules/registrars/virtualname/virtualname.php');
	virtualname_init();

	// CONFIG
	$module_name 	= 'virtualname-tcpanel-tools';
	$module_path 	= dirname(__FILE__);
	if(isset($_POST['action_domains']))
		$action_domains = $_POST['action_domains'];
	else
		$action_domains = '';

	if(isset($_GET['position']))
		$position 	= $_GET['position'];
	else
		$position 	= '';

	#$modulelink -> CUSTOM WHMCS VALUE MODULE URL
	$modulelink = $vars['modulelink'];

	#$version
	$version = $vars['version'];

	//DB TABLES
	$tb_virtualname_tools  = 'mod_virtualname_tools';

	//CHECK INSTALL STATUS OR REQUEST
	$is_installed = tools_check_install();
	if(!$is_installed){
		if($action == 'install'){
			virtualname_tools_installation();
			header('Location: '.$modulelink);
			exit;
		}
		else{
			$response  = '<p><strong>'.$_LANG_VN['install']['status'].'</strong></p>'.PHP_EOL;
			$response .= '<p>'.$_LANG_VN['install']['description'].'</p>'.PHP_EOL;
			$response .= '<p>'.$_LANG_VN['install']['install'].'</p>'.PHP_EOL;
			$response .= '<p><input type=\'button\' value=\''.$_LANG_VN['install']['installbutton'].'\' onclick="window.location=\''.$modulelink.'&tab=install\'"></p>';
		}
	}

	//CHECK VIRTUALNAME VALIDATE VERSION
	$is_validate_version = tools_check_version();
	if(!$is_validate_version){
		$response  = '<p><strong>'.$_LANG_VN['install']['domain_module_check'].'</strong></p>'.PHP_EOL;
		$response .= '<p>'.$_LANG_VN['install']['domain_module_check_desc_first'].'</p>'.PHP_EOL;
		$response .= '<p>'.$_LANG_VN['install']['domain_module_check_desc_second'].'</p>'.PHP_EOL;
		return $response;
	}
	//DOMAINS TOOLS ACTIONS
	if($action_domains){
		if($action_domains == 'clients_review'){
			$domains_contacts_review = $_POST['clients_review_fix'];
			virtualname_tools_contacts_fix($domains_contacts_review, 'clients');
			$position = 'clients_whmcs_fix';
			$action   = 'contacts';
		}
		elseif($action_domains == 'contacts_review'){
			$domains_contacts_review = $_POST['contacts_review_fix'];
			virtualname_tools_contacts_fix($domains_contacts_review, 'contacts');
			$position = 'contacts_whmcs_fix';
			$action   = 'contacts';
		}
		elseif($action_domains == 'domains_expirations_check'){
			$domains_checked = $_POST['domains_due_dates'];
			foreach($domains_checked as $domain){
				$sql = 'UPDATE tbldomains SET nextduedate = expirydate  WHERE id = '.$domain;
				$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
			}
			$position = 'domainManagementDueDate';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_clean_check'){
			$domains_clean = $_POST['domains_clean_fix'];
			foreach($domains_clean as $domain){
				$sql = 'UPDATE tbldomains SET registrar = \'virtualname\', domain = REPLACE(domain,\' \',\'\')  WHERE id = '.$domain;
				$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
			}
			$position = 'domainsCleaner';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_transfer_on_renewal_check'){
			$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
			$select_transfer_on_renewal  = $_POST['select_transfer_on_renewal_first'];
			$delete_transfer_on_renewal  = false;
			if($select_transfer_on_renewal == 'authcode_active'){
				$type = 1;
				$status = 'active';
			}
			elseif($select_transfer_on_renewal == 'authcode_inactive'){
				$type = 1;
				$status = 'disabled';
			}
			elseif($select_transfer_on_renewal == 'mail_active'){
				$type = 2;
				$status = 'active';
			}
			elseif($select_transfer_on_renewal == 'mail_inactive'){
				$type = 2;
				$status = 'disabled';
			}
			elseif($select_transfer_on_renewal == 'delete'){
				$delete_transfer_on_renewal = true;
			}
			else{
				$type = 1;
				$status = 'disabled';
			}

			if($delete_transfer_on_renewal){
				$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
				virtualname_tools_delete_tor($domains_transfer_on_renewal);
			}
			else{
				foreach($domains_transfer_on_renewal as $domain){
					$domain_data  = $vname_domains->get_whmcs_domain('', $domain);
					$sql  = 'INSERT INTO mod_virtualname_transfer_on_renewal (domainid, registrar, type, status) VALUES (\''.$domain.'\', \''.$domain_data['registrar'].'\', \''.$type.'\', \''.$status.'\') ON DUPLICATE KEY UPDATE ';
					$sql .= 'status = \''.$status.'\', type = '.$type;
					$res = mysql_query($sql);
					if($type == 2)
						virtualname_tools_set_authcode_transfer(array($domain));
				}
			}
			$position = 'domainsTransferOnRenewal';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_transfer_on_renewal_get_authcodes'){
			$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
			virtualname_tools_set_authcode_transfer($domains_transfer_on_renewal);
			$position = 'domainsTransferOnRenewal';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_transfer_on_renewal_set_old_mails'){
			$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
			virtualname_tools_set_old_emails($domains_transfer_on_renewal);
			$position = 'domainsTransferOnRenewal';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_transfer_on_renewal_check_status'){
			$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
			virtualname_tools_check_status($domains_transfer_on_renewal);
			$position = 'domainsTransferOnRenewal';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_transfer_on_renewal_init_transfer'){
			$domains_transfer_on_renewal = $_POST['domains_transfer_on_renewal_fix'];
			virtualname_tools_init_transfer($domains_transfer_on_renewal);
			$position = 'domainsLaunchTransferOnRenewal';
			$action   = 'domains';
		}
		elseif($action_domains == 'domains_import_list'){
			$domains_import = $_POST['domains_import_list_check'];
			$paymentmethod = $_POST['selectedGateway'];
			$clientid = $_POST['selectedClientContacts'];
			foreach($domains_import as $domainData){
				$domain = explode(';', $domainData);
				$expirydate = $domain[2];
				$status = $domain[1];
				$nextduedate = $domain[2];
				$nextinvoicedate = $domain[2];
				$tld = substr($domain[0], strrpos($domain[0], '.')+1);
				$sql = 'select msetupfee from tblpricing where relid = (select id from tbldomainpricing where extension = \'.'.$tld.'\' limit 1) and type = \'domainregister\' and tsetupfee = (select groupid from tblclients where id = \''.$clientid.'\')';
				$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
				$recurringamount = mysql_fetch_array($res);
				$sql  = 'insert into tbldomains (userid, type, domain, recurringamount, registrar, registrationperiod, expirydate, status, nextduedate, nextinvoicedate, paymentmethod)';
				$sql .= ' values (\''.$clientid.'\',\'Register\', \''.$domain[0].'\', \''.$recurringamount['msetupfee'].'\', \'virtualname\', \'1\', \''.$expirydate.'\', \''.$status.'\', \''.$nextduedate.'\', \''.$nextinvoicedate.'\', \''.$paymentmethod.'\')';
				$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
			}
			$position = 'domainsImport';
			$action   = 'imports';
		}
		elseif($action_domains == 'domains_import_tld_list'){
			$domains_import_tld = $_POST['domains_import_tld_list_check'];
			foreach($domains_import_tld as $tld){
				$sql  = 'insert into tbldomainpricing (extension, autoreg)';
				$sql .= ' values (\'.'.$tld.'\',\'virtualname\')';
				$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
			}
			$position = 'TLDsImport';
			$action   = 'imports';
		}
	}
	if($is_installed){
		$nav_tab = '';
		$status = tools_check_api();
		if($status == true){
			//CHECK VIRTUALNAME SYNC STATUS
			$is_last_sync = tools_check_last_sync();
			if($is_last_sync){
				$response = '<div id="refresh">';
				$response .= '<p><strong>'.$_LANG_VN['install']['domain_module_check_sync'].'</strong></p>'.PHP_EOL;
				$response .= '<p>'.$_LANG_VN['install']['domain_module_check_sync_desc_first'].'</p>'.PHP_EOL;
				$response .= '<p>'.$_LANG_VN['install']['domain_module_check_sync_desc_second'].'</p>'.PHP_EOL;
				$response .= '<p><a onclick=\'refreshSync("1", "");return true;\' class=\'btn btn-warning\'>'.$_LANG_VN['install']['domain_refresh_sync'].'</a><img id=\'refresh_loading\' name=\'refresh_loading\' src=\'../modules/addons/virtualname_tools/themes/images/vn_loading.gif\' style=\'display:none;padding-left: 20px;padding-right:20px;height:30px;\' /></p>';
				$response .= '</div>';
			}
			$response .= tools_menu($action, $position, $nav_tab);
		}
		else{
			$response  = '<p><strong>'.$_LANG_VN['apierror']['status'].'</strong></p>'.PHP_EOL;
			$response .= '<p>'.$_LANG_VN['apierror']['description'].'</p>'.PHP_EOL;
			$response .= '<p>'.$_LANG_VN['apierror']['check'].'</p>'.PHP_EOL;
			$response .= '<p><a href=\'mailto:dominios@virtualname.es\' style=\'color:blue\'>DOMINIOS@VIRTUALNAME.ES</a></p>';
		}
	}
	$response .= '</div>'.PHP_EOL;
	return $response;
}
//OUTPUT MODULE SHOW
function virtualname_tools_output($vars){
	$tab = '';
	if(isset($_GET['tab']))
		$tab = $_GET['tab'];
	$response = virtualname_tools_load($vars, $tab);
	print($response);
}
//MODULE INSTALLATION
function virtualname_tools_installation() {
	global $tb_virtualname_tools;
	$sql =
	'CREATE TABLE IF NOT EXISTS `'.$tb_virtualname_tools.'` (
	  `config` char(50) NOT NULL,
	  `type` char(50) NOT NULL,
	  `value`  longtext DEFAULT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=utf8';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
}
//DEACTIVATE MODULE
function virtualname_tools_deactivate() {
	global $tb_virtualname_tools;
	$query = 'DROP TABLE `mod_virtualname_tools`';
	$result = full_query($query);
}
//UPGRADE VERSION
function virtualname_tools_upgrade($vars){
	$version = $vars['version'];
}
?>