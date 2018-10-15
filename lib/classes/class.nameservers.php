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
// * File description: VIRTUALNAME NAMESERVERS class
// *************************************************************************
class Virtualname_nameservers extends Virtualname_domains{
	//GET WHMCS DEFAULTNAMESERVERS
	public function get_whmcs_default_nameservers(){
	    $sql_error  = 'select * from tblconfiguration where setting like (\'DefaultNameserver%\')';
	    $result     = mysql_query($sql_error);
	    $ns         = array();
	    while($data = mysql_fetch_array($result))
	        $ns[$data['setting']] = $data['value'];
	    return $ns;
	}
	public function get_domain_hosts($params, $domain_id){
	    //INIT MODULE
	    global $vname_admin, $vname_domains;
	    virtualname_init();
	    $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_id.'/hosts.json?limit=2';
        $RESTful= 'GET';
        $params['action'] = 'GetDomainHosts';
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
        else{
        	foreach($request['response'] as $ns){
        		$values[$ns['name']] = array('id'=>$ns['id'], 'ips'=>$ns['ips']);
        	}
        }
	    return $values;
	}
	public function create_domain_hosts($params, $domain_id){
	    //INIT MODULE
	    global $vname_admin, $vname_domains;
	    virtualname_init();
	    $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_id.'/hosts.json';
        $RESTful= 'POST';
        $nameserver = explode(".",$params["nameserver"]);
        $hosts = $nameserver[0];
        $ips = array();
        if($params['ipaddress'])
            $ips[] = $params["ipaddress"];
        $fields['json'] = json_encode(array('host'=>array('name'=>$hosts, 'ips'=>$ips)));
        $params['action'] = 'RegisterNameserver';
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
        return $values;
	}
	public function delete_domain_hosts($params, $domain_id, $ns_id){
	    //INIT MODULE
	    global $vname_admin, $vname_domains;
	    virtualname_init();
	    $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_id.'/hosts/'.$ns_id.'.json';
        $RESTful= 'DELETE';
        $params['action'] = 'DeleteNameserver';
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
        return $values;
	}
	public function save_nameservers($params, $domain_id){
	    //INIT MODULE
	    global $vname_admin, $vname_domains;
	    virtualname_init();
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_id.'.json';
        $RESTful= 'PATCH';
        $nameservers = array();
        if($params['ns1']) $nameservers[] = $params['ns1'];
        if($params['ns2']) $nameservers[] = $params['ns2'];
        if($params['ns3']) $nameservers[] = $params['ns3'];
        if($params['ns4']) $nameservers[] = $params['ns4'];
        $fields['json'] = json_encode(array('domain'=>array('nameservers'=>$nameservers)));
        $params['action'] = 'SaveNameservers';
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
?>