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
// * File description: VIRTUALNAME CONTACTS class
// *************************************************************************
class Virtualname_contacts extends Virtualname_domains{
    //CHECK IF CONTACT HAVE TCPANEL ID
    public function check_contact($contactID, $params){
        if($contactID == 0 || $contactID == '' || is_null($contactID)){
            $type = 1;
            $contactID = $params['userid'];
            $contact_review = '[USERID: '.$contactID.']';
        }
        else{
            $type = 2;
            $contact_review = '[CONTACTID: '.$contactID.']';
        }
        $tcpanel_whmcs   = $this->get_tcpanel_contact($contactID, $type);
        $tcpanel_contact = $tcpanel_whmcs['id_contact_tcpanel'];
        if($tcpanel_contact == 0){
            $response = $this->add_contact($contactID,$params,$type, array());
            if($response['error']){
                $description = '';
                if(isset($response['response'])){
                    foreach($response['response'] as $key => $value){
                        $description .= '['.$key;
                        if(isset($value[0]) && isset($value[1])){
                            $description .= ' => '.$value[0].': '.$value[1].'] ';
                        }
                        elseif(isset($value[0])){
                            $description .= ' => '.$value[0].'] ';
                        }
                        else
                            $description .= ']';
                    }
                }
                $values = array('response' => 0, 'error' => $response['error'], 'description' => $description, 'review' => $contact_review);
            }
            else{
                $tcpanel_contact = $response['response']['id'];
                $ic = $response['response']['ic'];
                $legal_form = $response['response']['legal_form'];
                $this->add_tcpanel_contact($contactID, $tcpanel_contact, $type, $ic, $legal_form);
                $values = array('response' => $tcpanel_contact);
            }
        }
        else
            $values = array('response' => $tcpanel_contact);
        return $values;
    }
    //GET TCPANEL FROM CONTACT
    public function get_tcpanel_contact($contactID, $type){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_tcpanel, identification_number, legal_form';
        $where = array('id_contact_whmcs'=>$contactID, 'contact_type'=>$type);
        $result = select_query($table,$fields,$where);
        if(mysql_num_rows($result) == 0)
            return 0;
        else{
            $data = mysql_fetch_array($result);
            return $data;
        }
    }
    //ADD CONTACT
    public function add_contact($contactID, $params, $type, $contact){
        $fields = array();
        $module = 'domains';
        $action = 'contacts.json';
        $RESTful= 'POST';
        if(count($contact)==0){
            if($type == 1)
                $contact = $this->get_client_whmcs($contactID);
            elseif($type == 2)
                $contact = $this->get_contact_whmcs($contactID);
            $tcpanel_whmcs        = $this->get_tcpanel_contact($contactID, $type);
            $contact['idnumber'] = $tcpanel_whmcs['identification_number'];
        }
        if(count($params)==0)
            $params = $this->config();

        if($params['defaultvatnumber'] != 'Disabled'){
            if($contact['idnumber'] == '' || $contact['idnumber'] == 'EMPTY'){
                $contact['idnumber'] = $this->get_client_vatnumber($contactID, $type, $params['defaultvatnumber']);
            }
        }
        if($contact['idnumber'] == 'N/A')
            $contact['idnumber'] = '';
        $fields['json'] = json_encode(
                                        array(
                                                'contact' => array( 'name'      =>$contact['firstname'],
                                                                    'lastname'  =>$contact['lastname'],
                                                                    'company'   =>$contact['company'],
                                                                    'ic'        =>trim($contact['idnumber']),
                                                                    'email'     =>$contact['email'],
                                                                    'country'   =>$contact['country'],
                                                                    'state'     =>$this->fix_state($contact['state']),
                                                                    'city'      =>$contact['city'],
                                                                    'address'   =>$contact['address1'],
                                                                    'zipcode'   =>$contact['postcode'],
                                                                    'phonecc'   =>$this->get_country_phone_code($contact['country']),
                                                                    'phone'     =>$contact['phonenumber'],
                                                                    'legal_form'=>$contact['legal_form'])
                                            )
                                    );
        $params['action'] = 'AddContact';

        try{
            $request = $this->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code'] < 200 || $request['status']['code'] > 299){
            $values = $request;
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
        else
            $values = $request;
        return $values;
    }
    //INSERT HANDLE TCPANEL
    public function add_tcpanel_contact($contactID,$tcpanel_contact, $type, $ic, $legal_form){
        $table = 'mod_contacts_virtualname_tcpanel';
        $values = array('id_contact_whmcs'=>$contactID,'id_contact_tcpanel'=>$tcpanel_contact, 'contact_type'=>$type, 'identification_number' => $ic, 'legal_form' => $legal_form);
        insert_query($table,$values);
    }
    //GET CLIENT BY ID
    public function get_client_whmcs($contactID){
        $table = 'tblclients';
        $fields = 'firstname,lastname,companyname,email,country,state,city,address1,address2,postcode,phonenumber';
        $where = array('id'=>$contactID);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        return $data;
    }
    //GET CONTACT BY ID
    public function get_contact_whmcs($contactID){
        $table = 'tblcontacts';
        $fields = 'firstname,lastname,companyname,email,country,state,city,address1,postcode,phonenumber,userid';
        $where = array('id'=>$contactID);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        return $data;
    }
    //GET CONTACT CLIENT CUSTOMFIELD VAT NUMBER
    public function get_client_vatnumber($contactID, $type, $defaultvatnumber){
        if($type == 1){
            //CUSTOMFIELDS
            $table = 'tblcustomfields';
            $fields = 'id';
            $where = array('type'=>'client', 'fieldname'=> trim($defaultvatnumber));
            $result_custom = select_query($table,$fields,$where);
            $data_custom = mysql_fetch_array($result_custom);
            //CUSTOMFIELDSVALUES
            $table = 'tblcustomfieldsvalues';
            $fields = 'value';
            $where = array('fieldid'=>$data_custom['id'], 'relid'=>$contactID);
            $result_values = select_query($table,$fields,$where);
            $data_customfield = mysql_fetch_array($result_values);
        }
        elseif($type == 2){
            //USERID
            $table = 'tblcontacts';
            $fields = 'userid';
            $where = array('id'=>$contactID);
            $result_contacts = select_query($table,$fields,$where);
            $data_user = mysql_fetch_array($result_contacts);
            //CUSTOMFIELDS
            $table = 'tblcustomfields';
            $fields = 'id';
            $where = array('type'=>'client', 'fieldname'=> trim($defaultvatnumber));
            $result_custom = select_query($table,$fields,$where);
            $data_custom = mysql_fetch_array($result_custom);
            //CUSTOMFIELDSVALUES
            $table = 'tblcustomfieldsvalues';
            $fields = 'value';
            $where = array('fieldid'=>$data_custom['id'], 'relid'=>$data_user['userid']);
            $result = select_query($table,$fields,$where);
            $data_customfield = mysql_fetch_array($result);
        }
        return $data_customfield['value'];
    }
    //FIX CONTACT STATES
    public function fix_state($state){
        $exceptions = array('de');
        $result = '';
        $states = explode(' ', $state);
        foreach($states as $fix_state){
            if (in_array(strtolower($fix_state), $exceptions))
                $result .= ' '.mb_strtolower($fix_state, 'UTF-8');
            else
                $result .= ' '.ucwords(mb_strtolower($fix_state, 'UTF-8'));
        }
        $result = $this->replace_state(trim($result));
        return $result;
    }
    public function replace_state($state){
        $uk = array(
            'Bath and North East Somerset' => 'Bath and NE Somerset',
            'Cornwall' => 'Cornwall/Scilly',
            'Londonderry' => 'Derry/Londonderry',
            'Co. Down' => 'Down',
            'City of Edinburgh' => 'Edinburgh',
            'Co. Fermanagh' => 'Fermanagh',
            'Glasgow City' => 'Glasgow',
            'London' => 'Greater London',
            'Manchester' => 'Greater Manchester',
            'Kinross' => 'Kinross-Shire',
            'Co. Londonderry' => 'Londonderry',
            'Tydfil' => 'Merthyr Tydfil',
            'Midlothian' => 'Mid Lothian',
            'Neath Port Talbot' => 'Neath & Port Talbot',
            'Orkney Islands' => 'Orkney and Shetland Isles',
            'The Scottish Borders' => 'Scottish Borders',
            'Shetland Islands' => 'Shetland',
            'Stockton-on-Tees' => 'Stockton On Tees',
            'Co. Tyrone' => 'Tyrone',
            'The Vale of Glamorgan' => 'Vale Of Glamorgan'
        );
        $es = array(
            'Araba/álava' => 'Araba',
            'Málaga' => 'Malaga'
        );
        if(array_key_exists($state, $uk))
            $state = $uk[$state];
        elseif(array_key_exists($state, $es))
            $state = $es[$state];
        return $state;
    }
    //GET PHONE COUNTRY CODE
    public function get_country_phone_code($country){
        $default_country_code = '34';
        $country_phone_codes = array('AD'=>'376','AE'=>'971','AG'=>'1','AI'=>'1','AL'=>'355','AM'=>'374','AN'=>'599','AO'=>'244','AQ'=>'672','AR'=>'54','AS'=>'1','AT'=>'43','AU'=>'61','AW'=>'297','AX'=>'','AZ'=>'994','BA'=>'387','BB'=>'1','BD'=>'880','BE'=>'32','BF'=>'226','BG'=>'359','BH'=>'973','BI'=>'257','BJ'=>'229','BL'=>'590','BM'=>'1','BN'=>'673','BO'=>'591','BR'=>'55','BS'=>'1','BT'=>'975','BV'=>'','BW'=>'267','BY'=>'375','BZ'=>'501','CA'=>'1','CC'=>'61','CD'=>'243','CF'=>'236','CG'=>'242','CH'=>'41','CI'=>'225','CK'=>'682','CL'=>'56','CM'=>'237','CN'=>'86','CO'=>'57','CR'=>'506','CU'=>'53','CV'=>'238','CX'=>'61','CY'=>'357','CZ'=>'420','DE'=>'49','DJ'=>'253','DK'=>'45','DM'=>'1','DO'=>'1','DZ'=>'213','EC'=>'593','EE'=>'372','EG'=>'20','EH'=>'','ER'=>'291','ES'=>'34','ET'=>'251','FI'=>'358','FJ'=>'679','FK'=>'500','FM'=>'691','FO'=>'298','FR'=>'33','GA'=>'241','GB'=>'44','GD'=>'1','GE'=>'995','GF'=>'','GG'=>'','GH'=>'233','GI'=>'350','GL'=>'299','GM'=>'220','GN'=>'224','GP'=>'','GQ'=>'240','GR'=>'30','GS'=>'','GT'=>'502','GU'=>'1','GW'=>'245','GY'=>'592','HK'=>'852','HM'=>'','HN'=>'504','HR'=>'385','HT'=>'509','HU'=>'36','ID'=>'62','IE'=>'353','IL'=>'972','IM'=>'44','IN'=>'91','IO'=>'','IQ'=>'964','IR'=>'98','IS'=>'354','IT'=>'39','JE'=>'','JM'=>'1','JO'=>'962','JP'=>'81','KE'=>'254','KG'=>'996','KH'=>'855','KI'=>'686','KM'=>'269','KN'=>'1','KP'=>'850','KR'=>'82','KW'=>'965','KY'=>'1','KZ'=>'7','LA'=>'856','LB'=>'961','LC'=>'1','LI'=>'423','LK'=>'94','LR'=>'231','LS'=>'266','LT'=>'370','LU'=>'352','LV'=>'371','LY'=>'218','MA'=>'212','MC'=>'377','MD'=>'373','ME'=>'382','MF'=>'1599','MG'=>'261','MH'=>'692','MK'=>'389','ML'=>'223','MM'=>'95','MN'=>'976','MO'=>'853','MP'=>'1','MQ'=>'','MR'=>'222','MS'=>'1','MT'=>'356','MU'=>'230','MV'=>'960','MW'=>'265','MX'=>'52','MY'=>'60','MZ'=>'258','NA'=>'264','NC'=>'687','NE'=>'227','NF'=>'','NG'=>'234','NI'=>'505','NL'=>'31','NO'=>'47','NP'=>'977','NR'=>'674','NU'=>'683','NZ'=>'64','OM'=>'968','PA'=>'507','PE'=>'51','PF'=>'689','PG'=>'675','PH'=>'63','PK'=>'92','PL'=>'48','PM'=>'508','PN'=>'870','PR'=>'1','PS'=>'','PT'=>'351','PW'=>'680','PY'=>'595','QA'=>'974','RE'=>'','RO'=>'40','RS'=>'381','RU'=>'7','RW'=>'250','SA'=>'966','SB'=>'677','SC'=>'248','SD'=>'249','SE'=>'46','SG'=>'65','SH'=>'290','SI'=>'386','SJ'=>'','SK'=>'421','SL'=>'232','SM'=>'378','SN'=>'221','SO'=>'252','SR'=>'597','ST'=>'239','SV'=>'503','SX'=>'1','SY'=>'963','SZ'=>'268','TC'=>'1','TD'=>'235','TF'=>'','TG'=>'228','TH'=>'66','TJ'=>'992','TK'=>'690','TL'=>'670','TM'=>'993','TN'=>'216','TO'=>'676','TR'=>'90','TT'=>'1','TV'=>'688','TW'=>'886','TZ'=>'255','UA'=>'380','UG'=>'256','UM'=>'1','US'=>'1','UY'=>'598','UZ'=>'998','VA'=>'39','VC'=>'1','VE'=>'58','VG'=>'1','VI'=>'1','VN'=>'84','VU'=>'678','WF'=>'681','WS'=>'685','YE'=>'967','YT'=>'262','ZA'=>'27','ZM'=>'260','ZW'=>'263');
        $country_code = $country_phone_codes[$country];
        if($country_code == '')
            $country_code = $default_country_code;
        return $country_code;
    }
    //GET ALL RELATIONED DOMAINS FROM CONTACT ID
    public function related_contacts($contactid){
        $fields = array();
        $module = 'domains/contacts';
        $action = $contactid.'/related-domains.json';
        $RESTful= 'GET';
        $params = $this->config();
        $params['action'] = 'relatedContacts';
        try{
            $request = $this->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
        else
            $values = $request;
        return $values;
    }
    //GET CUSTOMFIELD VAR POSITION
    public function get_vatnumber_position($defaultvatnumber){
        //CUSTOMFIELDS
        $table = 'tblcustomfields';
        $fields = 'id';
        $where = array('type'=>'client', 'fieldname'=> trim($defaultvatnumber));
        $result_custom = select_query($table,$fields,$where);
        if(mysql_num_rows($result_custom) == 0)
            return 0;
        $data_custom = mysql_fetch_array($result_custom);
        return $data_custom['id'];
    }

    //DELETE CONTACT
    public function del_contact($contactID, $params, $type){
        $fields = array();
        if($contactID){
            $tcpanel_whmcs   = $this->get_tcpanel_contact($contactID, $type);
            $tcpanel_contact = $tcpanel_whmcs['id_contact_tcpanel'];
        }

        if(!$tcpanel_contact){
            return true;
        }
        $module  = 'domains/contacts';
        $action  = $tcpanel_contact.'.json';
        $RESTful = 'DELETE';
        if(count($params)==0)
            $params = $this->config();
        $params['action'] = 'DelContact';
        try{
            $request = $this->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code'] < 200 || $request['status']['code'] > 299){
            $values = $request;
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
        else
            $values = $request;
        return $values;
    }

    //VALIDATE CONTACT
    public function validate_contact($contactID, $params, $type, $contact){
        //RELOAD PARAMS
        if(count($params)==0)
            $params = $this->config();
        //CHECK IF ENABLE NEW CLIENTS VALIDATIONS
        if(isset($params['validationNewClient'])){
            if($params['validationNewClient'] == 'on'){
                $request_uri = basename($_SERVER['REQUEST_URI'], ".php");
                if($request_uri == 'cart.php?a=checkout')
                    if(!isset($_SESSION['uid']))
                        return true;
            }
        }
        //GET TCPANEL CONTACT IF EXISTS
        if(count($contact)==0){
            if($type == 1)
                $contact = $this->get_client_whmcs($contactID);
            elseif($type == 2)
                $contact = $this->get_contact_whmcs($contactID);
            $tcpanel_whmcs        = $this->get_tcpanel_contact($contactID, $type);
            $contact['idnumber'] = $tcpanel_whmcs['identification_number'];
        }
        //CHECK IF DISABLE CONTACT VERIFICATION
        if(isset($params['disableContactVerification'])){
            if($params['disableContactVerification'] == 'on'){
                if(!isset($tcpanel_whmcs))
                    return true;
            }
        }
        $fields = array();
        $module = 'domains/contacts';
        $action = 'validate.json';
        $RESTful= 'POST';
        $fields['json'] = json_encode(
                                        array(
                                                'contact' => array( 'name'      =>$contact['firstname'],
                                                                    'lastname'  =>$contact['lastname'],
                                                                    'company'   =>$contact['company'],
                                                                    'email'     =>$contact['email'],
                                                                    'country'   =>$contact['country'],
                                                                    'state'     =>$this->fix_state($contact['state']),
                                                                    'city'      =>$contact['city'],
                                                                    'address'   =>$contact['address1'],
                                                                    'zipcode'   =>$contact['postcode'],
                                                                    'phonecc'   =>$this->get_country_phone_code($contact['country']),
                                                                    'phone'     =>$contact['phonenumber'])
                                            )
                                    );
        //'ic'        =>$contact['idnumber'],
        //'legal_form'=>$contact['legal_form'])
        $params['action'] = 'ValidateContact';

        try{
            $request = $this->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code'] < 200 || $request['status']['code'] > 299){
            $values = $request;
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
        else
            $values = $request;
        return $values;
    }
    //EDIT SINGLE CONTACT
    public function edit_contact($tcpanel_contact, $contact, $contactID, $type){
        $fields = array();
        $params = $this->config();

        foreach($contact as $key => $value){
            if($key == 'idnumber'){
                if($params['defaultvatnumber'] != 'Disabled' AND $params['hideicnumber'] == 'on' AND $type == 1){
                    $vat_number_pos = $this->get_vatnumber_position($params['defaultvatnumber']);
                    if($vat_number_pos != 0){
                        $contact_array['contact']['ic'] = $contact['customfield'][$vat_number_pos];
                    }
                }
                elseif(!is_null($value)){
                    $contact_array['contact']['ic'] = $value;
                }
            }
            elseif($value != '')
                $contact_array['contact'][$key] = $value;
        }
        if($contact_array['contact']['ic'] == 'N/A')
            $contact_array['contact']['ic'] = '';
        $this->update_tcpanel_contact($tcpanel_contact, $contact_array['contact']['ic'], $contact_array['contact']['legal_form']);
        $fields['json'] = json_encode(
                                        array(
                                                'contact' => array( 'name'      =>$contact_array['contact']['firstname'],
                                                                    'lastname'  =>$contact_array['contact']['lastname'],
                                                                    'company'   =>$contact_array['contact']['company'],
                                                                    'ic'        =>trim($contact_array['contact']['ic']),
                                                                    'email'     =>$contact_array['contact']['email'],
                                                                    'country'   =>$contact_array['contact']['country'],
                                                                    'state'     =>$this->fix_state($contact_array['contact']['state']),
                                                                    'city'      =>$contact_array['contact']['city'],
                                                                    'address'   =>$contact_array['contact']['address1'],
                                                                    'zipcode'   =>$contact_array['contact']['postcode'],
                                                                    'phonecc'   =>$this->get_country_phone_code($contact_array['contact']['country']),
                                                                    'phone'     =>$contact_array['contact']['phonenumber'],
                                                                    'legal_form'=>$contact_array['contact']['legal_form'])
                                                )
                                    );

        //CHECK IF CURRENT CONTACT HAVE MORE LINKED DOMAIN
        $contactDuplication = false;
        $clientDomains      = $this->client_domains($contactID, $type);
        $responseDomains    = $this->related_contacts($tcpanel_contact);

        if(count($clientDomains) == 0 && count($responseDomains['response']) > 0){
            $contactDuplication = true;
        }
        else{
            $params['contact']  = $tcpanel_contact;
            foreach($responseDomains['response'] as $domain){
                if(!in_array($domain['name'], $clientDomains))
                    $contactDuplication = true;
            }
        }

        if(!$contactDuplication){
            $params['action'] = 'EditContact';
            $module = 'domains/contacts';
            $action = $tcpanel_contact.'.json';
            $RESTful= 'PATCH';
            try{
                $request = $this->api_call($params,$fields, $module, $action, $RESTful);
            }catch (Exception $e){
                return ($e->getMessage());
            }
            if($request['status']['code']< 200 || $request['status']['code'] > 299){
                $values = $request;
                $values['error'] = $request['status']['description'];
                if(isset($request['response']['name']))
                    $values['error'] .= ': '.implode(',', $request['response']['name']);
            }
            else
                $values = $request;
        }
        else{
            // ADD DUPLICATE CONTACT
            $params['action'] = 'AddContact';
            $module = 'domains';
            $action = 'contacts.json';
            $RESTful= 'POST';
            try{
                $request = $this->api_call($params,$fields, $module, $action, $RESTful);
            }catch (Exception $e){
                return ($e->getMessage());
            }

            if($request['status']['code'] < 200 || $request['status']['code'] > 299){
                $values = $request;
                $values['error'] = $request['status']['description'];
                if(isset($request['response']['name']))
                    $values['error'] .= ': '.implode(',', $request['response']['name']);
            }
            else{
                // UPDATE HANDLES
                $updateHandle  = $request['response']['id'];
                $handle_updates = array();
                $data           = array();
                foreach($responseDomains['response'] as $domain){
                    if(in_array($domain['name'], $clientDomains)){
                        $data['id'] = $domain['id'];
                        if($domain['contacts']['registrant']['id'] == $tcpanel_contact)
                            $data['contacts']['registrant'] = $updateHandle;
                        if($domain['contacts']['administrative']['id'] == $tcpanel_contact)
                            $data['contacts']['administrative'] = $updateHandle;
                        if($domain['contacts']['technical']['id'] == $tcpanel_contact)
                            $data['contacts']['technical'] = $updateHandle;
                        if($domain['contacts']['billing']['id'] == $tcpanel_contact)
                            $data['contacts']['billing'] = $updateHandle;
                        $handle_updates[] = $data;
                    }
                }

                //UPDATE CONTACT NEW HANDLE
                $this->update_tcpanel_contact_handle($contactID, $updateHandle, $type);

                //UPDATE EACH DOMAIN
                foreach($handle_updates as $update_domain){
                    $id_domain = $update_domain['id'];
                    $contacts  = $update_domain['contacts'];
                    $this->set_domain_contacts_update($params, $contacts, $id_domain);
                }

                /*DEVELOPMENT
                // BULK DOMAIN UPDATES
                $module = 'domains/domains/bulk';
                $action = 'update.json';
                $RESTful= 'PATCH';
                $fields['json'] = json_encode(array('domains' => $handle_updates));
                $params['action'] = 'BulkDomainUpdates';
                try{
                    $request = $this->api_call($params,$fields, $module, $action, $RESTful);
                }catch (Exception $e){
                    return ($e->getMessage());
                }
                if($request['status']['code'] < 200 || $request['status']['code'] > 299){
                    $values = $request;
                    $values['error'] = $request['status']['description'];
                    if(isset($request['response']['name']))
                        $values['error'] .= ': '.implode(',', $request['response']['name']);
                }
                else
                    $values = $request;
                */
            }
        }
        return $values;
    }
    //UPDATE TCPANEL IDNUMBER
    public function update_tcpanel_contact($tcpanel_contact, $ic, $legal_form){
        $table = 'mod_contacts_virtualname_tcpanel';
        $where = array('id_contact_tcpanel'=>$tcpanel_contact);
        $update= array('identification_number'=>$ic, 'legal_form'=>$legal_form);
        update_query($table,$update,$where);
    }
    //UPDATE TCPANEL IDNUMBER
    public function update_tcpanel_contact_handle($contactID, $tcpanel_contact, $type){
        $table = 'mod_contacts_virtualname_tcpanel';
        $where = array('id_contact_whmcs'=>$contactID, 'contact_type'=>$type);
        $update= array('id_contact_tcpanel'=>$tcpanel_contact);
        update_query($table,$update,$where);
    }
    //LINK CONTACTS - DOMAIN
    public function link_domain_contacts($params, $TCPanelID, $contactID, $contactType){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
        if(!class_exists('Punycode'))
            @include_once('class.punicode.php');
        $Punycode = new Punycode();
        $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
        if(!$TCPanelID){
            //ITS MEAN THAT THE DOMAIN NOT EXIST
            $values['error'] = 'Contact not found';
        }
        else{
            $cType = $contactType;
            if($contactType == 'registrant')
                $cType = 'regContact';
            if($contactType == 'administrative')
                $cType = 'adminContact';
            if($contactType == 'billing')
                $cType = 'billingContact';
            if($contactType == 'technical')
                $cType = 'techContact';

            //SELECT BBDD
            $sql  = 'select * from tbldomainsadditionalfields';
            $sql .= ' WHERE name = \''.$cType.'\' AND domainid=\''.$params['domainid'].'\'';
            $res = mysql_query($sql);
            if ($error = mysql_error()) die('link_domain_contacts ERR: '. $error.' SQL: ' . $sql);
            if(mysql_num_rows($res) == 0){
                //INSERT VINCULATION
                $sql  = 'insert into tbldomainsadditionalfields';
                $sql .= ' VALUES (\'\', \''.$params['domainid'].'\', \''.$cType.'\', \''.$contactID.'\', \'\', \'\')';
                $resIn = mysql_query($sql);
                if ($error = mysql_error()) die('link_domain_contacts ERR: '. $error.' SQL: ' . $sql);
            }
            else{
                //UPDATE VINCULATION
                $data = mysql_fetch_array($res);
                $sql  = 'UPDATE tbldomainsadditionalfields';
                $sql .= ' SET tbldomainsadditionalfields.value = \''.$contactID.'\'';
                $sql .= ' WHERE id = \''.$data['id'].'\'';
                $resUp = mysql_query($sql);
                if ($error = mysql_error()) die('link_domain_contacts ERR: '. $error.' SQL: ' . $sql);
                $updated = mysql_affected_rows();
                $values = array('message' => 'Successfull linked domain');
            }
        }
        return $values;
    }
    //DELETE HANDLE TCPANEL
    public function del_tcpanel_contact($contactID,$tcpanel_contact, $type){
        $table = 'mod_contacts_virtualname_tcpanel';
        $where = array('id_contact_whmcs'=>$contactID,'id_contact_tcpanel'=>$tcpanel_contact, 'contact_type'=>$type);
        delete_query($table,$where);
    }
    //GET TCPANEL CONTACT FROM ID
    public function get_whmcs_tcpanel_type($TCpanelid, $userID){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_whmcs, contact_type, identification_number, legal_form';
        $sql    = 'select '.$fields.' from '.$table.' where id_contact_tcpanel = '.$TCpanelid.' and id_contact_whmcs in (select id from tblcontacts where userid = '.$userID.') LIMIT 1';
        $result = mysql_query($sql);
        if(mysql_num_rows($result) == 0){
            //CHECK IF THE CURRENT TCPANELID BELONG TO USER PROFILE
            $sql    = 'select '.$fields.' from '.$table.' where id_contact_tcpanel = '.$TCpanelid.' and id_contact_whmcs = '.$userID.' and contact_type = 1';
            $result = mysql_query($sql);
            if(mysql_num_rows($result) == 0)
                return 0;
            else{
                $data = mysql_fetch_array($result);
                return $data;
            }
        }
        else{
            $data = mysql_fetch_array($result);
            return $data;
        }
    }
    //GET ALL TCPANEL CONTACT FROM ID
    public function get_all_whmcs_tcpanel_type($userID){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_whmcs, id_contact_tcpanel, contact_type, identification_number, legal_form';
        $sql    = 'select '.$fields.' from '.$table.' where id_contact_whmcs in (select id from tblcontacts where userid = '.$userID.' AND contact_type = 2) OR (id_contact_whmcs = '.$userID.' AND contact_type = 1)';
        $result = mysql_query($sql);
        $allData = array();
        while($data = mysql_fetch_array($result))
            $allData[$data['id_contact_tcpanel']] = $data;
        return $allData;
    }
    //GET ALL TCPANEL CONTACT FROM ID
    public function get_all_whmcs_tcpanel_type_indexed(){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_whmcs, id_contact_tcpanel, contact_type, identification_number, legal_form';
        $sql    = 'select '.$fields.' from '.$table.' where contact_type = 1';
        $result = mysql_query($sql);
        $allData = array();
        $userAllData = array();
        while($data = mysql_fetch_array($result)){
            $userAllData[$data['id_contact_whmcs']][$data['id_contact_tcpanel']] = $data;
        }
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = '(select userid from tblcontacts where id =m.id_contact_whmcs) as userid, id_contact_whmcs, id_contact_tcpanel, contact_type, identification_number, legal_form';
        $sql    = 'select '.$fields.' from '.$table.' m where contact_type = 2';
        $result = mysql_query($sql);
        while($data = mysql_fetch_array($result)){
            $userAllData[$data['userid']][$data['id_contact_tcpanel']] = $data;
        }
        return $userAllData;
    }
    //GET ALL TCPANEL CONTACT IN WHMCS
    public function get_all_tcpanel_contact(){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_whmcs, id_contact_tcpanel, contact_type, identification_number, legal_form';
        $result = select_query($table,$fields,'');
        $allData = array();
        while($data = mysql_fetch_array($result))
            $allData[$data['id_contact_whmcs'].'_'.$data['contact_type']] = $data;
        return $allData;
    }
    //RETURN VALIDATE TICKER FROM TCPANEL CONTACT
    public function get_validate_contact($contactid,$type){
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'id_contact_tcpanel';
        $where = array('id_contact_whmcs'=>$contactid, 'contact_type'=>$type);
        $result = select_query($table,$fields,$where);
        if(mysql_num_rows($result) > 0){
            $row = mysql_fetch_array($result);
             $data = $row['id_contact_tcpanel'];
            if($data != '')
                $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:green;\'>✔</span>';
            else
                $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:red;\'>✘</span>';
        }
        else
            $ticker = '<span style=\'font-family: wingdings; font-size: 200%;color:red;\'>✘</span>';
        return $ticker;
    }
    //GET WHMCS ORDER CONTACT
    public function get_whmcs_order_contact($orderid){
        $table = 'tblorders';
        $fields = 'contactid';
        $where = array('id'=>$orderid);
        $result = select_query($table,$fields,$where);
        $data = mysql_fetch_array($result);
        $orderContact = $data['contactid'];
        if($orderContact == '')
            $orderContact = 0;
        return $orderContact;
    }
    //GET PHONE CODE FROM FULL PHONE NUMBER
    public function get_phone_code($phone){
        $code_lenght = strlen($phone)-7;
        $code_phone = substr($myStr, 0, $code_lenght);
        return $code_phone;
    }
    //GET ALL CLIENT CONTACTS
    public function get_client_contacts($userID){
        $table = 'tblcontacts';
        $fields = 'id,firstname,lastname,email';
        $where = array('userid'=>$userID);
        $result = select_query($table,$fields,$where);
        $data = array();
        while ($row = mysql_fetch_array($result)){
            $data[] = array('id'=>$row['id'],'firstname'=>$row['firstname'],'lastname'=>$row['lastname'],'email'=>$row['email']);
        }
        return $data;
    }
    //ADD NEW WHMCS CONTACT
    public function add_whmcs_contact($clientId, $contactInfo){
        $TCpanelid = $contactInfo['id'];
        $identification_number = $contactInfo['ic'];
        $legal_form  = $contactInfo['legal_form'];
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

        if (!$country) $country = 'ES';
        $table = 'tblcontacts';
        $array = array('userid' => $clientId, 'firstname' => $firstname, 'lastname' => $lastname, 'companyname' => $companyname,
                       'email' => $email, 'address1' => $address1, 'city' => $city, 'state' => strtoupper($state), 'postcode' => $postcode,
                       'country' => $country, 'phonenumber' => $phonenumber, 'subaccount' => 0);

        $contactID = insert_query($table, $array);
        if ($contactID) {
            Virtualname_contacts::add_tcpanel_contact($contactID,$TCpanelid, '2', $identification_number, $legal_form);
            $values['message'] = 'Created';
            $values['contactid'] = $contactID;
        }
        else {
            $values['message'] = 'Error';
            $values['error'] = 'Can\'t create CONTACT';
        }
        return $values;
    }
    //UPDATE NEW WHMCS CONTACT
    public function update_whmcs_contact($contactInfo, $contactID, $updateSync){
        $updMessage = 'Checked';
        if ($contactID) {
            $TCpanelid = $contactInfo['id'];
            $identification_number = $contactInfo['ic'];
            $legal_form  = $contactInfo['legal_form'];
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

            //UPDATE WHMCS CONTACT
            $table = 'tblcontacts';
            $updateqry= array();
            $updateqry['firstname']     = $firstname;
            $updateqry['lastname']      = $lastname;
            $updateqry['companyname']   = $companyname;
            $updateqry['email']         = $email;
            $updateqry['address1']      = $address1;
            $updateqry['city']          = $city;
            $updateqry['state']         = strtoupper($state);
            $updateqry['postcode']      = $postcode;
            $updateqry['country']       = $country;
            $updateqry['phonenumber']   = $phonenumber;
            if($updateSync){
                //UPDATE WHMCS CONTACT VALUES
                update_query($table, $updateqry, array('id' => $contactID));
                if(mysql_affected_rows()){
                    $updMessage = 'Updated';
                }
                //UPDATE WHMCS IDENTIFICATION NUMBER CONTACT
                Virtualname_contacts::update_tcpanel_contact($TCpanelid, $identification_number, $legal_form);
                if(mysql_affected_rows()){
                    $updMessage = 'Updated';
                }
            }
            else{
                //UPDATE WHMCS CONTACT VALUES
                $updateqry['id'] = $contactID;
                $user_search = select_query($table, '', $updateqry);
                if(!mysql_num_rows($user_search)){
                    $updMessage = 'Updated';
                }
                //UPDATE WHMCS IDENTIFICATION NUMBER CONTACT
                //$tcpanel_whmcs = $this->get_tcpanel_contact($contactID, 2);
                $tcpanel_whmcs = Virtualname_contacts::get_tcpanel_contact($contactID, 2);
                if($identification_number != $tcpanel_whmcs['identification_number'] || $legal_form != $tcpanel_whmcs['legal_form']){
                    $updMessage = 'Updated';
                }
            }
            $values['message'] = $updMessage;
        }
        else{
            $values['message'] = 'Error';
            $values['error'] = 'Can\'t update CONTACT';
        }
        return $values;
    }
    //CHECK WHMCS CLIENT DATA
    public function check_whmcs_client($clientId, $contactInfo, $ic_number, $legal_form_contact, $updateSync){
        $TCpanelid = $contactInfo['id'];
        $identification_number = $contactInfo['ic'];
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
        $legal_form  = $contactInfo['legal_form'];

        $where = array('id'          => $clientId,
                       'firstname'   => $firstname,
                       'lastname'    => $lastname,
                       'companyname' => $companyname,
                       'email'       => $email,
                       'address1'    => $address1,
                       'city'        => $city,
                       'state'       => strtoupper($state),
                       'postcode'    => $postcode,
                       'country'     => $country,
                       'phonenumber' => $phonenumber,
        );

        $client_details = select_query('tblclients', '', $where);
        $data_details   = mysql_fetch_array($client_details);

        if(!$data_details || $ic_number != $identification_number || $legal_form != $legal_form_contact){
            if($updateSync){
                $this->del_tcpanel_contact($clientId,$TCpanelid, '1');
                $res = $this->add_whmcs_contact($clientId, $contactInfo);
                if(!$res['error']){
                    $values['message'] = 'Created';
                }
                else{
                    $values['message'] = 'Error';
                    $values['error']    = 'Error on create new contact. Default client data was changed';
                }
            }
            else{
                $values['message'] = 'Created';
            }
        }
        else
            $values['message'] = 'Checked';
        return $values;
    }
    //GET IF WHMCS CONTACT DATA EXIST
    public function get_whmcs_contacts($clientId, $contactInfo, $allWHMCSTcpanelType){
        $contactID = 0;
        $type      = 0;
        $TCpanelid   = $contactInfo['id'];
        $ic_number   = $contactInfo['ic'];
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

        $tcpanel_whmcs_client  = 0;
        foreach($allWHMCSTcpanelType as $key => $value){
            if($value['id_contact_whmcs'] == $clientId AND $value['contact_type'] == 1)
                $tcpanel_whmcs_client = 1;
        }

        if(!$tcpanel_whmcs_client){
            $where = array('id'          => $clientId,
                           'firstname'   => $firstname,
                           'lastname'    => $lastname,
                           'companyname' => $companyname,
                           'email'       => $email,
                           'address1'    => $address1,
                           'city'        => $city,
                           'state'       => strtoupper($state),
                           'postcode'    => $postcode,
                           'country'     => $country,
                           'phonenumber' => $phonenumber,
            );
            $contact_details = select_query('tblclients', '', $where);
            if(mysql_num_rows($contact_details)){
                    $contactID = $clientId;
                    $type      = 1;
            }
        }

        if(!$contactID){
            $where = array('userid'      => $clientId,
                           'firstname'   => $firstname,
                           'lastname'    => $lastname,
                           'companyname' => $companyname,
                           'email'       => $email,
                           'address1'    => $address1,
                           'city'        => $city,
                           'state'       => strtoupper($state),
                           'postcode'    => $postcode,
                           'country'     => $country,
                           'phonenumber' => $phonenumber,
            );
            $contact_details = select_query('tblcontacts', '', $where);
            $data_details = mysql_fetch_array($contact_details);

            $tcpanel_whmcs_contact  = 0;

            foreach($allWHMCSTcpanelType as $key => $value){
                if($value['id_contact_whmcs'] == $data_details['id'] AND $value['contact_type'] == 2)
                    $tcpanel_whmcs_contact = 1;
            }

            if(!$tcpanel_whmcs_contact){
                $contactID = $data_details['id'];
                $type      = 2;
            }
        }
        return array('contactID'=>$contactID, 'type'=>$type);
    }
    //CHECK WHMCS CONTACT DATA
    public function check_whmcs_contacts($clientId, $contactInfo, $new){
        $contactID = 0;
        $type      = 0;
        $id_number = 0;
        $ic_number = 0;
        $TCpanelid = $contactInfo['id'];
        $identification_number = $contactInfo['ic'];
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

        $tcpanel_whmcs_client = $this->get_tcpanel_contact($clientId,1);

        if(!$new){
            $id_number    = $tcpanel_whmcs_client['identification_number'];
            $ic_number    = $contactInfo['ic'];
            $legal_form_contact = $tcpanel_whmcs_client['identification_number'];
            $legal_form = $contactInfo['ic'];
        }

        if(($new AND !$tcpanel_whmcs_client) OR !$new){
            $contact_details = select_query('tblclients', '', array('id' => $clientId));
            $client_details  = mysql_fetch_array($contact_details);
            if($client_details['firstname']   == $firstname   && $client_details['lastname']    == $lastname &&
               $client_details['companyname'] == $companyname && $client_details['email']       == $email &&
               $client_details['address1']    == $address1    && $client_details['city']        == $city &&
               strtoupper($client_details['state']) == strtoupper($state) &&
               $client_details['postcode']    == $postcode    && $client_details['country'] == $country  &&
               $client_details['phonenumber'] == $phonenumber && $ic_number == $id_number &&
               $legal_form == $legal_form_contact){
                    $contactID = $clientId;
                    $type      = 1;
            }
        }

        if($contactID == 0){
            $contact_details = select_query('tblcontacts', '', array('userid' => $clientId));
            while($data_details = mysql_fetch_array($contact_details)){
                    $tcpanel_whmcs = $this->get_tcpanel_contact($data_details['id'],2);
                    if($new){
                        if($tcpanel_whmcs)
                            continue;
                    }
                    else{
                        $id_number    = $tcpanel_whmcs['identification_number'];
                        $ic_number    = $contactInfo['ic'];
                    }
                    if($data_details['firstname']    == $firstname       && $data_details['lastname']    == $lastname &&
                            $data_details['companyname'] == $companyname && $data_details['email']       == $email &&
                            $data_details['address1']    == $address1    && $data_details['city']        == $city &&
                            strtoupper($data_details['state']) == strtoupper($state) &&
                            $data_details['postcode']    == $postcode    && $data_details['country']     == $country &&
                            $data_details['phonenumber'] == $phonenumber && $ic_number == $id_number &&
                            $legal_form == $legal_form_contact){
                        $contactID = $data_details['id'];
                        $type      = 2;
                        break;
                    }
            }
        }
        return array('contactID'=>$contactID, 'type'=>$type);
    }
    //GET LEGAL FORM
    public function get_legal_forms($langs, $selected_form){
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
    //GET VIRTUALNAME-TCPANEL IDENTIFICATION NUMBER
    public function get_identification_number($contactid,$type){
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
    //GET ALL VIRTUALNAME-TCPANEL IDENTIFICATION NUMBER
    public function get_all_identification_number($userid){
        $table = 'tblcontacts';
        $fields = 'id';
        $where = array('userid'=>$userid);
        $result = select_query($table,$fields,$where);
        while($row = mysql_fetch_array($result)){
            $idn_array[$row['id']] = $this->get_identification_number($row['id'],2);
        }
        $idn_array[0] = $this->get_identification_number($userid,1);
        return $idn_array;
    }
    public function admin_delete_contact($contactid, $params){
        $response = $this->del_contact($contactid, $params, 2);
        if(!$response['error'])
        return true;
        else
        return false;
    }
    //GET VIRTUALNAME-TCPANEL LEGAL FORM
    public function get_legal_form_contact($contactid,$type){
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
    //CHANGE VIRTUALNAME-TCPANEL IDENTIFICATION NUMBER
    public function edit_client_identification_number($identification_number, $contactid, $type, $contactParams){
        if($identification_number == 'N/A')
          $identification_number = '';
        $table = 'mod_contacts_virtualname_tcpanel';
        $fields = 'identification_number';
        $where = array('id_contact_whmcs'=>$contactid, 'contact_type'=>$type);
        $result = select_query($table,$fields,$where);
        if(mysql_num_rows($result) > 0){
          $update= array('identification_number'=>$identification_number);
          $where = array('id_contact_whmcs'=>$contactid, 'contact_type'=>$type);
          update_query($table,$update,$where);
          //EXECT CONTACT CHANGE
        }
        else{
          $values = array('id_contact_whmcs'=>$contactid,'contact_type'=>$type,'identification_number'=>$identification_number);
          insert_query($table,$values);
          //EXECT CONTACT CHANGE
        }
    }
    //LAUNCHED WHEN CONTACT WAS EDITED
    public function admin_contact_edit($var){
        $userID     = $var['userid'];
        $contactID  = $var['contactid'];
        if($contactID){
            $type = 2;
        }
        else{
            if(is_null($var['userid']) || empty($var['userid'])){
                $var['userid'] = $var['customerid'];
            }
            $contactID = $var['userid'];
            $type = 1;
        }

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
        $contact['idnumber']    = $var['identificationnumber'];
        $contact['legal_form']  = $var['legal_form'];

        $tcpanel_whmcs   = $this->get_tcpanel_contact($contactID, $type);
        $tcpanel_contact = $tcpanel_whmcs['id_contact_tcpanel'];

        if($tcpanel_contact == 0){
            if($var['linked'])
                $response = $this->add_contact($contactID, array(), $type, $contact);
            else
                $response = $this->validate_contact($contactID, array(), $type, $contact);

            if(!$response['error'] AND $response['status']['code'] != 422){
                $tcpanel_contact = $response['response']['id'];
                $ic = $response['response']['ic'];
                $legal_form = $response['response']['legal_form'];
                if($tcpanel_contact != 0)
                    $this->add_tcpanel_contact($contactID, $tcpanel_contact, $type, $ic, $legal_form);
            }
            elseif($response['status']['code'] == 422){
                $error = array();
                foreach($response['response'] as $key => $value){
                    $error[] = $key.': '.$value[0];
                }
                return array('error'=>$error);
            }
            else{
                $error = $response['error'];
                return $error;
            }
        }
        else{
            if($var['unlinked'])
                $response = $this->del_tcpanel_contact($contactID, $tcpanel_contact, $type);
            else
                $response = $this->edit_contact($tcpanel_contact, $contact, $contactID, $type);
            if($response['error'] && $response['status']['code'] != 422){
                $error = $response['error'];
                return $error;
            }
            elseif($response['status']['code'] == 422){
                $error = array();
                foreach($response['response'] as $key => $value){
                    $error[] = $key.': '.$value[0];
                }
                return array('error'=>$error);
            }
        }
        return array();
    }
    //GENERATE CONTACT FROM CLIENT
    public function client_to_contact($userid){
        //CONTACT ARRAY DATA
        $client = $this->get_client_whmcs($userid);
        $firstname   = $client['firstname'];
        $lastname    = $client['lastname'];
        $companyname = $client['companyname'];
        $email       = $client['email'];
        $address1    = $client['address1'];
        $address2    = $client['address2'];
        $city        = $client['city'];
        $state       = $client['state'];
        $postcode    = $client['postcode'];
        $country     = $client['country'];
        $phonenumber = $client['phonenumber'];
        $password    = '';
        $permissions = '';

        $contactID = addContact($userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions,0, 0, 0, 0, 0);

        if ($contactID) {
            $table = 'mod_contacts_virtualname_tcpanel';
            $update= array('id_contact_whmcs'=>$contactID,'contact_type'=>'2');
            $where = array('id_contact_whmcs'=>$userid, 'contact_type'=>'1');
            update_query($table,$update,$where);
            $generate = 'generated';
        }
        else
            $generate = 'error';
    }
    //GET ALL RELATIONED DOMAINS FROM CONTACT ID
    public function get_tcpanel_contact_from_id($contactid){
        $fields = array();
        $module  = 'domains/contacts';
        $action  = $contactid.'.json';
        $RESTful = 'GET';
        $params = $this->config();
        $params['action'] = 'GetContact';
        try{
            $request = $this->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['name']))
                $values['error'] .= ': '.implode(',', $request['response']['name']);
        }
        else
            $values = $request;
        return $values;
    }
    //GET ADMIN CONTACTS DETAILS
    public function get_contact_details($params){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
        if(!class_exists('Punycode'))
            @include_once('class.punicode.php');
        $Punycode = new Punycode();
        $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
        $domain_info = $vname_domains->view_domain_info($params);

        if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
            //$values['error']  = $domain_info['status']['description'];
        }

        $langs = array('registrant'=>'reg','administrative'=>'admin','billing'=>'billing','technical'=>'tech');
        $contacts = array('registrant','administrative','billing','technical');
        //CHECK CONTACT RULES
        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];

        $additionalcontactsfields = $vname_domains->get_whmcs_additional_domains($params['domainid']);
        foreach ($contacts as $key){
            if($domain_rules['.'.$params['tld']]){
                if($domain_rules['.'.$params['tld']]['contacts'][$langs[$key]] != 1){
                    if($domain_info['response'][0]){
                        $infoContact = $domain_info['response'][0]['contacts'][$key];
                        if($infoContact['company'] != '')
                            $contactData = $infoContact['company'].' - ';
                        $contactData .= $infoContact['name'].' '.$infoContact['lastname'];
                    }
                    else{
                        $contactData = 'N/A';
                    }
                    $values[$langs[$key]]['contact']['editRules'] = 2;
                    $values[$langs[$key]]['contact']['contactData'] = $contactData;
                    continue;
                }
            }
            if($domain_info['response'][0])
                $id = $domain_info['response'][0]['contacts'][$key]['id'];
            else
                $id = 0;
            if(isset($params['domainid']))
                $domainid = $params['domainid'];
            else
                $domainid = 0;
            $domain_whmcs = $vname_domains->get_whmcs_domain($domain, $domainid);
            $userID = $domain_whmcs['userid'];
            $response = $this->get_whmcs_tcpanel_type($id, $userID);

            if($key == 'registrant')
                $addiotionalKey = 'reg';
            elseif($key == 'technical')
                $addiotionalKey = 'tech';
            elseif($key == 'administrative')
                $addiotionalKey = 'admin';
            elseif($key == 'billing')
                $addiotionalKey = 'billing';

            $type = $response['contact_type'];
            $contactID = $response['id_contact_whmcs'];
            if($type == 2){
                $selectedContactAdmin = '/clientsdatadomaincontacts.php?userid='.$userID .'&contactid='.$contactID.'&action=clientscontacts';
                $message = 'edit';
            }
            elseif($type == 1){
                $selectedContactAdmin = '/clientsdatadomaincontacts.php?userid='.$userID .'&contactid=0&action=clientscontacts';
                $message = 'edit';
            }
            elseif($type == 0){
                if($domain_info['response'][0])
                    $contactInfo  = $domain_info['response'][0]['contacts'][$key];
                else
                    $contactInfo = false;
                if(!$contactInfo){
                    $selectedContactAdmin = 'UNAVAILABLE';
                }
                else{
                    $selectedContactAdmin = '/clientsdatadomaincontacts.php?action=generateContact&domainid='.$params['domainid'].'&contactinfo='.$contactInfo['id'];
                }
                $message = 'generate';
            }
            $clientContacts = $vname_domains->get_contacts_from_domain($params['domainid']);
            $contactOptions = '';

            foreach($clientContacts as $contact){
                $ticker = $this->get_validate_contact($contact['id'],2);
                $contactName = $ticker.$contact['firstname'].' '.$contact['lastname'].' '.$contact['email'];
                if($contact['id']==$contactID)
                    $contactOptions .= '<option value='.$contact['id'].' selected>'.$contactName.'</option>';
                elseif($contact['id']==$additionalcontactsfields[$addiotionalKey.'Contact'])
                    $contactOptions .= '<option value='.$contact['id'].' selected>'.$contactName.'</option>';
                else
                    $contactOptions .= '<option value='.$contact['id'].'>'.$contactName.'</option>';
            }
            $values[$langs[$key]]['contact']['hrefAdmin'] = $selectedContactAdmin;
            $values[$langs[$key]]['contact']['message']   = $message;
            $values[$langs[$key]]['contact']['options']   = $contactOptions;
            $values[$langs[$key]]['contact']['contactID'] = $contactID;
            $values[$langs[$key]]['contact']['ticker']    = $this->get_validate_contact($userID,1);
            $values[$langs[$key]]['contact']['editRules'] = 1;
        }
        return $values;
    }
    //GET CONTACT DETAILS VIRTUALNAME ADVANCE CONTACTS ENABLED
    public function get_contacts_advance_details($params, $domain_info){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        //CHECK CONTACT RULES
        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];
        $langs = array('registrant'=>'reg','administrative'=>'admin','billing'=>'billing','technical'=>'tech');
        $contacts = array('registrant','administrative','billing','technical');
        foreach ($contacts as $key){
            if($domain_rules['.'.$params['tld']]){
                if($domain_rules['.'.$params['tld']]['contacts'][$langs[$key]] != 1){
                    $infoContact = $domain_info['response'][0]['contacts'][$key];
                    if($infoContact['company'] != '')
                        $contactData = $infoContact['company'].' - ';
                    $contactData .= $infoContact['name'].' '.$infoContact['lastname'];
                    $values[$langs[$key]]['contact']['editRules'] = 2;
                    $values[$langs[$key]]['contact']['contactData'] = $contactData;
                    continue;
                }
            }
            $id = $domain_info['response'][0]['contacts'][$key]['id'];
            if(isset($params['domainid']))
                $domainid = $params['domainid'];
            else
                $domainid = 0;
            $domain_whmcs = $vname_domains->get_whmcs_domain($domain, $domainid);
            $userID = $domain_whmcs['userid'];
            $response = $this->get_whmcs_tcpanel_type($id, $userID);

            if($key == 'registrant')
                $addiotionalKey = 'reg';
            elseif($key == 'technical')
                $addiotionalKey = 'tech';
            elseif($key == 'administrative')
                $addiotionalKey = 'admin';
            elseif($key == 'billing')
                $addiotionalKey = 'billing';

            $type = $response['contact_type'];
            $contactID = $response['id_contact_whmcs'];
            if($type == 2){
                $selectedContact = '/clientareadata.php?action=contacts&contactid='.$contactID;
                $message = 'edit';
            }
            elseif($type == 1){
                $selectedContact = '/clientareadata.php?action=details';
                $message = 'edit';
            }
            elseif($type == 0){
                $contactInfo  = $domain_info['response'][0]['contacts'][$key];
                if(is_null($contactInfo)){
                    $selectedContact      = 'UNAVAILABLE';
                }
                else{
                    $selectedContact      = '/clientareadata.php?action=generateContact&domainid='.$params['domainid'].'&contactinfo='.$contactInfo['id'];
                }
                $message = 'generate';
            }
            $clientContacts = $vname_domains->get_contacts_from_domain($params['domainid']);
            $contactOptions = '';

            foreach($clientContacts as $contact){
                $ticker = $this->get_validate_contact($contact['id'],2);
                $contactName = $ticker.$contact['firstname'].' '.$contact['lastname'].' '.$contact['email'];
                if($contact['id']==$contactID)
                    $contactOptions .= '<option value='.$contact['id'].' selected>'.$contactName.'</option>';
                elseif($contact['id']==$additionalcontactsfields[$addiotionalKey.'Contact'])
                    $contactOptions .= '<option value='.$contact['id'].' selected>'.$contactName.'</option>';
                else
                    $contactOptions .= '<option value='.$contact['id'].'>'.$contactName.'</option>';
            }
            $values[$langs[$key]]['contact']['href']      = $selectedContact;
            $values[$langs[$key]]['contact']['message']   = $message;
            $values[$langs[$key]]['contact']['options']   = $contactOptions;
            $values[$langs[$key]]['contact']['contactID'] = $contactID;
            $values[$langs[$key]]['contact']['ticker']    = $this->get_validate_contact($userID,1);
            $values[$langs[$key]]['contact']['editRules'] = 1;
        }
        return $values;
    }

    //GET CONTACT DETAILS VIRTUALNAME ADVANCE CONTACTS DISABLED
    public function get_contacts_simple_details($params, $domain_info){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        //CHECK CONTACT RULES
        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];
        $langs = array('registrant'=>'reg','administrative'=>'admin','technical'=>'tech','billing'=>'billing');
        $contacts = array('registrant','administrative','billing','technical');
        foreach ($contacts as $key){
            if($key == 'registrant')
                $contactName = 'Propietario';
            if($key == 'administrative')
                $contactName = 'Administrativo';
            if($key == 'technical')
                $contactName = 'Tecnico';
            if($key == 'billing')
                $contactName = 'Facturacion';

            if($domain_rules['.'.$params['tld']]){
                if($domain_rules['.'.$params['tld']]['contacts'][$langs[$key]] != 1){
                    $infoContact = $domain_info['response'][0]['contacts'][$key];
                    if($infoContact['company'] != '')
                        $contactData = $infoContact['company'].' - ';
                    $contactData .= $infoContact['name'].' '.$infoContact['lastname'];
                    $values[$contactName]['Handle'] = $contactData;
                    continue;
                }
            }
            $infoContact = $domain_info['response'][0]['contacts'][$key];
            $values[$contactName]['Handle'] = $infoContact['id'];
            $values[$contactName]['Nombre'] = $infoContact['name'];
            $values[$contactName]['Apellidos'] = $infoContact['lastname'];
            $values[$contactName]['Empresa'] = $infoContact['company'];
            $values[$contactName]['Email'] = $infoContact['email'];
            $values[$contactName]['Domicilio'] = $infoContact['address'];
            $values[$contactName]['Ciudad'] = $infoContact['city'];
            $values[$contactName]['Provincia'] = $infoContact['state'];
            $values[$contactName]['Cod. Postal'] = $infoContact['zipcode'];
            $values[$contactName]['Cod. Pais'] = $infoContact['country'];
            $values[$contactName]['Tfno.'] = $infoContact['phone'];
            $values[$contactName]['Documento Identidad'] = $infoContact['ic'];
            $values[$contactName]['Forma Legal'] = $infoContact['legal_form'];
        }
        return $values;
    }
    //SAVE CONTACT DETAILS VIRTUALNAME ADVANCE CONTACTS ENABLED
    public function set_contacts_advance_details($params){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $adminID    = $_SESSION['adminid'];
        $configLang = $vname_admin->get_config_lang($adminID);
        if(!isset($params['additionalfields'])){
            if($params['reg'])
                $regContact     = $params['reg'];
            else
                $regContact     = $_POST['sel']['reg'];
            if($params['admin'])
                $adminContact   = $params['admin'];
            else
                $adminContact   = $_POST['sel']['admin'];
            if($params['bill'])
                $billingContact = $params['bill'];
            else
                $billingContact = $_POST['sel']['billing'];
            if($params['tech'])
                $techContact    = $params['tech'];
            else
                $techContact    = $_POST['sel']['tech'];
            $configs = $vname_admin->config();
            $params = array_merge($params, $configs);
            if(isset($params['domainid']))
                $domainid = $params['domainid'];
            else
                $domainid = 0;
            if($domainid != 0)
                $params['additionalfields'] = $vname_domains->get_whmcs_additional_domains($domainid);
            $domainInfo = $vname_domains->get_whmcs_domain($params['domain'], $domainid);
            $domain = explode('.',$params['domain']);
            $params['domainid'] = $domainInfo['id'];
            $sld = $domain[0];
            $tld = substr($params['domain'], strlen($sld)+1);
            $params['tld'] = strtolower($tld);
            $params['sld'] = strtolower($sld);
        }
        else{
            $regContact     = $_POST['sel']['reg'];
            $adminContact   = $_POST['sel']['admin'];
            $billingContact = $_POST['sel']['billing'];
            $techContact    = $_POST['sel']['tech'];
        }

        $userID = $_SESSION['uid'];
        if(is_null($userID))
            $userID = $_POST['customerid'];

        $table  = 'tbldomainsadditionalfields';
        $tcpanel_contacts = array();
        $contact_types    = array();

        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];

        $regDis  = true;
        $admDis  = true;
        $techDis = true;
        $billDis = true;
        if(isset($domain_rules['.'.$params['tld']])){
            if($domain_rules['.'.$params['tld']]['contacts']['reg'] != 1)
                $regDis = false;
            if($domain_rules['.'.$params['tld']]['contacts']['admin'] != 1)
                $admDis = false;
            if($domain_rules['.'.$params['tld']]['contacts']['tech'] != 1)
                $techDis = false;
            if($domain_rules['.'.$params['tld']]['contacts']['billing'] != 1)
                $billDis = false;
        }
        if($regDis){
            if($regContact != $params['additionalfields']['regContact'] || !isset($params['additionalfields'])){
                if($regContact == 0){
                    $TCpanelWHMCS = $this->get_tcpanel_contact($userID, 1);
                    $TCpanelId = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                else{
                    $TCpanelWHMCS = $this->get_tcpanel_contact($regContact, 2);
                    $TCpanelId = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                $tcpanel_contacts[] = $TCpanelId;
                $contact_types[] = 'registrant';
                //$this->link_domain_contacts($params, $TCpanelId, $TCpanelWHMCS, 'regContact');
                $this->link_domain_contacts($params, $TCpanelId, $regContact, 'regContact');
            }
        }
        if($admDis){
            if($adminContact != $params['additionalfields']['adminContact'] || !isset($params['additionalfields'])){
                if($adminContact == 0){
                    $TCpanelWHMCS = $this->get_tcpanel_contact($userID, 1);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                else{
                    $TCpanelWHMCS = $this->get_tcpanel_contact($adminContact, 2);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                $tcpanel_contacts[] = $TCpanelId;
                $contact_types[] = 'administrative';
                //$this->link_domain_contacts($params, $TCpanelId, $TCpanelWHMCS, 'adminContact');
                $this->link_domain_contacts($params, $TCpanelId, $adminContact, 'adminContact');
            }
        }
        if($techDis){
            if($billingContact != $params['additionalfields']['billingContact'] || !isset($params['additionalfields'])){
                if($billingContact == 0){
                    $TCpanelWHMCS = $this->get_tcpanel_contact($userID, 1);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                else{
                    $TCpanelWHMCS = $this->get_tcpanel_contact($billingContact, 2);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                $tcpanel_contacts[] = $TCpanelId;
                $contact_types[] = 'billing';
                //$this->link_domain_contacts($params, $TCpanelId, $TCpanelWHMCS, 'billingContact');
                $this->link_domain_contacts($params, $TCpanelId, $billingContact, 'billingContact');
            }
        }
        if($billDis){
            if($techContact != $params['additionalfields']['techContact'] || !isset($params['additionalfields'])){
                if($techContact == 0){
                    $TCpanelWHMCS = $this->get_tcpanel_contact($userID, 1);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                else{
                    $TCpanelWHMCS = $this->get_tcpanel_contact($techContact, 2);
                    $TCpanelId    = $TCpanelWHMCS['id_contact_tcpanel'];
                }
                $tcpanel_contacts[] = $TCpanelId;
                $contact_types[] = 'technical';
                //$this->link_domain_contacts($params, $TCpanelId, $TCpanelWHMCS, 'techContact');
                $this->link_domain_contacts($params, $TCpanelId, $techContact, 'techContact');
            }
        }
        $error = false;
        foreach ($tcpanel_contacts as $key => $value){
            if(is_null($value))
                $error = true;
        }

        if($error == true){
            logModuleCall('virtualname', 'SetDomainContacts ERROR', array('contacts'=>$tcpanel_contacts), array('error' => $configLang['errorContactNotFound']), '', '');
            $params['error'] = 'panelContactError';
            return $params;
        }

        //IF AT LEST ONE OR MORE CONTACTS WAS CHANGED
        if(count($tcpanel_contacts) > 0){
            $response = $this->set_domain_contacts($params, $tcpanel_contacts, $contact_types);
            if($response['error']){
                $params['error'] = $response['error'];
                #ROLLBACK ON ERRORS
                foreach($contact_types as $contact_type)
                    $this->link_domain_contacts($params, $TCpanelId, $params['additionalfields'][$contact_type], $contact_type);
            }
        }
        return $params;
    }

    //SAVE CONTACT DETAILS VIRTUALNAME ADVANCE CONTACTS DISABLED
    public function set_contacts_simple_details($params){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $adminID    = $_SESSION['adminid'];
        $configLang = $vname_admin->get_config_lang($adminID);
        $contacts = array('registrant' => 'Registrant', 'administrative' => 'Admin', 'technical' => 'Technical', 'billing' => 'Billing');
        //CHECK CONTACT RULES
        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];
        foreach ($contacts as $key => $value){
            if($key == 'registrant')
                $contactName = 'Propietario';
            if($key == 'administrative')
                $contactName = 'Administrativo';
            if($key == 'technical')
                $contactName = 'Tecnico';
            if($key == 'billing')
                $contactName = 'Facturacion';

            if($domain_rules['.'.$params['tld']]){
                if($domain_rules['.'.$params['tld']]['contacts'][$langs[$key]] != 1)
                    continue;
            }

            if(!empty($params['contactdetails'][$contactName]['Contact Name'])) {
                $phone = $params['contactdetails'][$contactName]['Phone Number'];
                $contact_phone = substr($phone,strpos($phone, '.')+1);

                $contact[$key] = array(
                    'firstname' => trim($params['contactdetails'][$contactName]['First Name']),
                    'lastname'  => trim($params['contactdetails'][$contactName]['Last Name']),
                    'company'   => trim($params['contactdetails'][$contactName]['Company Name']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email Address']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Address 1']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['ZIP Code']),
                    'phonenumber' => trim($contact_phone));
            }
            else {
                $contact[$key]['firstname'] = $params['contactdetails'][$contactName]['Nombre'];
                $contact[$key]['lastname'] = $params['contactdetails'][$contactName]['Apellidos'];
                $contact[$key]['company'] = $params['contactdetails'][$contactName]['Empresa'];
                $contact[$key]['email'] = $params['contactdetails'][$contactName]['Email'];
                $contact[$key]['address1'] = $params['contactdetails'][$contactName]['Domicilio'];
                $contact[$key]['city'] = $params['contactdetails'][$contactName]['Ciudad'];
                $contact[$key]['state'] = $params['contactdetails'][$contactName]['Provincia'];
                $contact[$key]['postcode'] = $params['contactdetails'][$contactName]['Cod. Postal'];
                $contact[$key]['country'] = $params['contactdetails'][$contactName]['Cod. Pais'];
                $contact[$key]['phonenumber'] = $params['contactdetails'][$contactName]['Tfno.'];
                $contact[$key]['ic'] = $params['contactdetails'][$contactName]['Documento Identidad'];
                $contact[$key]['legal_form'] = $params['contactdetails'][$contactName]['Forma Legal'];
            }

            $check_contact = $this->validate_contact(0, array(), 0, $contact[$key]);
            if($check_contact['error']){
                foreach($check_contact['response'] as $field => $field_value)
                    $error_values .= ' '.$field;
                return array('error' => $value.' '.$check_contact['error'].$error_values);
            }
            $create_contact = $this->add_contact(0, array(), 0, $contact[$key]);
            if($create_contact['error']){
                foreach($create_contact['response'] as $field => $field_value)
                    $error_values .= ' '.$field;
                return array('error' => $value.' '.$create_contact['error'].$create_contact);
            }
            $contact_types[] = $key;
            $new_contacts[] = $create_contact['response']['id'];
        }

        $error = false;
        foreach ($new_contacts as $key => $value){
            if(is_null($value))
                $error = true;
        }
        if($error == true){
            logModuleCall('virtualname', 'SetDomainContacts ERROR', array('contacts'=>$new_contacts), array('error' => $configLang['errorContactNotFound']), '', '');
            $params['error'] = 'panelContactError';
            return $params;
        }
        //IF AT LEST ONE OR MORE CONTACTS WAS CHANGED
        if(count($new_contacts)>0)
            $this->set_domain_contacts($params, $new_contacts, $contact_types);

        return $params;
    }

    //GET NEW ADMIN CONTACT FOR TRANSFER ON RENEWAL
    public function transfer_contact_registrar($values, $registrar, $email){
        global $vname_admin;
        virtualname_init();
        $configarray  = $vname_admin->config();
        if(empty($email)){
            $email = $configarray['defaultDomainsMail'];
        }
        $same_mail_action = array('opensrs', 'opensrspro', 'enom', 'resellerclub', 'rrpproxy');
        if(in_array($registrar, $same_mail_action)){
            $old_email = $values['Admin']['Email'];
            $values['Admin']['Email'] = $email;
            $contacts = $values;
        }
        elseif($registrar == 'openprovider'){
            $old_email = $values['Admin']['Email Address'];
            $values['Admin']['Email Address'] = $email;
            $contacts = $values;
        }
        $response['contacts'] = $contacts;
        if($old_email)
            $response['old_email'] = $old_email;
        return $response;
    }

    //CREATE CONTACT ON VIRTUALNAME AND WHMCS
    public function create_contacts_from_domain($params){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $adminID    = $_SESSION['adminid'];
        $configLang = $vname_admin->get_config_lang($adminID);
        $langs = array('registrant'=>'reg','administrative'=>'admin','billing'=>'billing','technical'=>'tech');
        $contacts = array('registrant','administrative','billing','technical');
        //CHECK CONTACT RULES
        $configarray  = $vname_admin->config();
        $domain_rules = $configarray['domain_rules'];

        if($params['registrar'] == 'rrproxy'){
            $registrant_field = 'Owner';
            $email_field = 'Email';
        }
        elseif($params['registrar'] == 'openprovider'){
            $registrant_field = 'Owner';
            $email_field = 'Email Address';
        }
        else{
            $registrant_field = 'Registrant';
            $email_field = 'Email';
        }

        foreach ($contacts as $key){
            if($key == 'registrant')
                $contactName = $registrant_field;
            if($key == 'administrative')
                $contactName = 'Admin';
            if($key == 'technical')
                $contactName = 'Tech';
            if($key == 'billing')
                $contactName = 'Billing';

            if($domain_rules['.'.$params['tld']]){
                if($domain_rules['.'.$params['tld']]['contacts'][$langs[$key]] != 1)
                    continue;
            }

            if(empty($params['contactdetails'][$contactName][$email_field]) || is_null($params['contactdetails'][$contactName][$email_field]))
                continue;

            if($params['registrar'] == 'opensrs' || $params['registrar'] == 'opensrspro'){
                //OPENSRS
                $phone = $params['contactdetails'][$contactName]['Phone'];
                $contact_phone = substr($phone,strpos($phone, '.')+1);
                $contact[$key] = array(
                    'firstname' => trim($params['contactdetails'][$contactName]['First Name']),
                    'lastname'  => trim($params['contactdetails'][$contactName]['Last Name']),
                    'company'   => trim($params['contactdetails'][$contactName]['Organization Name']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Address 1']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['Postal Code']),
                    'phonenumber' => trim($contact_phone));
            }
            elseif($params['registrar'] == 'enom'){
                //ENOM
                $phone = $params['contactdetails'][$contactName]['Phone'];
                $contact_phone = substr($phone,strpos($phone, '.')+1);
                $contact_phone = str_replace('+', '', $contact_phone);
                $contact[$key] = array(
                    'firstname' => trim($params['contactdetails'][$contactName]['First Name']),
                    'lastname'  => trim($params['contactdetails'][$contactName]['Last Name']),
                    'company'   => trim($params['contactdetails'][$contactName]['Organisation Name']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Address 1']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['Postcode']),
                    'phonenumber' => trim($contact_phone));
            }
            elseif($params['registrar'] == 'resellerclub'){
                //RESELLERCLUB
                $fullname = $this->split_name($params['contactdetails'][$contactName]['Full Name']);
                $contact[$key] = array(
                    'firstname' => $fullname[0],
                    'lastname'  => $fullname[1],
                    'company'   => trim($params['contactdetails'][$contactName]['Company Name']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Address 1']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['Postcode']),
                    'phonenumber' => trim($params['contactdetails'][$contactName]['Phone Number']));
            }
            elseif($params['registrar'] == 'rrpproxy'){
                //RRPPROXY
                $phone = $params['contactdetails'][$contactName]['Phone'];
                $contact_phone = substr($phone,strpos($phone, '.')+1);
                $contact_phone = str_replace('+', '', $contact_phone);
                $contact[$key] = array(
                    'firstname' => trim($params['contactdetails'][$contactName]['First Name']),
                    'lastname'  => trim($params['contactdetails'][$contactName]['Last Name']),
                    'company'   => trim($params['contactdetails'][$contactName]['Organisation']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Street']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['Zip']),
                    'phonenumber' => trim($contact_phone));
            }
            elseif($params['registrar'] == 'openprovider'){
                //OPENPROVIDER
                $phone = $params['contactdetails'][$contactName]['Phone Number'];
                $contact_phone = substr($phone,strpos($phone, '.')+1);
                $contact_phone = str_replace('+', '', $contact_phone);
                $contact[$key] = array(
                    'firstname' => trim($params['contactdetails'][$contactName]['First Name']),
                    'lastname'  => trim($params['contactdetails'][$contactName]['Last Name']),
                    'company'   => trim($params['contactdetails'][$contactName]['Company Name']),
                    'email'     => trim($params['contactdetails'][$contactName]['Email Address']),
                    'country'   => trim($params['contactdetails'][$contactName]['Country']),
                    'state'     => trim($params['contactdetails'][$contactName]['State/Region']),
                    'city'      => trim($params['contactdetails'][$contactName]['City']),
                    'address1'   => trim($params['contactdetails'][$contactName]['Address']),
                    'postcode'   => trim($params['contactdetails'][$contactName]['Zip Code']),
                    'phonenumber' => trim($contact_phone));
            }
            //CHECK CONTACT VIRTUALNAME
            $check_contact = $this->validate_contact(0, array(), 0, $contact[$key]);
            if($check_contact['error'] AND $key == 'registrant'){
                foreach($check_contact['response'] as $field => $field_value)
                    $error_values .= ' '.$field;
                $values['error'] = $value.' '.$check_contact['error'].$error_values;
                return $values;
            }
        }
        foreach($contact as $key => $value){
            //CREATE CONTACT VIRTUALNAME
            $create_contact = $this->add_contact(0, array(), 0, $value);
            if($create_contact['error'] AND $key == 'registrant'){
                foreach($create_contact['response'] as $field => $field_value)
                    $error_values .= ' '.$field;
                return array('error' => $value.' '.$create_contact['error'].$create_contact);
            }
            else{
                $TCpanelContact = $create_contact['response']['id'];
            }
            //CREATE CONTACT WHMCS
            $userid = $params['userid'];
            if(!function_exists('addContact'))
                require_once(realpath(dirname(__FILE__).'/../../../../..').'/includes/clientfunctions.php');
            $contactID = addContact($userid, $value['firstname'], $value['lastname'], $value['company'], $value['email'], $value['address1'], '', $value['city'], $value['state'], $value['postcode'], $value['country'], $contact_phone, '', '',0, 0, 0, 0, 0);
            if($contactID){
                $table = 'mod_contacts_virtualname_tcpanel';
                //CHECK IF CONTACT EXIST
                $where  = array('id_contact_whmcs'=>$contactID,'contact_type'=>'2');
                $select = $this->get_identification_number($contactID, 2);
                $legalContact = $this->get_legal_form_contact($contactID, 2);
                if($select == 'EMPTY'){
                    $values = array('id_contact_whmcs'=>$contactID,'id_contact_tcpanel'=>$TCpanelContact, 'contact_type'=>2, 'identification_number' => $ic);
                    insert_query($table,$values);
                }
                else{
                    $update = array('identification_number'=>$identification_number,'id_contact_tcpanel'=>$TCpanelid);
                    update_query($table,$update,$where);
                }
            }
            else{
                if($key != 'registrant')
                    continue;
                else
                    return array('error' => 'Can\'t create REGISTRANT CONTACT');
            }
            $transfers_contacts[$key] = $contactID;
        }
        return $transfers_contacts;
    }
    function split_name($fullname) {
        $fullname = trim($fullname);
        $last_name = (strpos($fullname, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $fullname);
        $first_name = trim(preg_replace('#'.$last_name.'#', '', $fullname));
        if(empty($last_name))
            $last_name = $first_name;
        return array($first_name, $last_name);
    }
}
?>