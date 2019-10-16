<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.8.X
// * @copyright Copyright (c) 2019, Virtualname
// * @version 1.1.19
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: VIRTUALNAME DOMAIN class
// *************************************************************************
class Virtualname_domains extends Virtualname_api{
	//GET AVAILABLE DOMAINS TLDs FROM TCPANEL
	public function available_tlds($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = 'domains';
	    $action = 'extensions.json';
	    $RESTful= 'GET';
	    $params['action'] = 'Available_TLDS';
	    try{
	        $request = $this->api_call($params,$fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    return $request;
	}
	//GET IF THE DOMAIN IS AVAILABLE TO REGISTER
	public function available($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = 'domains/domains/register';
	    $action = 'available.json';
	    $RESTful= 'POST';
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    $fields['json'] = json_encode(array('domains'=>array($domain)));
	    $params['action'] = 'Available';
	    try{
	        $request = $this->api_call($params,$fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    return $request;
	}
	//GET IF THE DOMAIN IS AVAILABLE FOR TRANSFER
	public function transfer_available($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = 'domains/domains/transfer';
	    $action = 'available.json';
	    $RESTful= 'POST';
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    $fields['json'] = json_encode(array('domains'=>array($domain)));
	    $params['action'] = 'Transfer Available';
	    try{
	        $request = $this->api_call($params,$fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    return $request;
	}
	//DOMAIN CACHE
	public function get_domain_cache($domain){
		//CACHE FILE, SECONDS
		$cache_life = 120;
		//FILE DIR + FILE NAME
		$temp_file = sys_get_temp_dir().'/'.$domain;
		//FILE LAST UPDATE
		$file_time = filemtime($temp_file);
		$seconds_active = time() - $file_time;
		if(file_exists($temp_file) && $file_time && $seconds_active <= $cache_life){
			$content = file_get_contents($temp_file);
			$content_request = json_decode($content, true);
			return $content_request;
		}
		return false;
	}
	public function set_domain_cache($domain, $request){
		//FILE DIR + FILE NAME
		$temp_file = sys_get_temp_dir().'/'.$domain;
		$this->destroy_domain_cache($domain);
	    tempnam(sys_get_temp_dir(), $domain);
	    $content = json_encode($request);
	    file_put_contents($temp_file, $content);
	}
	public function destroy_domain_cache($domain){
		$temp_file = sys_get_temp_dir().'/'.$domain;
		if(file_exists($temp_file))
		    unlink($temp_file);
	}
	//GET DOMAIN DETAILS
	public function view_domain_info($params) {
	    $this->check_configuration($params);
	    $adminID = $_SESSION['adminid'];
   	    $configLang = $this->get_config_lang($adminID);
	    $fields = array();
	    $module = 'domains';
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    $action = 'domains.json?name='.$domain;
	    $RESTful= 'GET';
	    $params['action'] = 'ViewDomainInfo';
	    //CACHE
	    /*try{
	    	//VIEW DOMAIN INFO CACHE
	    	$cache_info = $this->get_domain_cache($domain);
	    	if(!$cache_info){
		        $request = $this->api_call($params, $fields, $module, $action, $RESTful);
		        $this->set_domain_cache($domain, $request);
		    }
		    else
		    	$request = $cache_info;
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }*/
	    //NO CACHE
	    try{
	    	$request = $this->api_call($params, $fields, $module, $action, $RESTful);
		}catch (Exception $e){
	        return ($e->getMessage());
	    }
  	    if($request['status']['code'] == 404 || (!$request['response'][0] && $request['status']['code'] == 200)){
	    	//DOMAIN NOT FOUND
	    	$request['status']['code'] = 404;
	    	$request['status']['description'] = $configLang['resource_not_found'];
	    }
	    return $request;
	}
	public function view_domain_info_hook($domain) {
		$params = $this->config();
	    $this->check_configuration($params);
	    $adminID = $_SESSION['adminid'];
   	    $configLang = $this->get_config_lang($adminID);
	    $fields = array();
	    $module = 'domains';
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($domain)));
	    $action = 'domains.json?name='.$domain;
	    $RESTful= 'GET';
	    $params['action'] = 'ViewDomainInfo';
	    //CACHE
	    /*try{
	    	//VIEW DOMAIN INFO CACHE
	    	$cache_info = $this->get_domain_cache($domain);
	    	if(!$cache_info){
		        $request = $this->api_call($params, $fields, $module, $action, $RESTful);
		        $this->set_domain_cache($domain, $request);
		    }
		    else
		    	$request = $cache_info;
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }*/
	    //NO CACHE
	    try{
			$request = $this->api_call($params, $fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    $info = $request['response'][0];

	    if($info == 'Connection error' || !$info)
	    	return false;

	    $response = array();

        $response['status']['value'] = $configLang[$info['product_info']['product_status']];

		switch ($info['product_info']['product_status']) {
		    case 'active':
		        $response['status']['class'] = 'active';
		        break;
		    case 'expired':
		    case 'inactive':
		    	$response['status']['class'] = 'suspended';
		    	break;
		    case 'transfer_rejected':
		    case 'outbound_transfer':
		    case 'redemption':
		    case 'transfer_expired':
		        $response['status']['class'] = 'terminated';
		        break;
		    case 'active_pending_registrant_approval':
		    case 'pending':
		    case 'paid':
		    case 'transferring':
		    case 'transfer_requested':
		    case 'transfer_initiated':
		    case 'transfer_email_sent':
		    case 'transfer_approved':
		    case 'transfer_finished':
		    case 'transfer_waiting_unlocked':
		    case 'transfer_waiting_admin':
		    case 'transfer_waiting_registrar':
		    case 'transfer_order_locked':
		    case 'transfer_waiting_authcode':
		    case 'transfer_email_not_sent':
		    case 'transfer_waiting_pending_registrant_approval':
		        $response['status']['class'] = 'pending';
		        break;
		    default:
		        $response['status']['value'] = $info['product_info']['product_status'] ;
		        $response['status']['class'] = 'pending';
		}


	    if($info['auto_renew']){
	        $response['auto_renew']['value'] = $configLang['active'];
	        $response['auto_renew']['class'] = 'active';
	    }
	    else{
	        $response['auto_renew']['value'] = $configLang['inactive'];
	        $response['auto_renew']['class'] = 'terminated';
	    }

	    if($info['privacy']){
	        $response['privacy']['value'] = $configLang['active'];
	        $response['privacy']['class'] = 'active';
	    }
	    else{
	        $response['privacy']['value'] = $configLang['inactive'];
	        $response['privacy']['class'] = 'terminated';
	    }

	    if($info['protection']){
	        $response['protection']['value'] = $configLang['active'];
	        $response['protection']['class'] = 'active';
	    }
	    else{
	        $response['protection']['value'] = $configLang['inactive'];
	        $response['protection']['class'] = 'terminated';
	    }

	    $product_expiration = $info['product_info']['product_expiration'];
	    if(!$product_expiration)
	    	$product_expiration = 'N\A';
	    $response['expiration_date'] = $product_expiration;
	    $response['created_at'] = date('Y-m-d',strtotime($info['created_at']));
	    $response['reg_id'] = $info['contacts']['registrant']['id'];
	    $response['adm_id'] = $info['contacts']['administrative']['id'];
	    $response['bill_id'] = $info['contacts']['billing']['id'];
	    $response['tech_id'] = $info['contacts']['technical']['id'];
	    $response['domain_id'] = $info['id'];
	    return $response;
	}
	//GET IF CURRENT DOMAIN WAS RENEWED AFTER SET TIME IN CONFIG
	public function check_secure_renovation($domain, $params){
	    $table  = 'tblactivitylog';
	    $fields = 'date';
	    $sql    = 'SELECT '.$fields.' from '.$table;
	    $sql   .= ' WHERE LOWER(description) = \'renew-'.$domain.'\' AND DATE_ADD('.$table.'.date, INTERVAL '.$params['secureRenovation'].' HOUR) >= NOW()';
	    $sql   .= ' order by id DESC limit 1';
	    $result = mysql_query($sql);
	    // or die('ERR: ' . mysql_error());
	    if(mysql_num_rows($result) > 0)
	        return false;
	    else
	        return true;
	}
	//GET WHMCS DOMAIN VALUES
	public function get_whmcs_domain($domain, $domainid){
	    $table = 'tbldomains';
	    $fields = 'id,userid,registrationdate,expirydate,nextduedate,status,donotrenew,recurringamount,firstpaymentamount,orderid,domain,registrar';
	    if($domainid != 0){
	        $where = array('id'=>$domainid);
	        $result = select_query($table,$fields,$where);
	    }
	    else{
	    	$sql = 'select '.$fields.' from '.$table.' where BINARY domain = \''.$domain.'\'';
	        $result = mysql_query($sql);
	    }
	    $data = mysql_fetch_array($result);
	    return $data;
	}
    //SET DOMAIN CONTACTS
    public function set_domain_contacts($params, $contactID, $contactType){
        //EXCLUDE clientsdomains admin page to set domain contacts
        if(basename($_SERVER['SCRIPT_NAME'],'.php') == 'clientsdomains'){
            return 0;
        }
        $this->check_configuration($params);
        if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
        if(!class_exists('Punycode'))
            @include_once('class.punicode.php');
        $Punycode = new Punycode();
        $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
        $domain_info = $this->view_domain_info($params);
        if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
            $values['error'] = $domain_info['status']['description'];
        }
        else{
            $fields = array();
            $module = 'domains/domains';
            $action = $domain_info['response'][0]['id'].'.json';
            $RESTful= 'PATCH';
            if(is_array($contactID) AND is_array($contactType)){
                $contacts = array();
                for($i=0;$i<count($contactID);$i++){
                    $contacts[$contactType[$i]] = $contactID[$i];
                }
                $fields['json'] = json_encode(array('domain'=>array('contacts'=>$contacts)));
            }
            else
                $fields['json'] = json_encode(array('domain'=>array('contacts'=>array($contactType=>$contactID))));
            $params['action'] = 'SetDomainContacts';

            try{
                $request = $this->api_call($params,$fields, $module, $action, $RESTful);
            }catch (Exception $e){
                return ($e->getMessage());
            }
            if($request['status']['code']< 200 || $request['status']['code'] > 299){
                $values['error'] = $request['status']['description'];
                if(isset($request['response']['name']))
                    $values['error'] .= ': '.implode(',', $request['response']['name']);
                if(isset($request['response']['base']))
                    $values['error'] .= ': '.implode(',', $request['response']['base']);
            }
            //logactivity('['.$domain.'] - ['.$request['status']['code']);
        }
        return $values;
    }
    //SET DOMAIN CONTACTS UPDATE
    public function set_domain_contacts_update($params, $contacts, $domainID){
        $fields = array();
        $module = 'domains/domains';
        $action = $domainID.'.json';
        $RESTful= 'PATCH';
        $fields['json'] = json_encode(array('domain'=>array('contacts'=>$contacts)));
        $params['action'] = 'SetDomainContactsUpdate';
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
        return $values;
    }
	//GET ALL CLIENTS DOMAINS
	public function client_domains($contactid, $type){
	    if($type == 2){
	        $contact = Virtualname_contacts::get_contact_whmcs($contactid);
	        $userid = $contact['userid'];
	    }
	    else
	        $userid = $contactid;
	    $table = 'tbldomains';
	    $fields = 'id,domain,status';
	    $where = array('userid'=>$userid);
	    $result = select_query($table,$fields,$where);
	    $data = array();
	    while ($row = mysql_fetch_array($result)){
	        $data[] = $row['domain'];
	    }
	    return $data;
	}
	//GET WHMCS DOMAIN ADDITIONAL DOMAIN FIELDS
	public function get_whmcs_additional_domains($domainid){
	    $table  = 'tbldomainsadditionalfields';
	    $fields = 'name, value';
	    $where  = array('domainid'=>$domainid);
	    $result = select_query($table,$fields,$where);
	    $dataAdditionals = array();
	    while ($data = mysql_fetch_array($result)){
	        $dataAdditionals[$data['name']] = $data['value'];
	    }
	    return $dataAdditionals;
	}
	//GET ALL DOMAIN CONTACTS
	public function get_contacts_from_domain($domainID){
	    $table = 'tbldomains';
	    $fields = 'userid';
	    $where = array('id'=>$domainID);
	    $result = select_query($table,$fields,$where);
	    $row = mysql_fetch_array($result);
	    $userID = $row['userid'];
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
	//WHMCS SYNC TRANSFER STATUS AND DATES FROM DOMAIN
	public function transfer_sync($params) {
	    $this->check_configuration($params);
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    $domain_info = $this->view_domain_info($params);
	    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
	        $values['error'] = $domain_info['status']['description'];
	    }
	    else{
	        $expirydate = $domain_info['response'][0]['product_info']['product_expiration'];
	        $status     = $domain_info['response'][0]['product_info']['product_status'];
	        if($status == 'active') {
	            $values['completed'] = true;
	            $values['expirydate'] = $expirydate;
	        }
	    }
	    return $values;
	}
	//GET ALL DOMAINS FROM PANEL
	public function get_all_domains_tools($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = 'domains';
	    $action    = 'domains.json';
	    $urlParams = false;
	    $RESTful= 'GET';
	    $params['action'] = 'GetAllDomainInfo';
	    $list_config = array();
	    if($params['limit']){
	        if(!$urlParams){
	            $action .= '?limit='.$params['limit'];
	            $urlParams = true;
	        }
	        else
	            $action .= '&limit='.$params['limit'];
	    }
	    if($params['offset']){
	        if(!$urlParams){
	            $action .= '?offset='.$params['offset'];
	            $urlParams = true;
	        }
	        else
	            $action .= '&offset='.$params['offset'];
	    }
	    if($params['until']){
	        if(!$urlParams){
	            $action .= '?until='.$params['until'];
	            $urlParams = true;
	        }
	        else
	            $action .= '&until='.$params['until'];
	    }
	    if($params['status']){
	        if(!$urlParams){
	            $action .= '?status='.$params['status'];
	            $urlParams = true;
	        }
	        else
	            $action .= '&status='.$params['status'];
	    }
	    if($params['contact']){
	        $getContact = array('contacts'=> array('registrant'=>$params['contact']));
	        if(!$urlParams){
	            $action .= '?'.http_build_query($getContact);
	            $urlParams = true;
	        }
	        else
	            $action .= '&'.$urlContact;
	    }

	    try{
	        $request = $this->api_call($params,$fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }

	    return $request;
	}
	//WHMCS SYNC STATUS AND DATES FROM DOMAIN
	public function sync($params) {
	    $this->check_configuration($params);
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode')){
	        if(!@include_once('class.punicode.php')){
	            $Punycode   = new Punycode();
	            $domain     = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	        }
	        else{
	            $domain = strtolower(trim($params['sld'].'.'.$params['tld']));
	        }
	    }
	    $domain_info = $this->view_domain_info($params);
	    if($domain_info['status']['code']< 200 || $domain_info['status']['code'] > 299){
	        $values['error'] = $domain_info['status']['description'];
	    }
	    else{
	        $expirydate = $domain_info['response'][0]['product_info']['product_expiration'];

	        $status = $domain_info['response'][0]['product_info']['product_status'];
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
	//SYNC STATUS AND DATES FROM DOMAIN WITHOUT CALL
	public function sync_domain_list($params, $domain_info) {
	    $this->check_configuration($params);
	    $adminID    = $_SESSION['adminid'];
	    $configLang = $this->get_config_lang($adminID);
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    if(!$domain_info){
	        //ITS MEAN THAT THE DOMAIN NOT EXIST
	        $values['error'] = 'Domain not found';
	    }
	    else{
	        if($params['updateSync']){
	            $expirydate = $domain_info['product_info']['product_expiration'];
	            //UPDATE EXPIRATION DATE
	            $sql = 'UPDATE tbldomains SET expirydate = \''.$expirydate.'\'';
	            //UPDATE STATUS
	            $status = $domain_info['product_info']['product_status'];
	            if($status == 'active') {
	                $sql .= ', `status` = \'Active\'';
	            }
	            elseif($status == 'pending' || $status == 'paid'){
	                $sql .= ', `status` = \'Pending\'';
	            }
	            elseif($status == 'transferring' || $status == 'transfer_requested' || $status == 'transfer_initiated' ||
	                     $status == 'transfer_email_sent' || $status == 'transfer_rejected' || $status == 'transfer_approved' ||
	                     $status == 'transfer_finished' || $status == 'transfer_expired'){
	                $sql .= ' AND `status` = \'Pending Transfer\'';
	            }
	            elseif($status == 'cancelled' || $status == 'outbound_transfer' || $status == 'inactive'){
	                $sql .= ', `status` = \'Cancelled\'';
	            }
	            elseif($status == 'expired' || $status == 'redemption'){
	                $sql .= ', `status` = \'Expired\'';
	            }
	            else{
	                $sql .= ', `status` = \'Pending\'';
	            }
	            //UPDATE DOMAIN PROTECTION
	            if($domain_info['privacy'] == TRUE){
	                $newidprotection = 'on';
	                $sql .= ', `idprotection`=\'1\'';
	            }
	            elseif($domain_info['privacy'] == FALSE){
	                $newidprotection = '';
	                $sql .= ', `idprotection`=\'0\'';
	            }
	            //UPDATE BBDD
	            $sql .= ' WHERE id=\''.$params['domainid'].'\'';
	            $res = mysql_query($sql);
	            // or die('ERR: ' . $sql .mysql_error($sql));
	            $updated = mysql_affected_rows();
	            $values = array('message' => 'Successfull sync domain',
	                            'updated' => $updated);
	        }
	        else{
	            $expirydate = $domain_info['product_info']['product_expiration'];
	            //UPDATE EXPIRATION DATE
	            $sql = 'select * from tbldomains where expirydate = \''.$expirydate.'\'';
	            //UPDATE STATUS
	            $status = $domain_info['product_info']['product_status'];
	            if($status == 'active') {
	                $sql .= ' AND `status` = \'Active\'';
	            }
	            elseif($status == 'pending'){
	                $sql .= ' AND `status` = \'Pending\'';
	            }
	            elseif($status == 'transferring' || $status == 'transfer_requested' || $status == 'transfer_initiated' ||
	                     $status == 'transfer_email_sent' || $status == 'transfer_rejected' || $status == 'transfer_approved' ||
	                     $status == 'transfer_finished' || $status == 'transfer_expired'){
	                $sql .= ' AND `status` = \'Pending Transfer\'';
	            }
	            elseif($status == 'cancelled' || $status == 'outbound_transfer' || $status == 'inactive'){
	                $sql .= ' AND `status` = \'Cancelled\'';
	            }
	            elseif($status == 'expired' || $status == 'redemption'){
	                $sql .= ' AND `status` = \'Expired\'';
	            }
	            else{
	                $sql .= ' AND `status` = \'Pending\'';
	            }
	            //UPDATE DOMAIN PROTECTION
	            if($domain_info['privacy'] == TRUE){
	                $newidprotection = 'on';
	                $sql .= ' AND `idprotection`=\'1\'';
	            }
	            elseif($domain_info['privacy'] == FALSE){
	                $newidprotection = '';
	                $sql .= ' AND `idprotection`=\'0\'';
	            }
	            //UPDATE BBDD
	            $sql .= ' AND id=\''.$params['domainid'].'\'';
	            $res = mysql_query($sql);
	            // or die('ERR: ' . mysql_error());
	            if(!mysql_num_rows($res))
	                $updated = 1;
	            else
	                $updated = 0;
	            $values = array('message' => 'Successfull sync domain',
	                            'updated' => $updated);
	        }
	    }
	    return $values;
	}
	//SYNC CREATE WHMCS CONTACT
	public function sync_domain_contacts($params, $domain_info, $allWHMCSTcpanelType){
	    $this->check_configuration($params);
	    if(isset($params['original']['sld'])){if($params['sld'] != $params['original']['sld']){$params['sld'] = $params['original']['sld'];}}
        if(isset($params['original']['tld'])){if($params['tld'] != $params['original']['tld']){$params['tld'] = $params['original']['tld'];}}
	    if(!class_exists('Punycode'))
	        @include_once('class.punicode.php');
	    $Punycode = new Punycode();
	    $domain = $Punycode->decode(strtolower(trim($params['sld'].'.'.$params['tld'])));
	    if(!$domain_info){
	        //ITS MEAN THAT THE DOMAIN NOT EXIST
	        $values['error'] = 'Domain not found';
	    }
	    else{
	        $contacts = $domain_info['contacts'];
	        $status = $domain_info['product_info']['product_status'];
	        $userID = $params['userid'];
	        $domainid = $params['domainid'];
	        if($status == 'active'){
	            $checkedTCpanelContacts = array();
	            $res = array();
	            foreach($contacts as $key => $contact){
	                $TCpanelid = $contact['id'];
	                if($TCpanelid != 0){
	                    //IF CONTACT WAS CHECKED IN OTHER OF DOMAIN CONTACTS
	                    if(in_array($TCpanelid, $checkedTCpanelContacts)){
	                        $res['message'] = 'Checked';
	                        $values['synContact'][$key] = $res;
	                    }
	                    else{
	                        $checkedTCpanelContacts[] = $TCpanelid;
	                        $response  = $allWHMCSTcpanelType[$TCpanelid];
	                        if($response){
	                            $type      = $response['contact_type'];
	                            $ic_number = $response['identification_number'];
	                            $legal_form= $response['legal_form'];
	                            $contactID = $response['id_contact_whmcs'];
	                        }
	                        else{
	                            $type = 0;
	                        }

	                        if($type == 2){
	                            //CLIENT CONTACT EXIST
	                            $res = Virtualname_contacts::update_whmcs_contact($contact, $contactID, $params['updateSync']);
	                        }
	                        elseif($type == 1){
	                            //CLIENT DATA
	                            $res = Virtualname_contacts::check_whmcs_client($userID, $contact, $ic_number, $legal_form, $params['updateSync']);
	                        }
	                        elseif($type == 0){
	                            //CONTACT VINCULATION NOT FOUND. CHECK IF EXIST NOT USE CONTACT
	                            $resContact = Virtualname_contacts::get_whmcs_contacts($userID, $contact, $allWHMCSTcpanelType);
	                            if($resContact['contactID'] == 0){
	                                if($params['updateSync']){
	                                    $res = Virtualname_contacts::add_whmcs_contact($userID, $contact);
	                                    $res['new'] = array('id_contact_whmcs'=> $res['contactid'], 'id_contact_tcpanel'    => $TCpanelid,
	                                                        'contact_type'    => '2',               'identification_number' => $contact['ic']);
	                                }
	                                else{
	                                    $res['message'] = 'Vinculated';
	                                }
	                            }
	                            else{
	                                if($params['updateSync']){
	                                    $res['new'] = array('id_contact_whmcs'=>$resContact['contactID'], 'id_contact_tcpanel'   => $TCpanelid,
	                                                        'contact_type'    =>$resContact['type'],      'identification_number'=> $contact['ic']);
	                                    Virtualname_contacts::add_tcpanel_contact($resContact['contactID'],$TCpanelid, $resContact['type'], $contact['ic'], $contact['legal_form']);
	                                }
	                                $res['message'] = 'Vinculated';
	                            }
	                            $allWHMCSTcpanelType[$res['new']['id_contact_tcpanel']] = $res['new'];
	                        }
	                    }
	                }
	                $values['synContact'][$key] = $res;
	            }
	        }
	        else{
	            $values['error'] = 'Not active domain';
	        }
	    }
	    return $values;
	}

    //OUTBOUND TRANSFERS MAILING
    public function outbounds_mailing(){
        $domainOutbounds     = '';
        $domainOutboundsList = array();
        $resultList = select_query('mod_virtualname_outbounds', 'value', array('type' => 'syncList'));
        while($dataList = mysql_fetch_assoc($resultList)){
            $outbound_list = json_decode($dataList['value'], true);
            foreach($outbound_list as $key => $value){
                $domainOutboundsList[]  = $key;
            }
        }
        if(count($domainOutboundsList)>0){
            //CLIENTS NOTIFICATION
            $config = $this->config();
            if(isset($config['outboundTransferMailing'])){
                if($config['outboundTransferMailing'] == 'on'){
                    $client_subject  = 'Dominio cancelado por transferencia saliente / Domain cancelled by outgoing transfer';
                    foreach($domainOutboundsList as $outboundDomain){
                        $domainMailing = $this->get_whmcs_domain($outboundDomain, 0);
                        if(isset($domainMailing['userid']) AND $domainMailing['status'] != 'Cancelled'){
                            $client_body  = '<p>El siguiente dominio ha sido cancelado en nuestro sistema por ser transferido a otro agente registrador.</p>';
                            $client_body .= '<p>The following domain has been canceled in our system to be transferred to another registrar.</p>';
                            $client_body .= '<p><b><li>'.$outboundDomain.'</li></b></p>';
                            delete_query('tblemailtemplates', array('name' => 'Mass Mail Template'));
                            insert_query('tblemailtemplates', array('type' => 'general',
                                                                    'name' => 'Mass Mail Template',
                                                                    'subject' => html_entity_decode($client_subject),
                                                                    'message' => html_entity_decode($client_body)));
                            $messagename = 'Mass Mail Template';
                            sendMessage($messagename, $domainMailing['userid']);
                            delete_query('tblemailtemplates', array('name' => 'Mass Mail Template'));
                            $domainOutbounds       .= '<p>'.$outboundDomain.'</p>';
                        }
                    }
                }
                else{
                    foreach($domainOutboundsList as $outboundDomain){
                        $domainMailing = $this->get_whmcs_domain($outboundDomain, 0);
                        if(isset($domainMailing['userid']) AND $domainMailing['status'] != 'Cancelled'){
                            $domainOutbounds       .= '<p>'.$outboundDomain.'</p>';
                        }
                    }
                }
            }
            if($domainOutbounds != ''){
                //ADMIN NOTIFICATION
                $admin_subject  = 'WHMCS-Virtualname Outbounds Transfers - Transferencias Salientes';
                $admin_body     = '<p>Listado de dominios perdidos por transferencia saliente y cuyo estado es actualizado a CANCELADO.</p>';
                $admin_body    .= '<p>List of lost domains by outgoing transfer and whose status is updated to CANCELLED.</p><p></p>'.$domainOutbounds;
                $this->send_domains_notification($config['defaultAdminRoles'], $admin_subject, $admin_body);
            }
        }
    }

    //TCPANEL - GET DOMAINS TOTALS
    public function tools_total_count(){
        $resultList = select_query('mod_virtualname_tools', 'value', array('type' => 'syncList'));
        $num_rows   = mysql_num_rows($resultList);
        $res = array('total_count' => $num_rows*250);
        return $res;
    }

    //TCPANEL - DOMAINS SYNC CONTACTS ALL CUSTOMERS
    public function tools_all_contactsync($nloop, $updateSync){
        if($nloop == -1){
            $resultList = select_query('mod_virtualname_tools', 'value', array('type' => 'syncList'));
        }
        else{
            $config = '\'syncList'.($nloop*250).'\',';
            for($i=1;$i<=7;$i++){
                $config .= '\'syncList';
                $config .= ($nloop+$i)*250;
                $config .= '\'';
                if($i!=7) $config .= ',';
            }
            $sql    = 'select value from mod_virtualname_tools where config in ('.$config.')';
            $resultList = mysql_query($sql);
        }

        if(mysql_num_rows($resultList) == 0)
            return 'finishSync';

        $domainList = array();
        $whereList  = array();
        while($dataList = mysql_fetch_assoc($resultList)){
            $list = json_decode($dataList['value'], true);
            $addToList = array();
            foreach($list as $key => $value){
                $addToList[$key]['product_info'] = $value['product_info'];
                $addContact = array();
                foreach($value['contacts'] as $keyContact => $valueContact){
                    $addContact[$keyContact]['id']      = $valueContact['id'];
                    $addContact[$keyContact]['ic']      = $valueContact['ic'];
                    $addContact[$keyContact]['name']    = $valueContact['name'];
                    $addContact[$keyContact]['lastname']= $valueContact['lastname'];
                    $addContact[$keyContact]['company'] = $valueContact['company'];
                    $addContact[$keyContact]['email']   = $valueContact['email'];
                    $addContact[$keyContact]['address'] = $valueContact['address'];
                    $addContact[$keyContact]['city']    = $valueContact['city'];
                    $addContact[$keyContact]['state']   = $valueContact['state'];
                    $addContact[$keyContact]['zipcode'] = $valueContact['zipcode'];
                    $addContact[$keyContact]['country'] = $valueContact['country'];
                    $addContact[$keyContact]['phonecc'] = $valueContact['phonecc'];
                    $addContact[$keyContact]['phone']   = $valueContact['phone'];
                }
                $addToList[$key]['contacts'] = $addContact;
                $domainList[$key] = $addToList[$key];
                $whereList[] = $key;
            }
        }


        $where     = array('registrar' => 'virtualname',
                           'domain'    => array('sqltype'=>'IN','values'=>$whereList));
        $sort      = 'userid';
        $sortorder = 'ASC';
        $resultClientsDomains = select_query('tbldomains', '*', $where, $sort, $sortorder);

        $updatedDomains     = 0;
        $vinculatedDomains  = 0;
        $createdDomains     = 0;
        $checkedDomains     = 0;
        $errorDomains       = 0;
        $updatedList        = array();

        $switchClient = 0;
        $responseClients = array();

        $allRows         = mysql_num_rows($resultClientsDomains);
        $domainCount     = 0;

        $allWHMCSTcpanelType = Virtualname_contacts::get_all_whmcs_tcpanel_type_indexed();

        while($data = mysql_fetch_array($resultClientsDomains)){
            $domainCount++;

            if($switchClient == 0){
                $switchClient = $data['userid'];
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' ('.$dataClient['email'].')';
            }
            elseif($switchClient != $data['userid']){
                //ADD NEW RESPONSE
                $response   = array();
                $total = $updatedDomains+$vinculatedDomains+$createdDomains+$checkedDomains+$errorDomains;
                if($total > 0){
                    $response['updated_number']     = $updatedDomains;
                    $response['vinculated_number']  = $vinculatedDomains;
                    $response['created_number']     = $createdDomains;
                    $response['checked_number']     = $checkedDomains;
                    $response['error_number']       = $errorDomains;
                    $response['updated_list']       = $updatedList;
                    $response['client_id']          = $switchClient;
                    $response['client_name']        = $clientName;
                    $response['total']              = $total;
                    $responseClients[] = $response;
                }
                //SET INIT
                $updatedDomains     = 0;
                $vinculatedDomains  = 0;
                $createdDomains     = 0;
                $checkedDomains     = 0;
                $errorDomains       = 0;
                unset($updatedList);
                $updatedList = array();
                $switchClient = $data['userid'];
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' ('.$dataClient['email'].')';
            }
            $data['domain'] = strtolower($data['domain']);
            $getDomainsData = $domainList[$data['domain']];
            if($getDomainsData){
                //$updatedList  .= $data['domain'].'[';
                //$updatedList[$data['domain']] = array();
                $domain       = explode('.',$data['domain']);
                $sld          = $domain[0];
                $tld          = substr($data['domain'], strlen($sld)+1);
                $params       = $this->config();
                $params['tld'] = $tld;
                $params['sld'] = $sld;
                $params['domainid']  = $data['id'];
                $params['userid']    = $data['userid'];
                $params['updateSync']= $updateSync;
                $response_contacts = $this->sync_domain_contacts($params, $getDomainsData, $allWHMCSTcpanelType[$switchClient]);
                if(!$response_contacts['error']){
                    foreach($response_contacts['synContact'] as $key => $value){
                        if(!$value['error']){
                            if($value['new']){
                                $allWHMCSTcpanelType[$switchClient][$value['new']['id_contact_tcpanel']] = $value['new'];
                            }
                            if($value['message'] == 'Updated'){
                                $color = 'forestgreen';
                                $updatedDomains++;
                            }
                            elseif($value['message'] == 'Created'){
                                $color = 'deepskyblue';
                                $createdDomains++;
                            }
                            elseif($value['message'] == 'Checked'){
                                $color = 'blue';
                                $checkedDomains++;
                            }
                            elseif($value['message'] == 'Vinculated'){
                                $color = 'orange';
                                $vinculatedDomains++;
                            }
                            $updatedList[$data['domain']][$key] = array('color'=>$color,'message'=>$value['message']);
                            //$updatedList[$data['domain']]['status'] = $value['message'];
                            //$updatedList .= $key.'(<font color=''.$color.''>'.$value['message'].'</font>)';
                        }
                        else{
                            $updatedList[$data['domain']][$key] = array('color'=>'red','message'=>'ERROR');
                            //$updatedList .= $key.'(<font color='red'>ERROR</font>) ';
                            $errorDomains++;
                        }
                    }
                }
                //$updatedList .= '];';
                foreach($getDomainsData['contacts'] as $keyContact => $valueContact){
                    $contact = $allWHMCSTcpanelType[$switchClient][$valueContact['id']];
                    if($contact)
                        Virtualname_contacts::link_domain_contacts($params, $valueContact, $contact['id_contact_whmcs'], $keyContact);
                }
            }

            if($domainCount == $allRows){
                //ADD NEW RESPONSE
                $response   = array();
                $total = $updatedDomains+$vinculatedDomains+$createdDomains+$checkedDomains+$errorDomains;
                if($total > 0){
                    $response['updated_number']     = $updatedDomains;
                    $response['vinculated_number']  = $vinculatedDomains;
                    $response['created_number']     = $createdDomains;
                    $response['checked_number']     = $checkedDomains;
                    $response['error_number']       = $errorDomains;
                    $response['updated_list']       = $updatedList;
                    $response['client_id']          = $switchClient;
                    $response['client_name']        = $clientName;
                    $response['total']              = $total;
                    $responseClients[] = $response;
                }
            }
        }
        return $responseClients;
    }

    //TCPANEL - DOMAINS SYNC CONTACTS ONE CUSTOMERS
    public function tools_contactsync($client, $updateSync){
        $resultClientDomains = select_query('tbldomains', '*', array('userid' => $client, 'registrar' => 'virtualname'));
        $resultList = select_query('mod_virtualname_tools', 'value', array('type' => 'syncList'));

        $domainList = array();
        while($dataList = mysql_fetch_assoc($resultList)){
            $list = json_decode($dataList['value'], true);
            $addToList = array();
            foreach($list as $key => $value){
                $addToList[$key]['product_info'] = $value['product_info'];
                $addContact = array();
                foreach($value['contacts'] as $keyContact => $valueContact){
                    $addContact[$keyContact]['id']      = $valueContact['id'];
                    $addContact[$keyContact]['ic']      = $valueContact['ic'];
                    $addContact[$keyContact]['name']    = $valueContact['name'];
                    $addContact[$keyContact]['lastname']= $valueContact['lastname'];
                    $addContact[$keyContact]['company'] = $valueContact['company'];
                    $addContact[$keyContact]['email']   = $valueContact['email'];
                    $addContact[$keyContact]['address'] = $valueContact['address'];
                    $addContact[$keyContact]['city']    = $valueContact['city'];
                    $addContact[$keyContact]['state']   = $valueContact['state'];
                    $addContact[$keyContact]['zipcode'] = $valueContact['zipcode'];
                    $addContact[$keyContact]['country'] = $valueContact['country'];
                    $addContact[$keyContact]['phonecc'] = $valueContact['phonecc'];
                    $addContact[$keyContact]['phone']   = $valueContact['phone'];
                }
                $addToList[$key]['contacts'] = $addContact;
                $domainList[$key] = $addToList[$key];
            }
        }

        $allWHMCSTcpanelType = Virtualname_contacts::get_all_whmcs_tcpanel_type($client);

        $updatedDomains = 0;
        $updatedList = '';

        while($data = mysql_fetch_array($resultClientDomains)){
            $data['domain'] = strtolower($data['domain']);
            $getDomainsData = $domainList[$data['domain']];
            if($getDomainsData){
                $updatedDomains++;
                $updatedList  .= $data['domain'].'[';
                $domain       = explode('.',$data['domain']);
                $sld          = $domain[0];
                $tld          = substr($data['domain'], strlen($sld)+1);
                $params       = $this->config();
                $params['tld'] = $tld;
                $params['sld'] = $sld;
                $params['domainid'] = $data['id'];
                $params['userid']   = $data['userid'];
                $params['updateSync']= $updateSync;
                $response_contacts  = $this->sync_domain_contacts($params, $getDomainsData, $allWHMCSTcpanelType);
                if(!$response_contacts['error']){
                    foreach($response_contacts['synContact'] as $key => $value){
                        if(!$value['error']){
                            if($value['new']){
                                $allWHMCSTcpanelType[$value['new']['id_contact_tcpanel']] = $value['new'];
                            }
                            if($value['message'] == 'Updated'){
                                $color = 'forestgreen';
                                $updatedDomains++;
                            }
                            elseif($value['message'] == 'Created'){
                                $color = 'deepskyblue';
                                $createdDomains++;
                            }
                            elseif($value['message'] == 'Checked'){
                                $color = 'blue';
                                $checkedDomains++;
                            }
                            elseif($value['message'] == 'Vinculated'){
                                $color = 'orange';
                                $vinculatedDomains++;
                            }
                            $updatedList .= $key.'(<font color=\''.$color.'\'>'.$value['message'].'</font>)';
                        }
                        else{
                            $updatedList .= $key.'(<font color=\'red\'>ERROR</font>) ';
                            $errorDomains++;
                        }
                    }
                }
                else{
                    $updatedList .= $key.'(<font color=\'red\'>ERROR: '.$response_contacts['error'].'</font>) ';
                }
                foreach($getDomainsData['contacts'] as $keyContact => $valueContact){
                    $contact = $allWHMCSTcpanelType[$valueContact['id']];
                    if($contact)
                        Virtualname_contacts::link_domain_contacts($params, $valueContact, $contact['id_contact_whmcs'], $keyContact);
                }
                $updatedList .= '];';
            }
        }

        $currentClient = select_query('tblclients','*', array('id'=>$client));
        $data = mysql_fetch_array($currentClient);
        $clientName = $data['firstname'].' '.$data['lastname'].' ['.$data['email'].']';

        $response['updated_number'] = $updatedDomains;
        $response['updated_list']   = $updatedList;
        $response['client_id']      = $client;
        $response['client_name']    = $clientName;

        return $response;
    }

    //TCPANEL - DOMAINS SYNC OUTBOUNDS
    public function tools_domainsync_status($status, $checkTable = 'mod_virtualname_tools'){
        if($status){
            if($status == 'pending')
                $where = array('registrar' => 'virtualname', 'status' => array('sqltype'=>'IN','values'=>array('Pending', 'Pending Transfer')));
            else
                $where = array('registrar' => 'virtualname', 'status' => $status);
        }
        else
            $where = array('registrar' => 'virtualname');
        $sort = 'userid';
        $sortorder = 'ASC';

        $resultClientsDomains = select_query('tbldomains', '*', $where, $sort, $sortorder);
        $resultList = select_query($checkTable, 'value', array('type' => 'syncList'));

        while($dataList = mysql_fetch_assoc($resultList)){
            $list = json_decode($dataList['value'], true);
            foreach($list as $key => $value){
                $addToList[$key]['product_info'] = $value['product_info'];
                $addToList[$key]['protection'] = $value['protection'];
                $domainList[$key] = $addToList[$key];
            }
        }

        $updatedDomains = 0;
        $updatedList = '';
        $checkedDomains   = 0;
        $checkedList = '';

        $switchClient    = 0;
        $responseClients = array();
        $allRows         = mysql_num_rows($resultClientsDomains);
        $domainCount     = 0;

        while($data = mysql_fetch_array($resultClientsDomains)){
            $domainCount++;

            if($switchClient == 0){
                $switchClient = $data['userid'];
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' <'.$dataClient['email'].'>';
            }
            elseif($switchClient != $data['userid']){
                //ADD NEW RESPONSE
                $response   = array();
                $response['updated_number'] = $updatedDomains;
                $response['updated_list']   = $updatedList;
                $response['client_id']      = $switchClient;
                $response['client_name']    = $clientName;
                $response['checked_number'] = $checkedDomains;
                $response['checked_list']   = $checkedList;
                $responseClients[] = $response;
                //SET INIT
                $updatedDomains = 0;
                $updatedList = '';
                $checkedDomains   = 0;
                $checkedList = '';
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' ('.$dataClient['email'].')';
                $switchClient = $data['userid'];
            }
            $data['domain'] = strtolower($data['domain']);
            $getDomainsData = $domainList[$data['domain']];
            if($getDomainsData){
                $domain       = explode('.',$data['domain']);
                $sld          = $domain[0];
                $tld          = substr($data['domain'], strlen($sld)+1);
                $params       = $this->config();
                $params['tld'] = $tld;
                $params['sld'] = $sld;
                $params['domainid']     = $data['id'];
                $params['updateSync']   = true;
                $resSync = $this->sync_domain_list($params, $getDomainsData);
                if($resSync['updated']){
                    $updatedDomains++;
                    $updatedList .= $data['domain'].';';
                }
                else{
                    $checkedDomains++;
                    $checkedList .= $data['domain'].';';
                }
            }

            if($domainCount == $allRows){
                //ADD NEW RESPONSE
                $response   = array();
                $response['updated_number'] = $updatedDomains;
                $response['updated_list']   = $updatedList;
                $response['client_id']      = $switchClient;
                $response['client_name']    = $clientName;
                $response['checked_number'] = $checkedDomains;
                $response['checked_list']   = $checkedList;
                $responseClients[] = $response;
            }
        }
        return $responseClients;
    }

    //TCPANEL - DOMAINS SYNC DOMAINS ALL CUSTOMERS
    public function tools_all_domainsync($nloop, $updateSync, $syncExpire, $syncCancel){
        if($nloop == -1){
            $resultList = select_query('mod_virtualname_tools', 'value', array('type' => 'syncList'));
        }
        else{
            $config = '\'syncList'.($nloop*250).'\',';
            for($i=1;$i<=7;$i++){
                $config .= '\'syncList';
                $config .= ($nloop+$i)*250;
                $config .= '\'';
                if($i!=7) $config .= ',';
            }
            $sql    = 'select value from mod_virtualname_tools where config in ('.$config.')';
            $resultList = mysql_query($sql);
        }

        if(mysql_num_rows($resultList) == 0)
            return 'finishSync';

        while($dataList = mysql_fetch_assoc($resultList)){
            $list = json_decode($dataList['value'], true);
            foreach($list as $key => $value){
                $addToList[$key]['product_info'] = $value['product_info'];
                $addToList[$key]['protection'] = $value['protection'];
                $domainList[$key] = $addToList[$key];
                $whereList[] = $key;
            }
        }

        $array_status = array('Active', 'Pending', 'Pending Transfer');

        if($syncCancel)
            $array_status[] = 'Cancelled';
        if($syncExpire)
            $array_status[] = 'Expired';

        $where     = array('registrar'  => 'virtualname',
                           'domain'     => array('sqltype'=>'IN','values'=>$whereList),
                           'status'     => array('sqltype'=>'IN','values'=>$array_status));

        $sort      = 'userid';
        $sortorder = 'ASC';
        $resultClientsDomains = select_query('tbldomains', '*', $where, $sort, $sortorder);


        $updatedDomains = 0;
        $updatedList = '';
        $errorDomains   = 0;
        $errorList = '';
        $checkedDomains   = 0;
        $checkedList = '';

        $switchClient    = 0;
        $responseClients = array();
        $allRows         = mysql_num_rows($resultClientsDomains);
        $domainCount     = 0;

        while($data = mysql_fetch_array($resultClientsDomains)){
            $domainCount++;

            if($switchClient == 0){
                $switchClient = $data['userid'];
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' ('.$dataClient['email'].')';
            }
            elseif($switchClient != $data['userid']){
                //ADD NEW RESPONSE
                $response   = array();
                $response['updated_number'] = $updatedDomains;
                $response['updated_list']   = $updatedList;
                $response['error_number']   = $errorDomains;
                $response['error_list']     = $errorList;
                $response['client_id']      = $switchClient;
                $response['client_name']    = $clientName;
                $response['checked_number'] = $checkedDomains;
                $response['checked_list']   = $checkedList;
                $responseClients[] = $response;
                //SET INIT
                $updatedDomains = 0;
                $updatedList = '';
                $errorDomains   = 0;
                $errorList = '';
                $checkedDomains   = 0;
                $checkedList = '';
                $getClient  = select_query('tblclients','*', array('id'=>$data['userid']));
                $dataClient = mysql_fetch_array($getClient);
                $clientName = $dataClient['firstname'].' '.$dataClient['lastname'].' ('.$dataClient['email'].')';
                $switchClient = $data['userid'];
            }
            $data['domain'] = strtolower($data['domain']);
            $getDomainsData = $domainList[$data['domain']];
            if(!$getDomainsData){
                $errorDomains++;
                $errorList .= $data['domain'].':'.$data['id'].';';
            }
            else{
                $domain             = explode('.',$data['domain']);
                $sld                = $domain[0];
                $tld                = substr($data['domain'], strlen($sld)+1);
                $params             = $this->config();
                $params['tld']      = $tld;
                $params['sld']      = $sld;
                $params['domainid'] = $data['id'];
                $params['updateSync']= $updateSync;
                $resSync            = $this->sync_domain_list($params, $getDomainsData);
                if($resSync['updated']){
                    $updatedDomains++;
                    $updatedList .= $data['domain'].':'.$data['id'].';';
                }
                else{
                    $checkedDomains++;
                    $checkedList .= $data['domain'].':'.$data['id'].';';
                }
            }

            if($domainCount == $allRows){
                //ADD NEW RESPONSE
                $response   = array();
                $response['updated_number'] = $updatedDomains;
                $response['updated_list']   = $updatedList;
                $response['error_number']   = $errorDomains;
                $response['error_list']     = $errorList;
                $response['client_id']      = $switchClient;
                $response['client_name']    = $clientName;
                $response['checked_number'] = $checkedDomains;
                $response['checked_list']   = $checkedList;
                $responseClients[] = $response;
            }
        }
        return $responseClients;
    }

    //TCPANEL - DOMAINS SYNC DOMAINS ONE CUSTOMER
    public function tools_domainsync($client, $updateSync, $syncExpire, $syncCancel){
        $array_status = array('Active', 'Pending', 'Pending Transfer');

        if($syncCancel)
            $array_status[] = 'Cancelled';
        if($syncExpire)
            $array_status[] = 'Expired';

        $where = array('userid' => $client, 'registrar' => 'virtualname', 'status' => array('sqltype'=>'IN','values'=>$array_status));

        $resultClientDomains = select_query('tbldomains', '*', $where);
        $resultList = select_query('mod_virtualname_tools', 'value', array('type' => 'syncList'));

        while($dataList = mysql_fetch_assoc($resultList)){
            $list = json_decode($dataList['value'], true);
            foreach($list as $key => $value){
                $addToList[$key]['product_info'] = $value['product_info'];
                $addToList[$key]['protection'] = $value['protection'];
                foreach($value['contacts'] as $keyContact => $valueContact)
                    $domainContact[$keyContact] = $valueContact['id'];
                $addToList[$key]['contacts'] = $domainContact;
                $domainList[$key] = $addToList[$key];
            }
        }

        $response = array();
        $updatedDomains = 0;
        $errorDomains   = 0;
        $errorList = '';
        $updatedList = '';
        $checkedDomains   = 0;
        $checkedList = '';
        $allWHMCSTcpanelType = Virtualname_contacts::get_all_whmcs_tcpanel_type($client);

        while($data = mysql_fetch_array($resultClientDomains)){
            $data['domain'] = strtolower($data['domain']);
            $getDomainsData = $domainList[$data['domain']];
            if(!$getDomainsData){
                $errorDomains++;
                $errorList .= $data['domain'].':'.$data['id'].' ';
            }
            else{
                $resSync      = array();
                $domain       = explode('.',$data['domain']);
                $sld          = $domain[0];
                $tld          = substr($data['domain'], strlen($sld)+1);
                $params       = $this->config();
                $params['tld'] = $tld;
                $params['sld'] = $sld;
                $params['domainid'] = $data['id'];
                $params['updateSync'] = $updateSync;
                $resSync = $this->sync_domain_list($params, $getDomainsData);
                foreach($getDomainsData['contacts'] as $keyContact => $valueContact){
                    $contact = $allWHMCSTcpanelType[$valueContact];
                    if($contact){
                        Virtualname_contacts::link_domain_contacts($params, $valueContact, $contact['id_contact_whmcs'], $keyContact);
                    }

                }
                if($resSync['updated']){
                    $updatedDomains++;
                    $updatedList .= $data['domain'].':'.$data['id'].' ';
                }
                else{
                    $checkedDomains++;
                    $checkedList .= $data['domain'].':'.$data['id'].' ';
                }
            }
        }

        $currentClient = select_query('tblclients','*', array('id'=>$client));
        $data = mysql_fetch_array($currentClient);
        $clientName = $data['firstname'].' '.$data['lastname'].' ('.$data['email'].')';

        $response['updated_number'] = $updatedDomains;
        $response['updated_list']   = $updatedList;
        $response['error_number']   = $errorDomains;
        $response['error_list']     = $errorList;
        $response['client_id']      = $client;
        $response['client_name']    = $clientName;
        $response['checked_number'] = $checkedDomains;
        $response['checked_list']   = $checkedList;

        return $response;
    }

    //TCPANEL - DOMAINS FULL LIST
    public function tools_list($limit = 0, $offset = 0, $until = 0, $status = ''){
        $params = $this->config();
        $params['limit']  = $limit;
        $params['offset'] = $offset;
        $params['until']  = $until;
        if($status == 'pending_domains')
            $params['status'] = 'active';
        else
            $params['status'] = $status;
        $res = array();
        $response = array();
        $response = $this->get_all_domains_tools($params);
        if($response['status']['code']< 200 || $response['status']['code'] > 299){
            $res = array('error'=>1);
        }
        else{
            if($status == 'outbound_transfer')
                $virtualname_table_sync = 'mod_virtualname_outbounds';
            elseif($status == 'pending_domains')
                $virtualname_table_sync = 'mod_virtualname_pendings';
            else
                $virtualname_table_sync = 'mod_virtualname_tools';
            //SET SYNC ALL DOMAINS
            if($offset == 0){
                $where = array('type'=>'syncList');
                delete_query($virtualname_table_sync,$where);
            }
            $list = $this->addIndexList($response['response']);
            $where = array('config'=>'syncList');
            delete_query($virtualname_table_sync,$where);
            $values= array('value'  => json_encode($list), 'config' => 'syncList'.$offset, 'type' => 'synclist');
            insert_query($virtualname_table_sync,$values);
			$split_header = preg_split('~[\r\n]+~', $response['headers'][0]);
			$total_count = explode('X-Total-Count: ', $split_header[12]);
            $res = array('total_count' => $total_count[1]);
        }
        return $res;
    }
    //TCPANEL - DOMAINS ADD INDEX
    public function addIndexList($domainList){
        $indexedList = array();
        foreach ($domainList as $row) {
            $indexedList[$row['name']] = $row;
        }
        return $indexedList;
    }
	//VALIDATE IF THE CURRENT DOMAIN WAS ASSIGNED WITH VIRTUALNAME
	public function check_domain($domainid){
	    $table = 'tbldomains';
	    $fields = 'id';
	    $where = array('registrar'=>'virtualname', 'id'=>$domainid);
	    $result = select_query($table,$fields,$where);
	    $check = mysql_num_rows($result);
	    return $check;
	}
	//INIT TRANSFER ON RENEWAL
	public function manage_transfer_on_renewal($domainid, $type, $authcode, $mail, $status, $registrar){
	    $table = 'mod_virtualname_transfer_on_renewal';
	    $fields = 'status';
	    $where = array('domainid'=>$domainid);
    	if($type == 1 || $type == 3){
    		$value = $authcode;
    	}
    	elseif($type == 2  || $type == 4){
    		$value = $mail;
    	}
    	else{
    		$type = 1;
    		$value = '';
    	}
	    $result = select_query($table,$fields,$where);
	    if(mysql_num_rows($result)>0){
	    	mysql_query('update '.$table.' set registrar =\''.$registrar.'\' ,status = \''.$status.'\', type = \''.$type.'\', value = \''.$value.'\' where domainid = \''.$domainid.'\';');
	    }
	    else{
			$insert = array('domainid' => $domainid,
							'registrar' => $registrar,
							'status' => $status,
							'type' => $type,
							'value' => $value);
	        insert_query($table, $insert);
	    }
	}
	//SET TRANSFER ON RENEWAL EMAIL
	public function set_email_transfer_on_renewal($domainid, $email){
	    $table = 'mod_virtualname_transfer_on_renewal';
	    $fields = 'status';
	    $where = array('domainid'=>$domainid);
    	if($type == 1){
    		$value = $authcode;
    	}
    	elseif($type == 2){
    		$value = $mail;
    	}
    	else{
    		$type = 1;
    		$value = '';
    	}
	    $result = select_query($table,$fields,$where);
	    if(mysql_num_rows($result)>0){
	    	mysql_query('update '.$table.' set admin_email =\''.$email.'\' where domainid = \''.$domainid.'\';');
	    }
	}
	//SET TRANSFER ON RENEWAL EMAIL
	public function set_notes_transfer_on_renewal($domainid, $notes){
	    $table = 'mod_virtualname_transfer_on_renewal';
	    $fields = 'notes';
	    $where = array('domainid'=>$domainid);
	    $result = select_query($table,$fields,$where);
	    if(mysql_num_rows($result)>0)
	    	mysql_query('update '.$table.' set notes =\''.$notes.'\' where domainid = \''.$domainid.'\';');
	}
	//GET WHMCS DOMAIN TRANSFER ON RENEWAL
	public function get_transfer_on_renewal($domainid){
	    $table = 'mod_virtualname_transfer_on_renewal';
	    $fields = 'status,type,value,registrar,admin_email';
	    $where = array('domainid'=>$domainid);
	    $result = select_query($table,$fields,$where);
	    $data = mysql_fetch_array($result);
	    return $data;
	}
	public function transfer_on_renewal($vars, $transfer_domain, $admin){
		global $vname_admin, $vname_domains, $vname_contacts;
		virtualname_init();
	    $domainid = $vars['params']['domainid'];
	    $userid =  $vars['userid'];
	    $langs = $vname_admin->get_config_lang($admin);
	    $vname_config = $vname_admin->config();
        $data = $this->get_whmcs_domain('', $domainid);
        $vars['params']['userid'] = $data['userid'];
        $authcode = '';
        $email_transfer_status = array(2, 4);
        $authcode_transfer_status = array(1, 3);
        $transfer_in_progress_status = array(3, 4);
        if(in_array($transfer_domain['type'], $transfer_in_progress_status) && $transfer_domain['registrar'] == 'virtualname'){
        	return array('abortWithError' => $langs['error_not_transfer_available']);
        }
        elseif(in_array($transfer_domain['type'], $authcode_transfer_status)){
	        //AUTHCODE
			if(!function_exists('RegGetEPPCode'))
				require_once(realpath(dirname(__FILE__).'/../../../../..').'/includes/registrarfunctions.php');
            if(!empty($transfer_domain['value'])){
                $authcode = html_entity_decode($transfer_domain['value'], ENT_QUOTES);
            }
            else{
                $response_epp = RegGetEPPCode($vars['params']);
                if (!$response_epp['error'] && isset($response_epp['eppcode'])){
                    $authcode = html_entity_decode($response_epp['eppcode'], ENT_QUOTES);
                    $this->manage_transfer_on_renewal($domainid, 1, $authcode, '','active', $transfer_domain['registrar']);
                }
            }
            if(empty($authcode)){
            	return array('abortWithError' => $langs['empty_authcode']);
            }
            $lock = RegGetRegistrarLock($vars['params']);
            if($lock == 'locked')
                RegSaveRegistrarLock($vars['params']);
        }
        elseif(in_array($transfer_domain['type'], $email_transfer_status)){
   	        //MAIL
			if(!function_exists('RegGetContactDetails'))
				require_once(realpath(dirname(__FILE__).'/../../../../..').'/includes/registrarfunctions.php');
            $values = RegGetContactDetails($vars['params']);
        	$same_mail_action = array('opensrs', 'opensrspro', 'enom', 'resellerclub', 'rrpproxy');
        	if(in_array($vars['params']['registrar'], $same_mail_action))
        		$email_field = 'Email';
        	elseif($vars['params']['registrar'] == 'openprovider')
        		$email_field = 'Email Address';
            else
            	return array('abortWithError' => $langs['error_not_mail_transfer_available']);
        	if($values['error'])
        		return array('abortWithError' => $values['error']);
        	elseif(isset($values['Admin'])){
        		if(empty($values['Admin'][$email_field]) || is_null($values['Admin'][$email_field]))
        			$response_mail['error'] = 'Admin email: '.$langs['resource_not_found'];
        	}
        	else
        		$response_mail['error'] = 'Admin email: '.$langs['resource_not_found'];
        	//CHANGE ADMIN EMAIL
            if(!$response_mail['error']){
            	if(empty($transfer_domain['admin_email']) || $transfer_domain['value'] != $values['Admin'][$email_field]){
            		$registrant = $vars['params']['contactdetails']['Registrant'];
                    $contacts_response = $vname_contacts->transfer_contact_registrar($values, $vars['params']['registrar'], $transfer_domain['value']);
                    $vars['params']['contactdetails'] = $contacts_response['contacts'];
                    $vars['params']['contactdetails']['Registrant'] = $registrant;
                    $response_set_mail = RegSaveContactDetails($vars['params']);
                    if(!$response_set_mail['error'] AND $response_set_mail['success']){
                    	$this->set_email_transfer_on_renewal($domainid, $values['Admin'][$email_field]);
                        $lock = RegGetRegistrarLock($vars['params']);
                        if($lock == 'locked')
                            RegSaveRegistrarLock($vars['params']);
                    }
                    else{
                    	if($response_set_mail['error'])
                        	return array('abortWithError' => utf8_decode($response_set_mail['error']));
                       	else
                       		return array('abortWithError' => $langs['error_on_update_mail']);
                    }
                }
            }
            else
                return array('abortWithError' => $response_mail['error']);
        }
        else
        	return array('abortWithError' => $langs['unknow_transfer_status']);

        $vars['params']['registrar'] = 'virtualname';
        $vars['params']['transfersecret'] = $authcode;
        $params = array_merge($vars['params'], $vname_config);
        $request = virtualname_TransferDomain($params);

        if($request['error'])
            return array('abortWithError' => $request['error']);
        else{
            $sql = 'UPDATE tbldomains SET registrar = \'virtualname\' where id = '.$domainid;
            $res = mysql_query($sql);
            if($contacts_response && $contacts_response['old_email'])
            	$this->manage_transfer_on_renewal($domainid, 2, '', $transfer_domain['value'], 'active', $transfer_domain['registrar']);
           	else
	           	$this->manage_transfer_on_renewal($domainid, 1, $authcode, '','active', $transfer_domain['registrar']);
            return array('abortWithSuccess' => true);
        }
	}
	public function send_domains_notification($role, $subject, $message_mail, $admin_link = true) {
		//INIT GLOBAL
		global $CONFIG;
		global $whmcs;
		global $smtp_debug;
		//CHECK VALUES
		if (!$message_mail || !$role)
			return false;
		//CONFIG URL
		if ($CONFIG['LogoURL'])
			$message = '<p><a href=\'' . $CONFIG['Domain'] . '\' target=\'_blank\'><img src=\'' . $CONFIG['LogoURL'] . '\' alt=\'' . $CONFIG['CompanyName'] . '\' border=\'0\'></a></p>';
		$admin_url = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
		$admin_url .= '/' . $whmcs->get_admin_folder_name() . '/';
		$message .= '<font style=\'font-family:Verdana;font-size:11px\'><p>' . $message_mail . '</p>';
		//ADMIN URL
		if ($admin_link)
			$message .= '<p><a href=\'' . $admin_url . '\'>' . $admin_url . '</a></p>';
		//INIT MAILER
		if (!class_exists('PHPMailer'))
			$whmcs->load_class('phpmailer');
		$email = new PHPMailer();
		$email->From = $CONFIG['SystemEmailsFromEmail'];
		$email->FromName = html_entity_decode($CONFIG['SystemEmailsFromName'], ENT_QUOTES);
		$email->Subject = $subject;
		$email->CharSet = $CONFIG['Charset'];
		if ($CONFIG['MailType'] == 'mail') {
			$email->Mailer = 'mail';
		}
		else {
			if ($CONFIG['MailType'] == 'smtp') {
				$email->IsSMTP();
				$email->Host = $CONFIG['SMTPHost'];
				$email->Port = $CONFIG['SMTPPort'];
				$email->Hostname = $_SERVER['SERVER_NAME'];
				if ($CONFIG['SMTPSSL']) {
					$email->SMTPSecure = $CONFIG['SMTPSSL'];
				}
				if ($CONFIG['SMTPUsername']) {
					$email->SMTPAuth = true;
					$email->Username = $CONFIG['SMTPUsername'];
					$email->Password = decrypt($CONFIG['SMTPPassword']);
				}
				$email->Sender = $email->From;
			}
		}
		$email->XMailer = 'WHMCS v' . $whmcs->get_config('Version');
		if ($smtp_debug)
			$email->SMTPDebug = true;
		$message_text = str_replace('</p>', "\r\n\r\n", $message);
		$message_text = str_replace('<br>', "\r\n", $message_text);
		$message_text = str_replace('<br />', "\r\n", $message_text);
		$message_text = strip_tags($message_text);
		$email->Body = $message;
		$email->AltBody = $message_text;
		$emailcount = 0;
		$where = 'tbladmins.disabled=0 AND tbladminroles.name = \''.db_escape_string($role).'\'';
		$result = select_query('tbladmins', 'firstname,lastname,email,ticketnotifications', $where, '', '', '', 'tbladminroles ON tbladminroles.id=tbladmins.roleid');
		while ($data_admin = mysql_fetch_array($result)) {
			if ($data_admin['email']) {
				$admin_send = true;
				if ($admin_send) {
					$email->AddAddress(trim($data_admin['email']), $data_admin['firstname'] . ' ' . $data_admin['lastname']);
					++$emailcount;
				}
			}
		}
		if (!$emailcount) return false;
		if (!$email->Send())
			logActivity('Virtualname Admin Email Notification Sending Failed - ' . $email->ErrorInfo . (' (Subject: ' . $subject . ')'), 'none');
		$email->ClearAddresses();
	}
}
?>