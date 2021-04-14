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
function virtualname_tcpanel_list(){
	//DOMAINS ALL LIST
	$domainList = array();
    $resultList = select_query("mod_virtualname_tools", "value", array("type" => "syncList"));
    while($dataList = mysql_fetch_assoc($resultList)){
        $list = json_decode($dataList["value"], true);
        foreach($list as $key => $value){
            $domainList[] = strtolower($key);
        }
    }
    return $domainList;
}
// SYNCHRONIZATION DATA
function tools_synchronization($position){
	global $tb_virtualname_tools, $_LANG_VN, $module_config, $vname_prices;
    virtualname_init();
    //GET ALL TCPANEL PRICES
    $request = $vname_prices->prices_details(false);
    //GET ALL WHMCS CLIENTS WITH DOMAINS
    $optionsClient = "";
	$clients = mysql_query("select * from tblclients where id in (select distinct(userid) from tbldomains where registrar = 'virtualname');");
    while($data = mysql_fetch_array($clients)){
        $optionsClient .= "<option value='".$data["id"]."'>".$data["firstname"]." ".$data["lastname"]."-".$data["email"]."</option>";
    }

    //GET TCPANEL PRICES
    $optionsPrices = '';
    if(isset($request["response"])){
	    foreach($request["response"] as $price_request)
	        $optionsPrices .= "<option value='".$price_request["id"]."'>".$price_request["name"]."</option>";
	}
    if(isset($request["response"][0]))
    	$first_price = $request["response"][0]["id"];
    else
    	$first_price = 0;

    //GET ALL WHMCS CLIENTS GROUPS
    $optionsGroup = '';
    $clientsGroups =  getclientgroups();
    foreach($clientsGroups as $key => $group){
        $optionsGroup .= "<option style='background-color:".$group["colour"]."' value='".$key."'>".$group["name"]."</option>";
    }

    //GET LIST FROM RECOVER SYNC
    $resultListSync = select_query("mod_virtualname_tools","value", array("config"=>"syncListDomains"));
    $dataListSync	= mysql_fetch_array($resultListSync);
    $listSync   	= $dataListSync["value"];

	if(empty($position))
		$position = 'domains';

	$output	 = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='domains'?'active':'')."' onclick=\"showSyncOptions('syncDomainsOption')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['sync']['domains']."</a></li>
					<li class='nav-item ".($position=='contacts'?'active':'')."' onclick=\"showSyncOptions('syncContactsOption')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['sync']['contacts']."</a></li>
					<li class='nav-item ".($position=='prices'?'active':'')."' onclick=\"showSyncOptions('syncPricesOption')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['sync']['prices']."</a></li>
				</ul></br>\n";
    $output .=
    	'<div id="progressbar" class="progress ui-progressbar">
		  <div class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar"
		  aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
		    0 - 0
		  </div>
		</div>
		';

	//syncConfigOptions
	$config_loads = array();
	if($module_config["updateContacts"] == "on"){
		$messageUpdateContacts  = "<font color ='green'>&#10004; ".$_LANG_VN["sync"]["update"]." ".$_LANG_VN["sync"]["enable"]."</font>";
		$config_loads["updateContacts"] = 1;
	}
	else{
		$config_loads["updateContacts"] = 0;
	   	$messageUpdateContacts  = "<font color ='red'>&#10008; ".$_LANG_VN["sync"]["update"]." ".$_LANG_VN["sync"]["disable"]."</font>";

	}
	if($module_config["updateDomains"] == "on"){
		$config_loads["updateDomains"] = 1;
		$messageUpdateDomains = "<font color ='green'>&#10004; ".$_LANG_VN["sync"]["update"]." ".$_LANG_VN["sync"]["enable"]."</font>";
	}
	else{
		$config_loads["updateDomains"] = 0;
		$messageUpdateDomains = "<font color ='red'>&#10008; ".$_LANG_VN["sync"]["update"]." ".$_LANG_VN["sync"]["disable"]."</font>";
	}
	if($module_config["syncData"] == "on"){
		$config_loads["syncData"] = 1;
		$messageUpdateDomains .= "<br><font color ='green'>&#10004; ".$_LANG_VN["sync"]["real_data"]."</font>";
		$messageUpdateContacts  .= "<br><font color ='green'>&#10004; ".$_LANG_VN["sync"]["real_data"]."</font>";
	}
	else{
		$config_loads["syncData"] = 0;
		$messageUpdateDomains .= "<br><<font color ='red'>&#10008; ".$_LANG_VN["sync"]["real_data"]."</font>";
		$messageUpdateContacts  .= "<br><<font color ='red'>&#10008; ".$_LANG_VN["sync"]["real_data"]."</font>";

	}

	if($module_config["updateExpireDoms"] == "on")
		$config_loads["updateExpireDoms"] = 1;
	else
		$config_loads["updateExpireDoms"] = 0;

	if($module_config["updateCancelDoms"] == "on")
		$config_loads["updateCancelDoms"] = 1;
	else
		$config_loads["updateCancelDoms"] = 0;

	//syncPricessOption
    $eur_result = select_query('tblcurrencies',array(),array('code'=>'EUR'));
	if($optionsPrices == '' || mysql_num_rows($eur_result) == 0)
		$noPrices = 'disabled';
	else
		$noPrices = '';

    $output .=
	"<div ".($position=='prices'?'':"style='display:none;'")." name='syncPricesOption' id='syncPricesOption'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["sync"]["clientgroup"].":</h1></p></br></br>".
	        "<div class='contentbox'>".$_LANG_VN["sync"]["warningprice"]."</div></br>".
	        "<div class='contentbox'>".$_LANG_VN["sync"]["warningpriceconfig"]." <a href='./configcurrencies.php' target='_blank'>".$_LANG_VN["sync"]["here"]."</a></div></br>".
	        "<span>".$_LANG_VN["sync"]["tcpanelprice"].":</span></br>".
	        "<select name='selectedPrice' id='selectedPrice' style='min-width: 10%;' onchange='changeSelectedPrice();' ".$noPrices." class='form-control select-inline'>".
	            $optionsPrices.
	        "</select>".
	        "</br></br><span>".$_LANG_VN["sync"]["wgroup"].":</span></br>".
		    "<select name='selectedGroup' id='selectedGroup' onchange='changeSelectedGroup();' ".$noPrices." class='form-control select-inline'>".
		        "<option id='0' name='0' value='0' selected>".$_LANG_VN["sync"]["defaultprice"]."</option>".
		        $optionsGroup.
		    "</select>".
		    "</br></br>".
		    "<a id='priceURL' name='priceURL' href='../modules/addons/virtualname_tools/lib/sync/tools.php?action=updateprices&idprice=".$first_price."&group=0'".
		    " class='btn btn-primary ".$noPrices."' ".$noPrices.">".$_LANG_VN["sync"]["updateprice"]."</a>".
		"</div>".
    '</div>';

    //syncContactsOption
    $output .=
	"<div ".($position=='contacts'?'':"style='display:none;'")." name='syncContactsOption' id='syncContactsOption'>".
		"<p><h1>".$_LANG_VN["sync"]["client"].":</h1></p></br>".
	    "<div class='contentbox'>".
		    "<select name='selectedClientContacts' id='selectedClientContacts' style='max-width:25%;' class='form-control select-inline'>".
		        "<option value='0' selected>".$_LANG_VN["sync"]["allclients"]."</option>".
		        $optionsClient.
		    "</select>".
		    "<img id='vnloadingContact' name='vnloadingContact' src='../modules/addons/virtualname_tools/themes/images/vn_loading.gif' style='display:none;padding-left: 20px;padding-right:20px;height:14px;' />".
		    "<button id='abortAjaxSyncContact' name='abortAjaxSyncContact' style='display:none;' class='btn btn-primary' onclick='window.location.reload();return false;'>".$_LANG_VN["sync"]["stop"]."</button>".
		    "</br></br>".
		    "<a id='syncContactsButton' name='syncContactsButton' href='#' onclick='initSync(\"contacts\", \"".$config_loads["updateContacts"]."\", \"".$config_loads["syncData"]."\");return false;'".
		    "class='btn btn-primary'".
		    "display: inline-block; padding: 2px 8px;'>".$_LANG_VN["sync"]["contactlaunch"]."</a>".
		    "</br></br>".$messageUpdateContacts.
		    "</br></br>".
		    "<div id='container_contacts_sync' name='container_contacts_sync' style='overflow: auto;'></div>".
	    	'<ul class="legend">
			    <li><span class="created"></span> '.$_LANG_VN["sync"]["contactcreated"].'</li>
			    <li><span class="updated"></span> '.$_LANG_VN["sync"]["contactupdated"].'</li>
			    <li><span class="vinculated"></span> '.$_LANG_VN["sync"]["contactlinked"].'</li>
			    <li><span class="checked"></span> '.$_LANG_VN["sync"]["contactcorrect"].'</li>
			    <li><span class="error"  ></span> '.$_LANG_VN["sync"]["contacterror"].'</li>
			</ul></br>'.
			"</br><p>* ".$_LANG_VN["sync"]["contactmessage"]."</p>".
			"<script>$('#selectedClientContacts').select2({width:'50%'});</script>".
		"</div>".
    '</div>';

	//syncDomainsOption
    $output .=
	"<div ".($position=='domains'?'':"style='display:none;'")." name='syncDomainsOption' id='syncDomainsOption'>".
		"<p><h1>".$_LANG_VN["sync"]["clientgroup"].":</h1></p></br>".
	    "<div class='contentbox'>".
		    "<select name='selectedClient' id='selectedClient' style='max-width:50%;' class='form-control select-inline'>".
		        "<option value='0'>".$_LANG_VN["sync"]["allclients"]."</option>".
		        $optionsClient.
		    "</select>".
		    "<img id='vnloading' name='vnloading' src='../modules/addons/virtualname_tools/themes/images/vn_loading.gif' style='display:none;padding-left: 20px;padding-right:20px;height:14px;' />".
		    "<button id='abortAjaxSync' name='abortAjaxSync' style='display:none;' class='btn' onclick='window.location.reload();return false;'>".$_LANG_VN["sync"]["stop"]."</button>".
		    "</br></br>".
		    "<a id='syncDomainsButton' name='syncDomainsButton' href='#' onclick='initSync(\"domains\", \"".$config_loads["updateDomains"]."\", \"".$config_loads["syncData"]."\", \"\", \"".$config_loads["updateExpireDoms"]."\", \"".$config_loads["updateCancelDoms"]."\");return false;'".
		    "class='btn btn-primary'>".$_LANG_VN["sync"]["domainslaunch"]."</a>".
		    "</br></br>".$messageUpdateDomains.
		    "</br></br>".
		    "<div id='container_sync' name='container_sync' style='overflow: auto;'></div>".
	    	'<ul class="legend">
			    <li><span class="updated"></span> '.$_LANG_VN["sync"]["domainsfinish"].'</li>
			    <li><span class="checked"></span> '.$_LANG_VN["sync"]["domainscorrect"].'</li>
			    <li><span class="warning"></span> '.$_LANG_VN["sync"]["domainswarning"].'</li>
			    <li><span class="error"></span> '.$_LANG_VN["sync"]["domainsnotfound"].'</li>
			</ul></br>'.
			"<script>$('#selectedClient').select2({width:'50%'});</script>".
		'</div>'.
    '</div>';

    return $output;
}
//GET API STATUS
function tools_check_api(){
    //INIT MODULE
	global $tb_virtualname_tools, $vname_admin, $vname_api;
    virtualname_init();
	$params  = $vname_admin->config();
	$request = $vname_api->api_authentication($params);
	if(isset($request['status']['code']) && ($request['status']['code'] < 200 || $request['status']['code'] > 299))
		$status = false;
	else
		$status = true;
	return $status;
}
//GET CURRENT LANG
function tools_get_config_lang($adminID){
    $langs_vn = array();
    $table = "tbladmins";
    $fields = "language";
    $where = array("id"=>$adminID);
    $result = select_query($table,$fields,$where);
    $data = mysql_fetch_array($result);
    return $data["language"];
}
//MODULE HEADERS
function tools_header(){
	$header = '';
	//CUSTOM
	$header .= '<script type=\'text/javascript\' src=\'../modules/addons/virtualname_tools/themes/js/virtualname_tools.js\'></script>';
    $header .= '<link rel=\'stylesheet\' type=\'\' href=\'../modules/addons/virtualname_tools/themes/css/virtualname_tools.css\' media=\'screen\'/>';
    //DATATABLES
    $header .= '<link rel=\'stylesheet\' type=\'\' href=\'https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css\'/>';
    $header .= '<link rel=\'stylesheet\' type=\'\' href=\'https://cdn.datatables.net/buttons/1.2.1/css/buttons.dataTables.min.css\'/>';
	$header .= '<script type=\'text/javascript\' language=\'javascript\' src=\'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js\'></script>';
	$header .= '<script type=\'text/javascript\' language=\'javascript\' src=\'https://cdn.datatables.net/buttons/1.2.1/js/dataTables.buttons.min.js\'></script>';
	$header .= '<script type=\'text/javascript\' language=\'javascript\' src=\'//cdn.datatables.net/buttons/1.2.1/js/buttons.flash.min.js\'></script>';
	$header .= '<script type=\'text/javascript\' language=\'javascript\' src=\'//cdn.datatables.net/buttons/1.2.1/js/buttons.html5.min.js\'></script>';
	$header .= '<script type=\'text/javascript\' language=\'javascript\' src=\'//cdn.datatables.net/buttons/1.2.1/js/buttons.print.min.js\'></script>';
	//SELECT
	$header .= '<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />';
	$header .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>';
	return $header;
}
//MODULE TABS
function tools_menu($action, $position, $nav_tab){

	global $tb_virtualname_tools, $_LANG_VN, $module_config;
	$tools_tabs = '';
	if(isset($_GET['update']))
		$update = $_GET['update'];
	else
		$update = false;
	if($update){
		if($update == 'price'){
			$tools_tabs .= '<div class=\'successbox\'><strong><span class=\'title\'>'.$_LANG_VN['menu']['price'].'</span></strong><br>'.$_LANG_VN['menu']['pricemessage'].'</div>';
		}
	}
	if($action == '')
		$action = 'synchronization';
	$header = tools_header();
	//SET TABS
	$tab_url = 'addonmodules.php?module=virtualname_tools';
	$tools_tabs .= '
	<div id=\'content_padded\'>
		<div id=\'content_start\' style=\'display:none;\'>
			<div>
				<ul class=\'nav nav-pills\'>
					<li id=\'tab0\' class=\''.($action=='synchronization'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=synchronization\'>'.$_LANG_VN['menu']['sync'].'</a></li>
					<li id=\'tab1\' class=\''.($action=='domains'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=domains\'>'.$_LANG_VN['menu']['domain_management'].'</a></li>
					<li id=\'tab2\' class=\''.($action=='contacts'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=contacts\'>'.$_LANG_VN['menu']['contact_management'].'</a></li>
					<li id=\'tab3\' class=\''.($action=='imports'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=imports\'>'.$_LANG_VN['menu']['imports'].'</a></li>
					<li id=\'tab4\' class=\''.($action=='invoicing'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=invoicing\'>'.$_LANG_VN['menu']['invoicing'].'</a></li>
					<li id=\'tab5\' class=\'tab\'><a href=\'#\'>'.$_LANG_VN['menu']['accountBalance'].'</a></li>
					<li id=\'tab6\' class=\''.($action=='list'?'active':'').'\'><a href=\'./'.$tab_url.'&tab=list\'>'.$_LANG_VN['menu']['lists'].'</a></li>
					<li id=\'tab\' class=\'disabled\'><a href=\'#\' style=\'color: lightgrey;\'>'.$_LANG_VN['menu']['dnsManagement'].'</a></li>
					<li id=\'tab\' class=\'disabled\'><a href=\'#\' style=\'color: lightgrey;\'>'.$_LANG_VN['menu']['transfers'].'</a></li>
				</ul>
			</div>';

	$synchronization = '';
	$domains = '';
	$imports = '';
	$invoicing = '';
	$contacts = '';
	$list = '';

	if($action == 'synchronization')
		$synchronization = tools_synchronization($position);
	elseif($action == 'domains')
		$domains = tools_domains($position);
	elseif($action == 'imports')
		$imports = tools_imports($position);
	elseif($action == 'invoicing')
		$invoicing = tools_invoicing($position);
	elseif($action == 'contacts')
		$contacts = tools_contacts($position);
	elseif($action == 'list')
		$list = tools_domains_list($position);
	else
		$synchronization = tools_synchronization();

	$tools_tabs .= '	<div id=\'tab0box\' class=\'tabbox'.($action=='synchronization'?' tabmain':'').'\'><div id=\'tab_content\'>'.$synchronization.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab1box\' class=\'tabbox'.($action=='domains'?' tabmain':'').'\'><div id=\'tab_content\'>'.$domains.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab2box\' class=\'tabbox'.($action=='contacts'?' tabmain':'').'\'><div id=\'tab_content\'>'.$contacts.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab3box\' class=\'tabbox'.($action=='imports'?' tabmain':'').'\'><div id=\'tab_content\'>'.$imports.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab4box\' class=\'tabbox'.($action=='invoicing'?' tabmain':'').'\'><div id=\'tab_content\'>'.$invoicing.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab5box\' class=\'tabbox\'><div id=\'tab_content\'>'.tools_balance().'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tab6box\' class=\'tabbox'.($action=='list'?' tabmain':'').'\'><div id=\'tab_content\'>'.$list.'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tabbox\' class=\'tabbox\'><div id=\'tab_content\'>'.tools_dns().'</div></div>'."\n";
	$tools_tabs .= '	<div id=\'tabbox\' class=\'tabbox\'><div id=\'tab_content\'>'.virtualname_transfers().'</div></div>'."\n";
	$tools_tabs .= '</div></div>';

	$lang_vn = '<script>';
	$lang_vn .= '$(document).ready(function() {';
	$lang_vn .= ' $(\'#content_start\').css(\'display\', \'block\');';
	$lang_vn .= ' LANG_VN = '.json_encode($_LANG_VN).';';
	$lang_vn .= '});';
	$lang_vn .= '</script>';

	$response = $header . $tools_tabs . $lang_vn;
	return $response;
}
//GET INSTALL STATUS
function tools_check_install(){
	global $tb_virtualname_tools;
	if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$tb_virtualname_tools."'"))==1)
		return TRUE;
	else
		return FALSE;
}
function tools_check_version(){
	global $vname_admin;
    virtualname_init();
	$params  = $vname_admin->config();
	$verified_domains_module_version = tools_get_module_accepted_versions();
	if(in_array($params['Module-Version'], $verified_domains_module_version))
		return TRUE;
	else
		return FALSE;
}
//GET MODULE VERSION
function tools_get_module_accepted_versions(){
    $version_URL = "http://whmcs.virtualname.net/whmcs-repositories/whmcs-virtualname-tools-accepted-version";
    $version_content = explode("-",file_get_contents($version_URL));
    return $version_content;
}
//GET LAST SYNC DOMAINS
function tools_check_last_sync(){
	$need_sync = false;
	$sync_check = mysql_query('SELECT max(UPDATE_TIME) as last_check FROM information_schema.tables WHERE TABLE_NAME = \'mod_virtualname_tools\'');
	$data = mysql_fetch_array($sync_check);
	$last_check = $data['last_check'];
	$get_rows = mysql_query('select count(1) as total_rows from mod_virtualname_tools;');
	$data = mysql_fetch_array($get_rows);
	$total_rows = $data['total_rows'];
	if($last_check == '' || $last_check == NULL || $total_rows == 0){
		$need_sync = true;
	}
	else{
		$now = time();
		$last_date = strtotime($last_check);
		$datediff = $now - $last_date;
		$days = floor($datediff / (60 * 60 * 24));
		if($days >= 1)
			$need_sync = true;
	}
	return $need_sync;
}
function tools_setting(){
	return "In progress...";
}
?>