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
// * File description: HOOK functions
// *************************************************************************

if (!defined('WHMCS'))
    die('This file cannot be accessed directly');

$advance = hook_virtualname_check_advance_contact();
if($advance){
    add_hook('ClientAreaFooterOutput',1,'hook_return_contact_page');
    add_hook('AdminAreaFooterOutput',1,'hook_return_admin_contact_page');
    add_hook('ContactAdd',  1,  'hook_contactAdd');
    add_hook('ContactDetailsValidation', 1,  'hook_contactEdit');
    add_hook('ClientDetailsValidation',  1,  'hook_contactEdit');
    add_hook('AdminClientProfileTabFields', 1, 'hook_ic_legal_form_admin');
}
add_hook('AdminClientDomainsTabFields', 1, 'hook_domain_data');
add_hook('AdminClientDomainsTabFields', 1, 'hook_disable_admin_buttons');
add_hook('AdminHomepage', 1, 'hook_admin_version_popup');
add_hook('DomainEdit', 1, 'hook_domain_save');
add_hook('PreRegistrarRenewDomain', 1, 'hook_transfer_on_renewal');
add_hook('AfterShoppingCartCheckout', 1, 'hook_admin_set_transfer_order');


############################################################
############HOOK FUNCTIONS##################################
############################################################

