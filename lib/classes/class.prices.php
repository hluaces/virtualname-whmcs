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
// * File description: VIRTUALNAME CONTACTS class
// *************************************************************************
class Virtualname_prices extends Virtualname_domains{
	//GET ALL CLIENT PRICES
	public function prices_details($idprice){
	    $fields = array();
	    $module = 'domains';
	    if($idprice)
	        $action = 'pricings/'.$idprice.'.json';
	    else
	        $action = 'pricings.json';
	    $RESTful= 'GET';
	    $params = $this->config();
	    $params['action'] = 'getPrice';

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
	//GET ASSIGNED TLDs WHMCS-TCPANEL
	public function get_extension(){
	    $table = 'tbldomainpricing';
	    $fields = 'extension';
	    $where = array('autoreg'=>'virtualname');
	    $result = select_query($table,$fields,$where);
	    while ($row = mysql_fetch_array($result)){
	        $data[] = $row['extension'];
	    }
	    return $data;
	}
	//UPDATE WHMCS PRICES
	public function update_whmcs_prices($idprice, $client_group){
	    //GET CLIENT PRICES DETAILS
	    $request = $this->prices_details($idprice);
	    $values = $request['response']['items'];
	    $table = 'tbldomainpricing;';
	    $years_prices = array('1' => 'msetupfee', '2' => 'qsetupfee', '3' => 'ssetupfee', '4'=> 'asetupfee', '5' => 'bsetupfee',
	                          '6' =>'monthly', '7' =>'quarterly', '8' =>'semiannually', '9' =>'annually', '10' =>'biennially');
	    $type_prices  = array(
	                        array('name'=>'domainregister','type'=>'register'),
	                        array('name'=>'domaintransfer','type'=>'transfer'),
	                        array('name'=>'domainrenew',   'type'=>'renew'),
	                    );
	    foreach($values as $key => $value){
	        $where = array('autoreg'=>'virtualname','extension'=>'.'.$key);
	        $result = select_query($table,array(),$where);
	        //GET CURRENCY COINS
	        $eur_result = select_query('tblcurrencies',array(),array('code'=>'EUR'));
	        $usd_result = select_query('tblcurrencies',array(),array('code'=>'USD'));
	        if(mysql_num_rows($result)>0 AND $value['currency'] == 'EUR' AND mysql_num_rows($eur_result)>0){
	            $data           = mysql_fetch_array($result);
	            $eur_currency   = mysql_fetch_array($eur_result);
	            //IF THIS EXTENSION WAS ACTIVED NOW CHECK IF EXIST ALL RECORDS
	            $table_prices  = 'tblpricing';
	            foreach($type_prices as $type_price){
	                $where_prices  = array('type'=>$type_price['name'],'relid'=>$data['id'], 'tsetupfee'=>$client_group, 'currency'=>$eur_currency['id']);
	                $result_prices = select_query($table_prices,array(),$where_prices);
	                $query_prices = array();
	                foreach($years_prices as $key_price => $year){
	                    if($value['pricing'][$type_price['type']][$key_price])
	                        $query_prices[$year] =  $value['pricing']['register'][$key_price]*$key_price;
	                    else
	                        $query_prices[$year] =  0;
	                }
	                if(mysql_num_rows($result_prices)>0){
	                    $data_prices = mysql_fetch_array($result_prices);
	                    //UPDATE RECORD
	                    update_query($table_prices,$query_prices,$where_prices);
	                }
	                else{
	                    //CREATE RECORD
	                    $values_prices = $query_prices;
	                    $values_prices['tsetupfee'] = $client_group;
	                    $values_prices['type']      = $type_price['name'];
	                    $values_prices['currency']  = '1';
	                    $values_prices['relid']     = $data['id'];
	                    insert_query($table_prices,$values_prices);
	                }
	            }
	        }
	        elseif(mysql_num_rows($result)>0 AND $value['currency'] == 'USD' AND mysql_num_rows($usd_result)>0){
	            $data           = mysql_fetch_array($result);
	            $usd_currency   = mysql_fetch_array($usd_result);
	            //IF THIS EXTENSION WAS ACTIVED NOW CHECK IF EXIST ALL RECORDS
	            $table_prices  = 'tblpricing';
	            foreach($type_prices as $type_price){
	                $where_prices  = array('type'=>$type_price['name'],'relid'=>$data['id'], 'tsetupfee'=>$client_group, 'currency'=>$usd_currency['id']);
	                $result_prices = select_query($table_prices,array(),$where_prices);
	                $query_prices = array();
	                foreach($years_prices as $key_price => $year){
	                    if($value['pricing'][$type_price['type']][$key_price])
	                        $query_prices[$year] =  $value['pricing']['register'][$key_price]*$key_price;
	                    else
	                        $query_prices[$year] =  0;
	                }
	                if(mysql_num_rows($result_prices)>0){
	                    $data_prices = mysql_fetch_array($result_prices);
	                    //UPDATE RECORD
	                    update_query($table_prices,$query_prices,$where_prices);
	                }
	                else{
	                    //CREATE RECORD
	                    $values_prices = $query_prices;
	                    $values_prices['tsetupfee'] = $client_group;
	                    $values_prices['type']      = $type_price['name'];
	                    $values_prices['currency']  = '1';
	                    $values_prices['relid']     = $data['id'];
	                    insert_query($table_prices,$values_prices);
	                }
	            }
	        }
	    }
	    $values = array('status'=>'success');
	    return $values;
	}
	//TOOLS GET BALANCE
	public function get_balance($params){
	    $this->check_configuration($params);
	    $fields = array();
	    $module = 'deposit';
	    $action = 'balance.json';
	    $RESTful= 'GET';
	    $params['action'] = 'Balance';
	    try{
	        $request = $this->api_call($params, $fields, $module, $action, $RESTful);
	    }catch (Exception $e){
	        return ($e->getMessage());
	    }
	    return $request;
	}
    //UPDATE ALL WHMCS-PRICES
    public function check_update_price($customadminpath, $idprice, $client_group){
        $response = $this->update_whmcs_prices($idprice, $client_group);
        return $response;
    }
}