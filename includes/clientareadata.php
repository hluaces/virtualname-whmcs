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
// * @common true
// * File description: Clients Custom Domain Contacts
// *************************************************************************

//if (!defined('WHMCS'))
//    die('This file cannot be accessed directly');

//SET WHMCS PAGE
define('CLIENTAREA', true);

//SET REQUIRE FILES
require 'init.php';
require 'includes/gatewayfunctions.php';
require 'includes/ccfunctions.php';
require 'includes/customfieldfunctions.php';
require 'includes/invoicefunctions.php';
require 'includes/configoptionsfunctions.php';

use WHMCS\View\Menu\Item as MenuItem;

//SET ADMIN CONFIG PAGE
$whmcs_current = virtualname_get_current_whmcs_version();
$whmcs_version = explode('.',$whmcs_current);

//SET INIT PAGE
$currentAction = $whmcs->get_req_var('action');
$subAction   = $whmcs->get_req_var('sub');
$whmcsContact     = (int)$whmcs->get_req_var('id');
$paymentmethod = WHMCS\Gateways::makesafename($whmcs->get_req_var('paymentmethod'));

//PRIMARY SIDEBARS
$whmcs_version = virtualname_get_whmcs_version();
if(in_array($whmcs_version, array('7.0', '7.1', '7.2', '7.3', '7.4')))
  Menu::addContext();
else
  Menu::addContext('clientView', null);

//SET ACTIONS
if ($currentAction == 'changesq')
  redir('action=changesq', 'clientarea.php');

if ($currentAction == 'changepw')
  redir('action=changepw', 'clientarea.php');

if ($currentAction == 'changesq' || $whmcs->get_req_var('2fasetup')) {
  $currentAction = 'security';
}

//SET CLIENT AREA CLASS
$clientArea = new WHMCS\ClientArea();

$clientArea->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
$clientArea->addToBreadCrumb('clientarea.php', $whmcs->get_lang('clientareatitle'));
$clientArea->initPage();
$clientArea->requireLogin();

if ($currentAction == 'details') {
  $clientArea->setPageTitle($whmcs->get_lang('clientareanavdetails'));
  Menu::primarySidebar('clientView');
  $clientArea->addToBreadCrumb('clientarea.php?action=details', $whmcs->get_lang('clientareanavdetails'));
}
elseif ($currentAction == 'contacts') {
  Menu::primarySidebar('clientView');
  $clientArea->setPageTitle($whmcs->get_lang('clientareanavcontacts'));
}
elseif ($currentAction == 'domaincontacts') {
  $domainId = addslashes($_GET['id']);
  $domainId = ($domainId == '' ) ? addslashes($_GET['domainid']) : $domainId;
  $clientArea->assign('domainId' , $domainId);
  Menu::primarySidebar('domainView');
  Menu::secondarySidebar('domainView');
  $clientArea->setPageTitle($whmcs->get_lang('managedomain'));
}
else{
  Menu::primarySidebar('clientView');
  $clientArea->setPageTitle($whmcs->get_lang('clientareatitle'));
}
$client = new WHMCS\Client(WHMCS\Session::get('uid'));

$currency = $client->getCurrency();
$clientArea->assign('action', $currentAction);
$clientArea->assign('clientareaaction', $currentAction);
$clientArea->assign('currentVersion', $whmcs_version[0]);

//IF THE DOMAIN NOT ASSIGNED LIKE VIRTUALNAME REGISTRAR REDIR TO DEFAULT WHMCS CLIENTAREA PAGE
if(isset($domainid) AND $domainid != 0){
  $check = virtualname_check_Domain($domainid);
  if($check == 0)
    redir('action=domaincontacts&domainid='.$domainid, 'clientarea.php');
}

