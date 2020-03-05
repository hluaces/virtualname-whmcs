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
// * @common true
// * File description: VIRTUALNAME API class
// *************************************************************************
class Virtualname_api extends Virtualname_admin{
	#CLASS CONSTANTS#
	public $api_url = 'https://api.virtualname.net';
	public $dev_api_url = 'http://dev.api.virtualname.net';
	//API CALL
	public function api_call($params, $fields, $module, $action, $RESTful){
	    //DEBUG
	    if($params['debug'] == 'on'){
	        $debugActions = array('SaveNameservers', 'SetDomainContacts', 'AddContact', 'DelContact', 'EditContact', 'RenewDomain', 'RegisterDomain', 'SaveRegistrarLock', 'TransferDomain', 'SaveDomainPrivacy');
	        if(in_array($params['action'], $debugActions)){
	            $debug = $params;
	            unset($debug['APIKey']);
	            unset($debug['original']['APIKey']);
	            echo '<pre>';
	            var_dump(array('action'=>$debug['action']));
	            var_dump($debug);
	            var_dump($fields);
	            echo '</pre>';
	        }
	    }
	    //CALL CONFIGURATION
	    $config = $this->config();
	    if ($params['devMode'] == 'on') {
	        $URLBase = $config['URLBase_DEBUG'].'/'.$config['version'];
	    } else {
	        $URLBase = $config['URLBase'].'/'.$config['version'];
	    }
	    //CURL VERSION
	    $curl_info = curl_version();
	    //HEADERS
	    $virtualname_header[] = 'X-TCpanel-Token: '.$params['APIKey'];
	    $virtualname_header[] = 'X-TCPanel-Plugin-Version' . $config['pluginVersion'];
	    $virtualname_header[] = 'Content-Type: application/json';
	    //CURL CONEXION
	    if($module != '')
	        $cURL_init = $URLBase.'/'.$module.'/'.$action;
	    else
	        $cURL_init = $URLBase.'/'.$action;
	    $cURL = curl_init($cURL_init);
	    curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, $RESTful);
	    if($RESTful == 'POST' || $RESTful == 'PATCH' || $RESTful == 'PUT'){
	        $TCpanel_json = $fields['json'];
	        $virtualname_header[] = 'Content-Length: '.strlen($TCpanel_json);
	        curl_setopt($cURL, CURLOPT_POSTFIELDS, $TCpanel_json);
	    }
	    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($cURL, CURLOPT_HTTPHEADER, $virtualname_header);
	    curl_setopt($cURL, CURLOPT_HEADER, 1);
	    curl_setopt($cURL, CURLOPT_USERAGENT,'WHMCSVersion/'.$config['pluginVersion'].' TCPanelModuleVersion/'.$config['Module-Version'].' WHMCSModuleAction/'.$params['action'].' PHP/'.phpversion().' cURL/'.$curl_info['version'].' ModuleForWHMCS/'.$config['vn_whmcs']);
	    curl_setopt($cURL, CURLOPT_TIMEOUT_MS, 60000);
	    $request = curl_exec($cURL);
	    $cURLError = curl_error($cURL);
	    //SYSTEM MODULE DEBUG LOG
	    $params_config['devMode'] = $params['devMode'];
	    $params_config['action'] = $params['action'];
	    $all_fields = array('url'=>$cURL_init, 'fields'=>$fields,'params_config'=>$params_config,
	                       'cURL' => 'curl -vvv -H \'X-TCpanel-Token: '.$params['APIKey'].'\' -H \'Host: api.virtualname.net\' '.$cURL_init);
	    //GET HEADERS
	    $cURLInfo = curl_getinfo($cURL);

	    if($request === false){
	        $response = array('response'=>array('0'=>'Connection error'),
	                          'status'  =>array('code'=> 0,
	                                            'description'=>$config['module_error']['0']));
	    }
	    else{
	        if(!isset($config['apiResponse'][$cURLInfo['http_code']])){
	            $response = array('response'=>array('1'=>'Unknown api response'),
	                              'status'  =>array('code'=> 1,
	                                                'description'=>$config['module_error']['1']));
	        }
	        else{
	            $header_size   = curl_getinfo($cURL, CURLINFO_HEADER_SIZE);
	            $header        = explode('\r\n',substr($request, 0, $header_size));
	            $request       = substr($request, $header_size);
	            $json_response = json_decode($request, true);
	            $response = array('headers'  => $header,
	                              'status'   => array('code'=>$cURLInfo['http_code'], 'description'=>$config['apiResponse'][$cURLInfo['http_code']]),
	                              'response' => $json_response);
	        }
	    }
	    curl_close($cURL);
	    if(trim($cURLError))
	        logModuleCall('virtualname', 'cURLError', $all_fields, $cURLError, '', '');
	    logModuleCall('virtualname', $params['action'], $all_fields, $response, '', '');

	    if($response['status']['code']< 200 || $response['status']['code'] > 299){
		    $error_fields = array('url'=>$cURL_init, 'fields'=>$fields,'params_config'=>$params_config,
		                       'cURL' => 'curl -vvv -H \'X-TCpanel-Token: api-key\' -H \'Host: api.virtualname.net\' '.$cURL_init);
	        $result_admin = select_query('tbladmins', 'id,username', array('id' => $_SESSION['adminid']));
	        $data_admin   = mysql_fetch_array($result_admin);
	        $currentError = $response;
	        $values = array('date'          => date('Y-m-d H:i:s'),
	                        'action'        => $params['action'],
	                        'call'          => print_r($error_fields, true),
	                        'response'      => print_r($currentError, true),
	                        'user'          => $data_admin['username'],
	                        'userid'        => $data_admin['id'],
	                        'ipaddr'        => $_SERVER['REMOTE_ADDR']);
	        insert_query('mod_virtualname_error_logs',$values);
	    }

	    return $response;
	}
	//TOOLS CHECK API STATUS
	public function api_authentication($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = '';
	    $action = 'hello.json';
	    $RESTful= 'GET';
	    $params['action'] = 'Authentication';
	    try{
	        $request = $this->api_call($params, $fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    return $request;
	}
}

?>
