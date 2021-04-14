<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE TOOLS
// * PLUGIN Api v1
// * WHMCS version 8.1.X
// * @copyright Copyright (c) 2021, Virtualname
// * @version 1.1.9
// * @link http://www.virtualname.es
// * @package WHMCSModule
// * @subpackage TCpanel
// * File description: Tools Extra Module
// *************************************************************************
function tools_balance(){
    global $_LANG_VN;
    //GET BALANCE
    $balance = tools_get_balance();
    $output  = '';
    $output .= "<h2><strong>".$_LANG_VN["balance"]["founds"]."</strong></h2>\n";
    $output .= "<p><h3>".$_LANG_VN["balance"]["current"].": </h3><h1><strong style='color:green;'>".$balance["balance"]."</strong> ".$balance["currency"]."</h1></p></br>\n";
    return $output;
}
//GET BALANCE VNAME
function tools_get_balance(){
    global $tb_virtualname_tools, $vname_prices, $vname_admin;
    virtualname_init();
    $params  = $vname_admin->config();
    $request = $vname_prices->get_balance($params);
    if(isset($request['status']['code']) && ($request['status']['code'] < 200 || $request['status']['code'] > 299))
        return false;
    else{
        if(isset($request['response']))
            return array('balance'=>$request['response']['balance'], 'currency'=>$request['response']['currency']);
        else
            return false;
    }
}
//UPDATE WHMCS PRICES
function tools_update_whmcs_prices($idprice, $client_group){
    global $vname_prices, $vname_admin;
    virtualname_init();
    //GET CLIENT PRICES DETAILS
    $request = $vname_prices->prices_details($idprice);
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
        $eur_currency   = mysql_fetch_array($eur_result);
        //EUR CURRENCY
        if(mysql_num_rows($result)>0 AND $value['currency'] == 'EUR' AND mysql_num_rows($eur_result)>0){
            $data           = mysql_fetch_array($result);
            //IF THIS EXTENSION WAS ACTIVED NOW CHECK IF EXIST ALL RECORDS
            $table_prices  = 'tblpricing';
            foreach($type_prices as $type_price){
                //EUR
                $where_prices_eur  = array('type'=>$type_price['name'],'relid'=>$data['id'], 'tsetupfee'=>$client_group, 'currency'=>$eur_currency['id']);
                $result_prices_eur = select_query($table_prices,array(),$where_prices_eur);
                $query_prices_eur = array();
                foreach($years_prices as $key_price => $year){
                    if($value['pricing'][$type_price['type']][$key_price]){
                        $query_prices_eur[$year] =  $value['pricing'][$type_price['type']][$key_price]*$key_price;
                    }
                    else{
                        $query_prices_eur[$year] =  0;
                    }
                }
                //EUR
                if(mysql_num_rows($result_prices_eur)>0){
                    $data_prices = mysql_fetch_array($result_prices_eur);
                    //UPDATE RECORD
                    update_query($table_prices,$query_prices_eur,$where_prices_eur);
                }
                else{
                    //CREATE RECORD
                    $values_prices = $query_prices_eur;
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
function tools_return_page($customadminpath, $action, $error){
        $vn_module_dir = 'modules/addons/virtualname_tools/';
        $currentURL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $cleanURL   = explode($vn_module_dir.'lib/sync/tools.php', $currentURL);
        if($error)
            $returnURL  = $cleanURL[0].$customadminpath.'/configregistrars.php?action=\'error\'&error='.$error.'&saved=true#virtualname';
        else
            $returnURL  = $cleanURL[0].$customadminpath.'/addonmodules.php?module=virtualname_tools&update=price&tab=synchronization&position=prices';
        echo '<script type=\'text/javascript\'>window.location.replace(\''.$returnURL.'\')</script>';
}
?>