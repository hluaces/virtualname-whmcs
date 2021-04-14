<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 8.1.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.7
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: WHMCS - TCPANEL Contacts
// *************************************************************************

//DEFAULT CUSTOM CONTACTS METHOD
function setExtraAdditional($userID){
    global $vname_admin, $vname_contacts, $vname_prices, $smarty;
    //EXCLUDE clientsdomains admin page to set domain contacts
    if(basename($_SERVER['SCRIPT_NAME'],'.php') == 'clientsdomains' || !domain_contacts_check_advance_contact() || is_null($smarty))
        return 0;
    require_once(dirname(dirname(__FILE__)).'/virtualname.php');
    //INIT MODULE
    global $vname_admin;
    virtualname_init();
    $_LANG = $smarty->get_template_vars('LANG');
    if(isset($_SESSION['adminid']) AND $_SERVER['REQUEST_URI'] != '/cart.php?a=confdomains'){
        if($userID == '')
            $userID = $_GET['userid'];
        $contacts = $vname_contacts->get_client_contacts($userID);
    }
    else{
        if(!$userID || empty($userID)){
            if($clientDetails && $clientDetails['userid'])
                $userID = $clientDetails['userid'];
            else{
                $clientDetails = $smarty->get_template_vars('clientsdetails');
                $userID = $clientDetails['userid'];
            }
        }
        $contacts = $vname_contacts->get_client_contacts($userID);
    }

    $newContact     = $_LANG['clientareanavaddcontact'];
    $billingContact = $_LANG['domainbillingcontact'];
    $techContact    = $_LANG['domaintechcontact'];
    $adminContact   = $_LANG['domainadmincontact'];
    $regContact     = $_LANG['domainregcontact'];
    $defaultContact = $_LANG['domaindefaultcontact'];
    $addNewContact  = $_LANG['domainaddcontact'];
    $refreshContacts= $_LANG['domaincontactrefresh'];

    $extensions = $vname_prices->get_extension();

    $ticker = $vname_contacts->get_validate_contact($userID,'1');

    $clientContacts = array('0|'.$ticker.$defaultContact);
    foreach($contacts as $contact){
        $ticker = $vname_contacts->get_validate_contact($contact['id'],2);
        $contactName= $ticker.$contact['firstname'].' '.$contact['lastname'].' '.$contact['email'];
        $clientContacts[] =  $contact['id'].'|'.$contactName;
    }

    $vn_config = $vname_admin->config();
    $domain_rules = $vn_config['domain_rules'];

    foreach($extensions as $vn_TLD){
        $selection .= '';
        //CHECK ENABLE CONTACTS FROM SPECIFIC RULES FROM THIS PAGES:
        //clientsdomains, clientsdomaincontacts, clientareadata
        $currentPage       = basename($_SERVER['PHP_SELF'],'.php');
        $edition_pages     = array('clientsdomains', 'clientsdomaincontacts', 'clientareadata', '/clientsdomains', '/clientsdomaincontacts', '/clientareadata');
        $new_domain_pages  = array('cart', '/cart'); //'ordersadd', '/ordersadd',
        $availableContacts = array('regContact'=>'domainregcontact', 'adminContact'=>'domainadmincontact','techContact'=>'domaintechcontact', 'billingContact'=>'domainbillingcontact');
        $new_contact       = false;
        //CHECK IF HAVE RULES WITH THIS TLD
        if(isset($domain_rules[$vn_TLD])){
            if(in_array($currentPage, $edition_pages)){
                //EDIT PAGES CHECK
                unset($availableContacts);
                $rules_contact = $domain_rules[$vn_TLD]['contacts'];
                foreach($rules_contact as $key => $value){
                    $fullKey = 'domain'.$key.'contact';
                    if($value == 1){
                        $availableContacts[$key.'Contact'] = array('name' => $fullKey,'protected' => false);
                    }
                    elseif($value == 2){
                        $availableContacts[$key.'Contact'] = array('name' => $fullKey, 'protected' => true);
                    }
                }
                break;
            }
            //NEW PAGES CHECK
            if(in_array($currentPage, $new_domain_pages)){
                unset($availableContacts);
                $rules_contact = $domain_rules[$vn_TLD]['contacts'];
                foreach($rules_contact as $key => $value){
                    $fullKey = 'domain'.$key.'contact';
                    if($value == 1 || $value == 2)
                        $availableContacts[$key.'Contact'] = array('name' => $fullKey,'protected' => false);
                }
            }
        }
        else{
            foreach($availableContacts as $key => $value){
                $availableContacts[$key] = array('name' => $value,'protected' => false);
            }
        }

        foreach($availableContacts as $key => $value_){
            $protected = $value_['protected'];
            $value     = $value_['name'];
            if($protected == true){
                $type    = 'display';
                $default = ' N/A ';
                $required = false;
            }
            elseif($protected == false){
                $type    = 'dropdown';
                $default = $userID.'|'.$defaultContact;
                $required = true;
            }

            $additionaldomainfields[$vn_TLD][] = array(
                'Name' => $key,
                'LangVar' => $value,
                'Type' => $type,
                'Options' => implode(
                    ',',
                    $clientContacts
                ),
                'Default' => $default,
                'Require' => $required,
            );
        }
        if(in_array($currentPage, $new_domain_pages)){
            $additionaldomainfields[$vn_TLD][] = array(
                'Name' => 'addNew',
                'LangVar' => 'domainaddcontact',
                'Type' => 'display',
                'Default' => '<a href=\'./clientareadata.php?action=addcontact\' target=\'_blank\' class=\'btn btn-primary\'>'.$addNewContact.'</a> <a href=\'\' class=\'btn btn-default\'>'.$refreshContacts.'</a>',
                'Require' => 'false',
            );
        }
    }
    return $additionaldomainfields;
}

//CHECH IF ADVANCED CONTACTS MANAGEMENT WAS ENABLED
function domain_contacts_check_advance_contact(){
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