// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 8.2.X
// * @copyright Copyright (c) 2021, Virtualname
// * @version 1.3.1
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * File description: CLIENT JS FILE
// *************************************************************************
jQuery(document).ready(function(){
    update_urls();
});
function update_urls(){
    var links = document.getElementsByTagName('a');
    for (var l in links){
        var parser = document.createElement('a');
        parser.href = links[l].href;
        var urlData = parser.pathname+parser.search;
        var filename = urlData.substring(urlData.lastIndexOf('/')+1);
        if (filename == 'clientarea.php?action=details'){
            links[l].href = './clientareadata.php?action=details';
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename == 'clientarea.php?action=contacts' || filename == 'contacts'){
            links[l].href = './clientareadata.php?action=contacts';
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename == 'index.php?rp=/account/contacts'){
            links[l].href = './clientareadata.php?action=contacts';
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename == 'clientarea.php?action=addcontact'){
            links[l].href = './clientareadata.php?action=addcontact';
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename.indexOf('clientarea.php?action=domaincontacts')>=0){
            var resLink = urlData.replace('clientarea', 'clientareadata');
            links[l].href = resLink;
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename == 'clientareadata.php?action=changepw'){
            links[l].href = './clientarea.php?action=changepw';
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename.indexOf('clientarea.php?action=contacts&id=')>=0){
            var resLink = urlData.replace('clientarea', 'clientareadata');
            links[l].href = resLink;
            links[l].href = clear_friendly_urls(links[l].href)
        }
        else if (filename.indexOf('clientsdomaincontacts.php?domainid=')>=0){
            var resLink = urlData.replace('clientsdomaincontacts', 'clientsdatadomaincontacts');
            links[l].href = resLink;
            links[l].href = clear_friendly_urls(links[l].href)
        }
    }
}
function clear_friendly_urls(url){
    return url.replace('/account', '').replace('/index.php', '').replace('/user', '');
}
$(document).ready(function() {
    $('#tableDomainsList')
        .on( 'draw.dt',  function () { update_urls(); } )
        .dataTable();
});
