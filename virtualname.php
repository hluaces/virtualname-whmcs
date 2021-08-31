<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 8.2.X
// * @copyright Copyright (c) 2021, Virtualname
// * @version 1.3.1
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: VIRTUALNAME General functions
// *************************************************************************

if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

#######################################
###MODULE CONFIGURATION################
#######################################
virtualname_init();
//INIT
function virtualname_init(){
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts, $vname_install, $vname_api, $vname_domains, $vname_contacts, $vname_nameservers, $vname_prices;
    include_once dirname(__FILE__) . '/init.php';
}

//METADATA
function virtualname_MetaData(){
    return array(
        'DisplayName' => 'Modulo registrador Virtualname',
        'APIVersion' => '1.3.1',
        'Category' => 'Registrar'
    );
}

//DEFAULT WHMCS CONFIG ARRAY
function virtualname_getConfigArray(){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts, $vname_install;
    virtualname_init();
    //CHECK INSTALLATION
    $virtualnameInstall = $vname_install->check_install();
    //GET LANGUAGES
    $adminID = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    //GET CUSTOM CLIENT
    $client_custom_fields = $vname_admin->get_config_clients_customfields();
    //GET ALL CUSTOM
    $custom_buttons = $vname_admin->custom_buttons($virtualnameInstall, $configLang);
    $configarray = array(
        'FriendlyName'              => array('Type' => 'System',    'Value'=> 'Virtualname - TCpanel', 'Description' =>'Virtualname - TCpanel'),
        'Description'               => array('Type' => 'System',    'Value'=> $configLang['name'].' <a href=\'http://whmcs.virtualname.net\' target=\'_blank\'>Virtualname</a>  - v.'.$vname_admin->module_version, 'Description' =>'Module Domains Administration Virtualname - WHMCS'),
        'Virtualname - TCpanel'     => array('Type' => 'display',   'Description' => $configLang['description']),
        'Module-Version'            => array('Type' => 'display',   'Description' => 'v'.$vname_admin->module_version, 'value'=>$vname_admin->module_version, 'FriendlyName' => $configLang['moduleVersion']),
        'APIKey'                    => array('Type'=>  'text',      'Description' => '', 'Size' => '50', 'FriendlyName' => 'APIKey'),
        'autoRenew'                 => array('Type' => 'yesno',     'Description' => $configLang['autoRenew'], 'FriendlyName' => $configLang['autoRenewField']),
        'hideicnumber'              => array('Type' => 'yesno',     'Description' => $configLang['hideicnumber'], 'FriendlyName' => $configLang['hideicnumberField']),
        'freeRegisterDomains'       => array('Type' => 'yesno',     'Description' => $configLang['freeRegisterDomains'], 'FriendlyName' => $configLang['freeRegisterDomainsField']),
        'freeRenewDomains'          => array('Type' => 'yesno',     'Description' => $configLang['freeRenewDomains'], 'FriendlyName' => $configLang['freeRenewDomainsField']),
        'templateVersion'           => array('Type' => 'dropdown',  'Description' => $configLang['templateVersion'], 'Options' => 'six,five', 'Default' => 'six', 'FriendlyName' => $configLang['templateVersionField']),
        'secureRenovation'          => array('Type' => 'dropdown',  'Description' => $configLang['secureRenovation'], 'Options' => 'Disabled,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24', 'Default' => 'Disabled', 'FriendlyName' => $configLang['secureRenovationField'])
    );
    //CHECK IF TAX_ID CAN BE USED
    $check_enable_tax_id = $vname_admin->check_enable_customer_tax_id();
    if($check_enable_tax_id)
        $configarray = array_merge($configarray, array('taxid' => array('Type' => 'yesno',     'Description' => $configLang['taxid'], 'FriendlyName' => $configLang['taxidField'])));
    $configarray = array_merge($configarray, array(
        'defaultvatnumber'          => array('Type' => 'dropdown',  'Description' => $configLang['defaultvatnumber'], 'Options' => $client_custom_fields, 'Default' => 'Disabled', 'FriendlyName' => $configLang['defaultvatnumberField']),
        'disablelocktlds'           => array('Type' => 'textarea',  'Description' => $configLang['disablelocktlds'], 'Rows' => '3', 'Cols' => '50', 'Default' => 'es com.es org.es edu.es', 'FriendlyName' => $configLang['disablelocktldsField']),
        'outboundTransferMailing'   => array('Type' => 'yesno',     'Description' => $configLang['outboundTransferMailing'], 'FriendlyName' => $configLang['outboundTransferMailingField']),
        'defaultNameserversError'   => array('Type' => 'yesno',     'Description' => $configLang['defaultNameserversError'], 'FriendlyName' => $configLang['defaultNameserversErrorField']),
        'disableAdvanceContacts'    => array('Type' => 'yesno',     'Description' => $configLang['disableAdvanceContacts'], 'FriendlyName' => $configLang['disableAdvanceContactsField']),
        'defaultDomainsMail'        => array('Type' => 'text',      'Description' => $configLang['addDefaultDomainsMail'], 'FriendlyName' => $configLang['addDefaultDomainsMailField']),
        'defaultAdminRoles'         => array('Type' => 'dropdown',  'Description' => $configLang['defaultAdminRoles'], 'Options' => $custom_buttons['roles'], 'Default' => 'six', 'FriendlyName' => $configLang['defaultAdminRolesField']),
        'validationNewClient'       => array('Type' => 'yesno',     'Description' => $configLang['validationNewClient'], 'FriendlyName' => $configLang['validationNewClientField']),
        'disableContactVerification'=> array('Type' => 'yesno',     'Description' => $configLang['disableContactVerification'], 'FriendlyName' => $configLang['disableContactVerificationField']),
        'enableDomainRecords'       => array('Type' => 'yesno',     'Description' => $configLang['enableDomainRecords'], 'FriendlyName' => $configLang['enableDomainRecordsField']),
        'enableDomainLifecycle'       => array('Type' => 'yesno',     'Description' => $configLang['enableDomainLifecycle'], 'FriendlyName' => $configLang['enableDomainLifecycleField']),
        'devMode'                   => array('Type' => 'yesno',     'Description' => $configLang['devMode'], 'FriendlyName' => $configLang['devModeField']),
        'debug'                     => array('Type' => 'yesno',     'Description' => $configLang['debug'], 'FriendlyName' => $configLang['debugField']),
    ));
    if($custom_buttons['warnings'])
        $configarray['check-warnings']  = array('Type' => 'display',   'Description' => $custom_buttons['warnings'], 'FriendlyName' => $configLang['checkWarnings']);
    if($custom_buttons['crons'])
        $configarray['crons']           = array('Type' => 'display',   'Description' => $custom_buttons['crons'], 'FriendlyName'    => $configLang['crons']);
    if($custom_buttons['errors'])
        $configarray['errors']          = array('Type' => 'display',   'Description' => $custom_buttons['errors'], 'FriendlyName' => $configLang['errors']);
    if($custom_buttons['update'])
        $configarray['update-module']   = array('Type' => 'display',   'Description' => $custom_buttons['update'], 'FriendlyName' => $configLang['updateModule']);
    $configarray['install-virtualname'] = array('Type' => 'display',   'Description' => $custom_buttons['install'], 'FriendlyName' => $configLang['installVirtualname']);
    return $configarray;
}

