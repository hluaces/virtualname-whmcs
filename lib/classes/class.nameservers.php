<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.9.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.1.20
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
    //CREATE RECORDS
    public function create_zone_records($params, $domain, $recordname, $recordtype, $ttl, $prio, $content){
        $zone_id = $this->get_domain_dns_zone($params, $domain);
        if(!$zone_id)
            return array('error' => 'zonenotfound');
        else
            return $this->create_record($params, $zone_id, $recordname, $recordtype, $ttl, $prio, $content);        
    }
    public function create_record($params, $zone_id, $recordname, $recordtype, $ttl, $prio, $content){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        $fields = array();
        if($recordtype == 'MX')
            $fields['json'] = json_encode(array( 'record' => array('name' => $recordname, 'type' => $recordtype, 'ttl' => $ttl, 'prio' => $prio, 'server' => $content) ));
        else
            $fields['json'] = json_encode(array( 'record' => array('name' => $recordname, 'type' => $recordtype, 'ttl' => $ttl, 'prio' => $prio, 'content' => $content) ));
        $module = 'dns/zones';
        $action = $zone_id.'/records.json';
        $RESTful= 'POST';
        $params['action'] = 'CreateRecord';
        try{
            $request = $vname_domains->api_call($params,$fields, $module, $action, $RESTful);
        }catch (Exception $e){
            return ($e->getMessage());
        }
        if($request['status']['code']< 200 || $request['status']['code'] > 299){
            $values['error'] = $request['status']['description'];
            if(isset($request['response']['content']))
                $values['error'] .= ': '.implode(',', $request['response']['content']);
        }
        else
            $values = true;
        return $values; 
    }
    //DELETE RECORDS
    public function delete_zone_records($params, $domain, $record_id){
        $zone_id = $this->get_domain_dns_zone($params, $domain);
        if(!$zone_id)
            return array('error' => 'zonenotfound');
        else
            return $this->delete_record($params, $record_id, $zone_id);
    }
    public function delete_record($params, $record_id, $zone_id){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'dns/zones';
        $action = $zone_id.'/records/'.$record_id.'.json';
        $RESTful= 'DELETE';
        $params['action'] = 'DeleteRecord';
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
        else
            $values = true;
        return $values; 
    }
    //GET RECORDS
    public function get_domain_records($params, $domain){
        $zone_id = $this->get_domain_dns_zone($params, $domain);
        if(!$zone_id)
            return array('error' => 'zonenotfound');
        else
            return $this->get_zone_records($params, $zone_id);
    }

    public function get_domain_dns_zone($params, $domain){
        $values = $this->get_dns_zones($params);
        if($values && $values[$domain])
            return $values[$domain];
        else
            return false;
    }

    public function get_zone_records($params, $zone_id){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'dns/zones';
        $action = $zone_id.'/records.json';
        $RESTful= 'GET';
        $params['action'] = 'GetZoneRecords';
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
            foreach($request['response'] as $record){
                $values[] = array('id'=>$record['id'], 'name'=>$record['name'], 'type' => $record['type'], 'ttl' => $record['ttl'], 'prio' => $record['prio'], 'content' => $record['content']);
            }
        }
        return $values;        
    }
    public function get_dns_zones($params){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'dns';
        $action = 'zones.json';
        $RESTful= 'GET';
        $params['action'] = 'GetDnsZones';
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
            foreach($request['response'] as $zone){
                $values[$zone['name']] = $zone['id'];
            }
        }
        return $values;        
    }
	public function get_domain_hosts($params, $domain_id){
	    //INIT MODULE
	    global $vname_admin, $vname_domains;
	    virtualname_init();
	    $vname_admin->check_configuration($params);
        $fields = array();
        $module = 'domains/domains';
        $action = $domain_id.'/hosts.json';
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
    public function get_domain_gluerecords($domain){
        //INIT MODULE
        global $vname_admin, $vname_domains;
        virtualname_init();
        $domain_info = $vname_domains->view_domain_info_hook($domain);
        $params = $vname_admin->config();
        $values = $this->get_domain_hosts($params, $domain_info['domain_id']);
        //$vname_domains->destroy_domain_cache($domain);
        return $values;
    }
}
?>