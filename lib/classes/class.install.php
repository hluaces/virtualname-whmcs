<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.4
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common false
// * File description: VIRTUALNAME INSTALL class
// *************************************************************************
class Virtualname_install extends Virtualname_admin{
	#CLASS CONSTANTS#
	public $repository = 'http://whmcs.virtualname.net/whmcs-repositories/updateVname_1.php?version=';

	//GET REGISTRAR INSTALLATION STATUS
	public function check_install(){
	    $table = 'tblconfiguration';
	    $fields = 'value';
	    $where = array('setting'=>'virtualname-install');
	    $result = select_query($table,$fields,$where);
	    if(mysql_num_rows($result)>0){
	        $data = mysql_fetch_array($result);
	        $value = decrypt($data['value']);
	    }
	    else{
	        $values = array('setting'=>'virtualname-install', 'value'=>encrypt('uninstalled'));
	        insert_query($table,$values);
	        $value = 'uninstalled';
	    }
	    return $value;
	}
	//CUSTOM CHECK VIRTUALNAME INSTALLATION
	public function check_installation($version, $configLang){
	    $warnings = '';

	    if(!$customadminpath)
	        $customadminpath = 'admin';

	    //CHECK VIRTUALNAME VARIABLES
	    $virtualname_variables = array('APIKey','autoRenew','hideicnumber','freeRegisterDomains','freeRenewDomains','templateVersion','secureRenovation','defaultvatnumber','devMode','debug','install-virtualname','disablelocktlds','outboundTransferMailing','defaultNameserversError', 'disableAdvanceContacts', 'defaultDomainsMail', 'defaultAdminRoles', 'validationNewClient', 'disableContactVerification', 'enableDomainRecords', 'enableDomainLifecycle');
	    foreach($virtualname_variables as $variable){
	        $where = array('registrar'=>'virtualname','setting'=>$variable);
	        $var_search = select_query('tblregistrars','value',$where);
	        if(!mysql_num_rows($var_search)){
	            $warnings .= '<div style=\'color:red\'>'.$configLang['errorModuleVariables'].': '.$variable.'. '.$configLang['errorModuleVariablesSave'].'</div>';
	        }
	    }

	    //CHECK TABLES
	    $checkCustomTables = '';
	    $customTables = array('mod_contacts_virtualname_tcpanel', 'mod_virtualname_outbounds', 'mod_virtualname_pendings', 'mod_virtualname_error_logs');
	    foreach($customTables as $customTable){
	        $result = mysql_query('SHOW TABLES LIKE \''.$customTable.'\'');
	        $tableExists = mysql_num_rows($result) > 0;
	        if(!$tableExists){
	            $warnings .= '<div style=\'color:red\'>'.$configLang['installTablesErrors'];
	            $warnings .= '&nbsp;<a id=\'vn_ins_tables\' name=\'vn_ins_tables\' href=\'../'.(new Virtualname_install)->vn_module_dir.'lib/install/install.php?action=installtables\''.
	                          'style=\'appearance: button;-moz-appearance: button;-webkit-appearance: button;text-decoration: none; color: ButtonText;'.
	                          'display: inline-block; padding: 2px 8px;\'>'.$configLang['installTables'].'</a>';
	            $warnings .= '</div>';
	            break;
	        }
	        elseif($customTable == 'mod_contacts_virtualname_tcpanel'){
	        	$result = mysql_query('SHOW COLUMNS FROM mod_contacts_virtualname_tcpanel LIKE \'legal_form\'');
	        	$columnExist = mysql_num_rows($result);
	        	if($columnExist == 0){
		            $warnings .= '<div style=\'color:red\'>'.$configLang['installTablesErrors'];
		            $warnings .= '&nbsp;<a id=\'vn_ins_tables\' name=\'vn_ins_tables\' href=\'../'.(new Virtualname_admin)->vn_module_dir.'lib/install/install.php?action=installtables\''.
		                          'style=\'appearance: button;-moz-appearance: button;-webkit-appearance: button;text-decoration: none; color: ButtonText;'.
		                          'display: inline-block; padding: 2px 8px;\'>'.$configLang['installTables'].'</a>';
		            $warnings .= '</div>';
		            break;
	        	}
	        }
	    }

	    //ALL FILE UPLOADS, HAVE CORRECT VERSION, AND PERMISSIONS
	    $additionalFiles = array('hooks'=>'virtualname_hooks.php', 'phps'=>'clientareadata.php', 'admin'=>'clientsdatadomaincontacts.php', 'langs'=>array('english.php','spanish.php'));
	    foreach($additionalFiles as $key => $value){
	        if($key == 'hooks'){
	            $file = (new Virtualname_install)->vn_whmcs_dir.'includes/'.$key.'/'.$value;
	            if(file_exists($file) == false)
	                $warnings .= '<div style=\'color:red\'>'.$configLang['errorFileNotFound'].' HOOKS '.$value.'</div>';
	            else{
	                $getContent = file_get_contents($file);
	                $pos = strpos($getContent, '// * @version '.$version);
	                if ($pos == 0 || $pos == false)
	                    $warnings .= '<div style=\'color:darkorange\'>'.$configLang['errorIncorrectVersion'].' HOOKS '.$value.'</div>';
	                else{
	                    $perms = substr(decoct(fileperms($file)), -3);
	                    if($perms != '644')
	                        $warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm2'].' HOOKS '.$value.'</div>';
	                }
	            }
	        }
	        if($key == 'phps'){
	            $file = (new Virtualname_install)->vn_whmcs_dir.$value;
	            if(file_exists($file) == false){
	                $warnings .= '<div style=\'color:red\'>'.$configLang['errorFileNotFound'].' PHP '.$value.'</div>';
	            }
	            else{
	                $getContent = file_get_contents($file);
	                $pos = strpos($getContent, '// * @version '.$version);
	                if ($pos == 0 || $pos == false)
	                    $warnings .= '<div style=\'color:darkorange\'>'.$configLang['errorIncorrectVersion'].' PHP '.$value.'</div>';
	                else{
	                    $perms = substr(decoct(fileperms($file)), -3);
	                    if($perms != '644')
	                        $warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm2'].' PHP '.$value.'</div>';
	                }
	            }
	        }
	        if($key == 'admin'){
	            $file = $value;
	            if(file_exists($file) == false)
	                $warnings .= '<div style=\'color:red\'>'.$configLang['errorFileNotFound'].' ADMIN '.$value.'</div>';
	            else{
	                $getContent = file_get_contents($file);
	                $pos = strpos($getContent, '// * @version '.$version);
	                if ($pos == 0 || $pos == false)
	                    $warnings .= '<div style=\'color:darkorange\'>'.$configLang['errorIncorrectVersion'].' ADMIN '.$value.'</div>';
	                else{
	                    $perms = substr(decoct(fileperms($file)), -3);
	                    if($perms != '644')
	                        $warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm2'].' ADMIN '.$value.'</div>';
	                }
	            }
	        }
	        if($key == 'langs'){
				foreach($value as $lang_file){
					$file = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides/'.$lang_file;
					if(file_exists($file) == false)
						$warnings .= '<div style=\'color:red\'>'.$configLang['errorFileNotFound'].' LANG OVERRIDE '.$lang_file.'</div>';
					else{
						$getContent = file_get_contents($file);
						$pos = strpos($getContent, '// * @version '.$version);
						if ($pos == 0 || $pos == false)
							$warnings .= '<div style=\'color:darkorange\'>'.$configLang['errorIncorrectVersion'].' LANG OVERRIDE '.$lang_file.'</div>';
						else{
							$perms = substr(decoct(fileperms($file)), -3);
							if($perms != '644')
								$warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm2'].' LANG OVERRIDE '.$value.'</div>';
						}
					}
				}
	        }
	    }
	    //CHECK REGISTRAR FILES
	    $registrar_files = array(
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
                'themes/css/virtualname_adm.css',
                'themes/css/virtualname.css',
                'themes/js/virtualname_adm.js',
                'themes/js/virtualname_client.js',
                'logo.gif',
                'themes/images/vname_download.png'
        );
	    foreach($registrar_files as $value){
	        $file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.$value;
	        if(file_exists($file) == false)
	            $warnings .= '<div style=\'color:red\'>'.$configLang['errorFileNotFound'].' REGISTRAR '.$value.'</div>';
	        else{
	            if(in_array($value, array('logo.gif', 'themes/images/vname_download.png'))){
	                $pos = true;
	            }
	            else{
	                $getContent = file_get_contents($file);
	                $pos = strpos($getContent, '// * @version '.$version);
	            }
	            if ($pos == 0 || $pos == false)
	                $warnings .= '<div style=\'color:darkorange\'>'.$configLang['errorIncorrectVersion'].' REGISTRAR '.$value.'</div>';
	            else{
	                $perms = substr(decoct(fileperms($file)), -3);
	                if($perms != '644')
	                    $warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm2'].' REGISTRAR '.$value.'</div>';
	            }
	        }
	    }
	    //CHECK FOLDERS
	    $registrarsFolders = array('includes', 'includes/templates', 'includes/templates/six', 'includes/templates/five', 'includes/overrides', 'lib', 'lib/classes', 'lib/crons', 'lib/install', 'logs', 'config', 'config/locales', 'themes', 'themes/css', 'themes/images', 'themes/js');
	    foreach($registrarsFolders as $value){
	        $folder = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.$value;
	        $perms = substr(decoct(fileperms($folder)), -3);
	        if($perms != '755')
	            $warnings .= '<div style=\'color:orange\'>'.$configLang['errorIncorrectPerm1'].' '.$perms.' '.$configLang['errorIncorrectPerm3'].' REGISTRAR '.$value.'</div>';
	    }

	    //CHECK ADDITIONAL DOMAIN FIELDS
	    $additionalFile = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/additionalfields.php';
	    if(file_exists($additionalFile)){
	        $getContent = file_get_contents($additionalFile);
	        //CHECK IF THE CUSTOM LINES EXIST IN ADDITIONALFIELDS.PHP
	        $customAdd = Virtualname_install::get_additional_fields(false);
	        $customAddC = Virtualname_install::get_additional_fields(true);
	        $pos  = strpos($getContent, $customAdd);
	        $posC = strpos($getContent, $customAddC);
	        if($pos == 0 || $pos == false){
	            if($posC == 0 || $posC == false)
	                $warnings .= '<div style=\'color:red\'>'.$configLang['errorLinesNotFound'].' \'resources/domains/additionalfields.php\'</div>';
	        }
	    }
	    return $warnings;
	}
	//UPDATE ALL FILES
	function update_module($version){
	    $params = (new Virtualname_install)->config();
	    //FIRST READ ALL FILES AFTER UPDATE
	    $repositoryURL = (new Virtualname_install)->repository.$version;
	    ##CURL CALL##
	    //HEADERS
	    $virtualname_header[] = 'X-TCpanel-Token: '.$params['APIKey'];
	    $virtualname_header[] = 'X-TCPanel-Plugin-Version' . $params['pluginVersion'];
	    $virtualname_header[] = 'Content-Type: application/json';
	    //CURL CONEXION
	    $cURL = curl_init($repositoryURL);
	    curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'GET');
	    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($cURL, CURLOPT_HTTPHEADER, $virtualname_header);
	    curl_setopt($cURL, CURLOPT_USERAGENT,'WHMCS TCPanel Module Version: '.$params['version']);
	    $request = curl_exec($cURL);
	    $json_decode = json_decode($request);