//CHECK VALIDATE CUSTOM ACTIONS
$virtualname_posible_actions = array('details', 'contacts', 'addcontact', 'domaincontacts', 'generateContact');
if ($currentAction == '' || !in_array($currentAction, $virtualname_posible_actions)) {
  header('Location: ./clientarea.php');
}
else {

  if($currentAction == 'details'){

    checkContactPermission('profile');
    $config_template = virtualname_get_template_config();
    $clientArea->setTemplate('../../modules/registrars/virtualname/includes/templates/'.$config_template.'/clientareadetailsdata');
    $uneditablefields = explode(',', $CONFIG['ClientsProfileUneditableFields']);
    $smartyvalues['uneditablefields'] = $uneditablefields;
    $e = '';

    //INSERT OR UPDATE CONTACT
    $linked = $_POST['linked'];
    $unlinked = $_POST['unlinked'];

    if($save || $linked){
      if(!isset($_POST['state']))
        $_POST['state'] = $_POST['stateHidden'];
      check_token();
      $e = checkDetailsareValid($client->getID(), false);
      if($e){
        $clientArea->assign('errormessage', $e);
      }
      else{
        $client->updateClient();
        $clientArea->assign('successful', true);
        $client = new WHMCS\Client(WHMCS\Session::get('uid'));
      }
    }
    elseif($unlinked){
      virtualname_unlinked_client($client->getID());
    }

    if(!$e)
      $exdetails = $client->getDetails();

    //SET CURRENT CLIENT-CONTACT DATA
    include 'includes/countries.php';
    $clientArea->assign('clientid', $client->getID());
    $clientArea->assign('clientfirstname', $whmcs->get_req_var_if($e, 'firstname', $exdetails));
    $clientArea->assign('clientlastname', $whmcs->get_req_var_if($e, 'lastname', $exdetails));
    $clientArea->assign('clientcompanyname', $whmcs->get_req_var_if($e, 'companyname', $exdetails));
    $clientArea->assign('clientemail', $whmcs->get_req_var_if($e, 'email', $exdetails));
    $clientArea->assign('clientaddress1', $whmcs->get_req_var_if($e, 'address1', $exdetails));
    $clientArea->assign('clientaddress2', $whmcs->get_req_var_if($e, 'address2', $exdetails));
    $clientArea->assign('clientcity', $whmcs->get_req_var_if($e, 'city', $exdetails));
    $clientArea->assign('clientstate', $whmcs->get_req_var_if($e, 'state', $exdetails));
    $clientArea->assign('clientpostcode', $whmcs->get_req_var_if($e, 'postcode', $exdetails));
    $clientArea->assign('clientcountry', $countries[$whmcs->get_req_var_if($e, 'country', $exdetails)]);
    $clientArea->assign('clientcountriesdropdown', getCountriesDropDown($whmcs->get_req_var_if($e, 'country', $exdetails)));
    $clientArea->assign('clientphonenumber', $whmcs->get_req_var_if($e, 'phonenumber', $exdetails));
    $clientArea->assign('customfields', getCustomFields('client', '', $client->getID(), '', '', $_POST['customfield']));
    $clientArea->assign('contacts', $client->getContacts());
    $clientArea->assign('billingcid', $whmcs->get_req_var_if($e, 'billingcid', $exdetails));
    $clientArea->assign('paymentmethods', showPaymentGatewaysList());
    $clientArea->assign('emailoptout', $whmcs->get_req_var_if($e, 'emailoptout', $exdetails));
    $clientArea->assign('emailoptoutenabled', $whmcs->get_config('AllowClientsEmailOptOut'));
    $clientArea->assign('defaultpaymentmethod', $whmcs->get_req_var_if($e, 'defaultgateway', $exdetails));
    $userid       = $client->getID();
    $idNumber     = virtualname_get_identification_number($userid, 1);
    $legalContact = virtualname_get_legal_form_contact($userid, 1);
    $hideicnumber = virtualname_get_hide_config();
    $legal_forms  = virtualname_get_legal_forms($whmcs->get_lang('legal_form'), $legalContact);

    //IF 0 the user not was added in the system
    if($idNumber == 'EMPTY'){
      $idNumber = 'N/A';
      $validateTcpanel = '0';
    }
    else
      $validateTcpanel = '1';

    $clientArea->assign('clientidentificationnumber', $idNumber);
    $clientArea->assign('clientvirtualnamevalidation', $validateTcpanel);
    $clientArea->assign('clientlegalforms', $legal_forms);
    $clientArea->assign('hideicnumber', $hideicnumber);
    $clientArea->assign('currentAction', 'details');
  }
  elseif ($currentAction == 'generateContact'){

    checkContactPermission('managedomains');
    //CONTACT ARRAY DATA
    $contactId = $whmcs->get_req_var('contactinfo');
    $contactInfo = virtualname_get_contact($contactId, $params);
    $domainid    = $whmcs->get_req_var('domainid');
    $TCpanelid = $contactInfo['id'];
    $identification_number = $contactInfo['ic'];

    $contactsarray = $client->getContactsWithAddresses();
    $smartyvalues['contacts'] = $contactsarray;

    $domains  = new WHMCS\Domains();

    $domain_data = $domains->getDomainsDatabyID($domainid);
    if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction('GetContactDetails')) {
      redir('action=domains', 'clientarea.php');
    }

    //$clientArea->addToBreadCrumb('clientarea.php?action=domaindetails&id='.$domain_data['id'], $domain_data['domain']);
    $smartyvalues['domainid'] = $domains->getData('id');
    $smartyvalues['domain'] = $domains->getData('domain');

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
    $ic          = $contactInfo['ic'];
    $TCpanelContact = $contactInfo['id'];
    $password    = '';
    $permissions = '';

    $contactID = addContact($client->getID(), $firstname, $lastname, $companyname, $email, $address1, '', $city, $state, $postcode, $country, $phonenumber, $password, $permissions,0, 0, 0, 0, 0);
    if ($contactID) {
      $table = 'mod_contacts_virtualname_tcpanel';
      //CHECK IF CONTACT EXIST
      $where  = array('id_contact_whmcs'=>$contactID,'contact_type'=>'2');
      $select = virtualname_get_identification_number($contactID, 2);
      $legalContact = virtualname_get_legal_form_contact($contactID, 2);
      if($select == 'EMPTY'){
        $values = array('id_contact_whmcs'=>$contactID,'id_contact_tcpanel'=>$TCpanelContact, 'contact_type'=>2, 'identification_number' => $ic);
        insert_query($table,$values);
      }
      else{
        $update = array('identification_number'=>$identification_number,'id_contact_tcpanel'=>$TCpanelid);
        update_query($table,$update,$where);
      }
      $smartyvalues['successful'] = true;
      $generate = 1;
    }
    else {
      $smartyvalues['error'] = 'Can\'t create CONTACT';
      $generate = 0;
    }
    redir('action=domaincontacts&domainid='.$domainid.'&generate='.$generate, 'clientareadata.php');
  }
  elseif ($currentAction == 'domaincontacts'){
    checkContactPermission('managedomains');
    $config_template = virtualname_get_template_config();
    $clientArea->setTemplate('../../modules/registrars/virtualname/includes/templates/'.$config_template.'/clientareadetailsdata');
    $clientArea->assign('currentAction', 'domaincontacts');
    $contactsarray = $client->getContactsWithAddresses();
    $smartyvalues['contacts'] = $contactsarray;
    $domains  = new WHMCS\Domains();
    $domain_data = $domains->getDomainsDatabyID($domainid);
    if ((!$domain_data || !$domains->isActive()) || !$domains->hasFunction('GetContactDetails')) {
      redir('action=domains', 'clientarea.php');
    }
    $clientArea->addToBreadCrumb('clientarea.php?action=domaindetails&id='.$domain_data['id'], $domain_data['domain']);
    $clientArea->addToBreadCrumb('#', $whmcs->get_lang('domaincontactinfo'));

    if ($subAction== 'save') {
      check_token();
      $vn_contact = $whmcs->get_req_var('wc');
      $contactdetails = $whmcs->get_req_var('contactdetails');
      foreach($vn_contact as $vn_contact_key => $vn_contact_val) {
        if ($vn_contact_val == 'contact') {
          $selctype = $sel[$vn_contact_key][0];
          $selcid = substr($sel[$vn_contact_key], 1);
          $tmpcontactdetails = array();
          if ($selctype == 'c') {
            $tmpcontactdetails = get_query_vals('tblcontacts', '', array('userid' => $client->getID(), 'id' => $selcid));
          } else {
            if ($selctype == 'u') {
              $tmpcontactdetails = get_query_vals('tblclients', '', array('id' => $client->getID()));
            }
          }
          $contactdetails[$vn_contact_key] = $domains->buildWHOISSaveArray($tmpcontactdetails);
          continue;
        }
      }
      $success = $domains->moduleCall('SaveContactDetails', array('contactdetails' => $contactdetails));
      if ($success) {
        $smartyvalues['successmessage'] = 'linked';
      }
      else {
        $saveError = $domains->getLastError();
        if($saveError == 'panelContactError'){
          $smartyvalues['error'] = $whmcs->get_lang('clientareanaverrorsavecontacts');
        }
        else
          $smartyvalues['error'] = $domains->getLastError();
      }
    }
    $success = $domains->moduleCall('GetContactDetails');

    if ($success) {
      $smartyvalues['contactdetails'] = $domains->getModuleReturn();
      $smartyvalues['successful'] = true;
    }
    else {
      $smartyvalues['error'] = $domains->getLastError();
    }

    $generate = $whmcs->get_req_var('generate');
    if($generate)
      $smartyvalues['successmessage'] = 'linked';

    $tld = $domains->getTLD();
    $tlddata = get_query_vals("tbldomainpricing", "", array("extension" => ".".$tld));
    $smartyvalues['getepp'] = ($tlddata['eppcode'] ? true: false);
    $smartyvalues['domainid'] = $domains->getData('id');
    $smartyvalues['domain'] = $domains->getData('domain');
    $smartyvalues['contacts'] = $client->getContactsWithAddresses();
    add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar){
      global $tlddata, $whmcs;
      if (!is_null($primarySidebar->getChild('Domain Details Management'))) {
        $child = $primarySidebar->getChild('Domain Details Management')->getChild('Overview');
        if (is_null($child)) {
          $domainId = addslashes($_GET['id']);
          $domainId = ($domainId == '' ) ? addslashes($_GET['domainid']) : $domainId;
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Overview')
            ->setUri('clientarea.php?action=domaindetails&id='.$domainId.'#tabOverview')
            ->setLabel($whmcs->get_lang('overview'))
            ->setOrder(100);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Auto Renew Settings')
            ->setUri('clientarea.php?action=domaindetails&id='.$domainId.'#tabAutorenew')
            ->setLabel($whmcs->get_lang('domainsautorenew'))
            ->setOrder(110);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Modify Nameservers')
            ->setUri('clientarea.php?action=domaindetails&id='.$domainId.'#tabNameservers')
            ->setLabel($whmcs->get_lang('domainnameservers'))
            ->setOrder(120);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Registrar Lock Status')
            ->setUri('clientarea.php?action=domaindetails&id='.$domainId.'#tabReglock')
            ->setLabel($whmcs->get_lang('domainregistrarlock'))
            ->setOrder(130);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Addons')
            ->setUri('clientarea.php?action=domaindetails&id='.$domainId.'#tabAddons')
            ->setLabel($whmcs->get_lang('domainaddons'))
            ->setOrder(140);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Domain Contacts')
            ->setUri('clientarea.php?action=domaincontacts&domainid=' . $domainId)
            ->setLabel($whmcs->get_lang('domaincontactinfo'))
            ->setOrder(150);
          $primarySidebar->getChild('Domain Details Management')
            ->addChild('Manage Private Nameservers')
            ->setUri('clientarea.php?action=domainregisterns&domainid=' . $domainId)
            ->setLabel($whmcs->get_lang('domainprivatenameservers'))
            ->setOrder(160);
          if($tlddata['eppcode'] == 'on'){
            $primarySidebar->getChild('Domain Details Management')
              ->addChild('Get EPP Code')
              ->setUri('clientarea.php?action=domaingetepp&domainid=' . $domainId)
              ->setLabel($whmcs->get_lang('domaingeteppcode'))
              ->setOrder(170);
          }
        }
      }
    });
  }
  elseif ($currentAction == 'addcontact') {
    checkContactPermission('contacts');
    $config_template = virtualname_get_template_config();
    $clientArea->setTemplate('../../modules/registrars/virtualname/includes/templates/'.$config_template.'/clientareadetailsdata');
    $clientArea->addToBreadCrumb('clientarea.php?action=details', $whmcs->get_lang('clientareanavdetails'));
    $clientArea->addToBreadCrumb('clientarea.php?action=addcontact', $whmcs->get_lang('clientareanavaddcontact'));
    $clientArea->assign('currentAction', 'addcontact');
    $hideicnumber = virtualname_get_hide_config();
    $legal_forms  = virtualname_get_legal_forms($whmcs->get_lang('legal_form'), 'natural_person_or_individual');

    include 'includes/countries.php';

    if ($submit) {
      check_token();
      $errormessage = checkContactDetails('', true);

      if (!$subaccount) {
        $password = $permissions = '';
      }

      $smartyvalues['errormessage'] = $errormessage;

      if (!$errormessage) {
        $contactid = addContact($client->getID(), $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails);
        redir('action=contacts&id='.$contactid.'&success=1');
        exit();
      }
    }

    $contactsarray = $client->getContacts();
    $smartyvalues['contacts'] = $contactsarray;

    if (!$permissions) {
      $permissions = array();
    }

    $smartyvalues['contactfirstname'] = $firstname;
    $smartyvalues['contactlastname'] = $lastname;
    $smartyvalues['contactcompanyname'] = $companyname;
    $smartyvalues['contactemail'] = $email;
    $smartyvalues['contactaddress1'] = $address1;
    $smartyvalues['contactaddress2'] = $address2;
    $smartyvalues['contactcity'] = $city;
    $smartyvalues['contactstate'] = $state;
    $smartyvalues['contactpostcode'] = $postcode;
    $smartyvalues['contactphonenumber'] = $phonenumber;
    $smartyvalues['contactidentificationnumber'] = 'N/A';
    $smartyvalues['countriesdropdown'] = getCountriesDropDown($country);
    $smartyvalues['subaccount'] = $subaccount;
    $smartyvalues['permissions'] = $permissions;
    $smartyvalues['generalemails'] = $generalemails;
    $smartyvalues['productemails'] = $productemails;
    $smartyvalues['domainemails'] = $domainemails;
    $smartyvalues['invoiceemails'] = $invoiceemails;
    $smartyvalues['supportemails'] = $supportemails;
    $smartyvalues['hideicnumber'] = $hideicnumber;
    $smartyvalues['legalforms'] = $legal_forms;
  }
  elseif ($currentAction == 'contacts') {

    checkContactPermission('contacts');
    $config_template = virtualname_get_template_config();
    $clientArea->setTemplate('../../modules/registrars/virtualname/includes/templates/'.$config_template.'/clientareadetailsdata');
    $clientArea->addToBreadCrumb('clientarea.php?action=details', $whmcs->get_lang('clientareanavdetails'));
    $clientArea->addToBreadCrumb('clientarea.php?action=contacts', $whmcs->get_lang('clientareanavcontacts'));
    $clientArea->assign('currentAction', 'contacts');

    $contact_data = array();

    if($contactid){
      if($contactid == 'new'){
        redir('action=addcontact');
      }
      $whmcsContact = (int) $contactid;
    }

    if($whmcsContact) {
      $contact_data = $client->getContact($whmcsContact);
      if (!$contact_data) {
        redir('action=contacts', 'clientarea.php');
      }
      $whmcsContact = $contact_data['id'];
    }

    if($delete){
      $responseDelete = virtualname_delete_contact($whmcsContact, $params);
      if($responseDelete){
        $client->deleteContact($whmcsContact);
        redir('action=contacts&deleted=true');
      }
      else
        redir('action=contacts&deleted=false');
    }

    //CHECK DELETE
    if(isset($_GET['deleted'])){
      if($_GET['deleted'] == 'false'){
        $errormessage = $whmcs->get_lang('clientareanaverrordeletecontacts');
        $smartyvalues['errormessage'] = $errormessage;
      }
    }

    $linked = $_POST['linked'];
    $e = '';
    if($submit || $linked){
      check_token();
      $errormessage = $e = checkContactDetails($whmcsContact);

      if (!$subaccount) {
        $password = $permissions = '';
      }

      $smartyvalues['errormessage'] = $errormessage;

      if (!$errormessage) {
        $oldcontactdata = get_query_vals('tblcontacts', '', array('userid' => $client->getID(), 'id' => $whmcsContact));
        $array = db_build_update_array(array('firstname', 'lastname', 'companyname', 'email', 'address1', 'address2', 'city', 'state', 'postcode', 'country', 'phonenumber', 'subaccount', 'permissions', 'generalemails', 'productemails', 'domainemails', 'invoiceemails', 'supportemails'), 'implode');
        $array['subaccount'] = ($subaccount ? '1': '0');

        if ($password) {
          $array['password'] = generateClientPW($password);
        }

        update_query('tblcontacts', $array, array('userid' => $client->getID(), 'id' => $whmcsContact));
        run_hook('ContactEdit', array_merge(array('userid' => $client->getID(), 'contactid' => $whmcsContact, 'olddata' => $oldcontactdata), $array));
        logActivity('Client Contact Modified - Contact ID: '.$whmcsContact.' - User ID: '.$client->getID());
        $smartyvalues['successful'] = true;
      }
    }
    if ($success) {
      $smartyvalues['successful'] = true;
    }
    $contactsarray = $client->getContacts();

    if (!$whmcsContact && count($contactsarray)) {
      $whmcsContact = $contactsarray[0]['id'];
    }

    $smartyvalues['contacts'] = $contactsarray;
    include 'includes/countries.php';
    $smartyvalues['contactid'] = $whmcsContact;


    if (((!$errormessage && $submit) && $whmcsContact) || ($whmcsContact && !count($contact_data))){
      $contact_data = $client->getContact($whmcsContact);
      if (!$contact_data) {
        redir('action=contacts', 'clientarea.php');
      }
    }

    if (!count($contactsarray)){
      redir('action=addcontact', 'clientareadata.php');
    }

    $smartyvalues['contactfirstname'] = $whmcs->get_req_var_if($e, 'firstname', $contact_data);
    $smartyvalues['contactlastname'] = $whmcs->get_req_var_if($e, 'lastname', $contact_data);
    $smartyvalues['contactcompanyname'] = $whmcs->get_req_var_if($e, 'companyname', $contact_data);
    $smartyvalues['contactemail'] = $whmcs->get_req_var_if($e, 'email', $contact_data);
    $smartyvalues['contactaddress1'] = $whmcs->get_req_var_if($e, 'address1', $contact_data);
    $smartyvalues['contactaddress2'] = $whmcs->get_req_var_if($e, 'address2', $contact_data);
    $smartyvalues['contactcity'] = $whmcs->get_req_var_if($e, 'city', $contact_data);
    $smartyvalues['contactstate'] = $whmcs->get_req_var_if($e, 'state', $contact_data);
    $smartyvalues['contactpostcode'] = $whmcs->get_req_var_if($e, 'postcode', $contact_data);
    $smartyvalues['contactphonenumber'] = $whmcs->get_req_var_if($e, 'phonenumber', $contact_data);
    $idNumber = virtualname_get_identification_number($whmcsContact, 2);
    $legalContact = virtualname_get_legal_form_contact($whmcsContact, 2);
    if($idNumber == 'EMPTY'){
      $idNumber = 'N/A';
      $validateTcpanel = '0';
    }
    else
      $validateTcpanel = '1';

    $hideicnumber = virtualname_get_hide_config();
    $legal_forms  = virtualname_get_legal_forms($whmcs->get_lang('legal_form'), $legalContact);
    $smartyvalues['hideicnumber'] = $hideicnumber;
    $smartyvalues['contactidentificationnumber'] = $idNumber;
    $smartyvalues['contactvnamevalidation'] = $validateTcpanel;
    $smartyvalues['countriesdropdown'] = getCountriesDropDown($whmcs->get_req_var_if($e, 'country', $contact_data));
    $smartyvalues['legalforms'] = $legal_forms;
    $smartyvalues['subaccount'] = $whmcs->get_req_var_if($e, 'subaccount', $contact_data);
    $smartyvalues['permissions'] = $whmcs->get_req_var_if($e, 'permissions', $contact_data);
    $smartyvalues['generalemails'] = $whmcs->get_req_var_if($e, 'generalemails', $contact_data);
    $smartyvalues['productemails'] = $whmcs->get_req_var_if($e, 'productemails', $contact_data);
    $smartyvalues['domainemails'] = $whmcs->get_req_var_if($e, 'domainemails', $contact_data);
    $smartyvalues['invoiceemails'] = $whmcs->get_req_var_if($e, 'invoiceemails', $contact_data);
    $smartyvalues['supportemails'] = $whmcs->get_req_var_if($e, 'supportemails', $contact_data);
  }
}

