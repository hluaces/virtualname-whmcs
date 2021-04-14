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
function virtualname_invoicing_fix(){
	global $_LANG_VN;
	$invoices = array();
	//PAID INVOICES WITHOUT MORE THAN ONE TRANSACTIONS
	$sqlInv = "select invoiceid from tblaccounts group by invoiceid having count(1)>1";
	$resInv = mysql_query($sqlInv);

	$whereInv = '';
	while($rowInv = mysql_fetch_array($resInv))
		$whereInv .= $rowInv["invoiceid"].",";
	$whereInv .= "0";
	$sqlInv   = "select userid, id as invoiceid, status, invoicenum, datepaid, total, (select concat_ws(' ', firstname, lastname, companyname) as cliente from tblclients where id = userid) as client ";
	$sqlInv  .= "from tblinvoices ";
	$sqlInv  .= "where id in (".$whereInv.");";

	$resInv = mysql_query($sqlInv);

	while($rowInv = mysql_fetch_array($resInv)){
		$invoices[$rowInv['invoiceid']] = array(
			"invoiceid"   => $rowInv['invoiceid'],
			"invoicenum"   => $rowInv['invoicenum'],
			"url" 	 	  => "./invoices.php?action=edit&id=".$rowInv['invoiceid'],
			"userid"      => $rowInv['userid'],
			"status" 	  => $rowInv['status'],
			"client" 	  => $rowInv['client'],
			"datepaid" 	  => $rowInv['datepaid'],
			"total" 	  => $rowInv['total'],
			"error" 	  => $_LANG_VN["invoicing"]["multipletransactions"],
			"errorcode"	  => "multipletransactions"
		);
	}
	//PAID INVOICES WITHOUT TRANSACTIONS
	$sqlInv = "select invoiceid from tblaccounts";
	$resInv = mysql_query($sqlInv);
	while($rowInv = mysql_fetch_array($resInv))
		$whereInv .= $rowInv["invoiceid"].",";
	$whereInv .= "0";
	$sqlInv  = "select userid, id as invoiceid, status, invoicenum, datepaid, total, (select concat_ws(' ', firstname, lastname, companyname) as cliente from tblclients where id = userid) as client ";
	$sqlInv .= "from tblinvoices ";
	$sqlInv .= "where id not in (".$whereInv.") and (status='Paid' or invoicenum!='');";
	$resInv = mysql_query($sqlInv);
	while($rowInv = mysql_fetch_array($resInv)){
		$invoices[$rowInv['invoiceid']] = array(
			"invoiceid"   => $rowInv['invoiceid'],
			"invoicenum"   => $rowInv['invoicenum'],
			"url" 	 	  => "./invoices.php?action=edit&id=".$rowInv['invoiceid'],
			"userid"      => $rowInv['userid'],
			"status" 	  => $rowInv['status'],
			"client" 	  => $rowInv['client'],
			"datepaid" 	  => $rowInv['datepaid'],
			"total" 	  => $rowInv['total'],
			"error" 	  => $_LANG_VN["invoicing"]["notransactions"],
			"errorcode"	  => "notransactions"
		);
	}
	//PAID INCORRECT TRANSACTIONS TOTALS
	$sqlInv  = "select tblinvoices.userid, tblaccounts.invoiceid, tblinvoices.status, tblinvoices.invoicenum, tblinvoices.datepaid, tblinvoices.total, ";
	$sqlInv .= "(select concat_ws(' ', firstname, lastname, companyname) as cliente from tblclients where id = tblinvoices.userid) as client ";
	$sqlInv .= "from tblinvoices inner join tblaccounts on tblaccounts.invoiceid = tblinvoices.id ";
	$sqlInv .= "WHERE tblinvoices.total != tblaccounts.amountin GROUP BY tblinvoices.id;";
	$resInv = mysql_query($sqlInv);
	while($rowInv = mysql_fetch_array($resInv)){
		$invoices[$rowInv['invoiceid']] = array(
			"invoiceid"   => $rowInv['invoiceid'],
			"invoicenum"   => $rowInv['invoicenum'],
			"url" 	 	  => "./invoices.php?action=edit&id=".$rowInv['invoiceid'],
			"userid"      => $rowInv['userid'],
			"status" 	  => $rowInv['status'],
			"client" 	  => $rowInv['client'],
			"datepaid" 	  => $rowInv['datepaid'],
			"total" 	  => $rowInv['total'],
			"error" 	  => $_LANG_VN["invoicing"]["totaltransactionerror"],
			"errorcode"	  => "totaltransactionerror"
		);
	}

	//PAID INVOICES ERROR COUNT
	$sqlInv  = "select tblinvoices.userid, tblinvoices.id as invoiceid, tblinvoices.status, tblinvoices.invoicenum, tblinvoices.subtotal, tblinvoices.tax, tblinvoices.total, tblinvoices.credit,  tblinvoices.datepaid, ";
	$sqlInv .= "(select concat_ws(' ', firstname, lastname, companyname) as cliente from tblclients where id = tblinvoices.userid) as client ";
	$sqlInv .= "from tblinvoices inner join tblinvoiceitems on tblinvoiceitems.invoiceid = tblinvoices.id ";
	$sqlInv .= "GROUP BY tblinvoices.id having (abs(sum(tblinvoiceitems.amount)) + abs(tblinvoices.tax)) != abs(tblinvoices.total+tblinvoices.credit);";
	$resInv = mysql_query($sqlInv);
	while($rowInv = mysql_fetch_array($resInv)){
		$invoices[$rowInv['invoiceid']] = array(
			"invoiceid"   => $rowInv['invoiceid'],
			"invoicenum"   => $rowInv['invoicenum'],
			"url" 	 	  => "./invoices.php?action=edit&id=".$rowInv['invoiceid'],
			"userid"      => $rowInv['userid'],
			"status" 	  => $rowInv['status'],
			"client" 	  => $rowInv['client'],
			"datepaid" 	  => $rowInv['datepaid'],
			"total" 	  => $rowInv['total'],
			"error" 	  => $_LANG_VN["invoicing"]["totalerror"],
			"errorcode"	  => "totalerror"
		);
	}
	$sqlInv  = "select tblinvoices.userid, tblinvoices.id as invoiceid, tblinvoices.status, tblinvoices.invoicenum, tblinvoices.subtotal, tblinvoices.tax, tblinvoices.total, tblinvoices.credit,  tblinvoices.datepaid, ";
	$sqlInv .= "(select concat_ws(' ', firstname, lastname, companyname) as cliente from tblclients where id = tblinvoices.userid) as client ";
	$sqlInv .= "from tblinvoices inner join tblinvoiceitems on tblinvoiceitems.invoiceid = tblinvoices.id ";
	$sqlInv .= "GROUP BY tblinvoices.id having abs(sum(tblinvoiceitems.amount)) != abs(tblinvoices.subtotal);";
	$resInv = mysql_query($sqlInv);
	while($rowInv = mysql_fetch_array($resInv)){
		$invoices[$rowInv['invoiceid']] = array(
			"invoiceid"   => $rowInv['invoiceid'],
			"invoicenum"   => $rowInv['invoicenum'],
			"url" 	 	  => "./invoices.php?action=edit&id=".$rowInv['invoiceid'],
			"userid"      => $rowInv['userid'],
			"status" 	  => $rowInv['status'],
			"client" 	  => $rowInv['client'],
			"datepaid" 	  => $rowInv['datepaid'],
			"total" 	  => $rowInv['total'],
			"error" 	  => $_LANG_VN["invoicing"]["totalerror"],
			"errorcode"	  => "totalerror"
		);
	}

	$response["define_error"] 	 = $_LANG_VN["invoicing"]["define_error"];
	$response["define_solution"] = $_LANG_VN["invoicing"]["define_solution"];
	$response["invoices"] = $invoices;
	return $response;
}
function virtualname_domains_not_invoiced(){
	global $_LANG_VN;
	//GET INVOICED WITH
	$rowDom = mysql_fetch_array($resDom);
	$interval = $rowDom["value"];
	$sqlDom = 'select * from tbldomains where registrar = "virtualname" and status = "active" and nextduedate != nextinvoicedate';
	$resDom = mysql_query($sqlDom);
	$domains = array();

	while($rowDom = mysql_fetch_array($resDom)){
		$domains[$rowDom['domain']] = array(
			"domain" 	  => $rowDom['domain'],
			"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
			"userid"      => $rowDom['userid'],
			"id" 		  => $rowDom['id'],
			"status" 	  => $rowDom['status'],
			"expirydate"  => $rowDom['expirydate'],
			"nextduedate" => $rowDom['nextduedate'],
			"client" 	  => $rowDom['client']
		);
	}

	$response["define_error"] 	 = $_LANG_VN["duedates"]["define_error"];
	$response["define_solution"] = $_LANG_VN["cleaner"]["define_solution"];
	$response["domains"] = $domains;
	return $response;
}
function tools_invoicing($position){
	global $_LANG_VN;
    //GET ALL DOMAIN WITH EXPIRATION != DUE DATE
	if(empty($position))
		$position = 'creditClient';
	//$output .= "<h2><strong>".$_LANG_VN["invoicing"]["tab"].":</strong></h2></br>\n";
	$output  = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='creditClient'?'active':'')."' onclick=\"showSyncOptions('creditClient')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['invoicing']['labelCredit']."</a></li>
					<li class='nav-item ".($position=='invoicesWHMCSFix'?'active':'')."' onclick=\"showSyncOptions('invoicesWHMCSFix')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['invoicing']['labelInvoiceFix']."</a></li>
				</ul></br>\n";
	//creditClient
    $sort      = "credit";
    $sortorder = "DESC";
    try{
	    $clients = select_query("tblclients", "id, firstname, lastname, email, credit, status", array("credit"=>array("sqltype"=>"NEQ","values"=>"0")), $sort, "");
	}
	catch(Exception $e){
		$clients = false;
	}

	if($clients){
	    while($data = mysql_fetch_array($clients))
	        $credit_clients[] = $data;
	}

    $output .=
	"<div style='display:none;' name='creditClient' id='creditClient'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["invoicing"]["labelCredit"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=invoicing&position=creditClient">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    if(isset($credit_clients) && count($credit_clients)>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["invoicing"]["description"]."</div>";
    	$output .= "<table id='clients_credits' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th>".$_LANG_VN["clients"]["client"]."</th><th>".$_LANG_VN["clients"]["email"]."</th><th>".$_LANG_VN["clients"]["status"]."</th><th>".$_LANG_VN["clients"]["credit"]."</th></tr></thead><tbody>";
	    foreach($credit_clients as $credit_client){
	    	$output .= "<tr>";
	    	$output .= "<td><a target='_blank' href='./clientssummary.php?userid=".$credit_client['id']."'>".$credit_client["firstname"]." ".$credit_client["lastname"]."</a></td>";
	    	$output .= "<td><a target='_blank' href='./clientssummary.php?userid=".$credit_client['id']."'>".$credit_client["email"]."</a></td>";
	    	$output .= "<td>".$credit_client["status"]."</td>";
	    	$output .= "<td style='color:darkblue;'>".$credit_client["credit"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";

	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#clients_credits').DataTable( {".
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
	    $output .= "</script>";
	}
	else
		$output .= $_LANG_VN["invoicing"]["creditnotfound"];
    $output .= '</div>';
	//creditClient


	//invoiceFix
    $output .=
	"<div style='display:none;' name='invoicesWHMCSFix' id='invoicesWHMCSFix'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["invoicing"]["labelInvoiceFix"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=invoicing&position=invoicesWHMCSFix">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $invoicing_fix = virtualname_invoicing_fix();
    if(count($invoicing_fix["invoices"])>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["invoicing"]["labelInvoiceFix"]."</div>";
    	$output .= "<div class='contentbox'>".$invoicing_fix["define_error"]."</div>";
    	$output .= "<div class='contentbox'>".$invoicing_fix["define_solution"]."</div>";
    	$output .= "<table id='invoicing_fix' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th>".$_LANG_VN["invoicing"]["invoice"]."</th><th>".$_LANG_VN["invoicing"]["invoiceid"]."</th><th>".$_LANG_VN["invoicing"]["datepaid"]."</th><th>".$_LANG_VN["invoicing"]["status"]."</th><th>".$_LANG_VN["invoicing"]["client"]."</th><th>".$_LANG_VN["invoicing"]["total"]."</th><th>".$_LANG_VN["domains"]["error"]."</th></tr></thead><tbody>";
	    foreach($invoicing_fix["invoices"] as $invoice){
	    	$output .= "<tr>";
	    	$output .= "<td><a target='_blank' href='".$invoice["url"]."'>".$invoice["invoicenum"]."</a></td>";
	    	$output .= "<td><a target='_blank' href='".$invoice["url"]."'>".$invoice["invoiceid"]."</a></td>";
	    	$output .= "<td>".$invoice["datepaid"]."</td>";
	    	$output .= "<td>".$invoice["status"]."</td>";
	    	$output .= "<td>".$invoice["client"]."</td>";
	    	$output .= "<td>".$invoice["total"]."</td>";
	    	if($invoice["errorcode"] == "multipletransactions")
	    		$color = "red";
	    	elseif($invoice["errorcode"] == "notransactions")
	    		$color = "orange";
	    	elseif($invoice["errorcode"] == "totalerror")
	    		$color = "#8B1820";
	    	else
	    		$color = "darkblue";
	    	$output .= "<td style='color:".$color."''>".$invoice["error"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";
	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#invoicing_fix').DataTable( {".
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
	    $output .= "</script>";
	}
	else
		$output .= $_LANG_VN["invoicing"]["creditnotfound"];
    $output .= '</div>';
	//invoiceFix

    if($position != ""){
		$output .=
		'<script "text/javascript">'.
			'showSyncOptions("'.$position.'");'.
		'</script>';
    }

    return $output;
}
?>