	    foreach($json_decode as $key => $value){
	        $file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.$key;
	        $is_pic = false;
	        if(!in_array(substr($key, -4), array('.png', '.gif', '.jpg'))){
	        	$value = stripslashes($value);
	        	$is_pic = true;
	        }
			$update_file = (new Virtualname_install)->force_file_put_contents($file, $value);
	        if($update_file)
	            $response =  array('status'=>'success');
	        else{
	            $response = array('status'=>'error','error'=>'There was a problem to update the current file: '.$key);
	            break;
	        }

	    }
	    return $response;
	}

    //CHECK INSTALL
    public function check_module_install($customadminpath){
        $return = array();
        $result = mysql_query('select * from tblconfiguration where setting = \'virtualname-install\';');
        if(mysql_num_rows($result)>0){
            $data = mysql_fetch_array($result);
            $value = decrypt($data['value']);
        }
        else
            $value = 0;

        if($value != 'installed'  || $value != ''){
            $response = (new Virtualname_install)->install_virtualname($customadminpath);
            if(!$response['error']){
                //NO ERRORS
                $valueEncrypt = encrypt('installed');
                $update = mysql_query('update tblconfiguration set value = \''.$valueEncrypt.'\' where setting = \'virtualname-install\';');
                if($update == FALSE)
                    $error = 'ERROR ON INSTALL VIRTUALNAME MODULE: UPDATE BBDD Status';
            }
            else{
                $error = 'ERROR ON INSTALL VIRTUALNAME MODULE: '.$response['error'];
            }
        }
        else{
            $error = 'VIRTUALNAME MODULE WAS INSTALLED AFTER';
        }
        $return['error'] = $error;
        return $return;
    }
    //CHECK UPDATE
    public function check_update($customadminpath, $version){
        if(!$customadminpath)
            $customadminpath = 'admin';
        //SET ON MAINTENANCE MODE
        $updateInit = mysql_query('update tblconfiguration set value = \'on\' where setting = \'MaintenanceMode\';');
        if($updateInit){
            $uninstall = Virtualname_install::check_uninstall_registrar($customadminpath);
            if(!$uninstall['error']){
	            $update = Virtualname_install::update_module($version);
	            if(!$update['error']){
	                $install = Virtualname_install::check_module_install($customadminpath);
	                if(!$install['error'])
	                	$error = $install['error'];
	            }
	            else
	                $error = $update['error'];
	        }
	        else
            	$error = $uninstall['error'];
            $updateEnd = mysql_query('update tblconfiguration set value = \'\' where setting = \'MaintenanceMode\';');
            if(!$updateEnd)
                $error .= 'ERROR ON UPDATED VIRTUALNAME MODULE: UPDATE maintenance MODE off';
        }
        else
            $error = 'ERROR ON UPDATED VIRTUALNAME MODULE: UPDATE maintenance MODE on';
        $return['error'] = $error;
        return $return;
    }
    //CHECK UNINSTALL
    public function check_uninstall_registrar($customadminpath){
        $result = mysql_query('select * from tblconfiguration where setting = \'virtualname-install\';');
        if(mysql_num_rows($result)>0){
            $data = mysql_fetch_array($result);
            $value = decrypt($data['value']);
        }
        else
            $value = 0;

        if($value == 'installed'){
            $response = Virtualname_install::uninstall_virtualname($customadminpath);
            if($response['status'] != 'error'){
                //NO ERRORS
                $valueEncrypt = encrypt('uninstalled');
                $update = mysql_query('update tblconfiguration set value = \''.$valueEncrypt.'\' where setting = \'virtualname-install\';');
                if($update == FALSE)
                    $error = 'ERROR ON UNINSTALL VIRTUALNAME MODULE: UPDATE BBDD Status';
            }
            else{
                $error = 'ERROR ON UNINSTALL VIRTUALNAME MODULE: '.$response['error'];
            }
        }
        else{
            $error = 'VIRTUALNAME MODULE NOT INSTALLED YET';
        }
        $return['error'] = $error;
        return $return;
    }
    //MAIN UNINSTALL FUNCTION
    public function uninstall_virtualname($customadminpath){
        //DELETE CUSTOM FILES
        $additionalFiles = array('hooks'=>'virtualname_hooks.php', 'phps'=>'clientareadata.php', 'admin'=>'clientsdatadomaincontacts.php');
        foreach($additionalFiles as $key => $value){
            if($key == 'hooks'){
                $file = (new Virtualname_install)->vn_whmcs_dir.'includes/'.$key.'/'.$value;
                if (file_exists($file)) unlink ($file);
                if (file_exists($file)){
                    return array('status'=>'error','error'=>'Fail on delete HOOKS files.');
                }
            }
            if($key == 'phps'){
                $file = (new Virtualname_install)->vn_whmcs_dir.$value;
                if (file_exists($file)) unlink ($file);
                if (file_exists($file)){
                    return array('status'=>'error','error'=>'Fail on delete PHP files.');
                }
            }
            if($key == 'admin'){
                if($customadminpath == ''){
                    return array('status'=>'error','error'=>'Can not get the current admin path.');
                }
                else{
                    $file = (new Virtualname_install)->vn_whmcs_dir.$customadminpath.'/'.$value;
                    if (file_exists($file)) unlink ($file);
                    if (file_exists($file)){
                        return array('status'=>'error','error'=>'Fail on delete ADMIN files.');
                    }
                }
            }
        }
        //REMOVE CUSTOM LINES IN ADDITIONALFIELDS.PHP
        $additionalFile = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/additionalfields.php';
        if(file_exists($additionalFile)){
            $getContent = file_get_contents($additionalFile);
            //CHECK IF THE CUSTOM OLD LINES EXIST IN ADDITIONALFIELDS.PHP
	        $customAdd = Virtualname_install::get_additional_fields_old(false);
	        $customAddC = Virtualname_install::get_additional_fields_old(true);
            $getContent=str_replace($customAdd,  '',$getContent);
            $getContent=str_replace($customAddC, '',$getContent);
            file_put_contents($additionalFile, $getContent);
            //CHECK IF THE CUSTOM LINES EXIST IN ADDITIONALFIELDS.PHP
	        $customAdd = Virtualname_install::get_additional_fields(false);
	        $customAddC = Virtualname_install::get_additional_fields(true);
            $getContent=str_replace($customAdd,  '',$getContent);
            $getContent=str_replace($customAddC, '',$getContent);
            file_put_contents($additionalFile, $getContent);
            $pos  = strpos($getContent, $customAdd);
            $posC = strpos($getContent, $customAddC);
            if($pos != 0 && $pos != false){
                if($pos != 0 && $pos != false)
                    return array('status'=>'error','error'=>'There was a problem to uninstall the module. ADDITIONALFIELDS.PHP was edited before this installation.');
            }
        }
        //REMOVE CUSTOM LINES IN WHOIS.JSON
        $whoisFile     = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/whois.json';
        if(file_exists($whoisFile)){
            $getContent = file_get_contents($whoisFile);
            //CHECK IF THE CUSTOM LINES EXIST IN WHOIS.JSON
            $customAdd  = Virtualname_install::get_additional_whois();

            $getContent=str_replace($customAdd, '', $getContent);
            $getContent=str_replace($customAddC, '', $getContent);
            file_put_contents($whoisFile, $getContent);

            $posUninstall  = strstr($getContent, $customAdd);
            if($posUninstall == false){
                if($pos != 0 && $pos != false)
                    return array('status'=>'error','error'=>'There was a problem to uninstall the module. WHOIS.JSON was edited before this installation.');
            }
        }
        //REMOVE CUSTOM LANGS LINES IN OVERRIDES
        $virtualname_additional_lines = Virtualname_install::delete_langs();
        if($virtualname_additional_lines['status'] == 'error'){
            return array('status' => 'error', 'error' => $virtualname_additional_lines['error']);
        }
    }
    //MAIN INSTALL FUNCTION
    public function install_virtualname($customadminpath){
        //TABLES
        $virtualname_contact_table = (new Virtualname_install)->activate_contact_table();
        if($virtualname_contact_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_contact_table['error']);
            return $values;
        }
        $virtualname_outbound_table = (new Virtualname_install)->activate_outbound_table();
        if($virtualname_outbound_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_outbound_table['error']);
            return $values;
        }
        $virtualname_pending_table = (new Virtualname_install)->activate_pending_table();
        if($virtualname_pending_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_pending_table['error']);
            return $values;
        }
        $virtualname_error_logs_table = (new Virtualname_install)->error_logs_table();
        if($virtualname_error_logs_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_error_logs_table['error']);
            return $values;
        }
        $virtualname_transfer_on_renewal_table = (new Virtualname_install)->transfer_on_renewal_table();
        if($virtualname_transfer_on_renewal_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_transfer_on_renewal_table['error']);
            return $values;
        }
        //CUSTOM LINES
        $virtualname_additional_lines = (new Virtualname_install)->add_additional_domain_fields();
        if($virtualname_additional_lines['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_additional_lines['error']);
            return $values;
        }
        //CUSTOM FILES
        $virtualname_additional_files = (new Virtualname_install)->add_additional_files($customadminpath);
        if($virtualname_additional_files['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_additional_files['error']);
            return $values;
        }
        //CUSTOM LINES WHOIS
        $virtualname_additional_lines = (new Virtualname_install)->add_whois();
        if($virtualname_additional_lines['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_additional_lines['error']);
            return $values;
        }
        //CUSTOM LINES LANGS
        $virtualname_additional_lines = (new Virtualname_install)->add_langs();
        if($virtualname_additional_lines['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_additional_lines['error']);
            return $values;
        }
        return 0;
    }
    //TABLES INSTALL FUNCTION
    public function install_tables(){
        //TABLES
        $virtualname_contact_table = (new Virtualname_install)->activate_contact_table();
        if($virtualname_contact_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_contact_table['error']);
            return $values;
        }
        $virtualname_outbound_table = (new Virtualname_install)->activate_outbound_table();
        if($virtualname_outbound_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_outbound_table['error']);
            return $values;
        }
        $virtualname_pending_table = (new Virtualname_install)->activate_pending_table();
        if($virtualname_pending_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_pending_table['error']);
            return $values;
        }
        $virtualname_error_logs_table = (new Virtualname_install)->error_logs_table();
        if($virtualname_error_logs_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_error_logs_table['error']);
            return $values;
        }
        $virtualname_transfer_on_renewal_table = (new Virtualname_install)->transfer_on_renewal_table();
        if($virtualname_transfer_on_renewal_table['status'] == 'error'){
            $values = array('response' => 0, 'error' => $virtualname_transfer_on_renewal_table['error']);
            return $values;
        }
        return 0;
    }
    //CREATE CUSTOM DB TABLE
    public function activate_contact_table() {
        $result = mysql_query('SHOW TABLES LIKE \'mod_contacts_virtualname_tcpanel\';');
        $tableExist = mysql_num_rows($result);
        if($tableExist == 0){
            $query = 'CREATE TABLE `mod_contacts_virtualname_tcpanel` (`id_contact_whmcs` INT(10), `id_contact_tcpanel` INT(10), `contact_type` INT(2), `identification_number` varchar(50), `legal_form` varchar(50), PRIMARY KEY (id_contact_whmcs,contact_type));';
            $result = mysql_query($query);
            if($result)
                $response = array('status'=>'success','description'=>'Virtualname contact table was succesfully created');
            else
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        }
        else{
        	$result = mysql_query('SHOW COLUMNS FROM mod_contacts_virtualname_tcpanel LIKE \'legal_form\'');
        	$columnExist = mysql_num_rows($result);
        	if($columnExist == 0){
        		$query = 'ALTER TABLE `mod_contacts_virtualname_tcpanel` ADD legal_form varchar(50)';
        		$result = mysql_query($query);
        		if($result)
	        		$response = array('status'=>'success','description'=>'Virtualname contact legal_form contact was succesfully created');
	            else
	                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        	}
        	else
	            $response = array('status'=>'success','description'=>'Virtualname outbound table already exist');
        }
        return $response;
    }
    //OUTBOUNDS TABLE
    public function activate_outbound_table() {
        $result = mysql_query('SHOW TABLES LIKE \'mod_virtualname_outbounds\';');
        $tableExist = mysql_num_rows($result);
        if($tableExist == 0){
            $query  =
                'CREATE TABLE IF NOT EXISTS `mod_virtualname_outbounds` (
                  `config` char(50) NOT NULL,
                  `type` char(50) NOT NULL,
                  `value`  longtext DEFAULT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
            $result = mysql_query($query);
            if($result)
                $response = array('status'=>'success','description'=>'Virtualname outbound table was succesfully created');
            else
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        }
        else{
            $response = array('status'=>'success','description'=>'Virtualname outbound table already exist');
        }
        return $response;
    }
    //PENDING TABLE
    public function activate_pending_table() {
        $result = mysql_query('SHOW TABLES LIKE \'mod_virtualname_pendings\';');
        $tableExist = mysql_num_rows($result);
        if($tableExist == 0){
            $query  =
                'CREATE TABLE IF NOT EXISTS `mod_virtualname_pendings` (
                  `config` char(50) NOT NULL,
                  `type` char(50) NOT NULL,
                  `value`  longtext DEFAULT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
            $result = mysql_query($query);
            if($result)
                $response = array('status'=>'success','description'=>'Virtualname pending table was succesfully created');
            else
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        }
        else{
            $response = array('status'=>'success','description'=>'Virtualname pending table already exist');
        }
        return $response;
    }
    //ERROR LOGS TABLE
    public function error_logs_table() {
        $result = mysql_query('SHOW TABLES LIKE \'mod_virtualname_error_logs\';');
        $tableExist = mysql_num_rows($result);
        if($tableExist == 0){
            $query  =
                'CREATE TABLE IF NOT EXISTS `mod_virtualname_error_logs` (
                  `id` int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `date` datetime NOT NULL,
                  `action` char(100) NOT NULL,
                  `call` longtext NOT NULL,
                  `response` longtext NOT NULL,
                  `user` char(100) NOT NULL,
                  `userid` int(5) NOT NULL,
                  `ipaddr` char(100) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
            $result = mysql_query($query);
            if($result)
                $response = array('status'=>'success','description'=>'Virtualname error logs table was succesfully created');
            else
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        }
        else{
            $response = array('status'=>'success','description'=>'Virtualname error logs table already exist');
        }
        return $response;
    }
    //TRANSFER ON RENEWAL TABLE
    public function transfer_on_renewal_table() {
        $result = mysql_query('SHOW TABLES LIKE \'mod_virtualname_transfer_on_renewal\';');
        $tableExist = mysql_num_rows($result);
        if($tableExist == 0){
            $query  =
                'CREATE TABLE IF NOT EXISTS `mod_virtualname_transfer_on_renewal` (
                  `domainid` int(5) NOT NULL PRIMARY KEY,
                  `registrar` char(100) NOT NULL,
                  `status` char(20) NOT NULL,
                  `type` tinyint(1) NOT NULL,
                  `value` tinytext NOT NULL,
                  `admin_email` char(100) NOT NULL,
                  `notes` tinytext NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
            $result = mysql_query($query);
            if($result)
                $response = array('status'=>'success','description'=>'Virtualname transfer on renewal table was succesfully created');
            else
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. Please contact with support.');
        }
        else{
            $response = array('status'=>'success','description'=>'Virtualname transfer on renewal table already exist');
        }
        return $response;
    }
    //ADD CUSTOM LINES IN ADDITIONALFIELDS.PHP
    public function add_additional_domain_fields(){
        $additionalFile     = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/additionalfields.php';
        $distadditionalFile = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/dist.additionalfields.php';
        if(file_exists($additionalFile)){
            $response = Virtualname_install::get_additional_files($additionalFile);
        }
        elseif(file_exists($distadditionalFile)){
            if(copy($distadditionalFile, $additionalFile)){
                $response = Virtualname_install::get_additional_files($additionalFile);
            }
            else{
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. ADDITIONALFIELDS.PHP can not access.');
            }
        }
        else{
            $response = array('status'=>'error','error'=>'There was a problem to activating the module. ADDITIONALFIELDS.PHP not found.');
        }
        return $response;
    }
    //ADD CUSTOM LINES IN WHOIS.JSON
    public function add_whois(){
        $whoisFile     = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/whois.json';
        $distwhoisFile = (new Virtualname_install)->vn_whmcs_dir.'resources/domains/dist.whois.json';
        if(file_exists($whoisFile)){
			$response = Virtualname_install::get_additional_files_whois($whoisFile);
        }
        elseif(file_exists($distwhoisFile)){
            if(copy($distwhoisFile, $whoisFile)){
                $response = Virtualname_install::get_additional_files_whois($whoisFile);
            }
            else{
            	$response = array('status'=>'error','error'=>'There was a problem to activating the module. WHOIS.JSON can not access.');
            }
        }
        else{
           $response = array('status'=>'error','error'=>'There was a problem to activating the module. WHOIS.JSON not found.');
        }
        return $response;
    }
    //ADD VERSION LINES
    public function add_version($string, $needle){
    	$pos_start = strpos($string, $needle);
    	$pos_end = strpos($string, $needle, $pos_start + strlen($needle));
    	$lang_str = substr($string, $pos_start, $pos_end);
        return $lang_str;
    }
    //ADD CUSTOM LINES IN OVERRIDE LANGS
    public function add_langs(){
    	$override_dir = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides';
        $lang_en_file = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides/english.php';
        $lang_es_file = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides/spanish.php';
        $vname_en_file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/overrides/english.php';
        $vname_es_file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/overrides/spanish.php';
      	$needle = '######VIRTUALNAME REGISTRAR LANGS######';
      	#### CHECK FOLDER
      	$is_override = (new Virtualname_install)->install_make_dir($override_dir);
      	if(!$is_override)
      		$response = array('status'=>'error','error'=>'There was a problem to activating the module. Folder OVERRIDES can not be created.');
        #### ADD LINES OR FILES
        if(file_exists($lang_en_file)){
        	$lang_str = (new Virtualname_install)->add_version(file_get_contents($vname_en_file), $needle);
        	file_put_contents($lang_en_file, PHP_EOL.$lang_str.PHP_EOL, FILE_APPEND);
            $pos = strpos(file_get_contents($lang_en_file), $lang_str);
            if ($pos == 0 || $pos == false)
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. ENGLISH.PHP lines can not be added.');
        }
        else{
	        if (!copy($vname_en_file, $lang_en_file)) {
	           $response = array('status'=>'error','error'=>'There was a problem to copy the news OVERRIDES LANG file: english.php');
	        }
        }
        if(file_exists($lang_es_file)){
        	$lang_str = (new Virtualname_install)->add_version(file_get_contents($vname_es_file), $needle);
        	file_put_contents($lang_es_file,  PHP_EOL.$lang_str.PHP_EOL, FILE_APPEND);
            $pos = strpos(file_get_contents($lang_es_file), $lang_str);
            if ($pos == 0 || $pos == false)
                $response = array('status'=>'error','error'=>'There was a problem to activating the module. SPANISH.PHP lines can not be added.');
        }
        else{
	        if (!copy($vname_es_file, $lang_es_file)) {
	           $response = array('status'=>'error','error'=>'There was a problem to copy the news OVERRIDES LANG file: spanish.php');
	        }
        }
        return $response;
    }
    //DELETE CUSTOM LINES IN OVERRIDE LANGS
    public function delete_langs(){
        $lang_en_file = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides/english.php';
        $lang_es_file = (new Virtualname_install)->vn_whmcs_dir.'lang/overrides/spanish.php';
        $vname_en_file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/overrides/english.php';
        $vname_es_file = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/overrides/spanish.php';
      	$needle = '######VIRTUALNAME REGISTRAR LANGS######';
        if(file_exists($lang_en_file)){
        	$get_content = file_get_contents($lang_en_file);
        	$vname_content = file_get_contents($vname_en_file);
        	if($get_content != $vname_content){
                $remove_content = $needle . (new Virtualname_install)->get_between($get_content, $needle, $needle) . $needle;
                $cleared_content = str_replace($remove_content, '', $get_content);
		        file_put_contents($lang_en_file, $cleared_content);
	            $pos = strpos(file_get_contents($lang_en_file), $needle);
	            if ($pos != 0 || $pos != false)
	                $response = array('status'=>'error','error'=>'There was a problem to uninstall the module. ENGLISH.PHP lines can not be deleted.');
		    }
		    else{
				unlink ($lang_en_file);
                if (file_exists($lang_en_file)){
                    return array('status'=>'error','error'=>'Fail on delete LANGS files.');
                }
		    }
        }
        if(file_exists($lang_es_file)){
            $get_content = file_get_contents($lang_es_file);
            $vname_content = file_get_contents($vname_es_file);
            if($get_content != $vname_content){
                $remove_content = $needle . (new Virtualname_install)->get_between($get_content, $needle, $needle) . $needle;
                $cleared_content = str_replace($remove_content, '', $get_content);
                file_put_contents($lang_es_file, $cleared_content);
                $pos = strpos(file_get_contents($lang_es_file), $needle);
                if ($pos != 0 || $pos != false)
                    $response = array('status'=>'error','error'=>'There was a problem to uninstall the module. SPANISH.PHP lines can not be deleted.');
            }
            else{
                unlink ($lang_es_file);
                if (file_exists($lang_es_file)){
                    return array('status'=>'error','error'=>'Fail on delete LANGS files.');
                }
            }
        }
        return $response;
    }
    //GET TEXT BETWEEN TAGS
    function get_between($string, $start = "", $end = ""){
        if (strpos($string, $start)) {
            $startCharCount = strpos($string, $start) + strlen($start);
            $firstSubStr = substr($string, $startCharCount, strlen($string));
            $endCharCount = strpos($firstSubStr, $end);
            if ($endCharCount == 0) {
                $endCharCount = strlen($firstSubStr);
            }
            return substr($firstSubStr, 0, $endCharCount);
        }
        else{
            return '';
        }
    }
    //ADD ADITIONAL FILES
    public function add_additional_files($customadminpath){
        $additionalFiles = array('hooks'=>'virtualname_hooks.php', 'phps'=>'clientareadata.php', 'admin'=>'clientsdatadomaincontacts.php', 'langs'=>array('english.php','spanish.php'));
        $response = array('status'=>'success','description'=>'Virtualname files added');
        foreach($additionalFiles as $key => $value){
            if($key == 'hooks'){
                $file = (new Virtualname_install)->vn_whmcs_dir.'includes/'.$key.'/'.$value;
                $file_cp = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/'.$value;
                if(!file_exists($file)){
                    if (!copy($file_cp, $file)) {
                       $response = array('status'=>'warning','warning'=>'There was a problem to copy the news HOOKS files.');
                       break;
                    }
                }
            }
            if($key == 'phps'){
                $file = (new Virtualname_install)->vn_whmcs_dir.$value;
                $file_cp = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/'.$value;
                if(!file_exists($file)){
                    if (!copy($file_cp, $file)) {
                       $response = array('status'=>'warning','warning'=>'There was a problem to copy the news PHPs files.');
                       break;
                    }
                }
            }
            if($key == 'admin'){
                if($customadminpath == ''){
                    $response = array('status'=>'error','error'=>'Can not get the current admin path.');
                }
                else{
                    $file = (new Virtualname_install)->vn_whmcs_dir.$customadminpath.'/'.$value;
                    $file_cp = (new Virtualname_install)->vn_whmcs_dir.(new Virtualname_install)->vn_module_dir.'includes/'.$value;
                    if(!file_exists($file)){
                        if (!copy($file_cp, $file)) {
                           $response = array('status'=>'warning','warning'=>'There was a problem to copy the news ADMIN files.');
                        }
                    }
                }
            }
        }
        return $response;
    }

	public function get_additional_fields($copy){
		if(!$copy){
	        $return = "######VIRTUALNAME REGISTRAR ADDITIONALS######\r\n";
	        $return .= "\$path = str_replace('/resources/domains', '', dirname(__FILE__));";
	        $return .= "\r\n";
	        $return .= "require_once(\$path.\"/".(new Virtualname_install)->vn_module_dir."includes/domainContacts.php\");";
	        $return .= "\r\n";
	        $return .= "\$additionaldomainfields = setExtraAdditional(\$_SESSION[\"uid\"]);";
        	$return .= "\r\n######VIRTUALNAME REGISTRAR ADDITIONALS######";
        }
        else{
	        $return = "######VIRTUALNAME REGISTRAR ADDITIONALS######\n";
	        $return .= "\$path = str_replace('/resources/domains', '', dirname(__FILE__));\n";
	        $return .= "require_once(\$path.\"/".(new Virtualname_install)->vn_module_dir."includes/domainContacts.php\");\n";
	        $return .= "\$additionaldomainfields = setExtraAdditional(\$_SESSION[\"uid\"]);\n";
	        $return .= "######VIRTUALNAME REGISTRAR ADDITIONALS######";
	     }
        return $return;
	}

	public function get_additional_fields_old($copy){
		if(!$copy){
	        $return = "######VIRTUALNAME REGISTRAR ADDITIONALS######\r\n";
	        $return .= "require_once(dirname(dirname(__FILE__)).\"/../".(new Virtualname_install)->vn_module_dir."includes/domainContacts.php\");";
	        $return .= "\r\n";
	        $return .= "\$additionaldomainfields = setExtraAdditional(\$_SESSION[\"uid\"]);";
        	$return .= "\r\n######VIRTUALNAME REGISTRAR ADDITIONALS######";
        }
        else{
	        $return = "######VIRTUALNAME REGISTRAR ADDITIONALS######\n";
	        $return .= "require_once(dirname(dirname(__FILE__)).\"/../".(new Virtualname_install)->vn_module_dir."includes/domainContacts.php\");\n";
	        $return .= "\$additionaldomainfields = setExtraAdditional(\$_SESSION[\"uid\"]);\n";
	        $return .= "######VIRTUALNAME REGISTRAR ADDITIONALS######";
	     }
        return $return;
	}

	public function get_additional_whois(){
		$return  = "\r\n\t".'{';
		$return .= "\r\n\t\t".'"extensions": ".es,.com.es,.nom.es,.gob.es,.edu.es,.cat",';
		$return .= "\r\n\t\t".'"uri": "http://whois.virtualname.es/whois.php?domain=",';
		$return .= "\r\n\t\t".'"available": "LIBRE - DOMINIO DISPONIBLE"';
		$return .= "\r\n\t";
		$return .= '},';
		return $return;
	}

	public function get_additional_files($additionalFile){
        $getContent = file_get_contents($additionalFile);
        //CHECK IF THE CUSTOM LINES EXIST IN ADDITIONALFIELDS.PHP
        $customAdd = Virtualname_install::get_additional_fields(false);
        $pos = strpos($getContent, $customAdd);
        if ($pos == 0 || $pos == false){
            $pos = strpos($getContent, '######VIRTUALNAME REGISTRAR ADDITIONALS######');
            if ($pos !== false){
                $response = array('status'=>'warning','warning'=>'There was a problem to activating the module. ADDITIONALFIELDS.PHP was edited before this installation.');
            }
            else{
                file_put_contents($additionalFile, $customAdd, FILE_APPEND);
                $getContent = file_get_contents($additionalFile);
                $pos = strpos($getContent, $customAdd);
                if ($pos == 0 || $pos == false)
                    $response = array('status'=>'warning','warning'=>'There was a problem to activating the module. ADDITIONALFIELDS.PHP lines can not be added.');
                else
                    $response =  array('status'=>'success','description'=>'Custom lines succesfully added.');
            }
        }
        return $response;
	}

	public function get_additional_files_whois($whoisFile){
		$getContent = file_get_contents($whoisFile);
		//CHECK IF THE CUSTOM LINES EXIST IN WHOIS.JSON
		$customAdd  = '[';
		$customAdd .= Virtualname_install::get_additional_whois();
		$pos = strstr($getContent, $customAdd);
		if ($pos == false){
		    $pos = strstr($getContent, 'whois.virtualname.es/whois.php?domain=');
		    if ($pos !== false){
		        $response = array('status'=>'warning','warning'=>'There was a problem to activating the module. WHOIS.JSON was edited before this installation.');
		    }
		    else{
		        $posContent  = strpos($getContent, "[\r\n");
		        $restContent = substr($getContent, $posContent+1);
		        $restContent = str_replace(',.es,.com.es,.nom.es,.gob.es,.edu.es',  '', $restContent);
		        file_put_contents($whoisFile, $customAdd.$restContent);
		        $getContent = file_get_contents($whoisFile);
		        $posNew = strstr($getContent, $customAdd);
		        if ($posNew == false)
		            $response = array('status'=>'warning','warning'=>'There was a problem to activating the module. WHOIS.JSON lines can not be added.');
		        else
		            $response =  array('status'=>'success','description'=>'Custom lines succesfully added.');
		    }
		}
		else{
		    $response = array('status'=>'warning','warning'=>'There was a problem to activating the module. WHOIS.JSON was edited before this installation.');
		}
		return $response;
	}

	private function install_make_dir($path){
	     return is_dir($path) || mkdir($path, 0755, true);
	}

	private function force_file_put_contents($file_path, $message){
	    try {
	        $is_in_folder = preg_match("/^(.*)\/([^\/]+)$/", $file_path, $file_path_matches);
	        if($is_in_folder) {
	            $folder_name = $file_path_matches[1];
	            $fileName = $file_path_matches[2];
	            if (!is_dir($folder_name)) {
	                mkdir($folder_name, 0755, true);
	            }
	        }
	        file_put_contents($file_path, $message);
	        return true;
	    } catch (Exception $e) {
	        $error = "ERR: error writing to '$file_path', ". $e->getMessage();
	        return false;
	    }
	}
}
?>