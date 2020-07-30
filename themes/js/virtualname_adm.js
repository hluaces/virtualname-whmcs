// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.3
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: ADMIN JS FILE
// *************************************************************************
jQuery(document).ready(function(){
    if (document.cookie.indexOf('vname_update_popup') <= 0) {
        $('#vname_popup').show();
    }
    var links = document.getElementsByTagName('input');
    for (var l in links){
        if(links[l].type == 'button'){
            var parser     = document.createElement('input');
            parser.onclick = links[l].onclick;
            var urlData    = parser.onclick + '';
            if (urlData.indexOf('clientsdomaincontacts.php?domainid=')>=0){
                var resLink = urlData.replace('clientsdomaincontacts', 'clientsdatadomaincontacts');
                resLink = resLink.replace('function onclick(event) {', '');
                resLink = resLink.replace('}', '');
                resLink = resLink.trim();
                links[l].setAttribute('onclick', resLink);
            }
        }
    }
});
function virtualname_dialogClose(){
    $('#vname_popup').hide();
}
function setVirtualnameCookie(element) {
    if(element == true)
        createCookieVN('vname_update_popup', 1, 1);
    else
        createCookieVN('vname_update_popup','',-1);
}
function createCookieVN(name,value,days){
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = '; expires='+date.toGMTString();
    }
    else{
    	var expires = '';
    }
    document.cookie = name+'='+value+expires+'; path=/';
}
function change_transfer_on_renewal(elem){
    if(elem.value == 1 || elem.value == 3){
        $('#authcode_label').removeClass('hidden');
        $('#email_label').addClass('hidden');
    }
    else if(elem.value == 2 || elem.value == 4){
        $('#email_label').removeClass('hidden');
        $('#authcode_label').addClass('hidden');
    }
}
function active_transfer_on_renewal(elem){
    if(elem.value == 'disabled'){
        $('#select_transfer_on_renewal').prop('disabled',true);
        $('#authcode_transfer_on_renewal').prop('disabled',true);
        $('#mail_transfer_on_renewal').prop('disabled',true);
        $('#status_transfer_on_renewal').css('background-color', 'red');
    }
    else if(elem.value == 'active'){
        $('#select_transfer_on_renewal').prop('disabled',false);
        $('#authcode_transfer_on_renewal').prop('disabled',false);
        $('#mail_transfer_on_renewal').prop('disabled',false);
        $('#status_transfer_on_renewal').css('background-color', 'limegreen');
    }
}