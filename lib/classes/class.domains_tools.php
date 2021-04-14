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
function virtualname_domains_clean_check(){
	global $_LANG_VN;
	$domains = array();
	//DOMAINS WITH WHITE SPACES
	$sqlDom  = "SELECT domain,status,userid,id, (select concat_ws(' ', firstname, lastname, companyname) from tblclients where id = userid) as client FROM tbldomains";
	$sqlDom .= " WHERE status NOT IN ('Cancelled', 'Fraud') AND domain like ('% %') AND registrar = 'virtualname' order by domain";
	$resDom = mysql_query($sqlDom);
	while($rowDom = mysql_fetch_array($resDom)){
		$domains[$rowDom['domain']] = array(
			"domain" 	  => $rowDom['domain'],
			"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
			"userid"      => $rowDom['userid'],
			"id" 		  => $rowDom['id'],
			"status" 	  => $rowDom['status'],
			"error" 	  => $_LANG_VN["cleaner"]["whitespaces"],
			"errorcode"	  => "whitespaces",
			"client" 	  => $rowDom['client']
		);
	}
	//SUBDOMAINS CHECK VALIDATION
	$list = implode('","', array_keys($domains));
	$sqlDom  = "SELECT domain,status,userid,id FROM tbldomains WHERE domain not in (\"".$list."\") AND registrar = 'virtualname'";
	$resDom = mysql_query($sqlDom);
	while($rowDom = mysql_fetch_array($resDom)){
		if(check_subdomain_name($rowDom['domain']) != 1){
			$sqlCli = "select concat_ws(' ', firstname, lastname, companyname) as client from tblclients where id = ".$rowDom["userid"];
			$resCli = mysql_query($sqlCli);
			$rowCli = mysql_fetch_array($resCli);
			$domains[$rowDom['domain']] = array(
				"domain" 	  => $rowDom['domain'],
				"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
				"userid"      => $rowDom['userid'],
				"id" 		  => $rowDom['id'],
				"status" 	  => $rowDom['status'],
				"error" 	  => $_LANG_VN["cleaner"]["subdomain"],
				"errorcode"	  => "subdomain",
				"client" 	  => $rowCli['client']);
		}
	}
	//DOMAINS CHECK VALIDATION
	$list = implode('","', array_keys($domains));
	$sqlDom  = "SELECT domain,status,userid,id FROM tbldomains WHERE domain not in (\"".$list."\") AND registrar = 'virtualname'";
	$resDom = mysql_query($sqlDom);
	while($rowDom = mysql_fetch_array($resDom)){
		if(is_valid_domain_name($rowDom['domain']) != 1){
			$sqlCli = "select concat_ws(' ', firstname, lastname, companyname) as client from tblclients where id = ".$rowDom["userid"];
			$resCli = mysql_query($sqlCli);
			$rowCli = mysql_fetch_array($resCli);
			$domains[$rowDom['domain']] = array(
				"domain" 	  => $rowDom['domain'],
				"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
				"userid"      => $rowDom['userid'],
				"id" 		  => $rowDom['id'],
				"status" 	  => $rowDom['status'],
				"error" 	  => $_LANG_VN["cleaner"]["invalid"],
				"errorcode"	  => "notvalid",
				"client" 	  => $rowCli['client']);
		}
	}

	//DOMAINS WITHOUT REGISTRAR
	$sqlDom  = "SELECT domain,status,userid,id, (select concat_ws(' ', firstname, lastname, companyname) from tblclients where id = userid) as client FROM tbldomains";
	$sqlDom .= " WHERE status NOT IN ('Cancelled', 'Fraud') AND trim(registrar) = '' order by domain";
	$resDom = mysql_query($sqlDom);
	while($rowDom = mysql_fetch_array($resDom)){
		$domains[$rowDom['domain']] = array(
			"domain" 	  => $rowDom['domain'],
			"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
			"userid"      => $rowDom['userid'],
			"id" 		  => $rowDom['id'],
			"status" 	  => $rowDom['status'],
			"error" 	  => $_LANG_VN["cleaner"]["noregistrar"],
			"errorcode"	  => "noregistrar",
			"client" 	  => $rowDom['client']
		);
	}

	//VIRTUALNAME DOMAINS WITH INCORRECT REGISTRAR
	$list = implode('","', array_keys($domains));
	$sqlDom  = "SELECT domain,status,userid,id FROM tbldomains WHERE domain not in (\"".$list."\") AND registrar != 'virtualname'";
	$resDom = mysql_query($sqlDom);
	$tcpanel_list = virtualname_tcpanel_list();
	while($rowDom = mysql_fetch_array($resDom)){
		if(in_array(strtolower($rowDom["domain"]), $tcpanel_list)){
			$sqlCli = "select concat_ws(' ', firstname, lastname, companyname) as client from tblclients where id = ".$rowDom["userid"];
			$resCli = mysql_query($sqlCli);
			$rowCli = mysql_fetch_array($resCli);
			$domains[$rowDom['domain']] = array(
				"domain" 	  => $rowDom['domain'],
				"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
				"userid"      => $rowDom['userid'],
				"id" 		  => $rowDom['id'],
				"status" 	  => $rowDom['status'],
				"error" 	  => $_LANG_VN["cleaner"]["incorrectregistrar"],
				"errorcode"	  => "incorrectregistrar",
				"client" 	  => $rowCli['client']);
		}
	}

	$response["define_error"] 	 = $_LANG_VN["cleaner"]["define_error"];
	$response["define_solution"] = $_LANG_VN["cleaner"]["define_solution"];
	$response["domains"] = $domains;
	return $response;
}
function virtualname_domains_expirations_check(){
	global $_LANG_VN;
	$sqlDom  = "SELECT domain,status,userid,id,expirydate,nextduedate, (select concat_ws(' ', firstname, lastname, companyname) from tblclients where id = userid) as client FROM tbldomains";
	$sqlDom .= " WHERE expirydate < nextduedate AND STATUS IN ('Active', 'Expired', 'Pending', 'Pending Transfer') AND registrar = 'virtualname' order by expirydate";
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
function virtualname_domains_transfer_on_renewal(){
	global $_LANG_VN;
	$domains = array();
	//VIRTUALNAME DOMAINS WITH OTHERS REGISTRARS REGISTRAR
	$sqlDom  = "SELECT domain,status,userid,id,registrar,expirydate,(select concat_ws(' ', firstname, lastname, companyname) from tblclients where id = userid) as client,";
	$sqlDom .= "(select status from mod_virtualname_transfer_on_renewal where domainid = id) as transfer_on_renewal_status,";
	$sqlDom .= "(select value from mod_virtualname_transfer_on_renewal where domainid = id) as transfer_value,";
	$sqlDom .= "(select registrar from mod_virtualname_transfer_on_renewal where domainid = id) as transfer_registrar,";
	$sqlDom .= "(select type from mod_virtualname_transfer_on_renewal where domainid = id) as transfer_type,";
	$sqlDom .= "(select notes from mod_virtualname_transfer_on_renewal where domainid = id) as transfer_notes ";
	$sqlDom .= "FROM tbldomains";
	$sqlDom .= " WHERE (registrar != 'virtualname' OR id in (select domainid from mod_virtualname_transfer_on_renewal))";
	$sqlDom .= " AND status NOT IN ('Cancelled', 'Fraud') order by domain";
	$resDom = mysql_query($sqlDom);
	while($rowDom = mysql_fetch_array($resDom)){
		$domains[$rowDom['domain']] = array(
			"domain" 	  => $rowDom['domain'],
			"url" 	 	  => "./clientsdomains.php?userid=".$rowDom['userid']."&id=".$rowDom['id'],
			"userid"      => $rowDom['userid'],
			"id" 		  => $rowDom['id'],
			"status" 	  => $rowDom['status'],
			"expirydate"  => $rowDom['expirydate'],
			"registrar"   => $rowDom['registrar'],
			"transfer_on_renewal_status" => $rowDom['transfer_on_renewal_status'],
			"transfer_value" => $rowDom['transfer_value'],
			"transfer_registrar" => $rowDom['transfer_registrar'],
			"transfer_type" => $rowDom['transfer_type'],
			'transfer_notes' => $rowDom['transfer_notes'],
			"client" 	  => $rowDom['client']);
	}
	$response["message"] 	 = $_LANG_VN['transfer_on_renewal']['message'];
	$response["description"] = $_LANG_VN['transfer_on_renewal']['description'];
	$response["domains"] = $domains;
	return $response;
}
function is_valid_domain_name($domain_name){
    //https://github.com/ewwink/php-domain-name-validation/blob/master/is_domain.php - A-Za-z0-9- -a-z0-9
    $tlds = "/^(?! -)[A-Za-z0-9-_áÁéÉíÍóÓúÚçÇñÑ·]{1,63}(?<!-)\.(ac\.nz|co\.nz|geek\.nz|gen\.nz|kiwi\.nz|maori\.nz|net\.nz|org\.nz|school\.nz|ae|ae\.org|com\.af|asia|asn\.au|auz\.info|auz\.net|com\.au|id\.au|net\.au|org\.au|auz\.biz|az|com\.az|int\.az|net\.az|org\.az|pp\.az|biz\.fj|com\.fj|info\.fj|name\.fj|net\.fj|org\.fj|pro\.fj|or\.id|biz\.id|co\.id|my\.id|web\.id|biz\.ki|com\.ki|info\.ki|ki|mobi\.ki|net\.ki|org\.ki|phone\.ki|biz\.pk|com\.pk|net\.pk|org\.pk|pk|web\.pk|cc|cn|com\.cn|net\.cn|org\.cn|co\.in|firm\.in|gen\.in|in|in\.net|ind\.in|net\.in|org\.in|co\.ir|ir|co\.jp|jp|jp\.net|ne\.jp|or\.jp|co\.kr|kr|ne\.kr|or\.kr|co\.th|in\.th|com\.bd|com\.hk|hk|idv\.hk|org\.hk|com\.jo|jo|com\.kz|kz|org\.kz|com\.lk|lk|org\.lk|com\.my|my|com\.nf|info\.nf|net\.nf|nf|web\.nf|com\.ph|ph|com\.ps|net\.ps|org\.ps|ps|com\.sa|com\.sb|net\.sb|org\.sb|com\.sg|edu\.sg|org\.sg|per\.sg|sg|com\.tw|tw|com\.vn|net\.vn|org\.vn|vn|cx|fm|io|la|mn|nu|qa|tk|tl|tm|to|tv|ws|academy|careers|education|training|bike|biz|cat|co|com|info|me|mobi|name|net|org|pro|tel|travel|xxx|blackfriday|clothing|diamonds|shoes|tattoo|voyage|build|builders|construction|contractors|equipment|glass|lighting|plumbing|repair|solutions|buzz|sexy|singles|support|cab|limo|camera|camp|gallery|graphics|guitars|hiphop|photo|photography|photos|pics|center|florist|institute|christmas|coffee|kitchen|menu|recipes|company|enterprises|holdings|management|ventures|computer|systems|technology|directory|guru|tips|wiki|domains|link|estate|international|land|onl|pw|today|ac\.im|co\.im|com\.im|im|ltd\.co\.im|net\.im|org\.im|plc\.co\.im|am|at|co\.at|or\.at|ba|be|bg|biz\.pl|com\.pl|info\.pl|net\.pl|org\.pl|pl|biz\.tr|com\.tr|info\.tr|tv\.tr|web\.tr|by|ch|co\.ee|ee|co\.gg|gg|co\.gl|com\.gl|co\.hu|hu|co\.il|org\.il|co\.je|je|co\.nl|nl|co\.no|no|co\.rs|in\.rs|rs|co\.uk|org\.uk|uk\.net|com\.de|de|com\.es|es|nom\.es|org\.es|com\.gr|gr|com\.hr|com\.mk|mk|com\.mt|net\.mt|org\.mt|com\.pt|pt|com\.ro|ro|com\.ru|net\.ru|ru|su|com\.ua|ua|cz|dk|eu|fi|fr|pm|re|tf|wf|yt|gb\.net|ie|is|it|li|lt|lu|lv|md|mp|se|se\.net|si|sk|ac|ag|co\.ag|com\.ag|net\.ag|nom\.ag|org\.ag|ai|com\.ai|com\.ar|as|biz\.pr|com\.pr|net\.pr|org\.pr|pr|biz\.tt|co\.tt|com\.tt|tt|bo|com\.bo|com\.br|net\.br|tv\.br|bs|com\.bs|bz|co\.bz|com\.bz|net\.bz|org\.bz|ca|cl|co\.cr|cr|co\.dm|dm|co\.gy|com\.gy|gy|co\.lc|com\.lc|lc|co\.ms|com\.ms|ms|org\.ms|co\.ni|com\.ni|co\.ve|com\.ve|co\.vi|com\.vi|com\.co|net\.co|nom\.co|com\.cu|cu|com\.do|do|com\.ec|ec|info\.ec|net\.ec|com\.gt|gt|com\.hn|hn|com\.ht|ht|net\.ht|org\.ht|com\.jm|com\.kn|kn|com\.mx|mx|com\.pa|com\.pe|pe|com\.py|com\.sv|com\.uy|uy|com\.vc|net\.vc|org\.vc|vc|gd|gs|north\.am|south\.am|us|us\.org|sx|tc|vg|cd|cg|cm|co\.cm|com\.cm|net\.cm|co\.ke|or\.ke|co\.mg|com\.mg|mg|net\.mg|org\.mg|co\.mw|com\.mw|coop\.mw|mw|co\.na|com\.na|na|org\.na|co\.ug|ug|co\.za|com\.ly|ly|com\.ng|ng|com\.sc|sc|mu|rw|sh|so|st|club|kiwi|uno|email|ruhr|eus|gob\.es|tienda|gal|org\.mx|abogado|agency|associates|attorney|bar|barcelona|bargains|beer|bio|blue|boutique|cafe|capital|cards|care|casa|cash|catering|cheap|church|city|claims|cleaning|clinic|cloud|codes|community|condos|cool|cruises|dating|deals|dental|design|digital|direct|discount|engineering|events|exchange|expert|exposed|fail|farm|finance|financial|fish|fitness|flights|foundation|fund|furniture|futbol|global|golf|gratis|gripe|guide|holiday|host|house|industries|insure|investments|kim|legal|life|limited|live|maison|marketing|media|moda|net\.pe|news|one|ong|online|org\.pe|partners|parts|pink|pizza|place|productions|promo|properties|red|reisen|rentals|report|restaurant|rs|sa|schule|science|services|shiksha|social|solar|store|supplies|supply|surgery|tax|taxi|tools|town|toys|uk|university|vacations|viajes|video|villas|vision|watch|website|wine|works|world|wtf|xyz|zone)$/i";
    if (preg_match($tlds, $domain_name))
    	return true;
    else
    	return false;
}
function check_subdomain_name($domain_name){
    //https://github.com/ewwink/php-domain-name-validation/blob/master/is_domain.php
    $tlds = array(".ac",".co",".geek",".gen",".kiwi",".maori",".net",".org",".school",".ae",".ae.org",".com.af",".asia",".asn.au",".auz.info",".auz.net",".com.au",".id.au",".net.au",".org.au",".auz.biz",".az",".com.az",".int.az",".net.az",".org.az",".pp.az",".biz.fj",".com.fj",".info.fj",".name.fj",".net.fj",".org.fj",".pro.fj",".or.id",".biz.id",".co.id",".my.id",".web.id",".biz.ki",".com.ki",".info.ki",".ki",".mobi.ki",".net.ki",".org.ki",".phone.ki",".biz.pk",".com.pk",".net.pk",".org.pk",".pk",".web.pk",".cc",".cn",".com.cn",".net.cn",".org.cn",".co.in",".firm.in",".gen.in",".in",".in.net",".ind.in",".net.in",".org.in",".co.ir",".ir",".co.jp",".jp",".jp.net",".ne.jp",".or.jp",".co.kr",".kr",".ne.kr",".or.kr",".co.th",".in.th",".com.bd",".com.hk",".hk",".idv.hk",".org.hk",".com.jo",".jo",".com.kz",".kz",".org.kz",".com.lk",".lk",".org.lk",".com.my",".my",".com.nf",".info.nf",".net.nf",".nf",".web.nf",".com.ph",".ph",".com.ps",".net.ps",".org.ps",".ps",".com.sa",".com.sb",".net.sb",".org.sb",".com.sg",".edu.sg",".org.sg",".per.sg",".sg",".com.tw",".tw",".com.vn",".net.vn",".org.vn",".vn",".cx",".fm",".io",".la",".mn",".nu",".qa",".tk",".tl",".tm",".to",".tv",".ws",".academy",".careers",".education",".training",".bike",".biz",".cat",".co",".com",".info",".me",".mobi",".name",".net",".org",".pro",".tel",".travel",".xxx",".blackfriday",".clothing",".diamonds",".shoes",".tattoo",".voyage",".build",".builders",".construction",".contractors",".equipment",".glass",".lighting",".plumbing",".repair",".solutions",".buzz",".sexy",".singles",".support",".cab",".limo",".camera",".camp",".gallery",".graphics",".guitars",".hiphop",".photo",".photography",".photos",".pics",".center",".florist",".institute",".christmas",".coffee",".kitchen",".menu",".recipes",".company",".enterprises",".holdings",".management",".ventures",".computer",".systems",".technology",".directory",".guru",".tips",".wiki",".domains",".link",".estate",".international",".land",".onl",".pw",".today",".ac.im",".co.im",".com.im",".im",".ltd.co.im",".net.im",".org.im",".plc.co.im",".am",".at",".co.at",".or.at",".ba",".be",".bg",".biz.pl",".com.pl",".info.pl",".net.pl",".org.pl",".pl",".biz.tr",".com.tr",".info.tr",".tv.tr",".web.tr",".by",".ch",".co.ee",".ee",".co.gg",".gg",".co.gl",".com.gl",".co.hu",".hu",".co.il",".org.il",".co.je",".je",".co.nl",".nl",".co.no",".no",".co.rs",".in.rs",".rs",".co.uk",".org.uk",".uk.net",".com.de",".de",".com.es",".es",".nom.es",".org.es",".com.gr",".gr",".com.hr",".com.mk",".mk",".com.mt",".net.mt",".org.mt",".com.pt",".pt",".com.ro",".ro",".com.ru",".net.ru",".ru",".su",".com.ua",".ua",".cz",".dk",".eu",".fi",".fr",".pm",".re",".tf",".wf",".yt",".gb.net",".ie",".is",".it",".li",".lt",".lu",".lv",".md",".mp",".se",".se.net",".si",".sk",".ac",".ag",".co.ag",".com.ag",".net.ag",".nom.ag",".org.ag",".ai",".com.ai",".com.ar",".as",".biz.pr",".com.pr",".net.pr",".org.pr",".pr",".biz.tt",".co.tt",".com.tt",".tt",".bo",".com.bo",".com.br",".net.br",".tv.br",".bs",".com.bs",".bz",".co.bz",".com.bz",".net.bz",".org.bz",".ca",".cl",".co.cr",".cr",".co.dm",".dm",".co.gy",".com.gy",".gy",".co.lc",".com.lc",".lc",".co.ms",".com.ms",".ms",".org.ms",".co.ni",".com.ni",".co.ve",".com.ve",".co.vi",".com.vi",".com.co",".net.co",".nom.co",".com.cu",".cu",".com.do",".do",".com.ec",".ec",".info.ec",".net.ec",".com.gt",".gt",".com.hn",".hn",".com.ht",".ht",".net.ht",".org.ht",".com.jm",".com.kn",".kn",".com.mx",".mx",".com.pa",".com.pe",".pe",".com.py",".com.sv",".com.uy",".uy",".com.vc",".net.vc",".org.vc",".vc",".gd",".gs",".north.am",".south.am",".us",".us.org",".sx",".tc",".vg",".cd",".cg",".cm",".co.cm",".com.cm",".net.cm",".co.ke",".or.ke",".co.mg",".com.mg",".mg",".net.mg",".org.mg",".co.mw",".com.mw",".coop.mw",".mw",".co.na",".com.na",".na",".org.na",".co.ug",".ug",".co.za",".com.ly",".ly",".com.ng",".ng",".com.sc",".sc",".mu",".rw",".sh",".so",".st",".club",".kiwi",".uno",".email",".ruhr", ".eus", ".gob.es", ".tienda", ".gal", ".org.mx", ".abogado",".agency",".associates", ".attorney", ".bar", ".barcelona", ".bargains", ".beer", ".bio", ".blue",".boutique",".cafe",".capital",".cards",".care",".casa",".cash",".catering",".cheap",".church",".city",".claims",".cleaning",".clinic",".cloud",".codes",".community",".condos",".cool",".cruises",".dating",".deals",".dental",".design",".digital",".direct",".discount",".engineering",".events",".exchange",".expert",".exposed",".fail",".farm",".finance",".financial",".fish",".fitness",".flights",".foundation",".fund",".furniture",".futbol",".global",".golf",".gratis",".gripe",".guide",".holiday",".host",".house",".industries",".insure",".investments",".kim",".legal",".life",".limited",".live",".maison",".marketing",".media",".moda",".net.pe",".news",".one",".ong",".online",".org.pe",".partners",".parts",".pink",".pizza",".place",".productions",".promo",".properties",".red",".reisen",".rentals",".report",".restaurant",".rs",".sa",".schule",".science",".services",".shiksha",".social",".solar",".store",".supplies",".supply",".surgery",".tax",".taxi",".tools",".town",".toys",".uk",".university",".vacations",".viajes",".video",".villas",".vision",".watch",".website",".wine",".works",".world",".wtf",".xyz",".zone");
    $max_length = 0;
    foreach($tlds as $tld){
    	if(strpos(strtolower($domain_name), $tld) !== false){
    		if(strlen($tld)>$max_length){
    			$max_length = strlen($tld);
    			$tld_switch = $tld;
    		}
    	}
    }
	$sld = str_replace($tld_switch, "", strtolower($domain_name));

	if (strpos($sld, ".") !== false)
    	return false;
    else
    	return true;
}
function tools_dns(){
	return "In progress...";
}
function virtualname_transfers(){
	return "In progress...";
}
function virtualname_domains_import_list(){
	$domains = array();
	$domainList = array();
	$domainsWHMCS = array();
	//DOMAINS IMPORT LIST
    $resultList = select_query("mod_virtualname_tools", "value", array("type" => "syncList"));
    while($dataList = mysql_fetch_assoc($resultList)){
        $list = json_decode($dataList["value"], true);
        foreach($list as $key => $value){
        	$domain["status"] = ucfirst(str_replace('_', ' ', $value["product_info"]["product_status"]));
        	$domain["expiration"] = $value["product_info"]["product_expiration"];
        	$domain["registrant"] = $value["contacts"]["registrant"]["name"]." ".$value["contacts"]["registrant"]["lastname"]." - ".$value["contacts"]["registrant"]["company"];
        	$domain["admin"] 	  = $value["contacts"]["administrative"]["name"]." ".$value["contacts"]["administrative"]["lastname"]." - ".$value["contacts"]["administrative"]["company"];
        	$domain["domain"] = strtolower($key);
            $domainList[$key] = $domain;
        }
    }

    $resultClientsDomains = select_query("tbldomains", "*", '');
    while($dataList = mysql_fetch_assoc($resultClientsDomains)){
        $domainsWHMCS[strtolower($dataList["domain"])]["domain"] = strtolower($dataList["domain"]);
    }
	$domains = array_diff_key($domainList, $domainsWHMCS);
	$response["domains"] = $domains;

	return $response;
}
function virtualname_domains_irtp_list(){
	$domains = array();
	$domainList = array();
	$domainsWHMCS = array();
	//DOMAINS IMPORT LIST
    $resultList = select_query("mod_virtualname_tools", "value", array("type" => "syncList"));
    while($dataList = mysql_fetch_assoc($resultList)){
        $list = json_decode($dataList["value"], true);
        foreach($list as $key => $value){
        	if($value["product_info"]["product_status"] == 'active_pending_registrant_approval'){
	        	$domain["status"] = ucfirst(str_replace('_', ' ', $value["product_info"]["product_status"]));
	        	$domain["expiration"] = $value["product_info"]["product_expiration"];
	        	$domain["registrant"] = $value["contacts"]["registrant"]["name"]." ".$value["contacts"]["registrant"]["lastname"]." - ".$value["contacts"]["registrant"]["company"];
	        	$domain["admin"] 	  = $value["contacts"]["administrative"]["name"]." ".$value["contacts"]["administrative"]["lastname"]." - ".$value["contacts"]["administrative"]["company"];
	        	$domain["domain"] = strtolower($key);
	            $domainList[$key] = $domain;
	        }
        }
    }
	$response["domains"] = $domainList;
	return $response;
}
function virtualname_domains_pending_transfer_list(){
	$domains = array();
	$domainList = array();
	$domainsWHMCS = array();
	//DOMAINS IMPORT LIST
    $resultList = select_query("mod_virtualname_tools", "value", array("type" => "syncList"));
    while($dataList = mysql_fetch_assoc($resultList)){
        $list = json_decode($dataList["value"], true);
        foreach($list as $key => $value){
        	$transfers_status = array('transferring','transfer_requested','transfer_initiated','transfer_email_sent','transfer_rejected','transfer_approved','transfer_finished','transfer_expired','transfer_waiting_unlocked','transfer_waiting_admin','transfer_waiting_registrar','transfer_order_locked','transfer_waiting_authcode','transfer_email_not_sent','transfer_resend_authcode','transfer_waiting_pending_registrant_approval');
        	if(in_array($value["product_info"]["product_status"], $transfers_status)){
	        	$domain["status"] = ucfirst(str_replace('_', ' ', $value["product_info"]["product_status"]));
	        	$domain["expiration"] = $value["product_info"]["product_expiration"];
	        	$domain["registrant"] = $value["contacts"]["registrant"]["name"]." ".$value["contacts"]["registrant"]["lastname"]." - ".$value["contacts"]["registrant"]["company"];
	        	$domain["admin"] 	  = $value["contacts"]["administrative"]["name"]." ".$value["contacts"]["administrative"]["lastname"]." - ".$value["contacts"]["administrative"]["company"];
	        	$domain["domain"] = strtolower($key);
	            $domainList[$key] = $domain;
	        }
        }
    }
	$response["domains"] = $domainList;
	return $response;
}
function virtualname_domains_import_tld_list(){
	global $tb_virtualname_tools, $vname_admin, $vname_domains;
    virtualname_init();
	$params  = $vname_admin->config();
	$request = $vname_domains->available_tlds($params);
	$import_tlds = array();
	if(isset($request['response'])){
		$response = $request['response'];
		foreach($response as $TLD => $value){
			$resultList = select_query('tbldomainpricing', 'id', array('extension' => '.'.$TLD));
			if(mysql_num_rows($resultList) == 0)
				$import_tlds[] = $TLD;
		}
	}
	return $import_tlds;
}
function tools_domains($position){
	global $_LANG_VN;
	//TAB POS
	if(empty($position))
		$position = 'domainManagementDueDate';
	//$output .= "<h2><strong>".$_LANG_VN["domains"]["select"].":</strong></h2></br>\n";
	$output = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='domainManagementDueDate'?'active':'')."' onclick=\"showSyncOptions('domainManagementDueDate')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['domains']['duedates']."</a></li>
					<li class='nav-item ".($position=='domainsCleaner'?'active':'')."' onclick=\"showSyncOptions('domainsCleaner')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['domains']['cleaner']."</a></li>
					<li class='nav-item ".($position=='domainsTransferOnRenewal'?'active':'')."' onclick=\"showSyncOptions('domainsTransferOnRenewal')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['domains']['transfer_on_renewal']."</a></li>
				</ul></br>\n";
	//domainsCleaner
    $output .=
	"<div style='display:none;' name='domainsCleaner' id='domainsCleaner'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["cleaner"]["errors"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=domains&position=domainsCleaner">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_clean = virtualname_domains_clean_check();

    if(count($domains_clean["domains"])>0){
    	$output .= "<div class='contentbox'>".$domains_clean["define_error"]."</div>";
    	$output .= "<div class='contentbox'>".$domains_clean["define_solution"]."</div>";
    	$output .= "<form method='post'>";
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type="checkbox" id="domains_clean_check_checkall" class="checkall_box" title="'.$_LANG_VN["domains"]["selectall"].'" onclick="select_all_domains_clean(this);">';
	    $output .= ' <label for="select_all_domains_clean">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN["cleaner"]["clean"].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN["cleaner"]["cleandesc"].'\' class=\'btn btn-primary\'/>';
	    $output .= '</div>';
    	$output .= "<table id='domains_clean_check' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th></th><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN["domains"]["client"]."</th><th>".$_LANG_VN["domains"]["error"]."</th></tr></thead><tbody>";
	    foreach($domains_clean["domains"] as $domain){
	    	$output .= "<tr>";
	    	if($domain["errorcode"] == "whitespaces" || $domain["errorcode"] == "noregistrar" || $domain["errorcode"] == "incorrectregistrar")
	    		$output .= "<td><input type='checkbox' name='domains_clean_fix[]' value='".$domain["id"]."' style='width: 30px;max-width: 30px;'></td>";
	    	else
	    		$output .= "<td></td>";
	    	$output .= "<td><a href='".$domain["url"]."'>".$domain["domain"]."</a></td>";
	    	$output .= "<td>".$domain["status"]."</td>";
	    	$output .= "<td>".$domain["client"]."</td>";
	    	if($domain["errorcode"] == "noregistrar")
	    		$color = "red";
	    	elseif($domain["errorcode"] == "whitespaces")
	    		$color = "orange";
	    	elseif($domain["errorcode"] == "notvalid")
	    		$color = "brown";
	    	elseif($domain["errorcode"] == "subdomain")
	    		$color = "darkmagenta";
	    	elseif($domain["errorcode"] == "incorrectregistrar")
	    		$color = "#8B1820";
	    	else
	    		$color = "darkblue";
	    	$output .= "<td style='color:".$color."''>".$domain["error"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";

	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type="checkbox" id="domains_clean_check_checkall" class="checkall_box" title="'.$_LANG_VN["domains"]["selectall"].'" onclick="select_all_domains_clean(this);">';
	    $output .= ' <label for="select_all_domains_clean">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN["cleaner"]["clean"].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN["cleaner"]["cleandesc"].'\' class=\'btn btn-primary\'/>';
	    $output .= '</div>';
	    $output .= '<input type=\'hidden\' name=\'action_domains\' id=\'action_domains\' value=\'domains_clean_check\'>';

	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_clean_check').DataTable( {".
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
		$output .= "function select_all_domains_clean(source){".
						"checkboxes = document.getElementsByName('domains_clean_fix[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
	    $output .= "</script>";
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["cleaner"]["notfound"];
    $output .= '</div>';
	//domainsCleaner

	//domainManagementDueDate
    $output .=
	"<div style='display:none;' name='domainManagementDueDate' id='domainManagementDueDate'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["duedates"]["description"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=domains&position=domainManagementDueDate">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_expiration = virtualname_domains_expirations_check();

    if(count($domains_expiration["domains"])>0){
    	$output .= "<div class='contentbox'>".$domains_expiration["define_error"]."</div>";
    	$output .= "<div class='contentbox'>".$domains_expiration["define_solution"]."</div>";
    	$output .= "<form method='post'>";
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'domains_expirations_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN["domains"]["selectall"].'\' onclick=\'select_all_domains(this);\'>';
	    $output .= ' <label for="domains_expirations_check_checkall">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN["duedates"]["setdates"].'\' style=\'margin-left: 5%;\' class=\'btn btn-primary\' title=\''.$_LANG_VN["duedates"]["setdatesdesc"].'\'/>';
    	$output .= '</div>';
    	$output .= "<table id='domains_expirations_check' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th></th><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN["duedates"]["next"]."</th><th>".$_LANG_VN["duedates"]["expiry"]."</th><th>".$_LANG_VN["domains"]["client"]."</th></tr></thead><tbody>";
	    foreach($domains_expiration["domains"] as $domain){
	    	$output .= "<tr>";
	    	$output .= "<td><input type='checkbox' name='domains_due_dates[]' value='".$domain["id"]."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= "<td><a href='".$domain["url"]."'>".$domain["domain"]."</a></td>";
	    	$output .= "<td>".$domain["status"]."</td>";
	    	$output .= "<td>".$domain["nextduedate"]."</td>";
	    	$output .= "<td>".$domain["expirydate"]."</td>";
	    	$output .= "<td>".$domain["client"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= '</tbody></table>';

	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function(){".
					    "$('#domains_expirations_check').DataTable({".
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
							    "buttons:{".
								    "copy: \"".$_LANG_VN["datatable"]["copy"]."\",".
								    "csv: \"".$_LANG_VN["datatable"]["csv"]."\",".
								    "excel: \"".$_LANG_VN["datatable"]["excel"]."\",".
								    "pdf: \"".$_LANG_VN["datatable"]["pdf"]."\",".
								    "print: \"".$_LANG_VN["datatable"]["print"]."\",".
								"},".
					            "\"infoFiltered\": \"(".$_LANG_VN["datatable"]["infoFiltered"]." _MAX_ ".$_LANG_VN["datatable"]["infoFilteredTotal"].")\"".
					        "},".
					    "});".
					"});";
		$output .= "function select_all_domains(source){".
						"checkboxes = document.getElementsByName('domains_due_dates[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
	    $output .= "</script>";
	    $output .= '<input type=\'hidden\' name=\'action_domains\' id=\'action_domains\' value=\'domains_expirations_check\'>';

	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type=\'checkbox\' id=\'domains_expirations_check_checkall\' class=\'checkall_box\' title=\''.$_LANG_VN["domains"]["selectall"].'\' onclick=\'select_all_domains(this);\'>';
	    $output .= ' <label for="domains_expirations_check_checkall">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN["duedates"]["setdates"].'\' style=\'margin-left: 5%;\' class=\'btn btn-primary\' title=\''.$_LANG_VN["duedates"]["setdatesdesc"].'\'/>';
    	$output .= '</div>';
		$output .= '</form>';
	}
	else
		$output .= $_LANG_VN["duedates"]["notfound"];
    $output .= '</div>';
    //domainManagementDueDate

	//domainsTransferOnRenewal
    $output .=
	"<div style='display:none;' name='domainsTransferOnRenewal' id='domainsTransferOnRenewal'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN['domains']['transfer_on_renewal'].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=domains&position=domainsTransferOnRenewal">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_transfer_on_renewal = virtualname_domains_transfer_on_renewal();

    if(count($domains_transfer_on_renewal["domains"])>0){
    	$output .= "<div class='contentbox'>".$domains_transfer_on_renewal["message"]."</div>";
    	$output .= "<div class='contentbox'>".$domains_transfer_on_renewal["description"]."</div>";
    	$output .= '<form method=\'post\' onsubmit=\'return validate_transfer();\'>';
	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type="checkbox" id="domains_transfer_on_renewal_check_checkall" class="checkall_box" title="'.$_LANG_VN["domains"]["selectall"].'" onclick="select_all_domains_transfer_on_renewal(this);" />';
	    $output .= ' <label for="select_all_domains_transfer_on_renewal">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']["active_deactive"].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['transfer_on_renewal']['activate_description'].'\' class=\'btn btn-primary\' />';
	    $output .= ' <select name=\'select_transfer_on_renewal_first\' id=\'select_transfer_on_renewal_first\' class=\'form-control select-inline\' style=\'min-width: 100px;\' onchange=\'new_selected_tor(this);\'><option value=\'authcode_active\'>Authcode Active</option><option value=\'authcode_inactive\'>Authcode Inactive</option><option value=\'mail_active\'>Email Active</option><option value=\'mail_inactive\'>Email Inactive</option><option value=\'delete\'>'.$_LANG_VN['transfer_on_renewal']['delete'].'</option></select>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['authcode'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'authcode\');" title=\''.$_LANG_VN['transfer_on_renewal']['authcode_description'].'\' class=\'btn btn-default\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['set_old_mail'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'set_old_mail\');" title=\''.$_LANG_VN['transfer_on_renewal']['set_old_mail_description'].'\' class=\'btn btn-warning\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['check_status'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'check_status\');" title=\''.$_LANG_VN['transfer_on_renewal']['check_status_description'].'\' class=\'btn btn-success\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['init_transfer'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'init_transfer\');" title=\''.$_LANG_VN['transfer_on_renewal']['init_transfer_description'].'\' class=\'btn btn-danger\' />';
	    $output .= '</div>';
    	$output .= "<table id='domains_transfer_on_renewal_check' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th></th><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN['domains']['expiration']."</th><th>".$_LANG_VN['transfer_on_renewal']["transfer"]."</th><th>".$_LANG_VN['transfer_on_renewal']['type']."</th><th>".$_LANG_VN['transfer_on_renewal']['value']."</th><th>".$_LANG_VN['domains']['notes']."</th><th>".$_LANG_VN['domains']['client']."</th><th>".$_LANG_VN['domains']['registrar']."</th><th>".$_LANG_VN['domains']['origin']."</th></tr></thead><tbody>";
	    foreach($domains_transfer_on_renewal["domains"] as $domain){
	    	$output .= "<tr>";
	    	$output .= "<td><input type='checkbox' name='domains_transfer_on_renewal_fix[]' value='".$domain["id"]."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= "<td><a href='".$domain["url"]."'>".$domain["domain"]."</a></td>";
	    	$output .= "<td>".$domain['status']."</td>";
	    	$output .= "<td>".$domain['expirydate']."</td>";
	    	if($domain['transfer_on_renewal_status'] == 'active' && $domain["registrar"] != 'virtualname')
	    		$output .= "<td style='color:green'>".$_LANG_VN['transfer_on_renewal']['active']."</td>";
	    	elseif($domain['transfer_on_renewal_status'] == 'active' && $domain["registrar"] == 'virtualname')
	    		$output .= "<td style='color:darkorange'>".$_LANG_VN['transfer_on_renewal']['in_progress']."</td>";
	    	elseif($domain['transfer_on_renewal_status'] == 'disabled' && $domain["registrar"] == 'virtualname')
	    		$output .= "<td style='color:blue'>".$_LANG_VN['transfer_on_renewal']['complete']."</td>";
	    	elseif($domain['transfer_on_renewal_status'] == 'error')
	    		$output .= "<td style='color:red'>Error</td>";
	    	else
	    		$output .= "<td style='color:red'>".$_LANG_VN['transfer_on_renewal']['inactive']."</td>";
	    	if($domain['transfer_type'] == 1 || $domain['transfer_type'] == 3)
	    		$type = 'Authcode';
	    	elseif($domain['transfer_type'] == 2 || $domain['transfer_type'] == 4)
	    		$type = 'E-Mail';
	    	else
	    		$type = '';
	    	$output .= "<td>".$type."</td>";
	    	$output .= "<td>".$domain["transfer_value"]."</td>";
	    	$output .= '<td>'.$domain['transfer_notes'].'</td>';
	    	$output .= "<td>".$domain["client"]."</td>";
	    	if($domain["registrar"] != 'virtualname')
		    	$color = 'darkblue';
		    else
		    	$color = 'green';
	    	$output .= "<td style='color:".$color."''>".$domain["registrar"]."</td>";
	    	$output .= "<td style='color:purple''>".$domain["transfer_registrar"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";

	    $output .= '<div class=\'contentbox\'>';
	    $output .= ' <input type="checkbox" id="domains_transfer_on_renewal_check_checkall" class="checkall_box" title="'.$_LANG_VN["domains"]["selectall"].'" onclick="select_all_domains_transfer_on_renewal(this);" />';
	    $output .= ' <label for="select_all_domains_transfer_on_renewal">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']["active_deactive"].'\' style=\'margin-left: 5%;\' title=\''.$_LANG_VN['transfer_on_renewal']['activate_description'].'\' class=\'btn btn-primary\' />';
	    $output .= ' <select name=\'select_transfer_on_renewal_second\' id=\'select_transfer_on_renewal_second\' class=\'form-control select-inline\' style=\'min-width: 100px;\' onchange=\'new_selected_tor(this);\'><option value=\'authcode_active\'>Authcode Active</option><option value=\'authcode_inactive\'>Authcode Inactive</option><option value=\'mail_active\'>Email Active</option><option value=\'mail_inactive\'>Email Inactive</option><option value=\'delete\'>'.$_LANG_VN['transfer_on_renewal']['delete'].'</option></select>';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['authcode'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'authcode\');" title=\''.$_LANG_VN['transfer_on_renewal']['authcode_description'].'\' class=\'btn btn-default\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['set_old_mail'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'set_old_mail\');" title=\''.$_LANG_VN['transfer_on_renewal']['set_old_mail_description'].'\' class=\'btn btn-warning\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['check_status'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'check_status\');" title=\''.$_LANG_VN['transfer_on_renewal']['check_status_description'].'\' class=\'btn btn-success\' />';
	    $output .= ' <input type=\'submit\' value=\''.$_LANG_VN['transfer_on_renewal']['init_transfer'].'\' style=\'margin-left: 5%;\' onclick="setAction(\'init_transfer\');" title=\''.$_LANG_VN['transfer_on_renewal']['init_transfer_description'].'\' class=\'btn btn-danger\' />';
	    $output .= '</div>';

	    $output .= "<input type='hidden' name='select_transfer_on_renewal' id='select_transfer_on_renewal' value=''>";
	    $output .= "<input type='hidden' name='action_domains' id='action_domains' value='domains_transfer_on_renewal_check'>";

	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_transfer_on_renewal_check').DataTable( {".
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
		$output .= "function select_all_domains_transfer_on_renewal(source){".
						"checkboxes = document.getElementsByName('domains_transfer_on_renewal_fix[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
		$output .= "function setAction(action){".
						"if(action == 'authcode')".
						"	$('input#action_domains').val('domains_transfer_on_renewal_get_authcodes');".
						"else if(action == 'set_old_mail')".
						"	$('input#action_domains').val('domains_transfer_on_renewal_set_old_mails');".
						"else if(action == 'check_status')".
						"	$('input#action_domains').val('domains_transfer_on_renewal_check_status');".
						"else if(action == 'init_transfer'){".
						"	$('input#action_domains').val('domains_transfer_on_renewal_init_transfer');".
						"}".
					"}";
		$output .= "function new_selected_tor(element){".
						"$('input#select_transfer_on_renewal').val(element.value);".
						"$('select#select_transfer_on_renewal_first').val(element.value);".
						"$('select#select_transfer_on_renewal_second').val(element.value);".
					"}";
		$output .= "function validate_transfer(){".
						"if($('input#action_domains').val() == 'domains_transfer_on_renewal_init_transfer')".
						"	return confirm('".$_LANG_VN['transfer_on_renewal']['init_transfer_description']."');".
						"else".
						"	return TRUE;".
					"}";
	    $output .= "</script>";
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["cleaner"]["notfound"];
    $output .= '</div>';
	//domainsTransferOnRenewal

    //domainsLaunchTransferOnRenewal
    if($position == 'domainsLaunchTransferOnRenewal'){
	    $output .=
		"<div name='domainsTransferOnRenewal' id='domainsTransferOnRenewal'>".
			"<div class='contentbox'>".
				"<p><h1>".$_LANG_VN['domains']['transfer_on_renewal_launched'].":</h1></p>".
				'<a href="./addonmodules.php?module=virtualname_tools&tab=domains&position=domainsLaunchTransferOnRenewal">'.
					'<button class="btn btn-primary" type="button">'.
						'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
					'</button>'.
				'</a>'.
			"</div>";
		$output .= '<div id=\'launched_transfers\' name=\'launched_transfers\' class=\'form-control\' style=\'height: 300px;overflow-y: scroll;\' readonly></div>';
	    $output .= '</div>';
		$output .=
		'<script type=\'text/javascript\'>'.
			"$(document).ready(function() {".
				'ajaxTransfers();'.
			"});".
		'</script>';
    }
    //domainsLaunchTransferOnRenewal

    if($position != ""){
		$output .=
		'<script type=\'text/javascript\'>'.
			'showSyncOptions("'.$position.'");'.
		'</script>';
    }

    return $output;
}
function tools_imports($position){
	global $_LANG_VN;
    //GET ALL DOMAIN WITH EXPIRATION != DUE DATE
	if(empty($position))
		$position = 'domainsImport';
	//$output .= "<h2><strong>".$_LANG_VN["imports"]["select"].":</strong></h2></br>\n";
	$output  = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='domainsImport'?'active':'')."' onclick=\"showSyncOptions('domainsImport')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['imports']['label']."</a></li>
					<li class='nav-item ".($position=='TLDsImport'?'active':'')."' onclick=\"showSyncOptions('TLDsImport')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['imports']['labelTLD']."</a></li>
				</ul></br>\n";
	//clients list
    $sort      = "firstname";
    $sortorder = "ASC";
    $clients = select_query("tblclients", "id, firstname, lastname, email", array(), $sort, '');
    $optionsClient = '';
    while($data = mysql_fetch_array($clients)){
        $optionsClient .= "<option value='".$data["id"]."'>".$data["firstname"]." ".$data["lastname"]."-".$data["email"]."</option>";
    }

	//gateway
	$optionsGateway = '';
    $gateway = mysql_query("select distinct(gateway) as gate from tblpaymentgateways order by gateway ASC");
    while($data = mysql_fetch_array($gateway)){
        $optionsGateway .= "<option value='".$data["gate"]."'>".$data["gate"]."</option>";
    }

	//domainsImport
    $output .=
	"<div style='display:none;' name='domainsImport' id='domainsImport'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["imports"]["label"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=imports&position=domainsImport">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_import_list = virtualname_domains_import_list();

    if(count($domains_import_list["domains"])>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["imports"]["description"]."</div>";
    	$output .= "<form method='post'>";
    	$output .= "<div class='contentbox'>".
    					"<label>".$_LANG_VN["others"]["selectclient"]."&nbsp;</label>".
	    				"<select name='selectedClientContacts' id='selectedClientContacts' style='max-width:50%;'>".
					        $optionsClient.
					    "</select>".
					    "</br>".
    					"<label>".$_LANG_VN["others"]["selectgateway"]."&nbsp;</label>".
	    				"<select name='selectedGateway' id='selectedGateway' style='width:25%;' class='form-control select-inline'>".
					        $optionsGateway.
					    "</select>".
					    "<script>$('#selectedClientContacts').select2({width:'50%'});</script>".
				    "</div>";
    	$output .= "<table id='domains_import_list' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th></th><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN["domains"]["expiration"]."</th><th>".$_LANG_VN["domains"]["registrant"]."</th><th>".$_LANG_VN["domains"]["admin"]."</th></tr></thead><tbody>";
	    foreach($domains_import_list["domains"] as $domain){
	    	if(in_array($domain['status'], array('inactive', 'outbound_transfer')))
	    		continue;
	    	$output .= "<tr>";
	    	$output .= "<td><input type='checkbox' name='domains_import_list_check[]' value='".$domain["domain"].";".$domain["status"].";".$domain["expiration"]."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= "<td>".$domain["domain"]."</td>";
	    	$output .= "<td>".$domain["status"]."</td>";
	    	$output .= "<td>".$domain["expiration"]."</td>";
	    	$output .= "<td>".$domain["registrant"]."</td>";
	    	$output .= "<td>".$domain["admin"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";
	    $output .= '<input type="checkbox" id="domains_import_list_checkall" class="checkall_box" title="'.$_LANG_VN["domains"]["selectall"].'" onclick="select_all_domains_import_list(this);">';
	    $output .= '<label for="select_all_domains_import_list">&nbsp;'.$_LANG_VN["domains"]["selectall"].'</label>';

	    $output .= "<script type='text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_import_list').DataTable( {".
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
		$output .= "function select_all_domains_import_list(source){".
						"checkboxes = document.getElementsByName('domains_import_list_check[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
	    $output .= "</script>";
	    $output .= "<input type='hidden' name='action_domains' id='action_domains' value='domains_import_list'>";
	    $output .= "<input type='submit' value='".$_LANG_VN["imports"]["add"]."' style='margin-left: 5%;'/>";
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["imports"]["notfound"];
    $output .= '</div>';
	//domainsImport

	//domainsTLDs
    $output .=
	"<div style='display:none;' name='TLDsImport' id='TLDsImport'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["imports"]["labelTLD"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=imports&position=TLDsImport">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_import_tld_list = virtualname_domains_import_tld_list();

    if(count($domains_import_tld_list)>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["imports"]["labelTLD"]." WHMCS</div>";
    	$output .= "<form method='post'>";
    	$output .= "<div class='contentbox'>".
    					"<label>".$_LANG_VN["domains"]["selectTLD"]."&nbsp;</label>".
				    "</div>";
    	$output .= "<table id='domains_import_tld_list' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th></th><th>TLD</th></tr></thead><tbody>";
	    foreach($domains_import_tld_list as $TLD){
	    	$output .= "<tr>";
	    	$output .= "<td><input type='checkbox' name='domains_import_tld_list_check[]' value='".$TLD."' style='width: 30px;max-width: 30px;'></td>";
	    	$output .= "<td>.".$TLD."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";
	    $output .= '<input type="checkbox" id="domains_import_tld_list_checkall" class="checkall_box" title="Select All" onclick="select_all_tlds_import_list(this);">';
	    $output .= '<label for="select_all_domains_import_tld_list">&nbsp;Select All</label>';

	    $output .= "<script 'text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_import_tld_list').DataTable( {".
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
		$output .= "function select_all_tlds_import_list(source){".
						"checkboxes = document.getElementsByName('domains_import_tld_list_check[]');".
					  	"for(var i=0, n=checkboxes.length;i<n;i++){".
    						"checkboxes[i].checked = source.checked;".
						"}".
					"}";
	    $output .= "</script>";
	    $output .= "<input type='hidden' name='action_domains' id='action_domains' value='domains_import_tld_list'>";
	    $output .= "<input type='submit' value='".$_LANG_VN["imports"]["addTLD"]."' style='margin-left: 5%;'/>";
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["imports"]["notfoundTLDS"];
    $output .= '</div>';

	//domainsImport


    if($position != ""){
		$output .=
		'<script "text/javascript">'.
			'showSyncOptions("'.$position.'");'.
		'</script>';
    }

    return $output;
}
function tools_domains_list($position){
	global $_LANG_VN;
    //GET ALL DOMAIN WITH EXPIRATION != DUE DATE
	if(empty($position))
		$position = 'pendingTransfer';
	$output  = '';
	$output .= "<ul class='nav nav-pills nav-justified'>
					<li class='nav-item ".($position=='pendingTransfer'?'active':'')."' onclick=\"showSyncOptions('pendingTransfer')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['list']['pending_transfer']."</a></li>
					<li class='nav-item ".($position=='irtpList'?'active':'')."' onclick=\"showSyncOptions('irtpList')\"><a class='nav-link' href='' data-toggle='tab'>".$_LANG_VN['list']['irtp']."</a></li>
				</ul></br>\n";

	//pendingTransfer
    $output .=
	"<div style='display:none;' name='pendingTransfer' id='pendingTransfer'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["list"]["pending_transfer"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=list&position=pendingTransfer">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_pending_transfer_list = virtualname_domains_pending_transfer_list();

    if(count($domains_pending_transfer_list["domains"])>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["list"]["pending_transfer_description"]."</div>";
    	$output .= "<form method='post'>";
    	$output .= "<table id='domains_pending_transfer_list' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN["domains"]["expiration"]."</th><th>".$_LANG_VN["domains"]["registrant"]."</th><th>".$_LANG_VN["domains"]["admin"]."</th></tr></thead><tbody>";
	    foreach($domains_pending_transfer_list["domains"] as $domain){
	    	if(in_array($domain['status'], array('inactive', 'outbound_transfer')))
	    		continue;
	    	$output .= "<tr>";
	    	$output .= "<td>".$domain["domain"]."</td>";
	    	$output .= "<td>".$domain["status"]."</td>";
	    	$output .= "<td>".$domain["expiration"]."</td>";
	    	$output .= "<td>".$domain["registrant"]."</td>";
	    	$output .= "<td>".$domain["admin"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";
	    $output .= "<script type='text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_pending_transfer_list').DataTable( {".
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
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["list"]["notfound"];
    $output .= '</div>';
	//pendingTransfer

	//irtpList
    $output .=
	"<div style='display:none;' name='irtpList' id='irtpList'>".
		"<div class='contentbox'>".
			"<p><h1>".$_LANG_VN["list"]["irtp"].":</h1></p>".
			'<a href="./addonmodules.php?module=virtualname_tools&tab=list&position=irtpList">'.
				'<button class="btn btn-primary" type="button">'.
					'<span class="glyphicon glyphicon-refresh"></span> Refresh'.
				'</button>'.
			'</a>'.
		"</div>";

    $domains_irtp_list = virtualname_domains_irtp_list();

    if(count($domains_irtp_list["domains"])>0){
    	$output .= "<div class='contentbox'>".$_LANG_VN["list"]["pending_transfer_description"]."</div>";
    	$output .= "<form method='post'>";
    	$output .= "<table id='domains_irtp_list' class='display contentbox' cellspacing='0' style='width:100%;'>";
    	$output .= "<thead><tr><th>".$_LANG_VN["domains"]["domain"]."</th><th>".$_LANG_VN["domains"]["status"]."</th><th>".$_LANG_VN["domains"]["expiration"]."</th><th>".$_LANG_VN["domains"]["registrant"]."</th><th>".$_LANG_VN["domains"]["admin"]."</th></tr></thead><tbody>";
	    foreach($domains_irtp_list["domains"] as $domain){
	    	if(in_array($domain['status'], array('inactive', 'outbound_transfer')))
	    		continue;
	    	$output .= "<tr>";
	    	$output .= "<td>".$domain["domain"]."</td>";
	    	$output .= "<td>".$domain["status"]."</td>";
	    	$output .= "<td>".$domain["expiration"]."</td>";
	    	$output .= "<td>".$domain["registrant"]."</td>";
	    	$output .= "<td>".$domain["admin"]."</td>";
	    	$output .= "</tr>";
	    }
	    $output .= "</tbody></table>";
	    $output .= "<script type='text/javascript'>";
	    $output .= "$(document).ready(function() {".
					    "$('#domains_irtp_list').DataTable( {".
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
		$output .= "</form>";
	}
	else
		$output .= $_LANG_VN["list"]["notfound"];
    $output .= '</div>';
	//irtp


    if($position != ""){
		$output .=
		'<script "text/javascript">'.
			'showSyncOptions("'.$position.'");'.
		'</script>';
    }

    return $output;
}
//SET AUTHCODE ON TRANSFER ON RENEWAL
function virtualname_tools_set_authcode_transfer($domains_transfer_on_renewal){
	if(count($domains_transfer_on_renewal) <= 0 || empty($domains_transfer_on_renewal))
		return false;
	global $tb_virtualname_tools, $vname_admin;
    virtualname_init();
	$params = $vname_admin->config();
  	$mail = $params['defaultDomainsMail'];
	$domains_list = implode(',', $domains_transfer_on_renewal);
	$sql  = 'SELECT domainid, type FROM mod_virtualname_transfer_on_renewal WHERE status != \'disabled\' AND domainid in ('.$domains_list.')';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
	while($transfer_on_renewal = mysql_fetch_array($res)){
		$sql_dom = 'SELECT * FROM tbldomains where id = '.$transfer_on_renewal['domainid'].' AND status in (\'active\',\'expired\') AND registrar != \'virtualname\'';
		$res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
		if(mysql_num_rows($res_dom) == 0)
			continue;
		if($transfer_on_renewal['type'] == 1){
			$params = mysql_fetch_array($res_dom);
			$params['domainid'] = $params['id'];
			$dom_tld = explode(".", $params['domain'], 2);
			$params['sld'] = $dom_tld[0];
			$params['tld'] = $dom_tld[1];
	        $values = RegGetEPPCode($params);
	        if (!$values['error'] && isset($values['eppcode'])){
	            $authcode = mysql_real_escape_string($values['eppcode']);
		        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET value = \''.$authcode.'\' WHERE domainid = '.$params['id'];
				$res_upd = mysql_query($sql_upd);
	        }
	    }
	    elseif($transfer_on_renewal['type'] == 2){
		    $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET value = \''.$mail.'\' WHERE domainid = '.$transfer_on_renewal['domainid'];
			$res_upd = mysql_query($sql_upd);
	    }
	}
}
//SET TOR OLD EMAIL
function virtualname_tools_set_old_emails($domains_transfer_on_renewal){
	if(count($domains_transfer_on_renewal) <= 0 || empty($domains_transfer_on_renewal))
		return false;
	global $tb_virtualname_tools, $vname_admin, $vname_contacts, $vname_domains;
    virtualname_init();
	$params = $vname_admin->config();
	$domains_list = implode(',', $domains_transfer_on_renewal);
	$sql  = 'SELECT domainid, type FROM mod_virtualname_transfer_on_renewal WHERE status != \'disabled\' AND domainid in ('.$domains_list.')';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
	while($transfer_on_renewal = mysql_fetch_array($res)){
		$sql_dom = 'SELECT * FROM tbldomains where id = '.$transfer_on_renewal['domainid'].' AND status in (\'active\',\'expired\') AND registrar = \'virtualname\'';
		$res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
		if(mysql_num_rows($res_dom) == 0)
			continue;
		if($transfer_on_renewal['type'] == 2){
			$dom_whmcs = mysql_fetch_array($res_dom);
			$params['domainid'] = $dom_whmcs['id'];
			$dom_tld = explode(".", $dom_whmcs['domain'], 2);
			$params['sld'] = $dom_tld[0];
			$params['tld'] = $dom_tld[1];
			$domain_info = $vname_domains->view_domain_info($params);
			$contacts = $vname_contacts->get_contacts_simple_details($params, $domain_info);
	        $contacts['Administrativo']['Email'] = 'test@test.com';
	        $params['contactdetails'] = $contacts;
	        $values = $vname_contacts->set_contacts_simple_details($params);
	        if (!$values['error']){
		        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET status = \'disabled\' WHERE domainid = '.$dom_whmcs['id'];
				$res_upd = mysql_query($sql_upd) or die('<pre>'.$sql_upd.'</pre>ERROR: '.mysql_error());
	        }
	    }
	}
}
//DELETE TOR LIST
function virtualname_tools_delete_tor($domains_transfer_on_renewal){
	if(count($domains_transfer_on_renewal) <= 0 || empty($domains_transfer_on_renewal))
		return false;
	$domains_list = implode(',', $domains_transfer_on_renewal);
	$sql  = 'DELETE FROM mod_virtualname_transfer_on_renewal WHERE domainid in ('.$domains_list.')';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
}
//CHECK TOR STATUS
function virtualname_tools_check_status($domains_transfer_on_renewal){
	if(count($domains_transfer_on_renewal) <= 0 || empty($domains_transfer_on_renewal))
		return false;
	global $tb_virtualname_tools, $vname_admin, $vname_domains;
    virtualname_init();
	$params = $vname_admin->config();
	$domains_list = implode(',', $domains_transfer_on_renewal);
	$sql  = 'SELECT domainid, type FROM mod_virtualname_transfer_on_renewal WHERE domainid in ('.$domains_list.')';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());

	while($transfer_on_renewal = mysql_fetch_array($res)){
		$sql_dom = 'SELECT * FROM tbldomains where id = '.$transfer_on_renewal['domainid'].' AND status in (\'active\',\'expired\') AND registrar = \'virtualname\'';
		$res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
		if(mysql_num_rows($res_dom) == 0)
			continue;
		//IF REGISTRAR == VIRTUALNAME
		$dom_whmcs = mysql_fetch_array($res_dom);
		if($dom_whmcs['registrar'] == 'virtualname'){
			$params['domainid'] = $dom_whmcs['id'];
			$dom_tld = explode(".", $dom_whmcs['domain'], 2);
			$params['sld'] = $dom_tld[0];
			$params['tld'] = $dom_tld[1];
			$domain_info = $vname_domains->view_domain_info($params);
	        if($domain_info['status']['code'] == 400){
		        //404 NOT FOUND
		        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET status = \'error\' WHERE domainid = '.$dom_whmcs['id'];
				$res_upd = mysql_query($sql_upd);
	        }
	        elseif($domain_info['status']['code'] == 200){
	        	$transfers_status = array('transferring','transfer_requested','transfer_initiated','transfer_email_sent','transfer_rejected','transfer_approved','transfer_finished','transfer_expired','transfer_waiting_unlocked','transfer_waiting_admin','transfer_waiting_registrar','transfer_order_locked','transfer_waiting_authcode','transfer_email_not_sent','transfer_resend_authcode','transfer_waiting_pending_registrant_approval');
				$actives_status = array('active', 'active_pending_registrant_approval');
				if(in_array($domain_info['response'][0]['product_info']['product_status'], $transfers_status)){
			        //TRANSFER -> In progress
			        $note = '[OK]: Transfer in progress';
			        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET status = \'active\', notes = \''.$note.'\' WHERE domainid = '.$dom_whmcs['id'];
					$res_upd = mysql_query($sql_upd);
		        }
		        elseif(in_array($domain_info['response'][0]['product_info']['product_status'], $actives_status)){
			        //ACTIVE -> Complete
			        $note = '[OK]: Transfer complete';
			        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET status = \'disabled\', notes = \''.$note.'\' WHERE domainid = '.$dom_whmcs['id'];
					$res_upd = mysql_query($sql_upd);
		        }
		        else{
		        	//UNKNOW
			        $sql_upd  = 'UPDATE mod_virtualname_transfer_on_renewal SET status = \'error\' WHERE domainid = '.$dom_whmcs['id'];
					$res_upd = mysql_query($sql_upd);
		        }
	        }
		}
	}
}
//LAUNCH TOR LIST
function virtualname_tools_init_transfer($domains_transfer_on_renewal){
	if(count($domains_transfer_on_renewal) <= 0 || empty($domains_transfer_on_renewal))
		return false;
	$domains_list = implode(',', $domains_transfer_on_renewal);
	$sql  = 'SELECT domainid, type, value FROM mod_virtualname_transfer_on_renewal WHERE domainid in ('.$domains_list.') AND status in (\'active\')';
	$res = mysql_query($sql) or die('<pre>'.$sql.'</pre>ERROR: '.mysql_error());
	while($transfer_on_renewal = mysql_fetch_array($res)){
		$sql_dom = 'SELECT * FROM tbldomains where id = '.$transfer_on_renewal['domainid'].' AND status in (\'active\',\'expired\') AND registrar != \'virtualname\'';
		$res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
		if(mysql_num_rows($res_dom) == 0)
			continue;
		if($transfer_on_renewal['type'] == 1)
			$type = 3;
		elseif($transfer_on_renewal['type'] == 2)
			$type = 4;
		else
			continue;
		$sql_upd = 'UPDATE mod_virtualname_transfer_on_renewal SET type = '.$type.' WHERE domainid = '.$transfer_on_renewal['domainid'];
		$res_upd = mysql_query($sql_upd) or die('<pre>'.$sql_upd.'</pre>ERROR: '.mysql_error());
	}
}
//GET ALL PENDING TRANSFERS
function tools_transfers_list(){
	$response = array();
	$sql = 'SELECT domainid, type, value FROM mod_virtualname_transfer_on_renewal WHERE status IN (\'active\') AND type in (3, 4)';
	$res = mysql_query($sql);
	while($transfer_on_renewal = mysql_fetch_array($res)){
		$sql_dom = 'SELECT domain FROM tbldomains where id = '.$transfer_on_renewal['domainid'];
		$res_dom = mysql_query($sql_dom);
		$domain = mysql_fetch_array($res_dom);
		$transfer_on_renewal['name'] = $domain['domain'];
		$response[] = $transfer_on_renewal;
	}
	$total_count = count($response);
	if($total_count > 0)
		return array('domains' => $response, 'total_count' => count($response));
	else
		return array('error' => 'Not available transfers');
}
//LAUNCH TRANSFER
function tools_domain_transfer($transfer_on_renewal){
	global $tb_virtualname_tools, $vname_domains;
    virtualname_init();
	$sql_dom = 'SELECT * FROM tbldomains where id = '.$transfer_on_renewal['domainid'].' AND status in (\'active\',\'expired\') AND registrar != \'virtualname\'';
	$res_dom = mysql_query($sql_dom) or die('<pre>'.$sql_dom.'</pre>ERROR: '.mysql_error());
	if(mysql_num_rows($res_dom) == 0)
		return array('error' => 'Dominio no disponible para transferir');
	$params = mysql_fetch_array($res_dom);
	$params['domainid'] = $params['id'];
	$dom_tld = explode(".", $params['domain'], 2);
	$params['sld'] = $dom_tld[0];
	$params['tld'] = $dom_tld[1];
	$vars = array();
    $vars['params'] = $params;
    $vars['userid'] = $params['userid'];
	$adminID = $_SESSION['adminid'];
	$response = $vname_domains->transfer_on_renewal($vars, $transfer_on_renewal, $adminID);
    if($response['abortWithError'])
        $vname_domains->set_notes_transfer_on_renewal($domainid, '[Error]: '.$response['abortWithError']);
    elseif($response['abortWithSuccess'])
        $vname_domains->set_notes_transfer_on_renewal($domainid, '[OK]: Transferring');
    else
        $vname_domains->set_notes_transfer_on_renewal($domainid, '[Error]: Unknow response');
	return $response;
}
?>