//ADMINISTRATION BUTTONS *DEBUG
function virtualname_AdminCustomButtonArray(){
    global $vname_admin;
    virtualname_init();
    $adminID = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    $buttonarray = array(
        $configLang['sync_domain'] => 'SyncDomain',
        $configLang['resend_irtp'] => 'ResendIRTP'
    );
    return $buttonarray;
}

#######################################
###WHMCS DOMAIN FUNCTIONS##############
#######################################

//ADD DOMAIN RENEW
function virtualname_RenewDomain($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    // 0. CHECK IF DOMAIN WAS RENEWED
    if(isset($params['secureRenovation']) AND $params['secureRenovation'] != '' AND $params['secureRenovation'] != 'Disabled'){
        $secureRenovation = $vname_domains->check_secure_renovation($domain, $params);
        if(!$secureRenovation){
            $values['error']  = $configLang['errorRenewSecure'].$params['secureRenovation'].$configLang['errorRenewSecureHour'];
            return $values;
        }
    }
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
        if(empty($values['error']))
            $values['error']  = $configLang['errorRenewAvailable'];
    }
    else{
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_info['response'][0]['id'].'/renew.json';
        $RESTful= 'POST';
        $regperiod = $params['regperiod'];
        if(isset($params['domainid']))
            $domainid = $params['domainid'];
        else
            $domainid = 0;
        $domain_whmcs_info = $vname_domains->get_whmcs_domain($domain, $domainid);
        $expiryYear = date('Y', strtotime($domain_whmcs_info['expirydate']));
        $fields['json'] = json_encode(array('domain'=>array('expiration_year'=>$expiryYear, 'years'=>$regperiod)));
        $params['action'] = 'RenewDomain';
        // 1. CHECK FREE REGISTERS DOMAINS
        if($params['freeRenewDomains'] == 'on'){
            if($domain_whmcs_info['recurringamount'] == '0.00'){
                $values['error']  = $configLang['errorRenewFree'];
                return $values;
            }
        }
        // 2. RENEW DOMAIN
        try{
            $request = $vname_domains->api_call($params, $fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code'] >= 200 AND $request['status']['code'] <= 299){
            $values['active'] = true;
            $values['expirydate'] = $request['response']['product-info']['product-expiration'];
            logactivity('renew-'.$domain);
        }
        else{
            $values['active'] = false;
            if(isset($request['response']['expiration_year'])){
                $values['error'] = $configLang['errorRenewExpiration'];
            }
            else{
                $values['error'] = ''.$request['status']['description'];
                if(isset($request['response']['base']))
                    $values['error'] .= ' ['.$request['response']['base'][0].']';
            }

            $values['expirydate'] = $expiryYear;

            if(empty($values['error']))
                $values['error']  = $configLang['errorRenew'];
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

//DOMAIN REGISTRATION
function virtualname_RegisterDomain($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    //1. CHECK DOMAIN AVAILABLE
    $domain_available = $vname_domains->available($params);

    if($domain_available['response'][$domain] == FALSE){
        $values['error'] = $domain_info['status']['description'];
        if(empty($values['error']))
            $values['error']  = $configLang['errorRegisterAvailable'];
    }
    elseif($domain_available['response'][$domain] == TRUE){
        $fields = array();
        $module = 'domains/domains';
        $action = 'register.json';
        $RESTful= 'POST';

        // 2. GET AND CHECK DOMAIN NAMESERVERS
        $defaultWHMCSDNS = $vname_nameservers->get_whmcs_default_nameservers();
        $nameServers = array($params['ns1'], $params['ns2']);
        if(!empty($params['ns3'])) $nameServers[] = $params['ns3'];
        if(!empty($params['ns4'])) $nameServers[] = $params['ns4'];
        foreach($nameServers as $keyDNS => $checkDNS){
            if(gethostbyname($checkDNS) == $checkDNS){
                if($params['defaultNameserversError'] == 'on'){
                    $params['ns'.($keyDNS+1)] = $defaultWHMCSDNS['DefaultNameserver'.($keyDNS+1)];
                }
                else{
                    $values['error']  = $configLang['errorRegisterDNS'];
                    return $values;
                }
            }
        }
        $active_advance = $vname_admin->check_advance_contact();
        if($active_advance){
            // 3. CHECK DOMAIN CONTACTS
            if(isset($params['regContact']))
                $regContact = $params['regContact'];
            else
                $regContact = $params['additionalfields']['regContact'];

            if(isset($params['adminContact']))
                $adminContact = $params['adminContact'];
            else
                $adminContact = $params['additionalfields']['adminContact'];

            if(isset($params['techContact']))
                $techContact = $params['techContact'];
            else
                $techContact = $params['additionalfields']['techContact'];

            if(isset($params['billingContact']))
                $billingContact = $params['billingContact'];
            else
                $billingContact = $params['additionalfields']['billingContact'];

            $reg_id     = $vname_contacts->check_contact($regContact, $params);
            $admin_id   = $vname_contacts->check_contact($adminContact, $params);
            $tech_id    = $vname_contacts->check_contact($techContact, $params);
            $billing_id = $vname_contacts->check_contact($billingContact, $params);
        }
        else{
            $contact['firstname']   = $params['adminfirstname'];
            $contact['lastname']    = $params['adminlastname'];
            $contact['company']     = $params['admincompanyname'];
            $contact['email']       = $params['adminemail'];
            $contact['country']     = $params['admincountry'];
            $contact['state']       = $params['adminstate'];
            $contact['city']        = $params['admincity'];
            $contact['address1']    = $params['adminaddress1'];
            $contact['postcode']    = $params['adminpostcode'];
            $contact['phonenumber'] = $params['adminphonenumber'];
            $contact['idnumber']    = $params['adminidentificationnumber'];
            $contact['legal_form']  = $params['adminlegal_form'];
            $create_contact = $vname_contacts->add_contact(0, array(), 0, $contact);
            if($create_contact['error']){
                $reg_id = array('response' => 0, 'error' => $create_contact['error']);
            }
            else{
                $reg_id['response'] = $admin_id['response'] = $tech_id['response'] = $billing_id['response'] = $create_contact['response']['id'];
            }
        }
        if (!$reg_id['response'] OR !$admin_id['response'] OR !$tech_id['response'] OR !$billing_id['response']) {
            $values['error'] = $configLang['add_contact_error'];
            $all_errors = array();
            if($reg_id['error'])     $all_errors[] = $configLang['registrar'].': '.$vname_admin->set_current_error($reg_id['description'], $reg_id['review']);
            if($admin_id['error'])   $all_errors[] = $configLang['admin'].': '.$vname_admin->set_current_error($admin_id['description'], $admin_id['review']);
            if($tech_id['error'])    $all_errors[] = $configLang['technical'].': '.$vname_admin->set_current_error($tech_id['description'], $tech_id['review']);
            if($billing_id['error']) $all_errors[] = $configLang['billing'].': '.$vname_admin->set_current_error($billing_id['description'], $billing_id['review']);
            $values['error'] .= $vname_admin->get_formated_errors($all_errors);
            return $values;
        }
        // 4. STABLISH AUTORENEW STATUS, PRIVACY AND TRANSFER PROTECTION
        if($params['autoRenew'] == 'on')
            $auto_renew = TRUE;
        else
            $auto_renew = FALSE;

        if($params['idprotection'] == true)
            $privacy = TRUE;
        else
            $privacy = FALSE;

        // 5. CHECK FREE REGISTERS DOMAINS
        if($params['freeRegisterDomains'] == 'on'){
            if(isset($params['domainid']))
                $domainid = $params['domainid'];
            else
                $domainid = 0;
            $domainInfo = $vname_domains->get_whmcs_domain($domain, $domainid);
            if($domainInfo['firstpaymentamount'] == '0.00'){
                $values['error']  = $configLang['errorRegisterFree'];
                return $values;
            }
        }

        // 6. DOMAIN REGISTRATION
        $fields['json'] = json_encode(
                array(  'domain'=>array('name'          =>$domain,
                                        'contacts'      =>array('registrant'    =>$reg_id['response'],
                                                                'administrative'=>$admin_id['response'],
                                                                'technical'     =>$tech_id['response'],
                                                                'billing'       =>$billing_id['response']),
                                        'auto_renew'    =>$auto_renew,
                                        'years'         =>$params['regperiod'],
                                        'nameservers'   =>$nameServers,
                                        'protection'    =>true,
                                        'privacy'       =>$privacy
                )));
        $params['action'] = 'RegisterDomain';
        try{
            $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){

            if(isset($request['response']['expiration_year']))
                $values['error'] = 'expiration_year '.$request['response']['expiration_year'];
            elseif(isset($request['response']['contacts'])){
                $values['error']  = $request['status']['description'];
                $values['error'] .= ' [CONTACTOS]: ';
                foreach($request['response']['contacts'] as $contact_type => $contact_errors){
                    $errors = array();
                    foreach($contact_errors as $key => $value)
                        $errors[$key] = $key.': '.$vname_admin->get_formated_errors($value);
                    $resp_errors = $vname_admin->get_formated_errors($errors);
                    $all_errors[] = $contact_type.': '.$resp_errors;
                }
                $values['error'] .= $vname_admin->get_formated_errors($all_errors);
            }
            else{
                $values['error'] = $request['status']['description'];
                if(isset($request['response']['name']))
                    $values['error'] .= ': '.implode(',', $request['response']['name']);
                elseif(count($request['response']) > 0){
                    foreach($request['response'] as $error_type => $error_content){
                        $errors = array();
                        $resp_errors = $error_type.': '.$vname_admin->get_formated_errors($error_content);
                        $all_errors[] = $contact_type.': '.$resp_errors;
                    }
                    $values['error'] .= $vname_admin->get_formated_errors($all_errors);
                }
            }

            if(empty($values['error']))
                $values['error']  = $configLang['errorRegister'];

            $values['expirydate'] = $domain_whmcs_info['expirydate'];
        }
        else{
            $values['active'] = true;
            $values['expirydate'] = $request['response']['product-info']['product-expiration'];
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

//GET DOMAIN PROTECTION
function virtualname_GetRegistrarLock($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $request = $vname_domains->view_domain_info($params);
    $checkLock = $request['response'];
    if($request['status']['code']< 200 || $request['status']['code'] > 299){
        $values['error'] = $request['status']['description'];
        if(isset($request['response']['name']))
            $values['error'] .= ': '.implode(',', $request['response']['name']);
        return $values;
    }
    else{
        if(is_array($checkLock)){
            if ($checkLock[0]['protection']) {
                $lockstatus='locked';
            } else {
                $lockstatus='unlocked';
            }
        }
    }
    return $lockstatus;
}

//GET AUTHCODE
function virtualname_GetEPPCode($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $request = $vname_domains->view_domain_info($params);
    if($request['status']['code']< 200 || $request['status']['code'] > 299){
        $values['error'] = $request['status']['description'];
        if(isset($request['response']['name']))
            $values['error'] .= ': '.implode(',', $request['response']['name']);
    }
    else{
        $authcode = $request['response'][0]['authcode'];
        if($authcode == '')
            $authcode = 'N/A';
        $values['eppcode'] = $authcode;
    }
    return $values;
}

//UPDATE DOMAIN PROTECTION
function virtualname_SaveRegistrarLock($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $config     = $vname_admin->config();
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(!$vname_domains->tld_check_enable_lock($params['tld']))
        return array('error'=>$configLang['disableLockError']);
    $vname_admin->check_configuration($params);
    $lockstatus = virtualname_GetRegistrarLock($params);
    if($lockstatus == 'locked')
        $changeLock = FALSE;
    else
        $changeLock = TRUE;
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_info['response'][0]['id'].'.json';
        $RESTful= 'PATCH';
        $fields['json'] = json_encode(array('domain'=>array('protection'=>$changeLock)));
        $params['action'] = 'SaveRegistrarLock';
        try{
            $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

//TRANSFER DOMAIN
function virtualname_TransferDomain($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domainid = $params['domainid'];
    //1. CHECK DOMAIN AVAILABLE
    $domain_transfer_available = $vname_domains->transfer_available($params);
    if($domain_transfer_available['response'][$domain] == FALSE){
        $values['error']  = $configLang['transfer_not_available'];
    }
    elseif($domain_transfer_available['response'][$domain] == TRUE){
        $fields = array();
        $module = 'domains/domains';
        $action = 'transfer.json';
        $RESTful= 'POST';
        // 2. CHECK DOMAIN CONTACTS
        $domain_data  = $vname_domains->get_whmcs_domain($domain, $domainid);
        $orderContact = $vname_contacts->get_whmcs_order_contact($domain_data['orderid']);
        if(!isset($params['additionalfields']['regContact']))
            $params['additionalfields']['regContact']   = $orderContact;
        if(!isset($params['additionalfields']['adminContact']))
            $params['additionalfields']['adminContact']     = $orderContact;
        if(!isset($params['additionalfields']['techContact']))
            $params['additionalfields']['techContact']      = $orderContact;
        if(!isset($params['additionalfields']['billingContact']))
            $params['additionalfields']['billingContact']   = $orderContact;

        $reg_id     = $vname_contacts->check_contact($params['additionalfields']['regContact'],$params);
        $admin_id   = $vname_contacts->check_contact($params['additionalfields']['adminContact'],$params);
        $tech_id    = $vname_contacts->check_contact($params['additionalfields']['techContact'],$params);
        $billing_id = $vname_contacts->check_contact($params['additionalfields']['billingContact'],$params);

        if (!$reg_id['response'] OR !$admin_id['response'] OR !$tech_id['response'] OR !$billing_id['response']) {
            $values['error'] = $configLang['add_contact_error'];
            $all_errors = array();
            if($reg_id['error'])     $all_errors[] = $configLang['registrar'].': '.$vname_admin->set_current_error($reg_id['description'], $reg_id['review']);
            if($admin_id['error'])   $all_errors[] = $configLang['admin'].': '.$vname_admin->set_current_error($admin_id['description'], $admin_id['review']);
            if($tech_id['error'])    $all_errors[] = $configLang['technical'].': '.$vname_admin->set_current_error($tech_id['description'], $tech_id['review']);
            if($billing_id['error']) $all_errors[] = $configLang['billing'].': '.$vname_admin->set_current_error($billing_id['description'], $billing_id['review']);
            $values['error'] .= $vname_admin->get_formated_errors($all_errors);
            return $values;
        }
        // 3. STABLISH AUTORENEW STATUS
        if($params['autoRenew'] == 'on')
            $autoRenew = TRUE;
        else
            $autoRenew = FALSE;

        if($params['idprotection'] == true)
            $privacy = TRUE;
        else
            $privacy = FALSE;

        // 4. STABLISH AUTHCODE AND AUTORENEW
        $authCode  = urlencode($params['transfersecret']);
        if($params['autoRenew'] == 'on')
            $auto_renew = TRUE;
        else
            $auto_renew = FALSE;

        // 5. TRANSFER DOMAIN
        $fields['json'] = json_encode(
                array('domain'=>array('name'=>$domain,
                                      'contacts'      =>array('registrant'    =>$reg_id['response'],
                                                            'administrative'=>$admin_id['response'],
                                                            'technical'     =>$tech_id['response'],
                                                            'billing'       =>$billing_id['response']),
                                      'authcode'      =>urldecode($authCode),
                                      'auto_renew'    =>$auto_renew,
                                      'protection'    =>true,
                                      'privacy'       =>$privacy
                )));
        $params['action'] = 'TransferDomain';
        try{
            $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            if(isset($request['response']['expiration_year']))
                $values['error'] = 'Incorrect expiration_year '.$request['response']['expiration_year'];
            else{
                if($request['response']['name'][0])
                    $values['error'] = $request['response']['name'][0];
                elseif($request['response']['authcode'][0])
                    $values['error'] = 'Authcode: '. $request['response']['authcode'][0];
                else{
                    $values['error'] = $request['status']['description'];
                    if(isset($request['response']['name']))
                        $values['error'] .= ': '.implode(',', $request['response']['name']);
                }
            }

            $values['expirydate'] = $domain_whmcs_info['expirydate'];

            if(empty($values['error']))
                $values['error']  = 'Error on tranfer domain. Please check register logs.';

        }
        else{
            $values['active'] = true;
            $expirydate = $request['response']['product-info']['product-expiration'];
            $values['expirydate'] = $request['response']['product-info']['product-expiration'];
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

//CHANGE WHOIS PROTECTION
function virtualname_IDProtectToggle($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $privacy = ($params['protectenable'] == '1') ? 'true' : 'false';
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_info['response'][0]['id'].'.json';
        $RESTful= 'PATCH';
        $fields['json'] = json_encode(array('domain'=>array('privacy'=>$privacy)));
        $params['action'] = 'SaveDomainPrivacy';
        try{
            $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

#######################################
###WHMCS DOMAIN NAMESERVERS############
#######################################
//GET DOMAIN DNS
function virtualname_GetNameservers($params){
    //INIT MODULE
    global $vname_admin, $vname_api, $vname_domains;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $fields = array();
    $module = 'domains';
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $action = 'domains.json?name='.$domain;
    $RESTful= 'GET';
    $params['action'] = 'GetNameServers';
    //CACHE
    /*try{
        //VIEW DOMAIN INFO CACHE
        $cache_info = $vname_domains->get_domain_cache($domain);
        if(!$cache_info){
            $request = $vname_api->api_call($params, $fields, $module, $action, $RESTful);
            $vname_domains->set_domain_cache($domain, $request);
        }
        else
            $request = $cache_info;
    }catch (Exception $e){
        return ($e->getMessage());
    }*/
    //NO CACHE
    try{
        $request = $vname_api->api_call($params, $fields, $module, $action, $RESTful);
    }catch (Exception $e){
        return ($e->getMessage());
    }
    $value = array();
    if($request['status']['code']< 200 || $request['status']['code'] > 299){
        $values['error'] = $request['status']['description'];
        if(isset($request['response']['name']))
            $values['error'] .= ': '.implode(',', $request['response']['name']);
    }
    else{
        $i=0;
        foreach($request['response'][0]['nameservers'] as $dns){
            $i++;
            $values['ns'.$i] = $dns;
        }
    }
    return $values;
}
//REGISTER NAMESERVERS
function virtualname_RegisterNameserver($params) {
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers;
    virtualname_init();
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $values = $vname_nameservers->create_domain_hosts($params, $domain_info['response'][0]['id']);
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}
//MODIFY NAMESERVERS
function virtualname_ModifyNameserver($params) {
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $whmcs;
    virtualname_init();
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $domain_hosts = $vname_nameservers->get_domain_hosts($params, $domain_info['response'][0]['id']);
        $nameserver = explode('.',$params['nameserver']);
        $params['ipaddress'] = $params['newipaddress'];
        $hosts = $nameserver[0];
        if($domain_hosts[$hosts] AND $params['currentipaddress'] == $domain_hosts[$hosts]['ips'][0]){
            $values = $vname_nameservers->delete_domain_hosts($params, $domain_info['response'][0]['id'], $domain_hosts[$hosts]['id']);
            $values = $vname_nameservers->create_domain_hosts($params, $domain_info['response'][0]['id']);
        }
        else{
            $values['error'] = $whmcs->get_lang('modify_glue_records');
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}
//DELETE NAMESERVERS
function virtualname_DeleteNameserver($params) {
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $whmcs;
    virtualname_init();
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $domain_hosts = $vname_nameservers->get_domain_hosts($params, $domain_info['response'][0]['id']);
        $nameserver = explode('.',$params['nameserver']);
        $hosts = $nameserver[0];
        if($domain_hosts[$hosts]){
            $values = $vname_nameservers->delete_domain_hosts($params, $domain_info['response'][0]['id'], $domain_hosts[$hosts]['id']);
        }
        else{
            $values['error'] = $whmcs->get_lang('delete_glue_records');
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}
//SET DOMAIN DNS
function virtualname_SaveNameservers($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers;
    virtualname_init();
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $values = $vname_nameservers->save_nameservers($params, $domain_info['response'][0]['id']);
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

#######################################
###WHMCS CONTACTS FUNCTIONS##################
#######################################

//GET CONTACTS DETAILS
function virtualname_GetContactDetails($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if(is_array($domain_info)){
        if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
            $values['error']  = $domain_info['status']['description'];
        }
        else{
            $active_advance = $vname_admin->check_advance_contact();
            $additionalcontactsfields = $vname_domains->get_whmcs_additional_domains($params['domainid']);
            if($active_advance)
                $values = $vname_contacts->get_contacts_advance_details($params, $domain_info);
            else
                $values = $vname_contacts->get_contacts_simple_details($params, $domain_info);
        }
    }
    return $values;
}

//SAVE ALL CONTACTS
function virtualname_SaveContactDetails($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_contacts;
    virtualname_init();
    $active_advance = $vname_admin->check_advance_contact();
    if($active_advance){
        $values = $vname_contacts->set_contacts_advance_details($params);
    }
    else{
        $values = $vname_contacts->set_contacts_simple_details($params);
    }
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

#############################################
###ADMIN PANEL FUNCTIONS ####################
#############################################

//RESEND IRTP VERIFICATION
function virtualname_ResendIRTP($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $config     = $vname_admin->config();
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    $vname_admin->check_configuration($params);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $status = $domain_info['response'][0]['product_info']['product_status'];
        if($status != 'active_pending_registrant_approval'){
            $values['error'] = $configLang['error_resend_irtp'];
        }
        else{
            $fields = array();
            $module = 'domains/domains';
            $action = $domain_info['response'][0]['id'].'/resend-irtp-verification.json';
            $RESTful= 'GET';
            $params['action'] = 'ResendIRTP';
            try{
                $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
            }catch (Exception $e){
                return ($e->getMessage());
            }
            if($request['status']['code']< 200 || $request['status']['code'] > 299){
                $values['error'] = $request['status']['description'];
                if(isset($request['response']['name']))
                    $values['error'] .= ': '.implode(',', $request['response']['name']);
            }
        }
    }
    //$vname_domains->destroy_domain_cache($domain);
    return $values;
}

//SYNC STATUS AND DATES FROM DOMAIN
function virtualname_SyncDomain($params){
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}

    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    $synclog = '[$domain] Virtualname Sync Domain:';
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        if($domain_info['status']['code'] == 404){
            //UPDATE BBDD
            $sql = 'UPDATE tbldomains SET status = \'Cancelled\'';
            $sql .= ' WHERE id=\''.$params['domainid'].'\'';
            $res = mysql_query($sql);
            $GLOBALS['domainstatus']    = 'Cancelled';
        }
        $values['error'] = $domain_info['status']['description'];
    }
    else{
        $expirydate = $domain_info['response'][0]['product_info']['product_expiration'];
        //UPDATE EXPIRATION DATE
        $synclog .= ' expirydate=$expirydate';
        $sql = 'UPDATE tbldomains SET expirydate = \''.$expirydate.'\'';
        //UPDATE STATUS
        $whmcs_version = $vname_admin->get_whmcs_version();
        $whmcs_version = substr($whmcs_version, 0, 4);
        $status = $domain_info['response'][0]['product_info']['product_status'];
        if($status == 'active') {
            $synclog .= ' status=active';
            $newstatus = 'Active';
            $sql .= ', `status` = \'Active\'';
        }
        elseif($status == 'pending'){
            $synclog .= ' status=pending';
            $newstatus = 'Pending';
            $sql .= ', `status` = \'Pending\'';
        }
        elseif($status == 'transferring' || $status == 'transfer_requested' || $status == 'transfer_initiated' || $status == 'transfer_email_sent' || $status == 'transfer_rejected' || $status == 'transfer_approved' || $status == 'transfer_finished' || $status == 'transfer_expired'){
                $synclog .= ' status=pending_transfer';
                $newstatus = 'Pending Transfer';
                $sql .= ' AND `status` = \'Pending Transfer\'';
        }
        else{
            if($status == 'expired') {
                $synclog .= ' status=expired';
                $newstatus = 'Expired';
                $sql .= ', `status` = \'Expired\'';
            }
            elseif($status == 'redemption') {
                $synclog .= ' status=redemption';
                $newstatus = 'Redemption';
                $sql .= ', `status` = \'Redemption\'';
            }
            elseif($status == 'cancelled' || $status == 'inactive') {
                $synclog .= ' status=cancelled';
                $newstatus = 'Cancelled';
                $sql .= ', `status` = \'Cancelled\'';
            }
            elseif($status == 'outbound_transfer') {
                $synclog .= ' status=transferredaway';
                $newstatus = 'Transferred Away';
                $sql .= ', `status` = \'Transferred Away\'';
            }
        }
        //UPDATE DOMAIN PRIVACY
        if($domain_info['response'][0]['privacy'] == TRUE){
            $synclog .= ' idprotection=on';
            $newidprotection = 'on';
            $sql .= ', `idprotection`=\'1\'';
        }
        elseif($domain_info['response'][0]['privacy'] == FALSE){
            $synclog .= ' idprotection=off';
            $newidprotection = '';
            $sql .= ', `idprotection`=\'0\'';
        }
        //UPDATE BBDD
        $sql .= ' WHERE id=\''.$params['domainid'].'\'';
        $res = mysql_query($sql);
        //SESSION
        $dateformat = $GLOBALS['CONFIG']['DateFormat'];
        $dateformat = str_replace(array('DD', 'MM', 'YYYY'), array('d', 'm', 'Y'), $dateformat );
        $newdate = explode('-', $expirydate);
        $newdate = mktime(0, 0, 0, $newdate[1], $newdate[2], $newdate[0]);
        $newdate = date($dateformat, $newdate);
        $GLOBALS['expirydate']      = $newdate;
        $GLOBALS['domainstatus']    = $newstatus;
        $GLOBALS['idprotection']    = $newidprotection;
        return array('message' => $configLang['successSync']);
    }
    return $values;
}

//WHMCS SYNC STATUS AND DATES FROM DOMAIN
function virtualname_Sync($params) {
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}

    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        if($domain_info['status']['code'] == 404){
            //UPDATE BBDD
            if($params['domainid']){
                $sql = 'UPDATE tbldomains SET status = \'Cancelled\'';
                $sql .= ' WHERE id=\''.$params['domainid'].'\'';
                $res = mysql_query($sql);
            }
        }
        else
            $values['error'] = $domain_info['status']['description'];
    }
    else{
        $expirydate = $domain_info["response"][0]["product_info"]['product_expiration'];

        $status = $domain_info['response'][0]["product_info"]['product_status'];
        if($status == 'active') {
            $values['active'] = true;
        }
        if($status == 'expired') {
            $values['expired'] = true;
        }
        $values['expirydate'] = $expirydate;
    }
    return $values;
}

//WHMCS SYNC TRANSFER STATUS AND DATES FROM DOMAIN
function virtualname_TransferSync($params) {
    //INIT MODULE
    global $vname_admin, $vname_domains, $vname_nameservers, $vname_contacts;
    virtualname_init();
    $vname_admin->check_configuration($params);
    $adminID    = $_SESSION['adminid'];
    $configLang = $vname_admin->get_config_lang($adminID);
    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
    if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}

    if(!class_exists('Punycode'))
        @include_once('lib/classes/class.punicode.php');
    $Punycode = new Punycode();
    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
    $domain_info = $vname_domains->view_domain_info($params);
    $synclog = '[$domain] Virtualname Sync Domain:';
    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
        $values['error'] = $domain_info['status']['description'];
        $synclog .= ' Domain not found';
    }
    else{
        $expirydate = $domain_info["response"][0]["product_info"]['product_expiration'];
        $status     = $domain_info['response'][0]["product_info"]['product_status'];
        if($status == 'active') {
            $values['completed'] = true;
            $values['expirydate'] = $expirydate;
        }
    }
    return $values;
}

?>
