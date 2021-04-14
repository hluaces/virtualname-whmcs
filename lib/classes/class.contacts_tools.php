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
function tools_contacts($position){
	global $_LANG_VN;
    //MENU
	if(empty($position))
		$position = 'clients_whmcs_fix';
	//$output .= "<h2><strong>".$_LANG_VN['contacts']['tab'].":</strong></h2></br>\n";
	$output  = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='clients_whmcs_fix'?'active':'')."' onclick=\"showSyncOptions('clients_whmcs_fix')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['contacts']['fix_clients']."</a></li>
					<li class='nav-item ".($position=='contacts_whmcs_fix'?'active':'')."' onclick=\"showSyncOptions('contacts_whmcs_fix')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['contacts']['fix_contacts']."</a></li>
				</ul></br>\n";
	//CLIENTS_FIXING
    $output .=
	"<div style='display:none;' name='clients_whmcs_fix' id='clients_whmcs_fix'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN['contacts']['label_clients_fix'].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=contacts&position=clients_whmcs_fix">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $clients_fix = virtualname_clients_fix();
    if(count($clients_fix)>0){
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['label_clients_fix'].'</div>';
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['define_error'].'</div>';
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['define_solution'].'</div>';
    	$output .= '<form method=\'post\' onsubmit=\'return validate_fix();\'>';
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'clients_review_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN['domains']['selectall'].'\' onclick=\'select_all_clients_review(this);\' />';
	    $output .= ' <label for=\'select_all_clients_review\'>&nbsp;'.$_LANG_VN['domains']['selectall'].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['contacts']['fix'].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['contacts']['fix_description'].'\' class=\'btn btn-primary\' />';
	    $output .= '</div>';
    	$output .= '<table id=\'clients_review\' class=\'display contentbox\' cellspacing=\'0\' style=\'width:100%;\'>';
    	$output .= '<thead><tr><th></th><th>'.$_LANG_VN['contacts']['id'].'</th><th>'.$_LANG_VN['contacts']['client'].'</th><th>'.$_LANG_VN['contacts']['status'].'</th><th>'.$_LANG_VN['contacts']['value'].'</th><th>'.$_LANG_VN['contacts']['error']."</th></tr></thead><tbody>";
	    foreach($clients_fix as $contact){
	    	$output .= '<tr>';
	    	$output .= "<td><input type='checkbox' name='clients_review_fix[]' value='".$contact['id'].':*:'.$contact['errorcode'].':*:'.addslashes($contact['errors_value'])."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= '<td><a target=\'_blank\' href=\''.$contact['url'].'\'>'.$contact['id'].'</a></td>';
	    	if(isset($contact['client']))
	    		$output .= '<td><a target=\'_blank\' href=\''.$contact['url'].'\'>'.$contact['client'].'</a></td>';
	    	else
	    		$output .= '<td>'.$contact['contact'].'</td>';
	    	$output .= '<td>'.$contact['status'].'</td>';
	    	$output .= '<td>'.addslashes($contact['value']).'</td>';
	    	if($contact['errorcode'] == 'phone')
	    		$color = 'red';
	    	elseif($contact['errorcode'] == 'state_postcode')
	    		$color = 'orange';
	    	elseif($contact['errorcode'] == 'email')
	    		$color = '#8B1820';
	    	elseif($contact['errorcode'] == 'name')
	    		$color = 'darkmagenta';
	    	elseif($contact['errorcode'] == 'company_address')
	    		$color = 'darkred';
	    	else
	    		$color = 'darkblue';
	    	$output .= '<td style=\'color:'.$color.'\'>'.$contact['error'].'</td>';
	    	$output .= '</tr>';
	    }
	    $output .= '</tbody></table>';
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'clients_review_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN['domains']['selectall'].'\' onclick=\'select_all_clients_review(this);\' />';
	    $output .= ' <label for=\'select_all_clients_review\'>&nbsp;'.$_LANG_VN['domains']['selectall'].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['contacts']['fix'].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['contacts']['fix_description'].'\' class=\'btn btn-primary\' />';
	    $output .= '</div>';
	    $output .= '<input type=\'hidden\' name=\'action_domains\' id=\'action_domains\' value=\'clients_review\'>';
	    $output .= '<script \'text/javascript\'>';
	    $output .= "$(document).ready(function() {".
					    "$('#clients_review').DataTable( {".
   					    	"dom: 'Blfrtip',".
					        "buttons:[".
					            "'copy', 'csv', 'excel', 'pdf', 'print'".
					        "],".
					        "\"lengthMenu\": [[100, 250, 500, -1], [100, 250, 500, \"All\"]],".
					        "\"language\": {".
					            "\"lengthMenu\": \"".$_LANG_VN["datatable"]["lengthMenu"]." _MENU_ ".$_LANG_VN["datatable"]["lengthMenuRecord"]."\",".
					            "\"zeroRecords\": \"".$_LANG_VN["datatable"]["zeroRecords"]."\",".
					            "\"info\": \"".$_LANG_VN["datatable"]["info"]." _PAGE_ ".$_LANG_VN["datatable"]["infoOf"]." _PAGES_\",".
					            "\"infoEmpty\": \"".$_LANG_VN["datatable"]["infoEmpty"]."\",".
					            "\"search\": \"".$_LANG_VN["datatable"]["search"]."\",".
							    "\"paginate\": {".
						            "\"previous\": \"".$_LANG_VN["datatable"]["previous"]."\",".
						            "\"next\": \"".$_LANG_VN["datatable"]["next"]."\"".
							    "},".
					            "\"infoFiltered\": \"(".$_LANG_VN["datatable"]["infoFiltered"]." _MAX_ ".$_LANG_VN["datatable"]["infoFilteredTotal"].")\"".
					        "}".
					    "});".
					"});";
		$output .= "function select_all_clients_review(source){".
						"checkboxes = document.getElementsByName('clients_review_fix[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
		$output .= "function validate_fix(){".
						"return confirm('".$_LANG_VN['contacts']['fix_description']."');".
					"}";
	    $output .= '</script>';
	    $output .= '</form>';
	}
	else
		$output .= $_LANG_VN['contacts']["fix_not_found"];

    $output .= '</div>';
	//CLIENTS_FIXING

	//CONTACTS_FIXING
    $output .=
	"<div style='display:none;' name='contacts_whmcs_fix' id='contacts_whmcs_fix'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN['contacts']['label_contacts_fix'].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=contacts&position=contacts_whmcs_fix">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $contacts_fix = virtualname_contacts_fix();
    if(count($contacts_fix)>0){
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['label_contacts_fix'].'</div>';
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['define_error'].'</div>';
    	$output .= '<div class=\'contentbox\'>'.$_LANG_VN['contacts']['define_solution'].'</div>';
    	$output .= '<form method=\'post\' onsubmit=\'return validate_fix();\'>';
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'contacts_review_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN['domains']['selectall'].'\' onclick=\'select_all_contacts_review(this);\' />';
	    $output .= ' <label for=\'select_all_contacts_review\'>&nbsp;'.$_LANG_VN['domains']['selectall'].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['contacts']['fix'].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['contacts']['fix_description'].'\' class=\'btn btn-primary\' />';
	    $output .= '</div>';
    	$output .= '<table id=\'contacts_review\' class=\'display contentbox\' cellspacing=\'0\' style=\'width:100%;\'>';
    	$output .= '<thead><tr><th></th><th>'.$_LANG_VN['contacts']['id'].'</th><th>'.$_LANG_VN['contacts']['contact'].'</th><th>'.$_LANG_VN['contacts']['status'].'</th><th>'.$_LANG_VN['contacts']['value'].'</th><th>'.$_LANG_VN['contacts']['error']."</th></tr></thead><tbody>";
	    foreach($contacts_fix as $contact){
	    	$output .= '<tr>';
	    	$output .= "<td><input type='checkbox' name='contacts_review_fix[]' value='".$contact['id'].':*:'.$contact['errorcode'].':*:'.addslashes($contact['errors_value'])."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= '<td><a target=\'_blank\' href=\''.$contact['url'].'\'>'.$contact['id'].'</a></td>';
	    	if(isset($contact['contact']))
	    		$output .= '<td><a target=\'_blank\' href=\''.$contact['url'].'\'>'.$contact['contact'].'</a></td>';
	    	else
	    		$output .= '<td>'.$contact['contact'].'</td>';
	    	if(isset($contact['status']))
		    	$output .= '<td>'.$contact['status'].'</td>';
			else
				$output .= '<td></td>';
	    	$output .= '<td>'.addslashes($contact['value']).'</td>';
	    	if($contact['errorcode'] == 'phone')
	    		$color = 'red';
	    	elseif($contact['errorcode'] == 'state_postcode')
	    		$color = 'orange';
	    	elseif($contact['errorcode'] == 'email')
	    		$color = '#8B1820';
	    	elseif($contact['errorcode'] == 'name')
	    		$color = 'darkmagenta';
	    	elseif($contact['errorcode'] == 'company_address')
	    		$color = 'darkred';
	    	else
	    		$color = 'darkblue';
	    	$output .= '<td style=\'color:'.$color.'\'>'.$contact['error'].'</td>';
	    	$output .= '</tr>';
	    }
	    $output .= '</tbody></table>';
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'contacts_review_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN['domains']['selectall'].'\' onclick=\'select_all_contacts_review(this);\' />';
	    $output .= ' <label for=\'select_all_contacts_review\'>&nbsp;'.$_LANG_VN['domains']['selectall'].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['contacts']['fix'].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['contacts']['fix_description'].'\' class=\'btn btn-primary\' />';
	    $output .= '</div>';
	    $output .= '<input type=\'hidden\' name=\'action_domains\' id=\'action_domains\' value=\'contacts_review\'>';
	    $output .= '<script \'text/javascript\'>';
	    $output .= "$(document).ready(function() {".
					    "$('#contacts_review').DataTable( {".
   					    	"dom: 'Blfrtip',".
					        "buttons:[".
					            "'copy', 'csv', 'excel', 'pdf', 'print'".
					        "],".
					        "\"lengthMenu\": [[100, 250, 500, -1], [100, 250, 500, \"All\"]],".
					        "\"language\": {".
					            "\"lengthMenu\": \"".$_LANG_VN["datatable"]["lengthMenu"]." _MENU_ ".$_LANG_VN["datatable"]["lengthMenuRecord"]."\",".
					            "\"zeroRecords\": \"".$_LANG_VN["datatable"]["zeroRecords"]."\",".
					            "\"info\": \"".$_LANG_VN["datatable"]["info"]." _PAGE_ ".$_LANG_VN["datatable"]["infoOf"]." _PAGES_\",".
					            "\"infoEmpty\": \"".$_LANG_VN["datatable"]["infoEmpty"]."\",".
					            "\"search\": \"".$_LANG_VN["datatable"]["search"]."\",".
							    "\"paginate\": {".
						            "\"previous\": \"".$_LANG_VN["datatable"]["previous"]."\",".
						            "\"next\": \"".$_LANG_VN["datatable"]["next"]."\"".
							    "},".
					            "\"infoFiltered\": \"(".$_LANG_VN["datatable"]["infoFiltered"]." _MAX_ ".$_LANG_VN["datatable"]["infoFilteredTotal"].")\"".
					        "}".
					    "});".
					"});";
		$output .= "function select_all_contacts_review(source){".
						"checkboxes = document.getElementsByName('contacts_review_fix[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
		$output .= "function validate_fix(){".
						"return confirm('".$_LANG_VN['contacts']['fix_description']."');".
					"}";
	    $output .= '</script>';
	    $output .= '</form>';
	}
	else
		$output .= $_LANG_VN['contacts']["fix_not_found"];
    $output .= '</div>';
	//CONTACTS_FIXING

    if($position != ""){
		$output .=
		'<script "text/javascript">'.
			'showSyncOptions("'.$position.'");'.
		'</script>';
    }

    return $output;
}
function virtualname_clients_fix(){
	global $_LANG_VN;
	$contacts = array();
	//INVALID EMAILS
	$sql_contact = 'select id, concat_ws(\' \', firstname, lastname, companyname) as client, status, email as value, \'email\' as type from tblclients where email NOT REGEXP \'^[^@]+@[^@]+\.[^@]{2,}$\';';
	$res_contact = mysql_query($sql_contact);
	while($row_contact = mysql_fetch_array($res_contact)){
		$contacts[$row_contact['id']] = array(
			'id' 		  => $row_contact['id'],
			'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['id'].'&contactid=0',
			'client' 	  => $row_contact['client'],
			'status' 	  => $row_contact['status'],
			'value' 	  => $row_contact['value'],
			'errors_value'=> $row_contact['value'],
			'error' 	  => $_LANG_VN['contacts']['fix_email'],
			'errorcode'	  => $row_contact['type']
		);
	}
	//INVALID COMPANY AND ADDRESS1
	$sql_contact = 'select id, concat_ws(\' \', firstname, lastname, companyname) as client, status, concat_ws(\' - \', companyname, address1) as value, \'company_address\' as type, companyname, address1 from tblclients WHERE company NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]*$\' OR address1 NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]*$\' OR trim(address1) = \'\'';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['id'].'&contactid=0',
				'client' 	  => $row_contact['client'],
				'status' 	  => $row_contact['status'],
				'value' 	  => $row_contact['value'],
				'errors_value'=> $row_contact['companyname'].':*:'.$row_contact['address1'],
				'error' 	  => $_LANG_VN['contacts']['fix_company_address'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	//INVALID CLIENTS PHONES
	$sql_contact = 'select id, concat_ws(\' \', firstname, lastname, companyname) as client, status, phonenumber as value, \'phone\' as type from tblclients where phonenumber NOT REGEXP \'^[0-9]+$\' OR CHAR_LENGTH(phonenumber) > 12;';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['id'].'&contactid=0',
				'client' 	  => $row_contact['client'],
				'status' 	  => $row_contact['status'],
				'value' 	  => $row_contact['value'],
				'errors_value'=> $row_contact['value'],
				'error' 	  => $_LANG_VN['contacts']['fix_phone'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	//INVALID STATES
	$sql_contact = 'select id, concat_ws(\' \', firstname, lastname, companyname) as client, status, state, postcode from tblclients where country = \'ES\';';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$response = virtualname_check_state($row_contact['state'], $row_contact['postcode']);
			if(isset($response['error']) && $response['error']){
				$contacts[$row_contact['id']] = array(
					'id' 		  => $row_contact['id'],
					'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['id'].'&contactid=0',
					'client' 	  => $row_contact['client'],
					'status' 	  => $row_contact['status'],
					'value' 	  => $row_contact['state'].' - '.$row_contact['postcode'],
					'errors_value'=> $row_contact['state'].':*:'.$row_contact['postcode'],
					'error' 	  => $_LANG_VN['contacts'][$response['error']],
					'errorcode'	  => 'state_postcode'
				);
			}
		}
	}
	//INVALID FIRSTNAME AND LASTNAME
	$sql_contact = 'select id, concat_ws(\' \', firstname, lastname, companyname) as client, status, \'name\' as type, firstname, lastname from tblclients WHERE firstname NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]*$\' OR lastname NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]*$\' OR trim(firstname) = \'\' OR trim(lastname)';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['id'].'&contactid=0',
				'client' 	  => $row_contact['client'],
				'status' 	  => $row_contact['status'],
				'value' 	  => $row_contact['firstname'].' - '.$row_contact['lastname'],
				'errors_value'=> $row_contact['firstname'].':*:'.$row_contact['lastname'],
				'error' 	  => $_LANG_VN['contacts']['fix_name'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	return $contacts;
}
function virtualname_contacts_fix(){
	global $_LANG_VN;
	$contacts = array();
	//INVALID EMAILS
	$sql_contact = 'select id, userid, concat_ws(\' \', firstname, lastname, companyname) as contact, email as value, \'email\' as type from tblcontacts where email NOT REGEXP \'^[^@]+@[^@]+\.[^@]{2,}$\';';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['userid'].'&contactid='.$row_contact['id'],
				'contact' 	  => $row_contact['contact'],
				'value' 	  => $row_contact['value'],
				'errors_value'=> $row_contact['value'],
				'error' 	  => $_LANG_VN['contacts']['fix_email'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	//INVALID COMPANY AND ADDRESS1
	$sql_contact = 'select id, userid, concat_ws(\' \', firstname, lastname, companyname) as client, concat_ws(\' - \', companyname, address1) as value, \'company_address\' as type, companyname, address1 from tblcontacts WHERE company NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]*$\' OR address1 NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]*$\' OR trim(address1) = \'\'';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['userid'].'&contactid='.$row_contact['id'],
				'contact' 	  => $row_contact['client'],
				'value' 	  => $row_contact['value'],
				'errors_value'=> $row_contact['companyname'].':*:'.$row_contact['address1'],
				'error' 	  => $_LANG_VN['contacts']['fix_company_address'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	//INVALID CONTACTS PHONES
	$sql_contact = 'select id, userid, concat_ws(\' \', firstname, lastname, companyname) as client, phonenumber as value, \'phone\' as type from tblcontacts where phonenumber NOT REGEXP \'^[0-9]+$\' OR CHAR_LENGTH(phonenumber) > 12;';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['userid'].'&contactid='.$row_contact['id'],
				'contact' 	  => $row_contact['client'],
				'value' 	  => $row_contact['value'],
				'errors_value'=> $row_contact['value'],
				'error' 	  => $_LANG_VN['contacts']['fix_phone'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	//INVALID STATES
	$sql_contact = 'select id, userid, concat_ws(\' \', firstname, lastname, companyname) as client, state, postcode from tblcontacts where country = \'ES\';';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$response = virtualname_check_state($row_contact['state'], $row_contact['postcode']);
			if(isset($response['error']) && $response['error']){
				$contacts[$row_contact['id']] = array(
					'id' 		  => $row_contact['id'],
					'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['userid'].'&contactid='.$row_contact['id'],
					'contact' 	  => $row_contact['client'],
					'value' 	  => $row_contact['state'].' - '.$row_contact['postcode'],
					'errors_value'=> $row_contact['state'].':*:'.$row_contact['postcode'],
					'error' 	  => $_LANG_VN['contacts'][$response['error']],
					'errorcode'	  => 'state_postcode'
				);
			}
		}
	}
	//INVALID FIRSTNAME AND LASTNAME
	$sql_contact = 'select id, userid, concat_ws(\' \', firstname, lastname, companyname) as client, \'name\' as type, firstname, lastname from tblcontacts WHERE firstname NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]*$\' OR lastname NOT REGEXP \'^[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]*$\' OR trim(firstname) = \'\' OR trim(lastname)';
	$res_contact = mysql_query($sql_contact);
	if($res_contact){
		while($row_contact = mysql_fetch_array($res_contact)){
			$contacts[$row_contact['id']] = array(
				'id' 		  => $row_contact['id'],
				'url' 	 	  => './clientsdatadomaincontacts.php?action=clientscontacts&userid='.$row_contact['userid'].'&contactid='.$row_contact['id'],
				'contact' 	  => $row_contact['client'],
				'value' 	  => $row_contact['firstname'].' - '.$row_contact['lastname'],
				'errors_value'=> $row_contact['firstname'].':*:'.$row_contact['lastname'],
				'error' 	  => $_LANG_VN['contacts']['fix_name'],
				'errorcode'	  => $row_contact['type']
			);
		}
	}
	return $contacts;
}
function virtualname_check_state($state, $zipcode){
	$es_states = array(
		'ARABA' => '01',
		'ALBACETE' => '02',
		'ALICANTE' => '03',
		'ALMERIA' => '04',
		'AVILA' => '05',
		'BADAJOZ' => '06',
		'ILLES BALEARS' => '07',
		'BARCELONA' => '08',
		'BURGOS' => '09',
		'CACERES' => '10',
		'CADIZ' => '11',
		'CASTELLON' => '12',
		'CIUDAD REAL' => '13',
		'CORDOBA' => '14',
		'CORUÑA, A' => '15',
		'CUENCA' => '16',
		'GIRONA' => '17',
		'GRANADA' => '18',
		'GUADALAJARA' => '19',
		'GIPUZKOA' => '20',
		'HUELVA' => '21',
		'HUESCA' => '22',
		'JAEN' => '23',
		'LEON' => '24',
		'LLEIDA' => '25',
		'RIOJA, LA' => '26',
		'LUGO' => '27',
		'MADRID' => '28',
		'MALAGA' => '29',
		'MURCIA' => '30',
		'NAVARRA' => '31',
		'OURENSE' => '32',
		'ASTURIAS' => '33',
		'PALENCIA' => '34',
		'PALMAS, LAS' => '35',
		'PONTEVEDRA' => '36',
		'SALAMANCA' => '37',
		'SANTA CRUZ DE TENERIFE' => '38',
		'CANTABRIA' => '39',
		'SEGOVIA' => '40',
		'SEVILLA' => '41',
		'SORIA' => '42',
		'TARRAGONA' => '43',
		'TERUEL' => '44',
		'TOLEDO' => '45',
		'VALENCIA' => '46',
		'VALLADOLID' => '47',
		'BIZKAIA' => '48',
		'ZAMORA' => '49',
		'ZARAGOZA' => '50',
		'CEUTA' => '51',
		'MELILLA' => '52'
	);
	if(!isset($es_states[$state]))
		$value = array('error' => 'state_not_found');
	else{
		if(strlen($zipcode) != 5 OR !is_numeric($zipcode))
			$value = array('error' => 'invalid_zipcode_format');
		else{
			if($es_states[$state] == substr($zipcode, 0, 2))
				$value = array('success');
			else
				$value = array('error' => 'invalid_zipcode_state');
		}
	}
	return $value;
}
function virtualname_replace_accents($value){
	$accents_values = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
	                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
	                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
	                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
	                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
	$str = strtr($value, $accents_values);
	return $str;
}
function virtualname_tools_contacts_fix($contacts, $type){
	if($type == 'clients')
		$table = 'tblclients';
	elseif($type == 'contacts')
		$table = 'tblcontacts';
	else
		return false;
	foreach($contacts as $fix){
		$exp = explode(':*:', $fix);
		$contact = $exp[0];
		$error = $exp[1];
		$sql_upd = '';
		//errors: phone email company_address state_postcode name
		if($error == 'name'){
			//$phone = preg_replace('/[0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]/', '', $exp[2]);
			$firstname = preg_replace('/[^0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]/', '', $exp[2]);
			$lastname = preg_replace('/[^0-9a-zA-ZñÑáéíóúÁÉÍÓÚ -]/', '', $exp[3]);
			if(empty($firstname))
				$firstname = $lastname;
			if(empty($lastname))
				$lastname = $firstname;
			$sql_upd = 'UPDATE '.$table.' SET firstname = \''.$firstname.'\', lastname = \''.$lastname.'\' WHERE id = '.$contact;
		}
		elseif($error == 'state_postcode'){
			$state = strtoupper(virtualname_replace_accents($exp[2]));
			$postcode = preg_replace('/[^0-9]/', '', $exp[3]);
			$sql_upd = 'UPDATE '.$table.' SET state = \''.$state.'\', postcode = \''.$postcode.'\' WHERE id = '.$contact;
		}
		elseif($error == 'phone'){
			$phone = preg_replace('/[^0-9]/', '', $exp[2]);
			if(empty($phone))
				continue;
			$sql_upd = 'UPDATE '.$table.' SET phonenumber = \''.$phone.'\' WHERE id = '.$contact;
		}
		elseif($error == 'company_address'){
			$companyname = preg_replace('/[^0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]/', '', $exp[2]);
			$address1 = preg_replace('/[^0-9a-zA-ZñÑáéíóúÁÉÍÓÚ.,/&()\º\ª\- -]/', '', $exp[3]);
			$sql_upd = 'UPDATE '.$table.' SET companyname = \''.$companyname.'\', address1 = \''.$address1.'\' WHERE id = '.$contact;
		}
		elseif($error == 'email'){
			$email = filter_var($exp[2], FILTER_SANITIZE_EMAIL);
			if(empty($email))
				continue;
			$sql_upd = 'UPDATE '.$table.' SET email = '.$email.' WHERE id = '.$contact;
		}
		if(!empty($sql_upd))
			$res_upd = mysql_query($sql_upd);
	}
}
?>