//PRINT CUSTOM PAGE
$clientArea->output();

############################################################
############CUSTOM FUNCTIONS################################
############################################################
//GET WHMCS TEMPLATE SELECTED CONFIGURATION
function virtualname_get_template_config(){
    $table  = 'tblregistrars';
    $fields = 'value';
    $where  = array('registrar'=>'virtualname','setting'=>'templateVersion');
    $result = select_query($table,$fields,$where);
    if(mysql_num_rows($result)>0){
        $data     = mysql_fetch_array($result);
        $template = decrypt($data['value']);
    }
    else
      $template = 'six';
    return $template;
}

//CHECK VIRTUALNAME-TCPANEL HIDE CONFIGURATION
function virtualname_get_hide_config(){
    $table  = 'tblregistrars';
    $fields = 'value';
    $where  = array('registrar'=>'virtualname','setting'=>'hideicnumber');
    $result = select_query($table,$fields,$where);
    $hide   = '';
    if(mysql_num_rows($result)>0){
        $data = mysql_fetch_array($result);
        $hide = decrypt($data['value']);
    }
    if($hide == 'on')
      return 1;
    else
      return 0;
}

//GET VIRTUALNAME-TCPANEL IDENTIFICATION NUMBER
function virtualname_get_identification_number($contactid,$type){
    $table = 'mod_contacts_virtualname_tcpanel';
    $fields = 'id_contact_tcpanel, identification_number';
    $where = array('id_contact_whmcs'=>$contactid, 'contact_type'=>$type);
    $result = select_query($table,$fields,$where);
    if(mysql_num_rows($result) > 0){
      $row = mysql_fetch_array($result);
      $data = $row['identification_number'];
      if($data == '')
        $data = 'N/A';
    }
    else
      $data = 'EMPTY';
    return $data;
}

