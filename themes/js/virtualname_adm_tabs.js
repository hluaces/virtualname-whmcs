// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 8.2.X
// * @copyright Copyright (c) 2021, Virtualname
// * @version 1.3.1
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * File description: ADMIN TAB JS FILE
// *************************************************************************
function virtualname_client_tab(client_id){
    var contact_li = document.getElementById('clientTab-3').parentNode;
    var parent_tab = contact_li.parentNode;
    var contact_name = document.createElement('li');
    contact_name.className = 'tab';
    contact_name.innerHTML = '<a href=\'clientsdatadomaincontacts.php?action=clientscontacts&userid='+client_id+'&contactid=0\' id=\'clientTab-TCPanel\'>TCPanel</a>';
    parent_tab.insertBefore(contact_name, contact_li);
}
