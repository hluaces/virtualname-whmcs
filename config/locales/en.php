<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.0
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: VIRTUALNAME ENGLISH CUSTOM LANGS
// *************************************************************************

    if (!defined('WHMCS'))
        die('This file cannot be accessed directly');

    $langs_vn['successSync']            = 'Successfull sync domain';
    $langs_vn['errorContactCreated']    = 'Invalid contact';
    $langs_vn['errorContactNotFound']   = 'Contacts not found';
    $langs_vn['errorAPIKEY']            = 'Invalid APIKey. Change it in \'Settings > Products/Services > Domain Registers\' and set your APIKey.';
    $langs_vn['errorModuleInstall']     = 'Invalid module installation. Install it in \'Settings > Products/Services > Domain Registers\' and clicked in \'Install Registrar\'.';
    $langs_vn['errorRegisterAvailable'] = 'This domain is not available for register';
    $langs_vn['errorRegisterFree']      = 'Free WHMCS domain not register. Can disable on virtualname configuration.';
    $langs_vn['errorRegister']          = 'Error on register domain. Please check register logs.';
    $langs_vn['errorRegisterDNS']       = 'Error on register domain. Domain DNS do not exist.';
    $langs_vn['errorTransferAvailable'] = 'This domain is not available for transfer';
    $langs_vn['errorTransfer']          = 'Error on tranfer domain. Please check register logs.';
    $langs_vn['errorRenewSecure']       = 'Secure renovation period: This domain can not renew becouse was renewed in the last ';
    $langs_vn['errorRenewSecureHour']   = ' hours';
    $langs_vn['errorRenewAvailable']    = 'This domain is not available for renew';
    $langs_vn['errorRenewFree']         = 'Free WHMCS domain not renew. Can disable on virtualname configuration.';
    $langs_vn['errorRenewExpiration']   = 'Expiration year is invalid. It must be the same year as the actual expiration of the domain.';
    $langs_vn['errorRenew']             = 'Error on renew domain. Please check register logs.';
    $langs_vn['module-0'] = 'Can\'t connect to registrar. Please wait a minutes and try again.';
    $langs_vn['module-1'] = 'Unknown register response';
    $langs_vn['200']  = 'sucess';
    $langs_vn['201']  = 'resource create';
    $langs_vn['202']  = 'success async';
    $langs_vn['400']  = 'ERROR 400 Action failed';
    $langs_vn['401']  = 'ERROR 401 Invalid TOKEN or not authorized IP';
    $langs_vn['402']  = 'ERROR 402 Not enough funds';
    $langs_vn['404']  = 'ERROR 404 Resource not found';
    $langs_vn['422']  = 'ERROR 422 Unprocessable entity please check logs';
    $langs_vn['429']  = 'ERROR 429 Limit petitions';
    $langs_vn['500']  = 'ERROR 500 Internal error';
    $langs_vn['502']  = 'ERROR 502 Action not found';
    $langs_vn['503']  = 'ERROR 503 Maintenance mode';
    $langs_vn['name'] = 'Module Domains Administration';
    $langs_vn['description'] = 'Module Domains Administration Virtualname - WHMCS';
    $langs_vn['autoRenew'] = 'Set default autorenew value on register and transfer domains';
    $langs_vn['hideicnumber'] = 'Hide \'Idenfitication Number\' and \'Legal Form\' fields in the contact and client form';
    $langs_vn['freeRegisterDomains'] = 'Not register free domain';
    $langs_vn['freeRenewDomains'] = 'Not renew free domain';
    $langs_vn['secureRenovation'] = 'Number of hours before you can get back to renew a domain from WHMCS';
    $langs_vn['templateVersion'] = 'Sets the template to be integrated into the client view';
    $langs_vn['defaultvatnumber'] = 'Sets the field personalized customer will be used as identification document (when it be send empty in any of client contacts). <span style=\'color:red\'>If the previous VAT field is checked, it will not be applied.</span>';
    $langs_vn['disablelocktlds'] = 'Set states that domain extensions will display an error when trying to run the transfers locks. To establish new extensions add the extension and a space as a separator between extensions';
    $langs_vn['devMode'] = 'Set development environment';
    $langs_vn['debug'] = 'Set debug response (please use only in maintenance mode)';
    $langs_vn['errorFileNotFound']      = 'File not found';
    $langs_vn['errorIncorrectVersion']  = 'Incorrect file version';
    $langs_vn['errorIncorrectPerm1']    = 'Incorrect';
    $langs_vn['errorIncorrectPerm2']    = 'permission files';
    $langs_vn['errorIncorrectPerm3']    = 'permission folder';
    $langs_vn['errorLinesNotFound']     = 'Missing additional lines from file';
    $langs_vn['errorModuleVariables']   = 'Missing configuration variable';
    $langs_vn['errorModuleVariablesSave']   = 'Save this module configuration to solve this error.';
    $langs_vn['moduleVersion'] = 'Module Version';
    $langs_vn['autoRenewField'] = 'Autorenew';
    $langs_vn['hideicnumberField'] = 'Hide identification number';
    $langs_vn['freeRegisterDomainsField'] = 'Free register domains';
    $langs_vn['freeRenewDomainsField'] = 'Free renew domains';
    $langs_vn['templateVersion'] = 'Template version';
    $langs_vn['secureRenovation'] = 'Secure renovation';
    $langs_vn['defaultvatnumberField'] = 'Default vat number';
    $langs_vn['disablelocktldsField'] = 'Disable lock tlds';
    $langs_vn['devModeField'] = 'Dev mode';
    $langs_vn['debugField'] = 'Debug mode';
    $langs_vn['installVirtualname'] = 'Install virtualname';
    $langs_vn['updateModule'] = 'Update module';
    $langs_vn['checkWarnings'] = 'Warnings';
    $langs_vn['uninstallconf'] = 'Uninstall Virtualname-Tcpanel module?';
    $langs_vn['uninstall'] = 'Uninstall Registrar';
    $langs_vn['installRegistrar'] = 'Install Registrar';
    $langs_vn['updateconf'] = 'Update Virtualname-Tcpanel module? This action will reinstall this module.';
    $langs_vn['updateRegistrar'] = 'Update Virtualname Module';
    $langs_vn['updateAvailable'] = 'An update is available for download';
    $langs_vn['disableLockError'] = 'This domain extension not have enable registrar lock';
    $langs_vn['filesWriteDisable'] = 'By the permissions assigned to users PHP can not use the automatic installation';
    $langs_vn['filesWriteDisableUpdate'] = 'By the permissions assigned to users PHP can not use the automatic update';
    $langs_vn['cronoutbound']   = 'Outbounds transfers';
    $langs_vn['cronpending']    = 'Pending Domains';
    $langs_vn['crons']          = 'Crons';
    $langs_vn['outboundTransferMailing']      = 'Send notice to customers when a domain has been canceled by transferred to another registrar';
    $langs_vn['outboundTransferMailingField'] = 'Notice outgoing domains';
    $langs_vn['download']       = 'Download';
    $langs_vn['errors']         = 'Errors Log';
    $langs_vn['sendErrors']     = 'Send to Virtualname';
    $langs_vn['confirmErrors']  = 'Confirm sending the errors log to Virtualname-Development?';
    $langs_vn['confirm_errors_clean']  = 'Confirm clean the errors log?';
    $langs_vn['errors_clean']  = 'Clean errors log';
    $langs_vn['installTables']       = 'Create tables';
    $langs_vn['installTablesErrors'] = 'Missing some of the tables for the correct operation of the module. To fix it click on \'Create tables\'';
    $langs_vn['defaultNameserversError']      = 'Use the default DNS set in case the client tries to register with an invalid DNS';
    $langs_vn['defaultNameserversErrorField'] = 'DNS Error';
    $langs_vn['new_module_update'] = 'New update available.';
    $langs_vn['current_version'] = 'Current version';
    $langs_vn['get_update'] = 'Get Virtualname Update';
    $langs_vn['hide_popup'] = 'Hide this popup';
    $langs_vn['domain_status'] = 'Domain Status';
    $langs_vn['auto_renew'] = 'Auto Renew';
    $langs_vn['privacy'] = 'Privacy';
    $langs_vn['protection'] = 'Protection';
    $langs_vn['reg_handle'] = 'Registrar handle';
    $langs_vn['adm_handle'] = 'Admin handle ';
    $langs_vn['bill_handle'] = 'Billing handle';
    $langs_vn['tech_handle'] = 'Technical handle';
    $langs_vn['exp_date'] = 'Expiration date';
    $langs_vn['crea_date'] = 'Creation date';
    $langs_vn['domain_data'] = 'Domain data';
    $langs_vn['active'] = 'Active';
    $langs_vn['inactive'] = 'Inactive';
    $langs_vn['transfer_waiting_unlocked'] = 'Transfer waiting for domain to be unlocked';
    $langs_vn['transfer_waiting_admin'] = 'Transfer waiting for Admin Contact Approval';
    $langs_vn['transfer_waiting_registrar'] = 'Transfer waiting for Losing Registrar Approval';
    $langs_vn['transfer_order_locked'] = 'Transfer cannot processing now';
    $langs_vn['transfer_waiting_authcode'] = 'Transfer waiting for authcode';
    $langs_vn['transfer_email_not_sent'] = 'Transfer pending to fetch current administrative contact email address';
    $langs_vn['transfer_resend_authcode'] = 'Resend Authcode';
    $langs_vn['transfer_waiting_pending_registrant_approval'] = 'Pending changes of registrant approval';
    $langs_vn['outbound_transfer'] = 'Outbound transfer';
    $langs_vn['expired'] = 'Expired';
    $langs_vn['redemption'] = 'Redemption';
    $langs_vn['active_pending_registrant_approval'] = 'Active pending registrant approval';
    $langs_vn['pending'] = 'Pending';
    $langs_vn['transferring'] = 'Transferring';
    $langs_vn['transfer_requested'] = 'Transfer requested';
    $langs_vn['transfer_initiated'] = 'Transfer initiated';
    $langs_vn['transfer_email_sent'] = 'E-Mail sent for transfer approval';
    $langs_vn['transfer_rejected'] = 'Transfer rejected';
    $langs_vn['transfer_approved'] = 'Transfer approved';
    $langs_vn['transfer_expired'] = 'Transfer expired';
    $langs_vn['paid'] = 'Paid';
    $langs_vn['legal_form_field'] = 'Legal form';
    $langs_vn['identification_number'] = 'Identification number';
    $langs_vn['legal_form']['natural_person_or_individual'] = 'Natural person or individual';
    $langs_vn['legal_form']['economic_interest_group'] = 'Economic interest grouping';
    $langs_vn['legal_form']['association'] = 'Association';
    $langs_vn['legal_form']['sports_association'] = 'Sports association';
    $langs_vn['legal_form']['trade_association'] = 'Trade association';
    $langs_vn['legal_form']['savings'] = 'Savings';
    $langs_vn['legal_form']['community_property'] = 'Community property';
    $langs_vn['legal_form']['community_of_owners'] = 'Community of owners';
    $langs_vn['legal_form']['congregation_or_religious_institution'] = 'Congregation or religious institution';
    $langs_vn['legal_form']['consulate'] = 'Consulate';
    $langs_vn['legal_form']['public_corporation'] = 'Public corporation';
    $langs_vn['legal_form']['embassy'] = 'Embassy';
    $langs_vn['legal_form']['local_organization'] = 'Local organization';
    $langs_vn['legal_form']['sports_federation'] = 'Sports federation';
    $langs_vn['legal_form']['foundation'] = 'Foundation';
    $langs_vn['legal_form']['mutual_insurance'] = 'Mutual insurance';
    $langs_vn['legal_form']['organ_of_the_regional_administration'] = 'Organ of the regional administration';
    $langs_vn['legal_form']['organ_of_the_state_administration'] = 'Organ of the state administration';
    $langs_vn['legal_form']['political_party'] = 'Political party';
    $langs_vn['legal_form']['union'] = 'Union';
    $langs_vn['legal_form']['agrarian_transformation'] = 'Agrarian transformation';
    $langs_vn['legal_form']['corporation'] = 'Corporation';
    $langs_vn['legal_form']['sports_corporation'] = 'Sports corporation';
    $langs_vn['legal_form']['civil_society'] = 'Civil society';
    $langs_vn['legal_form']['partnership'] = 'Partnership';
    $langs_vn['legal_form']['limited_partnership'] = 'Limited partnership';
    $langs_vn['legal_form']['cooperative_society'] = 'Cooperative society';
    $langs_vn['legal_form']['labour_society_limited'] = 'Labour society limited';
    $langs_vn['legal_form']['limited_society'] = 'Limited society';
    $langs_vn['legal_form']['branch_in_spain'] = 'Branch in spain';
    $langs_vn['legal_form']['consortium'] = 'Consortium';
    $langs_vn['legal_form']['education_corporation'] = 'Education corporation';
    $langs_vn['legal_form']['autonomous_public_organization'] = 'Autonomous public organization';
    $langs_vn['legal_form']['state_public_agency'] = 'State public agency';
    $langs_vn['legal_form']['local_public_agency'] = 'Local public agency';
    $langs_vn['legal_form']['other'] = 'Other';
    $langs_vn['legal_form']['designation_of_origin_control_board'] = 'Designation of origin control board';
    $langs_vn['legal_form']['natural_space_agency_manager'] = 'Natural space agency manager';
    $langs_vn['transfer_not_available'] = 'This domain is not available for transfer';
    $langs_vn['resource_not_found'] = 'Resource not found';
    $langs_vn['documentation'] = 'Documentation';
    $langs_vn['disableAdvanceContacts']      = 'Warning: NOT RECOMMENDED. Disables advanced contact management.';
    $langs_vn['disableAdvanceContactsField'] = 'Disable advanced management';
    $langs_vn['transfer_on_renewal'] = 'TCPanel: Transfer on renewal';
    $langs_vn['status_transfer_on_renewal'] = 'Status';
    $langs_vn['type_transfer_on_renewal'] = 'Transfer type';
    $langs_vn['info_transfer_on_renewal'] = 'Assigning email is valid for speeding up ESNIC domain transfers without AUTHCODE: .es, .com.es, .org.es, .edu.es';
    $langs_vn['second_info_transfer_on_renewal'] = 'Only domains without transfer blocking or the 60 day IRTP blocking can be transferred';
    $langs_vn['add_contact_error'] = 'Contact error: Can\'t register/transfer current domain.';
    $langs_vn['registrar'] = 'Registrar contact';
    $langs_vn['admin'] = 'Admin contact';
    $langs_vn['billing'] = 'Billing contact';
    $langs_vn['technical'] = 'Technical contact';
    $langs_vn['addDefaultDomainsMail'] = 'E-mail used by default for the transfers in the renewal.';
    $langs_vn['addDefaultDomainsMailField'] = 'E-mail transfers';
    $langs_vn['defaultAdminRoles'] = 'Admin role assigned to receive virtualname module notifications (crons).';
    $langs_vn['defaultAdminRolesField'] = 'Role of warnings';
    $langs_vn['launch_transfer'] = 'Launch transfer';
    $langs_vn['empty_authcode'] = 'Unable to get domain authcode';
    $langs_vn['error_on_update_mail'] = 'Unable to change e-mail transfer. Check contacts inside the current registrar';
    $langs_vn['error_not_mail_transfer_available'] = 'This registrar does not accept the transfer by acceptance of the administrative mail';
    $langs_vn['error_not_authcode_transfer_available'] = 'This registrar does not accept the transfer with authcode';
    $langs_vn['error_not_transfer_available'] = 'This transfer can not be re-launched because it is already in process';
    $langs_vn['unknow_transfer_status'] = 'Unknow transfer status';
    $langs_vn['in_progress'] = 'in progress';
    $langs_vn['transfer_on_renewal_active'] = 'Active';
    $langs_vn['transfer_on_renewal_inactive'] = 'Inactive';
    $langs_vn['validationNewClient'] = 'Do not validate new customer data during the registration process in case of register or transfer domains';
    $langs_vn['validationNewClientField'] = 'Validate new customers';
    $langs_vn['taxidField'] = 'Default VAT Number WHMCS';
    $langs_vn['taxid'] = 'Use the client/contact field TAX ID (VAT NUMBER) as default in WHMCS that will be used as identification document. Only for WHMCS version 7.7 and higher.';
    $langs_vn['disableContactVerificationField'] = 'Deactivate contacts verification';
    $langs_vn['disableContactVerification'] = 'Disables verification of contact and customer data if they are not linked';
    $langs_vn['enableDomainRecordsField'] = 'Enable DNS records';
    $langs_vn['enableDomainRecords'] = 'Enable DNS records management';
    $langs_vn['error_fields']['phone'] = 'Phone';
    $langs_vn['error_fields']['state'] = 'State';
    $langs_vn['error_fields']['name'] = 'Nombre';
    $langs_vn['error_fields']['lastname'] = 'Lastname';
    $langs_vn['error_fields']['company'] = 'Company';
    $langs_vn['error_fields']['ic'] = ' Identity Card';
    $langs_vn['error_fields']['email'] = 'Email';
    $langs_vn['error_fields']['country'] = 'Country';
    $langs_vn['error_fields']['name'] = 'Name';
    $langs_vn['error_fields']['city'] = 'City';
    $langs_vn['error_fields']['address'] = 'Address';
    $langs_vn['error_fields']['zipcode'] = 'Zipcode';
    $langs_vn['error_fields']['phonecc'] = 'Phone code';
    $langs_vn['error']['field'] = 'Field';
?>