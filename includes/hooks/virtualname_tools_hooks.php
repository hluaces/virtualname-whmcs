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
// * File description: Hook for Tools Extra Module
// *************************************************************************
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

add_hook("AdminHomepage", 1, "hook_admin_version_popup_tools");

############################################################
############HOOK FUNCTIONS##################################
############################################################
//POPUP NEW VERSION
function hook_admin_version_popup_tools($var){
    $version_popup  = '';
    $module_version = '1.1.9';
    $version = tools_get_module_version();
    if($module_version != $version["response"]["lastversion"]){
        $version_popup =
            '<style>
                #vnotice_tools .update_virtualname{
                  background: url(../modules/registrars/virtualname/vname-download.png) no-repeat #fcf8e3 15px 15px;
                  margin: 15px;
                  padding: 19px 15px 15px 70px;
                  height: 38px;
                  border: solid 1px #e9ebec;
                  -moz-border-radius: 5px;
                  -webkit-border-radius: 5px;
                  border-radius: 5px;
                  font-weight: bold;
                  color: #7AB353;;
                  font-family: Helvetica, Arial, sans-serif;
                  font-size: 20px;
                  text-shadow: 0px 1px 0px #fff;
                }
                #vnotice_tools .latestvname{
                    color: #333;
                    font-size: 50px;
                    font-weight: bold;
                    margin: 40px 0 0 0;
                    height: 78px;
                    font-family: Helvetica, Arial, sans-serif;
                    text-align: center;
                }
                #vnotice_tools .currentvname{
                    margin: 0;
                    line-height: 40px;
                    color: #999;
                    font-family: Helvetica, Arial, sans-serif;
                    text-align: center;
                }
                #vnotice_tools .btn-vname{
                  color: #ffffff;
                  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
                  background-color: #006dcc;
                  background-image: -moz-linear-gradient(top, #0088cc, #0044cc);
                  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0044cc));
                  background-image: -webkit-linear-gradient(top, #0088cc, #0044cc);
                  background-image: -o-linear-gradient(top, #0088cc, #0044cc);
                  background-image: linear-gradient(to bottom, #0088cc, #0044cc);
                  background-repeat: repeat-x;
                  border-color: #0044cc #0044cc #002a80;
                  border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
                  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ff0088cc2", endColorstr="#ff0044cc", GradientType=0);
                  filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
                  text-decoration: none;
                  display: inline-block;
                  padding: 5px 12px;
                  margin-bottom: 0;
                  font-size: 14px;
                  line-height: 20px;
                  text-align: center;
                  text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
                  vertical-align: middle;
                  cursor: pointer;
                  border-radius: 4px;
                }
            </style>
            <div class="admindialog" id="virtualname_tools_popup" style="position: absolute; z-index: 1001; top: 50px; left: auto; display: none;display: block;width: 410px;">
                <a href="" onclick="virtualname_tools_dialogClose();return false" class="close">x</a>
                <div id="virtualname_tools_popup_dialog">
                    <div class="content" style="width:400px;height:300px;overflow:hidden;">
                        <div class="boxy" id="virtualname_tools_popup" style="padding:0;width:400px;height:300px;overflow:hidden;">
                            <div id="vnotice_tools">
                                <div class="body">
                                    <h3 class="update_virtualname">
                                    Virtualname - TCpanel TOOLS
                                    <p class="desc">New update available.</p>
                                    </h3>
                                    <p class="latestvname">v'.$version["response"]["lastversion"].'</p>
                                    <p class="currentvname">Current version v'.$module_version.' <a href="http://whmcs.virtualname.net/descargas" target="_blank" class="btn-vname">Get Virtualname Tools Update</a></p>
                                </div>
                                <div class="donotshow">
                                    <label style="color:gray;">
                                        <input type="checkbox" onclick="setVirtualnameToolsCookie($(this).is(\':checked\'))"> Hide this popup
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function virtualname_tools_dialogClose(){
                    $("#virtualname_tools_popup").hide();
                }
                function setVirtualnameToolsCookie(element) {
                    if(element == true)
                        createCookieVnTools("vname_tools_update_popup", 1, 1);
                    else
                        createCookieVnTools("vname_tools_update_popup","",-1);
                }
                function createCookieVnTools(name,value,days){
                    if (days) {
                        var date = new Date();
                        date.setTime(date.getTime()+(days*24*60*60*1000));
                        var expires = "; expires="+date.toGMTString();
                    }
                    else var expires = "";
                    document.cookie = name+"="+value+expires+"; path=/";
                }
                if (document.cookie.indexOf("vname_tools_update_popup") > 0) {
                    $("#virtualname_tools_popup").hide();
                }
            </script>';
    }
    return $version_popup;
}
//GET MODULE VERSION
function tools_get_module_version(){
    $version_URL = "http://whmcs.virtualname.net/whmcs-repositories/whmcs-virtualname-tools-version";
    $version_content = explode("-",file_get_contents($version_URL));
    $versions = array("lastversion"=> $version_content[2]);
    $request = array("response"=>$versions);
    return $request;
}
?>