//POPUP NEW VERSION
function hook_admin_version_popup($var){
    $version_popup = '';
    require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
    //INIT MODULE
    global $vname_admin;
    virtualname_init();
    $params = $vname_admin->config();
    $adminID = $_SESSION['adminid'];
    $langs = $vname_admin->get_config_lang($adminID);
    $version = $vname_admin->get_module_version($params);
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $thisPath = dirname($_SERVER['PHP_SELF']);
    $currentPath = str_replace($rootPath, '', $thisPath);
    if($currentPath == '/')
        $currentPath = '';
    if($params['Module-Version'] != $version['response']['lastversion']){
        $version_popup =
            '<script type=\'text/javascript\' src=\''.$currentPath.'/../modules/registrars/virtualname/themes/js/virtualname_adm.js\'></script>
            <link rel=\'stylesheet\' type=\'text/css\' href=\''.$currentPath.'/../modules/registrars/virtualname/themes/css/virtualname_adm_popup.css\'/>
            <div class=\'admindialog\' id=\'vname_popup\'>
                <a href=\'#\' onclick=\'virtualname_dialogClose();return false\' class=\'close\'>x</a>
                <div id=\'vname_popup_dialog\'>
                    <div class=\'content\'>
                        <div class=\'boxy\'>
                            <div id=\'vnotice\'>
                                <div class=\'body\'>
                                    <h3 class=\'update_virtualname\'>
                                    Virtualname - TCpanel
                                    <p class=\'desc\'>'.$langs['new_module_update'].'</p>
                                    </h3>
                                    <p class=\'latestvname\'>v'.$version['response']['lastversion'].'</p>
                                    <p class=\'currentvname\'>'.$langs['current_version'].' v'.$params['Module-Version'].' <a href=\'configregistrars.php?#virtualname\' target=\'_blank\' class=\'btn-vname\'>'.$langs['get_update'].'</a></p>
                                </div>
                                <div class=\'donotshow\'>
                                    <label style=\'color:gray;\'>
                                        <input type=\'checkbox\' onclick=\'setVirtualnameCookie($(this).is(":checked"))\'> '.$langs['hide_popup'].'
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
    return $version_popup;
}

//LAUNCHED WHEN DOMAIN WAS SAVED IN ADMIN AREA
function hook_contactsEdit($var){
    $values['userid'] = $var['userid'];
    $values['domain'] = $var['domain'];
    $values['reg']    = $var['domainfield'][0];
    $values['admin']  = $var['domainfield'][1];
    $values['tech']   = $var['domainfield'][2];
    $values['bill']   = $var['domainfield'][3];
    require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
    virtualname_SaveContactDetails($values);
}

//LAUNCHED WHEN CONTACT WAS EDITED
function hook_contactEdit($var){

    //IF CANT HAVE ANY CONTACT OR USER IDS
    if(!$var['userid'] && !$var['contactid'] && !$var['customerid'])
        return 0;

    if($var['register'] == true)
        return 0;

    if($var['contact'])
        if($var['contact'] == 'addingnew')
            return 0;

    $userID     = $var['userid'];
    $contactID  = $var['contactid'];
    if($contactID){
        $type = 2;
        $contact['firstname']   = $var['firstname'];
        $contact['lastname']    = $var['lastname'];
        $contact['company']     = $var['companyname'];
        $contact['email']       = $var['email'];
        $contact['country']     = $var['country'];
        $contact['state']       = $var['state'];
        $contact['city']        = $var['city'];
        $contact['address1']    = $var['address1'];
        $contact['postcode']    = $var['postcode'];
        $contact['phonenumber'] = $var['phonenumber'];
        $contact['idnumber']    = $_POST['identificationnumber'];
        $contact['legal_form']  = $_POST['legal_form'];
        $contact['customfield'] = $var['customfield'];
    }
    else{
        if(is_null($var['userid']) || empty($var['userid'])){
            $var['userid'] = $var['customerid'];
        }
        $contactID = $var['userid'];
        $type = 1;
        $contact['firstname']   = $var['firstname'];
        $contact['lastname']    = $var['lastname'];
        $contact['company']     = $var['companyname'];
        $contact['email']       = $var['email'];
        $contact['country']     = $var['country'];
        $contact['state']       = $var['state'];
        $contact['city']        = $var['city'];
        $contact['address1']    = $var['address1'];
        $contact['postcode']    = $var['postcode'];
        $contact['phonenumber'] = $var['phonenumber'];
        if(isset($_POST['clientidentificationnumber']))
            $ic = $_POST['clientidentificationnumber'];
        elseif(isset($_POST['identificationnumber']))
            $ic = $_POST['identificationnumber'];
        else
            $ic = '';
        if(isset($_POST['legal_form']))
            $legal_form = $_POST['legal_form'];
        else
            $legal_form = '';
        $contact['idnumber']    =  $ic;
        $contact['legal_form']  = $legal_form;
        $contact['customfield'] = $var['customfield'];
    }

    require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
    require_once(dirname(dirname(__FILE__)).'/../includes/functions.php');
    //INIT MODULE
    global $vname_contacts;
    virtualname_init();

    $TCpanelWHMCS   = $vname_contacts->get_tcpanel_contact($contactID, $type);
    $TCpanelContact = $TCpanelWHMCS['id_contact_tcpanel'];

    if($TCpanelContact == 0){
        if($var['linked'])
            $response = $vname_contacts->add_contact($contactID, array(), 0, $contact);
        else
            $response = $vname_contacts->validate_contact($contactID, array(), 0, $contact);
        if(!$response['error']){
            $TCpanelContact = $response['response']['id'];
            if($TCpanelContact != 0)
                $vname_contacts->add_tcpanel_contact($contactID, $TCpanelContact, $type, $contact['idnumber'], $contact['legal_form']);
        }
        elseif($response['status']['code'] == 422){
            $error = array();
            foreach($response['response'] as $key => $value){
                $error[] = $key.': '.$value[0];
            }
            return $error;
        }
        else{
            $error = $response['error'];
            return $error;
        }
    }
    else{
        $response = $vname_contacts->edit_contact($TCpanelContact, $contact, $contactID, $type);
        if(!$response['error']){
            return 0;
        }
        elseif($response['status']['code'] == 422){
            $error = array();
            foreach($response['response'] as $key => $value){
                $error[] = $key.': '.$value[0];
            }
            return $error;
        }
        else{
            $error = $response['error'];
            return $error;
        }
    }
}


//LAUNCHED WHEN CONTACT WAS ADDED
function hook_contactAdd($var){

    $userID    = $var['userid'];
    $contactID = $var['contactid'];

    if($contactID){
        $type = 2;
        $contact['firstname']   = $var['firstname'];
        $contact['lastname']    = $var['lastname'];
        $contact['company']     = $var['company'];
        $contact['email']       = $var['email'];
        $contact['country']     = $var['country'];
        $contact['state']       = $var['state'];
        $contact['city']        = $var['city'];
        $contact['address1']    = $var['address1'];
        $contact['postcode']    = $var['postcode'];
        $contact['phonenumber'] = $var['phonenumber'];
        $contact['idnumber']    = $_POST['identificationnumber'];
        $contact['legal_form']  = $_POST['legal_form'];
    }
    else{
        $contactID = 0;
        $type      = 1;
    }

    require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
    require_once(dirname(dirname(__FILE__)).'/../includes/functions.php');
    //INIT MODULE
    global $vname_contacts;
    virtualname_init();

    $TCpanelWHMCS   = $vname_contacts->get_tcpanel_contact($contactID, $type);
    $TCpanelContact = $TCpanelWHMCS['id_contact_tcpanel'];
    if($TCpanelContact == 0){
        $response = $vname_contacts->validate_contact($contactID, array(), 0, $contact);
        if(!$response['error']){
            $TCpanelContact = $response['response']['id'];
            if($TCpanelContact != 0)
                $vname_contacts->add_tcpanel_contact($contactID, $TCpanelContact, $type, $contact['idnumber'], $contact['legal_form']);
        }
        elseif($response['status']['code'] == 422){
            $error = array();
            foreach($response['response'] as $key => $value){
                $error[] = $key.': '.$value[0];
            }
            return $error;
        }
        else{
            $error = $response['error'];
            return $error;
        }
    }
    else{
        $response = $vname_contacts->edit_contact($TCpanelContact, $contact, $contactID, $type);
        if(!$response['error']){
            return 0;
        }
        elseif($response['status']['code'] == 422){
            $error = array();
            foreach($response['response'] as $key => $value){
                $error[] = $key.': '.$value[0];
            }
            return $error;
        }
        else{
            $error = $response['error'];
            return $error;
        }
    }
}

//CHANGE URLS MODIFY DOMAINS JS
function hook_return_contact_page($var){
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $thisPath = dirname($_SERVER['PHP_SELF']);
    $currentPath = str_replace($rootPath, '', $thisPath);
    if($currentPath == '/')
        $currentPath = '';
    $js =  '<script type=\'text/javascript\' src=\''.$currentPath.'/modules/registrars/virtualname/themes/js/virtualname_client.js\'></script>';
    return $js;
}
function hook_return_admin_contact_page($var){
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $thisPath = dirname($_SERVER['PHP_SELF']);
    $currentPath = str_replace($rootPath, '', $thisPath);
    $js = '';
    if($currentPath == '/')
        $currentPath = '';
    $add_tab_files = array('clientssummary', 'clientscontacts', 'clientsprofile', 'clientsdomains', 'clientsbillableitems', 'clientsinvoices', 'clientsquotes', 'clientstransactions', 'clientsemails', 'clientsnotes', 'clientslog', 'clientsservices');
    if($var['filename'] == 'clientsdomains'){
        $js .=  '<script type=\'text/javascript\' src=\''.$currentPath.'/../modules/registrars/virtualname/themes/js/virtualname_adm.js\'></script>';
    }
    if(in_array($var['filename'], $add_tab_files)){
        if(isset($_GET['userid'])){
            $js .= '<script type=\'text/javascript\' src=\''.$currentPath.'/../modules/registrars/virtualname/themes/js/virtualname_adm_tabs.js\'></script>';
            $js .= '<script type=\'text/javascript\'>virtualname_client_tab('.$_GET['userid'].');</script>';
        }
    }
    return $js;
}

//SHOW DOMAIN DATA IN ADMIN VIEW
function hook_domain_data($vars){
    $domainid = $vars['id'];
    $check = hook_virtualname_check_domain($domainid);
    if($check){
        global $vname_domains;
        $domain = $vname_domains->get_whmcs_domain('', $domainid);
        $info = $vname_domains->view_domain_info_hook($domain['domain']);
        $adminID = $_SESSION['adminid'];
        $langs = $vname_domains->get_config_lang($adminID);

        if($info){
            $domain_data = '<link rel=\'stylesheet\' type=\'text/css\' href=\'../modules/registrars/virtualname/themes/css/virtualname_adm.css\'/>';
            $domain_data .= $langs['domain_status'].': <label class=\'label '.$info['status']['class'].' label_extra\'>'.$info['status']['value'].'</label> ';
            $domain_data .= $langs['auto_renew'].':  <label class=\'label '.$info['auto_renew']['class'].' label_extra\'>'.$info['auto_renew']['value'].'</label> ';
            $domain_data .= $langs['privacy'].': <label class=\'label '.$info['privacy']['class'].' label_extra\'>'.$info['privacy']['value'].'</label> ';
            $domain_data .= $langs['protection'].': <label class=\'label '.$info['protection']['class'].' label_extra\'>'.$info['protection']['value'].'</label></br>';
            $domain_data .= $langs['exp_date'].':  <label class=\'label cancelled inactive label_extra\'>'.$info['expiration_date'].'</label> ';
            $domain_data .= $langs['crea_date'].': <label class=\'label cancelled inactive label_extra\'>'.$info['created_at'].'</label></br>';
            $domain_data .= $langs['reg_handle'].': <label class=\'label cancelled inactive label_extra\'>'.$info['reg_id'].'</label> ';
            $domain_data .= $langs['adm_handle'].': <label class=\'label cancelled inactive label_extra\'>'.$info['adm_id'].'</label> ';
            $domain_data .= $langs['bill_handle'].': <label class=\'label cancelled inactive label_extra\'>'.$info['bill_id'].'</label> ';
            $domain_data .= $langs['tech_handle'].': <label class=\'label cancelled inactive label_extra\'>'.$info['tech_id'].'</label>';
            return array(
                '<b>'.$langs['domain_data'].'</b>' => $domain_data
            );
        }
    }
    else{
        require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
        global $vname_domains;
        $adminID = $_SESSION['adminid'];
        $domain = $vname_domains->get_whmcs_domain('', $domainid);
        $langs = $vname_domains->get_config_lang($adminID);
        $transfer_domain = $vname_domains->get_transfer_on_renewal($domainid);
        $current_status = '';
        $type = '';
        $email = '';
        $authcode = '';
        $registrar = '';
        if($transfer_domain){
            $current_status = $transfer_domain['status'];
            $type = $transfer_domain['type'];
            if($type == 1){
                $select_authcode = 'selected';
                $authcode = mysql_real_escape_string($transfer_domain['value']);
                $class_email = 'hidden';
            }
            elseif($type == 2){
                $select_email = 'selected';
                $email = $transfer_domain['value'];
                $class_authcode = 'hidden';
            }
            elseif($type == 3){
                $select_authcode_in_progress = 'selected';
                $authcode = mysql_real_escape_string($transfer_domain['value']);
                $class_email = 'hidden';
            }
            elseif($type == 4){
                $select_email_in_process = 'selected';
                $email = $transfer_domain['value'];
                $class_authcode = 'hidden';
            }
            else
                $class_email = 'hidden';
        }
        else
            $class_email = 'hidden';

        if($current_status == 'disabled'){
            $select_inactive = 'selected';
            $disabled = 'disabled';
        }
        elseif($current_status == 'active')
            $select_active = 'selected';

        $statusDropDown = '<option value=\'disabled\' '.$select_inactive.'>'.$langs['transfer_on_renewal_inactive'].'</option>';
        $statusDropDown .= '<option value=\'active\' '.$select_active.'>'.$langs['transfer_on_renewal_active'].'</option>';
        $registrarAccepted = $vname_domains->get_accepted_transfer_registrars();

        $typeDropDown  = '<option value=\'1\' '.$select_authcode.'>Authcode</option>';
        $typeDropDown .= '<option value=\'2\' '.$select_email.'>Email</option>';
        $typeDropDown .= '<option value=\'3\' '.$select_authcode_in_progress.'>Authcode '.$langs['in_progress'].'</option>';
        $typeDropDown .= '<option value=\'4\' '.$select_email_in_process.'>Email '.$langs['in_progress'].'</option>';

        if($current_status == 'active')
            $back_color = 'limegreen';
        else
            $back_color = 'red';


        $transfer_on_renewal = '<label class=\'fieldlabel-inline\' style=\'font-weight: 100;\'>'.$langs['status_transfer_on_renewal'].'<select name=\'status_transfer_on_renewal\' id=\'status_transfer_on_renewal\' class=\'form-control input-300\' onchange=\'active_transfer_on_renewal(this);\' style=\'background:'.$back_color.';color:white;\'>'.$statusDropDown.'</select></label>&nbsp;&nbsp;';
        $transfer_on_renewal .= '<label class=\'fieldlabel-inline\' style=\'font-weight: 100;\'>'.$langs['type_transfer_on_renewal'].'<select name=\'select_transfer_on_renewal\' id=\'select_transfer_on_renewal\' class=\'form-control input-300\' onchange=\'change_transfer_on_renewal(this);\' '.$disabled.'>'.$typeDropDown.'</select></label>&nbsp;&nbsp;';
        $transfer_on_renewal .= '<label class=\'fieldlabel-inline '.$class_authcode.'\' id=\'authcode_label\' style=\'font-weight: 100;\'>Authcode<input type=\'text\' name=\'authcode_transfer_on_renewal\' id=\'authcode_transfer_on_renewal\'class=\'form-control input-300\' placeholder=\'authcode\' value=\''.$authcode.'\' '.$disabled.'/></label>';
        $transfer_on_renewal .= '<label class=\'fieldlabel-inline '.$class_email.'\' id=\'email_label\' style=\'font-weight: 100;\'>Email administrativo<input type=\'text\' name=\'mail_transfer_on_renewal\' id=\'mail_transfer_on_renewal\'class=\'form-control input-300\' placeholder=\'email\' value=\''.$email.'\' '.$disabled.'/></label>';
        //$transfer_on_renewal .= '&nbsp;&nbsp;<label class=\'fieldlabel-inline\' style=\'font-weight: 100;\'><input type=\'submit\' name=\'launch_transfer_on_renewal\' id=\'launch_transfer_on_renewal\'class=\'form-control input-300\' value=\''.$langs['launch_transfer'].'\' '.$disabled.' /></label>';
        $transfer_on_renewal .= '</br><label style="font-weight: 100;">*'.$langs['info_transfer_on_renewal'].'</label> ';
        $transfer_on_renewal .= '</br><label style="font-weight: 100;">**'.$langs['registrar_transfer_on_renewal'].': <b>'.strtoupper(implode(',', $registrarAccepted)).'</b></label> ';
        $transfer_on_renewal .= '<input type=\'hidden\' name=\'registrant_transfer_on_renewal\' id=\'registrant_transfer_on_renewal\' value=\''.$domain['registrar'].'\'/>';
        return array(
                '<b>'.$langs['transfer_on_renewal'].':</b>' => $transfer_on_renewal
        );
    }
}

//SHOW CUSTOM FIELDS LEGAL FORM AND IC
function hook_ic_legal_form_admin($vars) {
    require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
    //INIT MODULE
    global $vname_admin, $vname_contacts;
    virtualname_init();
    $params = $vname_admin->config();
    $adminID = $_SESSION['adminid'];
    $langs = $vname_admin->get_config_lang($adminID);
    $userID = $vars['userid'];
    $tcpanel_contact = $vname_contacts->get_tcpanel_contact($userID, 1);
    $legal_form = $tcpanel_contact['legal_form'];
    $idnumber = $tcpanel_contact['identification_number'];
    $legalSelect = $vname_contacts->get_legal_forms($langs['legal_form'], $legal_form);
    return array(
        $langs['legal_form_field'] => $legalSelect,
        $langs['identification_number'] => '<input type=\'text\' name=\'identificationnumber\' class=\'form-control input-250\' value=\''.$idnumber.'\'/>',
    );
}

//VALIDATE IF THE CURRENT DOMAIN WAS ASSIGNED WITH VIRTUALNAME
function hook_virtualname_check_domain($domainid){
    $table = 'tbldomains';
    $fields = 'id';
    $where = array('registrar'=>'virtualname', 'id'=>$domainid);
    $result = select_query($table,$fields,$where);
    $check = mysql_num_rows($result);
    return $check;
}

//CHECH IF ADVANCED CONTACTS MANAGEMENT WAS ENABLED
function hook_virtualname_check_advance_contact(){
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

//CHECK DOMAIN TRANSFER ON RENEWAL AFTER SAVE
function hook_domain_save($vars){
    //CHECK ACTIONS
    if(isset($_POST['launch_transfer_on_renewal']))
        $launch_domain_transfer = $_POST['launch_transfer_on_renewal'];
    else
        $launch_domain_transfer = false;
    if(isset($_POST['status_transfer_on_renewal']))
        $check_status = $_POST['status_transfer_on_renewal'];
    else
        $check_status = false;
    //START ACTIONS
    if($launch_domain_transfer){
        $domainid = $vars['domainid'];
        $sql_dom = 'SELECT * FROM tbldomains where id = '.$domainid.' AND status in (\'active\',\'expired\') AND registrar != \'virtualname\'';
        $res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
        if(mysql_num_rows($res_dom) == 1){
            $params = mysql_fetch_array($res_dom);
            $params['domainid'] = $params['id'];
            $dom_tld = explode(".", $params['domain'], 2);
            $params['sld'] = $dom_tld[0];
            $params['tld'] = $dom_tld[1];
            $vars['params'] = $params;
            $vars['userid'] = $params['userid'];
            $adminID = $_SESSION['adminid'];
            require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
            //INIT MODULE
            global $vname_domains;
            virtualname_init();
            $transfer_domain = $vname_domains->get_transfer_on_renewal($vars['params']['domainid']);
            $response = $vname_domains->transfer_on_renewal($vars, $transfer_on_renewal, $adminID);
            if($response['abortWithError']){
                $url_renew = html_entity_decode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&conf=renew');
                header('Location:'.$url_renew);
                die();
            }
        }
    }
    if($check_status){
        $domainid = $vars['domainid'];
        $status_transfer_on_renewal = $_POST['status_transfer_on_renewal'];
        $type_transfer_on_renewal = $_POST['select_transfer_on_renewal'];
        $authcode_transfer_on_renewal = mysql_real_escape_string($_POST['authcode_transfer_on_renewal']);
        $mail_transfer_on_renewal = $_POST['mail_transfer_on_renewal'];
        $registrant_transfer_on_renewal = $_POST['registrant_transfer_on_renewal'];
        require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
        //INIT MODULE
        global $vname_domains;
        virtualname_init();
        $vname_domains->manage_transfer_on_renewal($domainid, $type_transfer_on_renewal, $authcode_transfer_on_renewal, $mail_transfer_on_renewal, $status_transfer_on_renewal, $registrant_transfer_on_renewal);
    }
}

function hook_transfer_on_renewal($vars){
    if($vars['params']['registrar'] != 'virtualname'){
        require_once(dirname(dirname(__FILE__)).'/../modules/registrars/virtualname/virtualname.php');
        global $vname_domains;
        $adminID = $_SESSION['adminid'];
        $domainid = $vars['params']['domainid'];
        $transfer_domain = $vname_domains->get_transfer_on_renewal($domainid);
        if($transfer_domain && $transfer_domain['status'] == 'active'){
            $response = $vname_domains->transfer_on_renewal($vars, $transfer_domain, $adminID);
            if($response['abortWithError'])
                $vname_domains->set_notes_transfer_on_renewal($domainid, '[Error]: '.$response['abortWithError']);
            elseif($response['abortWithSuccess'])
                $vname_domains->set_notes_transfer_on_renewal($domainid, '[OK]: Transferring');
            else
                $vname_domains->set_notes_transfer_on_renewal($domainid, '[Error]: Unknow response');
            return $response;
        }
    }
}

//SET DOMAIN CONTACTS ON TRANSFER ORDER
function hook_admin_set_transfer_order($vars){
    if(isset($_POST['domainfield']) && isset($vars['DomainIDs']) && isset($_SESSION['adminid'])){
        $domain_contacts = $_POST['domainfield'];
        $domains = $vars['DomainIDs'];
        foreach($domains as $key => $domain_id){
            if(count($domain_contacts[$key]) >= 4){
                $contacts = array_slice($domain_contacts[$key], count($domain_contacts[$key])-4);
                //REGISTRANT
                $sql  = 'insert into tbldomainsadditionalfields VALUES (\'\', \''.$domain_id.'\', \'regContact\', \''.$contacts[0].'\', \'\', \'\')';
                $resIn = mysql_query($sql);
                //ADMIN
                $sql  = 'insert into tbldomainsadditionalfields VALUES (\'\', \''.$domain_id.'\', \'adminContact\', \''.$contacts[1].'\', \'\', \'\')';
                $resIn = mysql_query($sql);
                //BILLING
                $sql  = 'insert into tbldomainsadditionalfields VALUES (\'\', \''.$domain_id.'\', \'billingContact\', \''.$contacts[2].'\', \'\', \'\')';
                $resIn = mysql_query($sql);
                //TECHNICAL
                $sql  = 'insert into tbldomainsadditionalfields VALUES (\'\', \''.$domain_id.'\', \'techContact\', \''.$contacts[3].'\', \'\', \'\')';
                $resIn = mysql_query($sql);
            }
        }
    }
}

function hook_disable_admin_buttons($vars){
    $disable_button = '
    <script type=\'text/javascript\'>
        $(".button.btn.btn-default[data-target=\'#modalRenew\']").click(function(){
            $(".button.btn.btn-default[data-target=\'#modalRenew\']").addClass("disabled").hide().delay(5000).queue(function(next){
                $(this).removeClass("disabled");
                $(this).show(0);
                next();
            });
        });
    </script>';
    return array('' => $disable_button);
}

?>