//GET VIRTUALNAME-TCPANEL LEGAL FORM
function virtualname_get_legal_form_contact($contactid,$type){
    $table = 'mod_contacts_virtualname_tcpanel';
    $fields = 'id_contact_tcpanel, identification_number, legal_form';
    $where = array('id_contact_whmcs'=>$contactid, 'contact_type'=>$type);
    $result = select_query($table,$fields,$where);
    if(mysql_num_rows($result) > 0){
      $row = mysql_fetch_array($result);
      $data = $row['legal_form'];
    }
    if($data == '')
      $data = 'natural_person_or_individual';
    return $data;
}

//VALIDATE IF THE CURRENT DOMAIN WAS ASSIGNED WITH VIRTUALNAME
function virtualname_check_Domain($domainid){
    $table = 'tbldomains';
    $fields = 'id';
    $where = array('registrar'=>'virtualname', 'id'=>$domainid);
    $result = select_query($table,$fields,$where);
    $check = mysql_num_rows($result);
    return $check;
}

//GET WHMCS VERSION
function virtualname_get_current_whmcs_version(){
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

function virtualname_delete_contact($contactid, $params){
  //INIT MODULE
  require_once(dirname(__FILE__).'/modules/registrars/virtualname/virtualname.php');
  global $vname_contacts;
  virtualname_init();
  $response = $vname_contacts->del_contact($contactid, $params, 2);
  if(!$response['error'])
    return true;
  else
    return false;
}

function virtualname_unlinked_client($clientid){
  //INIT MODULE
  require_once(dirname(__FILE__).'/modules/registrars/virtualname/virtualname.php');
  global $vname_contacts;
  virtualname_init();
  $response = $vname_contacts->client_to_contact($clientid);
  if(!$response['error'])
    return true;
  else
    return false;
}

function virtualname_get_legal_forms($langs, $selected_form){
  $response = '<select name=\'legal_form\' id=\'legal_form\' class=\'form-control select-inline\'>';
  foreach($langs as $key => $value){
    $response .= '<option value=\''.$key.'\'';
    if($selected_form == $key)
      $response .= ' selected';
    $response .= '>'.$value.'</option>';
  }
  $response .= '</select>';
  return $response;
}

function virtualname_get_contact($contactid){
  //INIT MODULE
  require_once(dirname(__FILE__).'/modules/registrars/virtualname/virtualname.php');
  global $vname_contacts;
  virtualname_init();
  $response = $vname_contacts->get_tcpanel_contact_from_id($contactid);
  if(!$response['error'])
    return $response['response'];
  else
    return false;
}

function virtualname_get_whmcs_version(){
  //INIT MODULE
  require_once(dirname(__FILE__).'/modules/registrars/virtualname/virtualname.php');
  global $vname_admin;
  virtualname_init();
  $response = $vname_admin->get_whmcs_version();
  return substr($response, 0, 3);
}

?>