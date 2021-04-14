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
// * File description: Admin Custom Domain Contacts
// *************************************************************************

//SET WHMCS PAGE
define('ADMINAREA', true);
require_once(dirname(dirname(__FILE__)).'/init.php');

//FILE VALIDATION
if (!defined('WHMCS')) die('This file cannot be accessed directly');

//SET REQUIRE FILES
require_once(dirname(dirname(__FILE__)).'/modules/registrars/virtualname/virtualname.php');
//INIT MODULE
global $vname_admin, $vname_domains, $vname_contacts;
virtualname_init();

//SET ADMIN CONFIG PAGE
$whmcs_current = $vname_admin->get_whmcs_version();
$whmcs_version = explode('.',$whmcs_current);
//DEFINED ADMIN CLASS FOR WHMCS 5
$adminPage = new WHMCS\Admin('Edit Clients Domains');
$adminPage->title = $adminPage->lang('domains', 'modifycontact');
$adminPage->sidebar = 'clients';
$adminPage->icon = 'clientsprofile';
$adminPage->requiredFiles(array('clientfunctions', 'registrarfunctions'));

if(is_null($action) AND is_null($domainid)){
    $action = 'clientscontacts';
}

if($action == 'clientscontacts' || $action == 'clientscontactssave' || $action == 'clientscontactsdelete'){
    $adminPage->inClientsProfile = true;
    $adminPage->valUserID($userid);
    $contactPage = '';
    if ($action == 'clientscontactssave') {
        check_token('WHMCS.admin.default');
        checkPermission('Edit Clients Details');
        if ($contactid == 'addnew') {
            if ($password && $password != $adminPage->lang('fields', 'password')) {
                $array['password'] = generateClientPW($password);
            }
            //CHECK NEW TAX_ID
            if ($vname_admin->can_be_use_customer_tax_id())
                $contactid = addContact($userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails, $affiliateemails, $_POST['identificationnumber']);
            else
                $contactid = addContact($userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails, $affiliateemails);
            $checkContactCreated = $vname_contacts->get_tcpanel_contact($contactid, 2);
            if($contactid AND $checkContactCreated)
                $contactPage = '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionsuccess'].'</span></strong><br>'.$_LANG['adminContactLinked'].'</div>';
            else
                $contactPage = '<div class=\'errorbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionfailed'].'</span></strong><br>'.$_LANG['adminContactNoLinked'].'</div>';
            logActivity('Added Contact - User ID: ' . $userid . ' - Contact ID: ' . $contactid);
        }
        else {
            if(isset($linked))
                $save_linked = true;
            else
                $save_linked = false;

            if(isset($unlinked))
                $save_unlinked = true;
            else
                $save_unlinked = false;

            if($contactid){
                //NEW EDIT CONTACT VALUES
                $vars = array('firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname, 'email' => $email, 'address1' => $address1,
                    'city' => $city, 'state' => $state, 'postcode' => $postcode, 'country' => $country,
                    'phonenumber' => $phonenumber, 'contactid' => $contactid, 'userid' => $userid, 'identificationnumber' => $_POST['identificationnumber'],
                    'legal_form' => $_POST['legal_form'], 'linked' => $save_linked, 'unlinked' => $save_unlinked);

                $response = $vname_contacts->admin_contact_edit($vars);

                if($response['error']){
                    foreach($response['error'] as $error){
                        $contactPage  .= $error;
                    }
                }
                else{
                    logActivity('Contact Modified - User ID: ' . $userid . ' - Contact ID: ' . $contactid);
                    $table = 'tblcontacts';
                    $array = array('firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname, 'email' => $email, 'address1' => $address1, 'city' => $city, 'state' => $state, 'postcode' => $postcode, 'country' => $country, 'phonenumber' => $phonenumber);
                    //TAX_ID
                    if ($vname_admin->can_be_use_customer_tax_id())
                        $array['tax_id'] = $_POST['identificationnumber'];
                    $where = array('id' => $contactid);
                    $editResp = update_query($table, $array, $where);
                    if($editResp)
                        $contactPage = '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionsuccess'].'</span></strong><br>'.$_LANG['adminContactUpdated'].'</div>';
                    else
                        $contactPage = '<div class=\'errorbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionfailed'].'</span></strong><br>'.$_LANG['adminContactNotUpdated'].'</div>';
                }
            }
            else{
                //NEW CONTACT VALUES
                $vars = array('firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname, 'email' => $email, 'address1' => $address1,
                    'city' => $city, 'state' => $state, 'postcode' => $postcode, 'country' => $country,
                    'phonenumber' => $phonenumber, 'contactid' => '', 'userid' => $userid, 'identificationnumber' => $_POST['identificationnumber'],
                    'legal_form' => $_POST['legal_form'], 'linked' => $save_linked, 'unlinked' => $save_unlinked);
                $response = $vname_contacts->admin_contact_edit($vars);
                if($response['error']){
                    foreach($response['error'] as $error){
                        $contactPage  .= $error;
                    }
                }
                else{
                    logActivity('Client Modified - User ID: ' . $userid);
                    $oldcontactdata = get_query_vals('tblclients', '', array('id' => $_SESSION['uid']));
                    $table = 'tblclients';
                    $array = array('firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname, 'email' => $email, 'address1' => $address1, 'city' => $city, 'state' => $state, 'postcode' => $postcode, 'country' => $country, 'phonenumber' => $phonenumber);
                    //TAX_ID
                    if ($vname_admin->can_be_use_customer_tax_id())
                        $array['tax_id'] = $_POST['identificationnumber'];
                    $where = array('id' => $userid);
                    $editResp = update_query($table, $array, $where);
                    if($editResp)
                        $contactPage = '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionsuccess'].'</span></strong><br>'.$_LANG['adminClientUpdated'].'</div>';
                    else
                        $contactPage = '<div class=\'errorbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionfailed'].'</span></strong><br>'.$_LANG['adminClientNotUpdated'].'</div>';
                }

            }
        }
    }

    if ($action == 'clientscontactsdelete') {
        check_token('WHMCS.admin.default');
        $responseDelete = $vname_contacts->admin_delete_contact($contactid, $params);
        if($responseDelete){
            delete_query('tblcontacts', array('id' => $contactid, 'userid' => $userid));
            update_query('tblclients', array('billingcid' => ''), array('id' => $userid, 'billingcid' => $contactid));
            run_hook('ContactDelete', array('userid' => $userid, 'contactid' => $contactid));
            redir('action=clientscontacts&userid='.$userid);
            exit();
        }
        else
            $error = $_LANG['clientareanaverrordeletecontacts'];
    }

    if ($error) {
        infoBox($adminPage->lang('global', 'validationerror'), $error);
        $contactPage .= $infobox;
    }
    if(!$contactid)
        $clientSelect = 'selected';
    else
        $clientSelect = '';

    $defaultContact = $vname_contacts->get_tcpanel_contact($userid, 1);

    if($defaultContact != 0){
        $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:green;\'>✔</span>';
        $handle_show = '['.$defaultContact['id_contact_tcpanel'].']';
    }
    else{
        $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:red;\'>✘</span>';
        $handle_show = '';
    }

    $contactPage .= '<div class=\'tab-content client-tabs\'>';
    $contactPage .= '   <div class="tab-pane active" id="profileContent">';
    $contactPage .= '
        <div class="context-btn-container">
            <div class="text-left">
                <form action=\''.$_SERVER['PHP_SELF'].'\' method=\'get\'>
                    <input type=\'hidden\' name=\'action\' value=\'clientscontacts\'>
                    <input type=\'hidden\' name=\'userid\' value=\''.$userid.'\'>'.$adminPage->lang('clientsummary', 'contacts').': '.
                    '<select name=\'contactid\' onChange=\'submit();\' class=\'form-control select-inline\'>'.
                        '<option value=\'0\' '.$clientSelect.'>'.$ticker.$_LANG['adminDefaultContact'].' '.$handle_show.'</option>';
    $result = select_query('tblcontacts', '', array('userid' => $userid), 'firstname` ASC,`lastname', 'ASC');
    while ($data = mysql_fetch_array($result)) {
        $contactlistid = $data['id'];
        $defaultContact = $vname_contacts->get_tcpanel_contact($contactlistid, 2);
        if($defaultContact != 0){
            $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:green;\'>✔</span>';
            $handle_show = '['.$defaultContact['id_contact_tcpanel'].']';
        }
        else{
            $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:red;\'>✘</span>';
            $handle_show = '';
        }
        $contactlistfirstname = $data['firstname'];
        $contactlistlastname = $data['lastname'];
        $contactlistemail = $data['email'];
        $contactPage .= '<option value=\'' . $contactlistid . '\'';
        if ($contactlistid == $contactid) {
            $contactPage .= ' selected';
        }
        $contactPage .= '>'.$ticker.$contactlistfirstname.' '.$contactlistlastname.' - '.$contactlistemail.' '.$handle_show.'</option>';
    }

    $contactPage .= '<option value=\'addnew\'';
    if ($contactid == 'addnew') {
        $contactPage .= ' selected';
    }
    $contactPage .= '>'.$adminPage->lang('global', 'addnew').'</option>
                    </select>
                    <input type=\'submit\' value=\''.$adminPage->lang('global', 'go').'\'>
                </form>
            </div>
        </div>
    ';

    $contactPage .= $adminPage->deleteJSConfirm('deleteContact', 'clients', 'deletecontactconfirm', '?action=clientscontactsdelete&userid=' . $userid . '&contactid=');


    if ($contactid != 'addnew') {
        if($contactid){
            $result = select_query('tblcontacts', '', array('userid' => $userid, 'id' => $contactid));
            $data = mysql_fetch_array($result);
            $contactid = $data['id'];
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $companyname = $data['companyname'];
            $email = $data['email'];
            $address1 = $data['address1'];
            $address2 = $data['address2'];
            $city = $data['city'];
            $state = $data['state'];
            $postcode = $data['postcode'];
            $country = $data['country'];
            $phonenumber = $data['phonenumber'];
            $subaccount = $data['subaccount'];
            $password = $data['password'];
            $password = ($CONFIG['NOMD5'] ? decrypt($data['password']) : $adminPage->lang('fields', 'entertochange'));
        }
        else{
            $result = select_query('tblclients', '', array('id' => $userid));
            $data = mysql_fetch_array($result);
            $contactid = 0;
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $companyname = $data['companyname'];
            $email = $data['email'];
            $address1 = $data['address1'];
            $address2 = $data['address2'];
            $city = $data['city'];
            $state = $data['state'];
            $postcode = $data['postcode'];
            $country = $data['country'];
            $phonenumber = $data['phonenumber'];
        }
    }
    if (!is_array($permissions)) {
        $permissions = array();
    }
    if($contactid){
        //TAX_ID
        if($vname_admin->can_be_use_customer_tax_id())
            $icnumber = $data['tax_id'];
        else
            $icnumber = $vname_contacts->get_identification_number($contactid, 2);
        $legal_form = $vname_contacts->get_legal_form_contact($contactid, 2);
    }
    else{
        //TAX_ID
        if($vname_admin->can_be_use_customer_tax_id())
            $icnumber = $data['tax_id'];
        else
            $icnumber = $vname_contacts->get_identification_number($userid, 1);
        $legal_form = $vname_contacts->get_legal_form_contact($userid, 1);
    }

    if(!$contactid)
        $checkLinkedContact = $vname_contacts->get_tcpanel_contact($userid, 1);
    else
        $checkLinkedContact = $vname_contacts->get_tcpanel_contact($contactid, 2);

    if($checkLinkedContact){
        $contactPage .= '<div id="userdetails" style="font-size:18px;">#TCPanel Handle ['.$checkLinkedContact['id_contact_tcpanel'].']</div>';
    }

    $contactPage .= '
            <form method=\'post\' action=\''.$_SERVER['PHP_SELF'].'?userid='.$userid.'&contactid='.$contactid.'&action=clientscontactssave\'>
                <table class=\'form\' width=\'100%\' border=\'0\' cellspacing=\'2\' cellpadding=\'3\'>
                    <tr>
                        <td width=\'15%\' class=\'fieldlabel\'>'.$adminPage->lang('fields', 'firstname').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'30\' name=\'firstname\' tabindex=\'1\' value=\''.$firstname.'\'></td>
                        <td width=\'15%\' class=\'fieldlabel\'>'.$adminPage->lang('fields', 'address').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\'size=\'30\' name=\'address1\' tabindex=\'7\' value=\''.$address1.'\'></td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'lastname').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'30\' name=\'lastname\' tabindex=\'2\' value=\''.$lastname.'\'></td>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'city').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\'tabindex=\'9\' size=\'25\' name=\'city\' value=\''.$city.'\'></td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'companyname').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'30\' name=\'companyname\' tabindex=\'3\' value=\''.$companyname.'\'> <font color=#cccccc>'.'<small>('.$adminPage->lang('global', 'optional').
                        ')</small></font></td>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'state').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'25\' name=\'state\' tabindex=\'10\' value=\''.$state.'\'></font></td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'email').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'35\' name=\'email\'  tabindex=\'4\' value=\''.$email.'\'></td>
                        <td class=\'fieldlabel\'>'.$_LANG['clientIdentificationNumber'].'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'25\' name=\'identificationnumber\' id=\'identificationnumber\' tabindex=\'10\' value=\''.$icnumber.'\'></font></td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'postcode').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' tabindex=\'11\' size=\'14\' name=\'postcode\' value=\''.$postcode.'\'></td>
                        <td class=\'fieldlabel\'>'.$_LANG['legal_form_contact'].'</td>
                        <td class=\'fieldarea\'>';
                    $contactPage .= $vname_contacts->get_legal_forms($_LANG['legal_form'], $legal_form);
                    $contactPage .= '
                        </td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'country').'</td>
                        <td class=\'fieldarea\'>';
                    include '../includes/countries.php';
                    $contactPage .= getCountriesDropDown($country, '', '12');
                    $contactPage .= '
                        </td>
                    </tr>
                    <tr>
                        <td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'phonenumber').'</td>
                        <td class=\'fieldarea\'><input type=\'text\' class=\'form-control\' size=\'20\' name=\'phonenumber\' tabindex=\'13\' value=\''.$phonenumber.'\'></td>
                    </tr>
                </table>
                <p align=\'center\'>';
    if ($contactid != 'addnew' || !$contactid){
        $contactPage .= '<input type=\'submit\' value=\''.$adminPage->lang('global', 'savechanges').'\' class=\'btn btn-primary\' tabindex=\''.$taxindex++.'\' />';
        $contactPage .= '<input type=\'reset\' value=\''.$adminPage->lang('global', 'cancelchanges').'\' class=\'btn btn-default\' tabindex=\''.$taxindex++.'\' /><br />';
        //CHECK LINKED DOMAIN
        if(!$checkLinkedContact)
            $contactPage .= '<input type=\'submit\' id=\'linked\' name=\'linked\' value=\''.$_LANG['clientarealinkedcontact'].'\' class=\'btn btn-success\' tabindex=\''.$taxindex++.'\' /><br />';
        else
            $contactPage .= '<input type=\'submit\' id=\'unlinked\' name=\'unlinked\' value=\''.$_LANG['clientareaunlinkedcontact'].'\' class=\'btn btn-danger\' tabindex=\''.$taxindex++.'\' /><br />';
    }
    else {
        $contactPage .= '<input type=\'submit\' value=\''.$adminPage->lang('clients', 'addcontact').'\' class=\'btn btn-primary\' tabindex=\''.$taxindex++.'\' />';
        $contactPage .= '<input type=\'reset\' value=\''.$adminPage->lang('global', 'cancelchanges').'\' class=\'btn btn-default\' tabindex=\''.$taxindex++.'\' /><br />';
    }
    $contactPage .= '<input type=\'button\' value=\''.$adminPage->lang('global', 'goback');
    $contactPage .= '\' class=\'btn btn-default\' onClick=\'window.location="clientsdomains.php?userid='.$userid.'&domainid='.$domainid.'"\'>';

    if($contactid AND $contactid != 'addnew')
        $contactPage .= '<br /><a href=\'#\' onclick=\'deleteContact("'.$contactid.'");return false\' style=\'color:#cc0000\'><b>'.$adminPage->lang('global', 'delete').'</b></a>';

    $contactPage .=
                    '</p>
                   </form>
                </div>
            </div>
        </div>
    </div>';
}
elseif ($action == 'generateContact'){

    $userid = $whmcs->get_req_var('uid');
    //CONTACT ARRAY DATA
    $contactId = $whmcs->get_req_var('contactinfo');
    $contactResponse = $vname_contacts->get_tcpanel_contact_from_id($contactId);
    $contactInfo = $contactResponse['response'];

    $domainid    = $whmcs->get_req_var('domainid');
    $TCpanelid   = $contactInfo['id'];
    $identification_number = $contactInfo['ic'];

    //GET DOMAIN DATA
    $result = select_query('tbldomains', '', array('id' => $domainid));
    $data = mysql_fetch_array($result);
    $userid = $data['userid'];
    $domain = $data['domain'];

    $client = new WHMCS\Client(WHMCS\Session::get($userid));

    $domains  = new WHMCS\Domains();

    $domain_data = $domains->getDomainsDatabyID($domainid);

    $firstname   = $contactInfo['name'];
    $lastname    = $contactInfo['lastname'];
    $companyname = $contactInfo['company'];
    $email       = $contactInfo['email'];
    $address1    = $contactInfo['address'];
    $city        = $contactInfo['city'];
    $state       = $contactInfo['state'];
    $postcode    = $contactInfo['zipcode'];
    $country     = $contactInfo['country'];
    $phonenumber = $contactInfo['phonecc'].$contactInfo['phone'];
    $password    = '';
    $permissions = '';

    //$contactID = addContact($userid, $firstname, $lastname, $companyname, $email, $address1, '', $city, $state, $postcode, $country, $phonenumber, $password, $permissions,0, 0, 0, 0, 0);
    $table = 'tblcontacts';
    $array = array('userid'=>$userid,'firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname, 'email' => $email, 'address1' => $address1, 'city' => $city, 'state' => $state, 'postcode' => $postcode, 'country' => $country, 'phonenumber' => $phonenumber);
    //TAX_ID
    if($vname_admin->can_be_use_customer_tax_id())
        $array['tax_id'] = $identification_number;
    //TAX_ID
    $where = array('id' => $contactid);
    $contactID = insert_query($table, $array);

    if ($contactID) {
        $table = 'mod_contacts_virtualname_tcpanel';
        $update= array('identification_number'=>$identification_number,'id_contact_tcpanel'=>$TCpanelid,'id_contact_whmcs'=>$contactID,'contact_type'=>'2');
        insert_query($table,$update);
        $generate = 'generated';
    }
    else
        $generate = 'error';

    $urlRedir = 'domainid='.$domainid.'&action=generate&response='.$generate.'&contactID='.$contactID;

    redir($urlRedir, 'clientsdatadomaincontacts.php');
}
else{

    $contactPage = '';
    //IF THE DOMAIN NOT ASSIGNED LIKE VIRTUALNAME REGISTRAR REDIR TO DEFAULT WHMCS CLIENTAREA PAGE
    if(isset($domainid) AND $domainid != 0){
      $check = $vname_domains->check_domain($domainid);
      if($check == 0)
        redir('domainid='.$domainid, 'clientsdomaincontacts.php');
    }

    if($action == 'generate'){
        if($_GET['response'] != 'error')
            $contactPage .= '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionsuccess'].'</span></strong><br>'.$_LANG['adminContactCreated'].'</div>';
        else
            $contactPage .= '<div class=\'errorbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionfailed'].'</span></strong><br>'.$_LANG['adminContactNotCreated'].'</div>';
    }

    //GET DOMAIN DATA
    $result = select_query('tbldomains', '', array('id' => $domainid));
    $data = mysql_fetch_array($result);
    $userid = $data['userid'];
    $domain = $data['domain'];
    $registrar = $data['registrar'];
    $registrationperiod = $data['registrationperiod'];
    $contactsarray = array();
    $result = select_query('tblcontacts', 'id,firstname,lastname', array('userid' => $userid, 'address1' => array('sqltype' => 'NEQ', 'value' => '')), 'firstname` ASC,`lastname', 'ASC');
    while ($data = mysql_fetch_assoc($result)) {
        $contactsarray[] = array('id' => $data['id'], 'name' => $data['firstname'] . ' ' . $data['lastname']);
    }
    $domainparts = explode('.', $domain, 2);

    $params = $vname_admin->config();
    $params['domainid'] = $domainid;
    $params['sld'] = $domainparts[0];
    $params['tld'] = $domainparts[1];
    $params['regperiod'] = $registrationperiod;
    $params['registrar'] = $registrar;

    if ($action == 'save') {

        check_token('WHMCS.admin.default');

        $values = RegSaveContactDetails($params);

        $reg_id = $_POST['sel']['reg'];
        $adm_id = $_POST['sel']['admin'];
        $bill_id = $_POST['sel']['billing'];
        $tech_id = $_POST['sel']['tech'];

        if($values['error']){
            if($values['error'] == 'panelContactError')
                $panel_contact_error = $_LANG['adminUpdatedContactsError'];
            else
                $panel_contact_error = $values['error'];
        }
        else
            $contactPage .= '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG['moduleactionsuccess'].'</span></strong><br>'.$_LANG['adminUpdatedContacts'].'</div>';

        if($panel_contact_error){
            infoBox($adminPage->lang('domains', 'registrarerror'), $panel_contact_error);
            //UPDATE CONTACTS ONLY IF DOMAIN NOT EXIST
            $info = $vname_domains->view_domain_info($vname_admin->config());
            if($info['status']['code'] == '404'){
                if($reg_id)
                    $vname_contacts->update_tbldomainsadditionalfields($domainid, $reg_id, 'regContact');
                if($adm_id)
                    $vname_contacts->update_tbldomainsadditionalfields($domainid, $adm_id, 'adminContact');
                if($bill_id)
                    $vname_contacts->update_tbldomainsadditionalfields($domainid, $bill_id, 'techContact');
                if($tech_id)
                    $vname_contacts->update_tbldomainsadditionalfields($domainid, $tech_id, 'billingContact');
            }
        }
    }

    $allIDN = $vname_contacts->get_all_identification_number($userid);

    $contactPage .= '<script language=\'javascript\'>
        function usedefaultwhois(id) {
            jQuery(\'.\'+id.substr(0,id.length-1)+\'customwhois\').attr(\'disabled\', true);
            jQuery(\'.\'+id.substr(0,id.length-1)+\'defaultwhois\').attr(\'disabled\', false);
            jQuery(\'#\'+id.substr(0,id.length-1)+\'1\').attr(\'checked\', \'checked\');
        }
        function usecustomwhois(id) {
            jQuery(\'.\'+id.substr(0,id.length-1)+\'customwhois\').attr(\'disabled\', false);
            jQuery(\'.\'+id.substr(0,id.length-1)+\'defaultwhois\').attr(\'disabled\', true);
            jQuery(\'#\'+id.substr(0,id.length-1)+\'2\').attr(\'checked\', \'checked\');
        }
        function updateContactIDN(contactID, type){
            var idns = \'.json_encode($allIDN).\';
            document.getElementById(\'idN\'+type).value = idns[contactID];
        }
        </script>
        <form method=\'post\' action=\'
    ';

    $contactPage .= $PHP_SELF;
    $contactPage .= '?domainid='.$domainid;
    $contactPage .= '&action=save\'><table class=\'form\' width=\'100%\' border=\'0\' cellspacing=\'2\' cellpadding=\'3\'>';
    $contactPage .= '<tr><td width=\'20%\' class=\'fieldlabel\'>'.$adminPage->lang('fields', 'registrar').'</td><td class=\'fieldarea\'>'.ucfirst($registrar).'</td></tr>';
    $contactPage .= '<tr><td class=\'fieldlabel\'>'.$adminPage->lang('fields', 'domain').'</td><td class=\'fieldarea\'>'.$domain.'</td></tr>';
    $contactPage .= '<tr><td class=\'fieldlabel\'></td><td class=\'fieldarea\'><a href=\'./clientsdatadomaincontacts.php?action=clientscontacts&userid='.$userid.'&contactid=0\'>'.$adminPage->lang('contactpermissions', 'permcontacts').' '.ucfirst($registrar).'</a></td></tr>';
    $contactPage .= '</table>';

    $adminValues = array('reg'=>'Register Contact', 'admin'=>'Admin Contact', 'tech'=>'Technical Contact', 'billing'=>'Billing Contact');


    $contactdetails = $vname_contacts->get_contact_details($params);

    $contactPage .= $infobox;
    $i = 0;
    //CHECK CONTACT RULES
    $configarray = $vname_admin->config();
    $domain_rules = $configarray['domain_rules'];
    $contact_cp = array('reg'=>'registrant','admin'=>'administrative','billing'=>'billing','tech'=>'technical');
    $params_contact = $configarray;
    foreach ($contactdetails as $contactdetail => $values){
        if($domain_rules['.'.$params['tld']]){
            if($domain_rules['.'.$params['tld']]['contacts'][$contactdetail] != 1){
                if($domain_rules['.'.$params['tld']]['contacts'][$contactdetail] == 2){
                    $contactID = $values['contact']['contactID'];
                    $contactPage .= '<p><b>';
                    $contactPage .= $adminValues[$contactdetail];
                    $contactPage .= '</b>';
                    $currentContact = $contactdetails[$contactdetail]['contact'];
                    $params_contact['sld'] = $params['sld'];
                    $params_contact['tld'] = $params['tld'];
                    $responseContact = $vname_domains->view_domain_info($params_contact);
                    $infoContact = $responseContact['response'][0]['contacts'][$contact_cp[$contactdetail]];
                    if($infoContact['company'] != '')
                        $contactData = $infoContact['company'].' - ';
                    $contactData .= $infoContact['name'].' '.$infoContact['lastname'];
                    $contactPage .= '</p><label>'.$adminPage->lang('fields', 'contact').'</label></p>';
                    $contactPage .= '<table><tr><td width=\'150\' align=\'right\'>'.$contactData.'</td></tr></table>';
                }
                continue;
            }
        }
        //GET ID NUMBER
        $contactID = $values['contact']['contactID'];

        if($contactID == 0)
            $idNumber = $vname_contacts->get_identification_number($userid, 1);
        else
            $idNumber = $vname_contacts->get_identification_number($contactID, 2);

        $contactPage .= '<p><b>';
        $contactPage .= $adminValues[$contactdetail];
        $contactPage .= '</b>';

        $currentContact = $contactdetails[$contactdetail]['contact'];

        if($currentContact['message'] == 'edit')
            $message = $adminPage->lang('global', $currentContact['message']);
        elseif($currentContact['message'] == 'generate')
            $message = $_LANG['admingenerate'];
        else
            $message = $currentContact['message'];

        $defaultContact = $vname_contacts->get_tcpanel_contact($userid, 1);
        if($defaultContact != 0)
            $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:green;\'>✔</span>';
        else
            $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:red;\'>✘</span>';

        $contactPage .= '</p>'.'<label for=\''.$contactdetail.'1\'>'.$adminPage->lang('domains', 'domaincontactusexisting').'</label></p><table id=\'';
        $contactPage .= $contactdetail.'defaultwhois\'><tr><td width=\'150\' align=\'right\'>'.$adminPage->lang('domains', 'domaincontactchoose').'</td>';
        $contactPage .= '<td style=\'padding-left: 10px;\'>'.'<select name=\'sel['.$contactdetail.']\' id=\''.$contactdetail.'3\' class=\''.$contactdetail.'defaultwhois form-control select-inline input-500\' onclick=\'usedefaultwhois(id)\' onchange=\'updateContactIDN(this.value, "'.$contactdetail.'");\'>';
        $contactPage .= '<option value=\'0\'>'.$ticker.$_LANG['adminDefaultContact'].'</option>'.$currentContact['options'].'</select></td></tr>';
        $contactPage .= '</table>';
        //CHECK IF THIS CONTACT CAN BE LINKED
        if($currentContact['hrefAdmin'] != 'UNAVAILABLE'){
            $contactPage .= '<p><label>'.$adminPage->lang('domains', 'modifycontact').'</label>'.'<table id=\'';
            $contactPage .= $contactdetail.'defaultwhois\'><tr><td width=\'150\' align=\'right\'>'.'<a href=\'.'.$currentContact['hrefAdmin'].'\' name=\'vn_url[';
            $contactPage .= $contactdetail.']\' id=\''.$contactdetail.'2\' value=\'custom\' class=\'btn btn-primary\'>'.$message.'</td></tr></table><p>';
        }
    }
    $contactPage .= '<input type=\'hidden\' value=\''.$userid.'\' id=\'customerid\' name=\'customerid\'></br>';
    $contactPage .= '<p align=center><input type=\'submit\' value=\''.$adminPage->lang('global', 'savechanges').'\' class=\'btn btn-primary\'> <input type=\'button\' value=\''.$adminPage->lang('global', 'goback');
    $contactPage .= '\' class=\'btn btn-default\' onClick=\'window.location="clientsdomains.php?userid='.$userid.'&domainid='.$domainid.'"\'></p></form>';
}

if($adminPage->inClientsProfile){
    $admin_folder = $whmcs->get_admin_folder_name();
    $header = tab_header($adminPage, $admin_folder);
    $contactPage = $header.$contactPage;
}
$adminPage->content = $contactPage;
$adminPage->display();

function tab_header($adminPage, $admin_folder) {
    global $CONFIG;
    $user_id = $_GET['userid'];
    $urls_array= array(
        'clientssummary' => $adminPage->lang('clientsummary', 'summary'),
        'clientsprofile' => $adminPage->lang('clientsummary', 'profile'),
        'clientsdatadomaincontacts' => 'TCPanel',
        'clientsusers' => $adminPage->lang('user', 'userTab'),
        'clientscontacts' => $adminPage->lang('clientsummary', 'contacts'),
        'clientsservices' => $adminPage->lang('clientsummary', 'products'),
        'clientsdomains' => $adminPage->lang('clientsummary', 'domains'),
        'clientsbillableitems' => $adminPage->lang('clientsummary', 'billableitems'),
        'clientsinvoices' => $adminPage->lang('clientsummary', 'invoices'),
        'clientsquotes' => $adminPage->lang('clientsummary', 'quotes'),
        'clientstransactions' => $adminPage->lang('clientsummary', 'transactions'),
        'clientsemails' => $adminPage->lang('clientsummary', 'emails'),
        'clientsnotes' => $adminPage->lang('clientsummary', 'notes').' ('.get_query_val('tblnotes', 'COUNT(id)', array('userid' => $user_id)).')',
        'clientslog' => $adminPage->lang('clientsummary', 'log')
    );
    $result = select_query('tblclients', '', array('id' => $user_id));
    $data = mysql_fetch_array($result);
    $selectfirstname = $data['firstname'];
    $selectlastname = $data['lastname'];
    $selectcompanyname = $data['companyname'];
    $header .=  '<p>'.$selectfirstname.' '.$selectlastname;
    if ($selectcompanyname)
        $header .= ' ('.$selectcompanyname.')';
    $header .= '</p><ul class=\'nav nav-tabs client-tabs\' role=\'tablist\'>';
    foreach ($urls_array as $key => $value) {
        if ($key == 'clientsdatadomaincontacts')
            $header .= '<li class=\'active\'><a href=\''.$key.'.php?userid='.$user_id.'\'>'.$value.'</a></li>';
        elseif ($key == 'clientsusers')
            $header .= '<li class=\'tab\'><a href=\'index.php?rp=/'.$admin_folder.'/client/'.$user_id.'/users\'>'.$value.'</a></li>';
        else
            $header .= '<li class=\'tab\'><a href=\''.$key.'.php?userid='.$user_id.'\'>'.$value.'</a></li>';
    }
    $header .= '</ul>';
    return $header;
}
?>