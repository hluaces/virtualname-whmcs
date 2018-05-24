<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.5.X
// * @copyright Copyright (c) 2018, Virtualname
// * @version 1.1.14
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common false
// * File description: VIRTUALNAME ADMIN class
// *************************************************************************
class Virtualname_admin{
	#CLASS CONSTANTS#
	public $module_version = '1.1.14';
	public $vn_module_dir = 'modules/registrars/virtualname/';
	public $vn_whmcs_dir;
	public $vn_whmcs_version = '7';

	//CONSTRUCT
	public function __construct(){
		$this->vn_whmcs_dir = str_replace($this->vn_module_dir.'lib/classes', '', dirname(__FILE__));
	}

	//ADMINISTRATION CONFIG LANG
	public function get_config_lang($adminID){
	    $langs_vn = array();
	    $table = 'tbladmins';
	    $fields = 'language';
	    $where = array('id'=>$adminID);
	    $result = select_query($table,$fields,$where);
	    $data = mysql_fetch_array($result);
	    if(strtolower($data['language']) == 'spanish')
			include dirname(__FILE__) . '/../../config/locales/es.php';
	    else
	    	include dirname(__FILE__) . '/../../config/locales/en.php';
	    return $langs_vn;
	}

	//ADMINISTRATION CONFIG LANG
	public function get_config_clients_customfields(){
	    $table = 'tblcustomfields';
	    $fields = 'id, fieldname';
	    $where = array('type'=>'client');
	    $result = select_query($table,$fields,$where);
	    $data = 'Disabled';
	    while ($row = mysql_fetch_array($result)){
	        $data .= ','.$row['fieldname'];
	    }
	    return $data;

	}
	//CUSTOM ADMINISTRATION SCRIPTS AND BUTTONS
	public function custom_buttons($virtualnameInstall, $configLang){
	    $custom_buttons = array();
	    //CHECK IF FILE CAN BE WRITE-REWRITE
	    ##MODULE FILES##
	    $all_module_writable = true;
	    $writable_files_module = array(
	    		'init.php',
                'virtualname.php',
                'config/locales/en.php',
                'config/locales/es.php',
                'includes/clientareadata.php',
                'includes/clientsdatadomaincontacts.php',
                'includes/domainContacts.php',
			    'includes/virtualname_hooks.php',
			    'includes/templates/five/clientareadetailsdata.tpl',
			    'includes/templates/six/clientareadetailsdata.tpl',
                'includes/overrides/english.php',
                'includes/overrides/spanish.php',
                'lib/classes/class.admin.php',
                'lib/classes/class.api.php',
                'lib/classes/class.contacts.php',
                'lib/classes/class.domains.php',
                'lib/classes/class.install.php',
                'lib/classes/class.nameservers.php',
                'lib/classes/class.prices.php',
                'lib/classes/class.punicode.php',
                'lib/crons/sync.php',
                'lib/install/install.php',
                'logs/virtualname_errors_log.txt',
                'themes/css/virtualname_adm.css',
                'themes/js/virtualname_adm.js',
                'themes/js/virtualname_client.js'
        );
        $img_files_module = array('logo.gif', 'themes/images/vname_download.png');
	    foreach($writable_files_module as $writeFile){
	        if(!is_writable('../'.$this->vn_module_dir.$writeFile)){
	            $all_module_writable = false;
	        }
	    }
	    ##MODULE CUSTOM FILES##
	    $allCustomWritable = true;
	    $writableFilesCustom = array('../includes/hooks/virtualname_hooks.php', '../clientareadata.php', 'clientsdatadomaincontacts.php');
	    foreach($writableFilesCustom as $writeFile){
	        if(!is_writable($writeFile)){
	            $allCustomWritable = false;
	        }
	    }
	    ##MODULE ADDITIONAL FILES##
	    $allWHMCSWritable = true;
	    $writableFilesWHMCS = array('../resources/domains/dist.additionalfields.php', '../lang/overrides/english.php', '../lang/overrides/spanish.php');
	    foreach($writableFilesWHMCS as $writeFile){
	        if(!is_writable($writeFile)){
	            $allWHMCSWritable = false;
	        }
	    }
	    //CHECK CURRENT INSTALL STATUS
	    if($virtualnameInstall == 'installed' AND  $all_module_writable AND $allWHMCSWritable){
	        $installURL = '<a id=\'vn_ins\' name=\'vn_ins\' href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=uninstall\''.
	                      'class=\'btn btn-danger\' onclick=\'return confirm(\''.$configLang['uninstallconf'].'\')\'>'.$configLang['uninstall'].'</a>';
	        $warnings   = Virtualname_install::check_installation((new Virtualname_admin)->module_version, $configLang);
	        $crons      =
	            '<div class=\'contentbox\'>'.
	            $configLang['cronoutbound'].':<input type=\'text\' style=\'width:90%\' value=\'php -q '.realpath(dirname(__FILE__).'/../..').'/lib/crons/sync.php outboundTransfer\'>'.
	            '</div>'.
	            '<div class=\'contentbox\'>'.
	            $configLang['cronpending'].':<input type=\'text\' style=\'width:90%\' value=\'php -q '.realpath(dirname(__FILE__).'/../..').'/lib/crons/sync.php pendingDomains\'>'.
	            '</div>';
	    }
	    elseif($virtualnameInstall == 'uninstalled' AND  $all_module_writable){
	        $installURL = '<a id=\'vn_ins\' name=\'vn_ins\' href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=install\''.
	                      'class=\'btn btn-success\'>'.$configLang['installRegistrar'].'</a>';
	        $warnings   = '';
	        $crons      = '';
	    }
	    else{
	        $installURL = '<font color=\'red\'>'.$configLang['filesWriteDisable'].'</font>';
	        if($virtualnameInstall == 'installed'){
	            $warnings   = Virtualname_install::check_installation($this->module_version, $configLang);
	            $crons      =
	                '<div class=\'contentbox\'>'.
	                $configLang['cronoutbound'].':<input type=\'text\' style=\'width:90%\' value=\'php -q '.realpath(dirname(__FILE__).'/../..').'/lib/crons/sync.php outboundTransfer\'>'.
	                '</div>'.
	                '<div class=\'contentbox\'>'.
	                $configLang['cronpending'].':<input type=\'text\' style=\'width:90%\' value=\'php -q '.realpath(dirname(__FILE__).'/../..').'/lib/crons/sync.php pendingDomains\'>'.
	                '</div>';
	        }
	        else{
	            $warnings   = '';
	            $crons      = '';
	        }
	    }
	    //CHECK UPDATE BUTTON
	    $whmcs = $this->get_whmcs_version();
	    $version = $this->get_module_version($params);
	    $whmcs_version = explode('.',$whmcs);
	    if($version['response']['lastversion'] != $this->module_version){
	        if($all_module_writable AND $version['response']['lastversion'] != ''){
	            $updateURL = $configLang['updateAvailable'].': <span style=\'color:green;\'> v'.$version['response']['lastversion'].' </span>'.
	              '<a href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=update&version=whmcs-7.'.$version['response']['lastversion'].'\''.
	              'class=\'btn btn-success\' onclick=\'return confirm(\''.$configLang['updateconf'].'\')\'>'.$configLang['updateRegistrar'].'</a>';
	        }
	        else{
	            $updateURL = $configLang['updateAvailable'].': <span style=\'color:green;\'> v'.$version['response']['lastversion'].' </span><font color=\'red\'>'.$configLang['filesWriteDisableUpdate'].'</font>';
	        }
	    }
	    //CHECK ERROR LOG
	    $error_file = '../'.$this->vn_module_dir.'logs/virtualname_errors_log.txt';
	    if(file_exists($error_file)){
	        if(file_exists($error_file) == true){
	            $file_error  = '<a id=\'vn_errors\' name=\'vn_errors\' href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=saveErrors\' download=\'virtualname_errors_log.txt\' target=\'_blank\' download class=\'btn btn-primary\'>'.$configLang['download'].'</a>&nbsp;';
	            $file_error .= '<a id=\'vn_errors_send\' name=\'vn_errors_send\' href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=sendErrors\' class=\'btn btn-default\' onclick="return confirm(\''.$configLang['confirmErrors'].'\')">'.$configLang['sendErrors'].'</a>&nbsp;';
	            $file_error .= '<a id=\'vn_errors_clean\' name=\'vn_errors_clean\' href=\'../'.$this->vn_module_dir.'lib/install/install.php?action=errorsClean\' class=\'btn btn-warning\' onclick="return confirm(\''.$configLang['confirm_errors_clean'].'\');">'.$configLang['errors_clean'].'</a>';

	        }
	    }
	    //DOCUMENTATION URL
        $installURL .= '&nbsp;<a id=\'vn_doc\' name=\'vn_doc\' href=\'http://whmcs.virtualname.net/\''.
	                      'class=\'btn btn-primary\' target=\'_blank\'>'.$configLang['documentation'].'</a>';
	    //GET ADMIN ROLES
	    $admin_roles = $this->get_whmcs_admin_roles();

	    $custom_buttons['install']  = $installURL;
	    $custom_buttons['update']   = $updateURL;
	    $custom_buttons['warnings'] = $warnings;
	    $custom_buttons['crons']    = $crons;
	    $custom_buttons['roles']    = $admin_roles;
	    $custom_buttons['errors']   = $file_error;

	    return $custom_buttons;
	}
	//GET MODULE VERSION
	public function get_module_version($params){
	    $version_URL = 'http://whmcs.virtualname.net/whmcs-repositories/whmcs-7-virtualname-version-1';
	    $version_content = explode('-',file_get_contents($version_URL));
	    $versions = array('whmcs'=>'7.X.X', 'lastversion'=> $version_content[2]);
	    $request = array('response'=>$versions);
	    return $request;
	}
	//GET WHMCS VERSION
	public function get_whmcs_version(){
	    $table = 'tblconfiguration';
	    $fields = 'value';
	    $where = array('setting'=>'Version');
	    $result = select_query($table,$fields,$where);
	    if(mysql_num_rows($result)>0){
	        $data = mysql_fetch_array($result);
	        $version = explode('-', $data['value']);
	        $value = $version[0];
	    }
	    else{
	        $value = 0;
	    }
	    return $value;
	}
	//CUSTOM VIRTUALNAME CONFIG ARRAY
	public function config(){
		global $vname_api;
	    ######DOMAIN RULES#######
	    #### - TLD : contacts: reg, admin, tech, bill: reg => 0 DISABLE - reg => 1 ENABLE - reg => 2 ENABLE REGISTRATION ONLY
	    ######DOMAIN RULES#######
	    //GET LANGUAGES
	    $adminID = $_SESSION['adminid'];
	    $configLang = $this->get_config_lang($adminID);
	    $whmcs_version = $this->get_whmcs_version();
	    $configarray = array(
	        'URLBase'       => $vname_api->api_url,
	        'URLBase_DEBUG' => $vname_api->dev_api_url,
	        'version'       => 'v1',
	        'Module-Version'=> $this->module_version,
	        'pluginVersion' => $whmcs_version,
	        'vn_whmcs'		=> $this->vn_whmcs_version,
	        'apiResponse'   => array('200'=>$configLang['200'], '201'=>$configLang['201'], '202'=>$configLang['202'],
	                                 '400'=>$configLang['400'], '401'=>$configLang['401'], '402'=>$configLang['402'],
	                                 '404'=>$configLang['404'], '422'=>$configLang['400'], '429'=>$configLang['429'],
	                                 '500'=>$configLang['500'], '502'=>$configLang['502']),
	        'domain_rules'  => array(
	                                '.es'     => array('contacts' => array('reg'=>2,'admin'=>1, 'tech'=>1, 'billing'=>1)),
	                                '.nom.es' => array('contacts' => array('reg'=>2,'admin'=>1, 'tech'=>1, 'billing'=>1)),
	                                '.org.es' => array('contacts' => array('reg'=>2,'admin'=>1, 'tech'=>1, 'billing'=>1)),
	                                '.gob.es' => array('contacts' => array('reg'=>2,'admin'=>1, 'tech'=>1, 'billing'=>1)),
	                            ),
	        'module_error'  => array('0'=>$configLang['module-0'],
	                                 '1'=>$configLang['module-1']),
	    );
	    //GET ALL CONFIGS
	    if(!isset($params) || !isset($configarray['APIKey'])){
	        $configs = array('APIKey', 'devMode', 'defaultvatnumber', 'hideicnumber', 'disablelocktlds', 'outboundTransferMailing', 'defaultNameserversError', 'defaultDomainsMail', 'defaultAdminRoles', 'validationNewClient');
	        $table = 'tblregistrars';
	        $fields = 'value';
	        foreach($configs as $setting){
	            $where = array('registrar'=>'virtualname','setting'=>$setting);
	            $result = select_query($table,$fields,$where);
	            if(mysql_num_rows($result)>0){
	                $data = mysql_fetch_array($result);
	                $configarray[$setting] = decrypt($data['value']);
	            }
	            else
	                $configarray[$setting] = 0;
	        }
	    }
	    return $configarray;
	}
	//CHECK CURRENT ADMINISTRATION CONFIG
	public function check_configuration($params){
	    $adminID    = $_SESSION['adminid'];
	    $configLang = $this->get_config_lang($adminID);
	    if (!$params['APIKey']) {
	        return array( 'error' => $configLang['errorAPIKEY']);
	    }
	    if (isset($params['install-virtualname']) && !$params['install-virtualname'] != '1') {
	        return array( 'error' => $configLang['errorModuleInstall']);
	    }
	}
    //RETURN ADMIN PAGE
    public function return_admin_page($customadminpath, $action, $error){
        $currentURL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $cleanURL   = explode($this->vn_module_dir.'lib/install/install.php', $currentURL);
        if(!$error)
            $returnURL  = $cleanURL[0].$customadminpath.'/configregistrars.php?action='.$action.'&saved=true#virtualname';
        else{
            Virtualname_install::check_uninstall_registrar($customadminpath);
            $returnURL  = $cleanURL[0].$customadminpath.'/configregistrars.php?action=error#virtualname';
            echo '<script type=\'text/javascript\'>alert(\''.$error.'\');</script>';
        }
        echo '<script type=\'text/javascript\'>window.location.replace(\''.$returnURL.'\')</script>';
    }
    //RETURN ADMIN PAGE
    public function return_tools_page($customadminpath, $action, $error){
        $currentURL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $cleanURL   = explode($this->vn_module_dir.'lib/install/install.php', $currentURL);
        if($error)
            $returnURL  = $cleanURL[0].$customadminpath.'/configregistrars.php?action=\'error\'&error='.$error.'&saved=true#virtualname';
        else
            $returnURL  = $cleanURL[0].$customadminpath.'/addonmodules.php?module=virtualname_tools&update=price';
        echo '<script type=\'text/javascript\'>window.location.replace(\''.$returnURL.'\')</script>';
    }
    //SEND ERROR LOGS
    public function send_error_logs($empresa, $email){
        $headers = 'From: \''.$empresa.'\' <'.$email.'>'.PHP_EOL;
        $error_file = '../../logs/virtualname_errors_log.txt';
        $message = file_get_contents($error_file);
        $subject = 'Clientes - Virtualname - TCPANEL LOG de ERRORES - '.$empresa;
        $send    = mail('whmcs.development@virtualname.es', $subject, $message, $headers);
        return true;
    }
    //CLEAN TOKENS FROM LOGS
    public function clean_api($json_api){
    	$pos_ini = strpos($json_api, '\'X-TCpanel-Token: ');
    	$pos_end = strpos(substr($json_api , $pos_ini), '\'', 2);
    	$api_clean = substr($json_api , $pos_ini, $pos_end+1);
    	$json_api = str_replace ($api_clean , '\'X-TCpanel-Token: api-key\'', $json_api);
    	return $json_api;
    }
	public function clean_error_logs(){
		$res_save = $this->create_file_log();
		if($res_save){
			$error_file = '../../logs/virtualname_errors_log.txt';
			$new_error_file = '../../logs/'.date('Ymd_his_').'virtualname_errors_log.txt';
			rename($error_file, $new_error_file);
			file_put_contents($error_file, '');
		}
		$sql_clean  = 'delete from mod_virtualname_error_logs where 1';
	    $res_clean  = mysql_query($sql_clean);
	    return true;
	}
	public function create_file_log(){
		$sql_error  = 'select * from mod_virtualname_error_logs order by date desc';
	    $res_error  = mysql_query($sql_error);
	    $error_file = '../../logs/virtualname_errors_log.txt';
	    if(mysql_num_rows($res_error) > 0 AND file_exists($error_file)){
	        $errors  = '############################################'.PHP_EOL;
	        $errors .= '##############VIRTUALNAME LOGS##############'.PHP_EOL;
	        $errors .= '############################################'.PHP_EOL.PHP_EOL;
	        while($row_error = mysql_fetch_array($res_error)){
	            $errors .= '#####'.$row_error['action'].' - '.$row_error['date'].'#####'.PHP_EOL;
	            $errors .= '#####CALL#####'.PHP_EOL;
	            $errors .= $row_error['call'].''.PHP_EOL;
	            $errors .= '###RESPONSE###'.PHP_EOL;
	            $errors .= $row_error['response'].''.PHP_EOL;
	        }
	        $errors .= '############################################'.PHP_EOL;
	        $errors .= '##############VIRTUALNAME LOGS##############'.PHP_EOL;
	        $errors .= '############################################'.PHP_EOL;
	        $errors = $this->clean_api($errors);
	        file_put_contents($error_file, $errors);
	        return true;
	    }
	    else
	    	return false;
	}
	//CHECH IF ADVANCED CONTACTS MANAGEMENT WAS ENABLED
	public function check_advance_contact(){
	    $table  = 'tblregistrars';
	    $fields = 'value';
	    $where  = array('registrar'=>'virtualname','setting'=>'disableAdvanceContacts');
	    $result = select_query($table,$fields,$where);
	    $hide   = '';
	    if(mysql_num_rows($result)>0){
	        $data = mysql_fetch_array($result);
	        $hide = decrypt($data['value']);
	    }
	    if($hide == 'on')
	      return 0;
	    else
	      return 1;
	}
	//GET ADMIN ROLES
	public function get_whmcs_admin_roles(){
	    $table = 'tbladminroles';
	    $fields = 'name';
	    $where = array();
	    $data = '';
	    $result = select_query($table,$fields,$where);
  	    while ($row = mysql_fetch_array($result)){
  	    	if(!empty($data))
  	    		$data .= ',';
	        $data .= $row['name'];
	    }
	    return $data;
	}
	//ERROR FORMATS
	public function get_formated_errors($errors){
		$formated_errors = '<ul>';
		foreach($errors as $error){
			$formated_errors .= '<li>'.$error.'</li>';
		}
		$formated_errors .= '</ul>';
		return $formated_errors;
	}
	public function set_current_error($error_field, $user){
	    $adminID = $_SESSION['adminid'];
	    $configLang = $this->get_config_lang($adminID);
		$error_field = '<br>'.str_replace('] [', '<br>', $error_field);
		$error_field = str_replace(' => ', ': ', $error_field);
		$error_field = str_replace('[', '', $error_field);
		$error_field = trim(str_replace(']', '', $error_field));
		foreach($configLang['error_fields'] as $key => $value){
			if (strpos($error_field, $key.':') !== false)
				$error_field = str_replace($key.':', $configLang['error']['field'].' '.$value, $error_field);
		}
		return $error_field.'<br>'.$user;
	}
}
?>