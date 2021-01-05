<!--// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.4
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: Clients Custom Domain Contacts
// *************************************************************************-->

{if $currentAction == 'details'}
    {if $successful}
        {include file="$template/includes/alert.tpl" type="success" msg=$LANG.changessavedsuccessfully textcenter=true}
    {/if}

    {if $errormessage}
        {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
    {/if}

    <script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>
    <script type="text/javascript">
        {literal}
        jQuery(document).ready(function(){
            $('#clientidentificationnumber').change(function() {
                $('#tax_id').val($(this).val());
                true
            });
        });
        {/literal}
    </script>

    <div class="col-md-12 pull-md-right" id="rightMenu">

        <form method="post" action="?action=details" role="form">

            <div class="row">
                <div class="col-sm-6">

                    <div class="form-group">
                        <label class="control-label" for="clientvalidation"></label>
                        <div class="controls">
                            {if $clientvirtualnamevalidation == 1}
                                <span style="font-family: wingdings; font-size: 200%;color:green;">✔</span>{$LANG.clientareavirtualnamevalidate}
                            {else}
                                <span style="font-family: wingdings; font-size: 200%;color:red;">✘</span>{$LANG.clientareavirtualnamenovalidate}
                            {/if}
                            <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="bottom" title="{$LANG.clientarevalidationexplanation}"><i class="fa fa-question"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="inputFirstName" class="control-label">{$LANG.clientareafirstname}</label>
                        <input type="text" name="firstname" id="inputFirstName" value="{$clientfirstname}"{if in_array('firstname', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputLastName" class="control-label">{$LANG.clientarealastname}</label>
                        <input type="text" name="lastname" id="inputLastName" value="{$clientlastname}"{if in_array('lastname', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputCompanyName" class="control-label">{$LANG.clientareacompanyname}</label>
                        <input type="text" name="companyname" id="inputCompanyName" value="{$clientcompanyname}"{if in_array('companyname', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="control-label">{$LANG.clientareaemail}</label>
                        <input type="email" name="email" id="inputEmail" value="{$clientemail}"{if in_array('email', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                </div>
                <div class="col-sm-6 col-xs-12 pull-right">

                    <div class="form-group">
                        <label for="inputAddress1" class="control-label">{$LANG.clientareaaddress1}</label>
                        <input type="text" name="address1" id="inputAddress1" value="{$clientaddress1}"{if in_array('address1', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputAddress2" class="control-label">{$LANG.clientareaaddress2}</label>
                        <input type="text" name="address2" id="inputAddress2" value="{$clientaddress2}"{if in_array('address2', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputCity" class="control-label">{$LANG.clientareacity}</label>
                        <input type="text" name="city" id="inputCity" value="{$clientcity}"{if in_array('city', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputState" class="control-label">{$LANG.clientareastate}</label>
                        <input type="hidden" name="stateHidden" id="inputStateHidden" value="{$clientstate}" class="form-control" />
                        <input type="text" name="state" id="inputState" value="{$clientstate}"{if in_array('state', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputPostcode" class="control-label">{$LANG.clientareapostcode}</label>
                        <input type="text" name="postcode" id="inputPostcode" value="{$clientpostcode}"{if in_array('postcode', $uneditablefields)} readonly{/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="country">{$LANG.clientareacountry}</label>
                        <br/>
                        {$clientcountriesdropdown}
                    </div>

                    <div class="form-group">
                        <label for="inputPhone" class="control-label">{$LANG.clientareaphonenumber}</label>
                        <input type="tel" name="phonenumber" id="inputPhone" value="{$clientphonenumber}"{if in_array('phonenumber',$uneditablefields)} readonly {/if} class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputBillingContact" class="control-label">{$LANG.defaultbillingcontact}</label>
                        <select name="billingcid" id="inputBillingContact" class="form-control">
                            <option value="0">{$LANG.usedefaultcontact}</option>
                            {foreach from=$contacts item=contact}
                            <option value="{$contact.id}"{if $contact.id eq $billingcid} selected="selected"{/if}>{$contact.name}</option>
                            {/foreach}
                        </select>
                    </div>

                </div>
                <div class="col-sm-6 col-xs-12 pull-left">

                    <div class="form-group">
                        <label for="inputPaymentMethod" class="control-label">{$LANG.paymentmethod}</label>
                        <select name="paymentmethod" id="inputPaymentMethod" class="form-control">
                            <option value="none">{$LANG.paymentmethoddefault}</option>
                            {foreach from=$paymentmethods item=method}
                            <option value="{$method.sysname}"{if $method.sysname eq $defaultpaymentmethod} selected="selected"{/if}>{$method.name}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="form-group" {if $hideicnumber} style='display:none;' {/if}>
                        <label class="control-label" for="clientidentificationnumber">{$LANG.clientIdentificationNumber}</label>
                        <input class="form-control" type="text" name="clientidentificationnumber" id="clientidentificationnumber" value="{$clientidentificationnumber}" class="form-control"/>
                        <input class="form-control" type="hidden" name="tax_id" id="tax_id" value="{$clientidentificationnumber}" />
                    </div>

                    <div class="form-group" {if $hideicnumber} style='display:none;' {/if}>
                        <label class="control-label" for="legal_form">{$LANG.legal_form_contact}</label>
                        <br/>
                        {$clientlegalforms}
                    </div>

                    {if $customfields}
                        {foreach from=$customfields key=num item=customfield}
                            <div class="form-group">
                                <label class="control-label" for="customfield{$customfield.id}">{$customfield.name}</label>
                                <div class="control">
                                    {$customfield.input} {$customfield.description}
                                </div>
                            </div>
                        {/foreach}
                    {/if}
                    {if $emailPreferencesEnabled}
                        <div class="form-group">
                            <h3>{$LANG.clientareacontactsemails}</h3>
                            <div class="controls checkbox">
                                {foreach $emailPreferences as $emailType => $value}
                                    <label>
                                        <input type="hidden" name="email_preferences[{$emailType}]" value="0">
                                        <input type="checkbox" name="email_preferences[{$emailType}]" id="{$emailType}Emails" value="1"{if $value} checked="checked"{/if} />
                                        {lang key="emailPreferences."|cat:$emailType}
                                    </label>{if !($emailType@last)}<br />{/if}
                                {/foreach}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            {if $showMarketingEmailOptIn}
                <div class="marketing-email-optin">
                    <h4>{lang key='emailMarketing.joinOurMailingList'}</h4>
                    <p>{$marketingEmailOptInMessage}</p>
                    <input type="checkbox" name="marketingoptin" value="1"{if $marketingEmailOptIn} checked{/if} class="no-icheck toggle-switch-success" data-size="small" data-on-text="{lang key='yes'}" data-off-text="{lang key='no'}">
                </div>
            {/if}

            <div class="form-group text-center">
                <input class="btn btn-primary" type="submit" name="save" value="{$LANG.clientareasavechanges}" />
                {if $clientvirtualnamevalidation != 1}
                    <input class="btn btn-success" type="submit" name="linked" id="linked" value="{$LANG.clientarealinkedcontact}" />
                {else}
                    <input class="btn btn-danger" type="submit" name="unlinked" id="unlinked" value="{$LANG.clientareaunlinkedcontact}" />
                {/if}
                <input class="btn btn-default" type="reset" value="{$LANG.cancel}" />
                <input type="hidden" id="userid" name="userid" value="{$clientid}"/>
            </div>

        </form>
    </div>


{/if}

{if $currentAction == 'contacts'}
    {if $contactid}

        {if $successful}
            {include file="$template/includes/alert.tpl" type="success" msg=$LANG.changessavedsuccessfully textcenter=true}
        {/if}

        {if $errormessage}
            {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
        {/if}

        <script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>

        <script type="text/javascript">
            {literal}
            jQuery(document).ready(function(){
                jQuery("#subaccount").click(function () {
                    if (jQuery("#subaccount:checked").val()!=null) {
                        jQuery("#subaccountfields").slideDown();
                    }
                    else {
                        jQuery("#subaccountfields").slideUp();
                    }
                });
                $('#identificationnumber').change(function() {
                    $('#tax_id').val($(this).val());
                    true
                });
            });
            {/literal}
            function deleteContactVNAME()
                {ldelim}
                if (confirm("{$LANG.clientareadeletecontactareyousure}"))
                    {ldelim}
                    window.location='clientareadata.php?action=contacts&delete=true&id={$contactid}';
                    {rdelim}
                {rdelim}
        </script>

        <div class="col-md-12 pull-md-right" id="rightMenu">

            <div class="alert alert-block alert-info text-center">
                <form class="form-inline" role="form" method="post" action="{$smarty.server.PHP_SELF}?action=contacts">
                    <div class="form-group" style='max-width: 100%;'>
                        <label for="inputContactID">{$LANG.clientareachoosecontact}</label>
                        <select name="contactid" id="inputContactID" onchange="submit()" class="form-control" style='max-width: 95%;'>
                            {foreach item=contact from=$contacts}
                                <option value="{$contact.id}"{if $contact.id eq $contactid} selected="selected"{/if}>{$contact.name} - {$contact.email}</option>
                            {/foreach}
                            <option value="new">{$LANG.clientareanavaddcontact}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default">{$LANG.go}</button>
                </form>
            </div>

            <form role="form" method="post" action="{$smarty.server.PHP_SELF}?action=contacts&id={$contactid}">
                <input type="hidden" name="submit" value="true" />

                <div class="row">
                    <div class="col-sm-6">

                        <div class="form-group">
                            <label class="control-label" for="clientvalidation"></label>
                            <div class="controls">
                                {if $contactvnamevalidation == 1}
                                    <span style="font-family: wingdings; font-size: 200%;color:green;">✔</span>{$LANG.clientareavirtualnamevalidate}
                                {else}
                                    <span style="font-family: wingdings; font-size: 200%;color:red;">✘</span>{$LANG.clientareavirtualnamenovalidate}
                                {/if}
                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="bottom" title="{$LANG.clientarevalidationexplanation}"><i class="fa fa-question"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">

                        <div class="form-group">
                            <label for="inputFirstName" class="control-label">{$LANG.clientareafirstname}</label>
                            <input type="text" name="firstname" id="inputFirstName" value="{$contactfirstname}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputLastName" class="control-label">{$LANG.clientarealastname}</label>
                            <input type="text" name="lastname" id="inputLastName" value="{$contactlastname}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputCompanyName" class="control-label">{$LANG.clientareacompanyname}</label>
                            <input type="text" name="companyname" id="inputCompanyName" value="{$contactcompanyname}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputEmail" class="control-label">{$LANG.clientareaemail}</label>
                            <input type="email" name="email" id="inputEmail" value="{$contactemail}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputPhone" class="control-label">{$LANG.clientareaphonenumber}</label>
                            <input type="tel" name="phonenumber" id="inputPhone" value="{$contactphonenumber}" class="form-control" />
                        </div>

                        <div class="form-group" {if $hideicnumber} style='display:none;' {/if}>
                            <label class="control-label" for="identificationnumber">{$LANG.clientIdentificationNumber}</label>
                            <input class="form-control" type="text" name="identificationnumber" id="identificationnumber" value="{$contactidentificationnumber}" />
                            <input class="form-control" type="hidden" name="tax_id" id="tax_id" value="{$contactidentificationnumber}" />
                        </div>

                        <div class="form-group" {if $hideicnumber} style='display:none;' {/if}>
                            <label class="control-label" for="legal_form">{$LANG.legal_form_contact}</label>
                            {$legalforms}
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="inputSubaccountActivate">{$LANG.subaccountactivate}</label>
                            <div class="controls checkbox">
                                <label>
                                    <input type="checkbox" name="subaccount" id="inputSubaccountActivate"{if $subaccount} checked{/if} /> {$LANG.subaccountactivatedesc}
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-6 col-xs-12 pull-right">

                        <div class="form-group">
                            <label for="inputAddress1" class="control-label">{$LANG.clientareaaddress1}</label>
                            <input type="text" name="address1" id="inputAddress1" value="{$contactaddress1}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputAddress2" class="control-label">{$LANG.clientareaaddress2}</label>
                            <input type="text" name="address2" id="inputAddress2" value="{$contactaddress2}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputCity" class="control-label">{$LANG.clientareacity}</label>
                            <input type="text" name="city" id="inputCity" value="{$contactcity}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputState" class="control-label">{$LANG.clientareastate}</label>
                            <input type="text" name="state" id="inputState" value="{$contactstate}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="inputPostcode" class="control-label">{$LANG.clientareapostcode}</label>
                            <input type="text" name="postcode" id="inputPostcode" value="{$contactpostcode}" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="country" style="width: 100%;">{$LANG.clientareacountry}</label>
                            {$countriesdropdown}
                        </div>

                    </div>
                </div>

                <div id="subacct-container" class="well{if !$subaccount} hidden{/if}">

                    <div class="form-group">
                        <label class="full control-label">{$LANG.subaccountpermissions}</label>
                        <div class="checkbox clearfix">
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" value="profile"{if in_array('profile',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsprofile}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permcontacts" value="contacts"{if in_array('contacts',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermscontacts}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permproducts" value="products"{if in_array('products',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsproducts}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permmanageproducts" value="manageproducts"{if in_array('manageproducts',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsmanageproducts}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permdomains" value="domains"{if in_array('domains',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsdomains}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permmanagedomains" value="managedomains"{if in_array('managedomains',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsmanagedomains}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="perminvoices" value="invoices"{if in_array('invoices',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsinvoices}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permquotes" value="quotes"{if in_array('quotes', $permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsquotes}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permtickets" value="tickets"{if in_array('tickets',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermstickets}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permaffiliates" value="affiliates"{if in_array('affiliates',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsaffiliates}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permemails" value="emails"{if in_array('emails',$permissions)} checked{/if} />
                                    <span>{$LANG.subaccountpermsemails}</span>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" id="permorders" value="orders"{if in_array('orders',$permissions)} checked{/if} />
                                    {$LANG.subaccountpermsorders}
                                </label>
                            </div>
                        </div>
                    </div>

                    <fieldset class="form-horizontal">
                        <div id="newPassword1" class="form-group has-feedback">
                            <label for="inputNewPassword1" class="col-sm-5 control-label">{$LANG.newpassword}</label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" id="inputNewPassword1" />
                                <span class="form-control-feedback glyphicon"></span>
                                {include file="$template/includes/pwstrength.tpl" noDisable=true}
                            </div>
                        </div>
                        <div id="newPassword2" class="form-group has-feedback">
                            <label for="inputNewPassword2" class="col-sm-5 control-label">{$LANG.confirmnewpassword}</label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" id="inputNewPassword2" />
                                <span class="form-control-feedback glyphicon"></span>
                                <div id="inputNewPassword2Msg">
                                </div>
                            </div>
                        </div>
                        {if $hasLinkedProvidersEnabled}
                            <h3>Linked Accounts</h3>
                            {include file="$template/includes/linkedaccounts.tpl" linkContext="linktable" }
                        {/if}
                    </fieldset>

                </div>
                <div class="form-group">
                    <label class="control-label">{$LANG.clientareacontactsemails}</label>
                    <div class="controls checkbox">
                        {foreach $emailPreferences as $emailType => $value}
                            <input type="hidden" name="email_preferences[{$emailType}]" value="0">
                            <input type="checkbox" name="{$emailType}emails" id="{$emailType}emails" value="1"{if $value} checked="checked"{/if} />
                            {lang key="clientareacontactsemails"|cat:$emailType}
                            </label>{if !($emailType@last)}<br />{/if}
                        {/foreach}
                    </div>
                </div>
                <div class="form-group text-center">
                    <input class="btn btn-primary" type="submit" name="save" value="{$LANG.clientareasavechanges}" />
                    {if $contactvnamevalidation != 1}
                        <input class="btn btn-success" type="submit" name="linked" id="linked" value="{$LANG.clientarealinkedcontact}" />
                    {/if}
                    <input type="hidden" id="contactid" name="contactid" value="{$contactid}"/>
                    <input class="btn btn-default" type="reset" value="{$LANG.cancel}" />
                    <input class="btn btn-danger" type="button" value="{$LANG.clientareadeletecontact}" onclick="deleteContactVNAME();" />
                </div>

            </form>
        </div>
        <script type="text/javascript">
            $(".header-lined").prependTo("#rightMenu");
        </script>
    {else}
        {include file="$template/clientareaaddcontact.tpl"}
    {/if}
{/if}

{if $currentAction == 'addcontact'}
    {if $errormessage}
        {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
    {/if}
    <script>
        var stateNotRequired = true;
        {literal}
        jQuery(document).ready(function(){
            $('#identificationnumber').change(function() {
                $('#tax_id').val($(this).val());
                true
            });
        });
        {/literal}
    </script>
    <script type="text/javascript" src="{$BASE_PATH_JS}/StatesDropdown.js"></script>

    <div class="col-md-12 pull-md-right" id="rightMenu">
        <div class="alert alert-block alert-info text-center">
            <form class="form-inline" role="form" method="post" action="{$smarty.server.PHP_SELF}?action=contacts">
                <div class="form-group" style='max-width: 100%;'>
                    <label for="inputContactID">{$LANG.clientareachoosecontact}</label>
                    <select name="contactid" id="inputContactID" onchange="submit()" class="form-control" style='max-width: 95%;'>
                        {foreach item=contact from=$contacts}
                            <option value="{$contact.id}">{$contact.name} - {$contact.email}</option>
                        {/foreach}
                        <option value="new" selected="selected">{$LANG.clientareanavaddcontact}</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default">{$LANG.go}</button>
            </form>
        </div>

        <form role="form" method="post" action="{$smarty.server.PHP_SELF}?action=addcontact">
            <input type="hidden" name="submit" value="true" />

            <div class="row">
                <div class="col-sm-6">

                    <div class="form-group">
                        <label for="inputFirstName" class="control-label">{$LANG.clientareafirstname}</label>
                        <input type="text" name="firstname" id="inputFirstName" value="{$contactfirstname}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputLastName" class="control-label">{$LANG.clientarealastname}</label>
                        <input type="text" name="lastname" id="inputLastName" value="{$contactlastname}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputCompanyName" class="control-label">{$LANG.clientareacompanyname}</label>
                        <input type="text" name="companyname" id="inputCompanyName" value="{$contactcompanyname}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="control-label">{$LANG.clientareaemail}</label>
                        <input type="email" name="email" id="inputEmail" value="{$contactemail}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputPhone" class="control-label">{$LANG.clientareaphonenumber}</label>
                        <input type="tel" name="phonenumber" id="inputPhone" value="{$contactphonenumber}" class="form-control" />
                    </div>
                    <div class="form-group" {if $hideicnumber} style='display:none;' {/if}>
                        <label class="control-label" for="identificationnumber">{$LANG.clientIdentificationNumber}</label>
                        <input class="form-control" type="text" name="identificationnumber" id="identificationnumber" value="{$contactidentificationnumber}"/>
                        <input class="form-control" type="hidden" name="tax_id" id="tax_id" value="{$contactidentificationnumber}" />
                    </div>
                    <div class="form-group" style='display:none;'>
                        <label class="control-label" for="legal_form">{$LANG.legal_form_contact}</label>
                        <br/>
                        {$legalforms}
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputSubaccountActivate">{$LANG.subaccountactivate}</label>
                        <div class="controls checkbox">
                            <label><input type="checkbox" name="subaccount" id="inputSubaccountActivate"{if $subaccount} checked{/if} /> {$LANG.subaccountactivatedesc}</label>
                        </div>
                    </div>

                </div>
                <div class="col-sm-6 col-xs-12 pull-right">

                    <div class="form-group">
                        <label for="inputAddress1" class="control-label">{$LANG.clientareaaddress1}</label>
                        <input type="text" name="address1" id="inputAddress1" value="{$contactaddress1}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputAddress2" class="control-label">{$LANG.clientareaaddress2}</label>
                        <input type="text" name="address2" id="inputAddress2" value="{$contactaddress2}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputCity" class="control-label">{$LANG.clientareacity}</label>
                        <input type="text" name="city" id="inputCity" value="{$contactcity}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputState" class="control-label">{$LANG.clientareastate}</label>
                        <input type="text" name="state" id="inputState" value="{$contactstate}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label for="inputPostcode" class="control-label">{$LANG.clientareapostcode}</label>
                        <input type="text" name="postcode" id="inputPostcode" value="{$contactpostcode}" class="form-control" />
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="country">{$LANG.clientareacountry}</label>
                        {$countriesdropdown}
                    </div>

                </div>
            </div>

            <div id="subacct-container" class="well{if !$subaccount} hidden{/if}">

                <div class="form-group">
                    <label class="full control-label">{$LANG.subaccountpermissions}</label>
                    <div class="checkbox clearfix">
                        {foreach $allPermissions as $permission}
                            <div class="col-sm-6">
                                <label>
                                    <input type="checkbox" name="permissions[]" value="{$permission}"{if in_array($permission, $permissions)} checked{/if} />
                                    <span>
                                        {assign var='langPermission' value='subaccountperms'|cat:$permission}{$LANG.$langPermission}
                                    </span>
                                </label>
                            </div>
                        {/foreach}
                    </div>
                </div>
                <fieldset class="form-horizontal">
                    <div id="newPassword1" class="form-group has-feedback">
                        <label for="inputNewPassword1" class="col-sm-5 control-label">{$LANG.newpassword}</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="inputNewPassword1" name="password" />
                            <span class="form-control-feedback glyphicon"></span>
                            {include file="$template/includes/pwstrength.tpl" noDisable=true}
                        </div>
                    </div>
                    <div id="newPassword2" class="form-group has-feedback">
                        <label for="inputNewPassword2" class="col-sm-5 control-label">{$LANG.confirmnewpassword}</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" id="inputNewPassword2" name="password2" />
                            <span class="form-control-feedback glyphicon"></span>
                            <div id="inputNewPassword2Msg">
                            </div>
                        </div>
                    </div>
                </fieldset>

            </div>

            <div class="form-group">
                <label class="control-label">{$LANG.clientareacontactsemails}</label>
                <div class="controls checkbox">
                    {foreach $emailPreferences as $emailType => $value}
                        <input type="hidden" name="email_preferences[{$emailType}]" value="0">
                        <input type="checkbox" name="{$emailType}emails" id="{$emailType}emails" value="1"{if $value} checked="checked"{/if} />
                        {lang key="clientareacontactsemails"|cat:$emailType}
                        </label>{if !($emailType@last)}<br />{/if}
                    {/foreach}
                </div>
            </div>

            <div class="form-group text-center">
                <input class="btn btn-primary" type="submit" name="save" value="{$LANG.clientareasavechanges}" />
                <input class="btn btn-default" type="reset" value="{$LANG.cancel}" />
            </div>

        </form>
    </div>

{/if}

{if $currentAction == 'domaincontacts'}
    {literal}
    <script language="javascript">
    function usedefaultwhois(id) {
        jQuery("."+id.substr(0,id.length-1)+"customwhois").attr("disabled", true);
        jQuery("."+id.substr(0,id.length-1)+"defaultwhois").attr("disabled", false);
        jQuery('#'+id.substr(0,id.length-1)+'1').attr("checked", "checked");
    }
    function usecustomwhois(id) {
        jQuery("."+id.substr(0,id.length-1)+"customwhois").attr("disabled", false);
        jQuery("."+id.substr(0,id.length-1)+"defaultwhois").attr("disabled", true);
        jQuery('#'+id.substr(0,id.length-1)+'2').attr("checked", "checked");
    }
    $('#Primary_Sidebar-Domain_Details_Management-Domain_Contacts').addClass('active');
    </script>
    {/literal}

    <div class="col-md-12 pull-md-right" id="rightMenu">

        <div class="alert alert-block alert-info">
            <p>{$LANG.domainname}: <strong>{$domain}</strong></p>
        </div>

        {if $error}
            <div class="alert alert-danger">
                <p class="bold textcenter">{$error}</p>
            </div>
        {/if}

        <form method="post" action="{$smarty.server.PHP_SELF}?action=domaincontacts" class="form-horizontal">

        <input type="hidden" name="sub" value="save" />
        <input type="hidden" name="domainid" value="{$domainid}" />

        {foreach from=$contactdetails name=contactdetails key=contactdetail item=values}

            {assign var=getLang value="domain`$contactdetail`contact"}
            <h3><a name="{$contactdetail}"></a>
                {$LANG.$getLang}
            </h3>

            <fieldset class="onecol" id="{$contactdetail}defaultwhois">


            {foreach key=name item=value from=$values}
                {assign var="editRules" value=$value.editRules}
                {if $value.editRules == 2}
                   <label class="control-label" for="{$contactdetail}3">{$LANG.domaincontactinfo}</label>
                   <div class="controls">
                        {$value.contactData}
                   </div>
                {else}
                    {if $value.href != "UNAVAILABLE"}
                        <div class="control-group">
                            <label class="control-label" for="{$contactdetail}3">{$LANG.domaincontactinfoedit}</label>
                            <div class="controls">
                                <a href='.{$value.href}' class="{$contactdetail}customwhois btn btn-primary"/>
                                    {if $value.message == "edit"}{$LANG.clientareamodifydomaincontactinfo}{/if}
                                    {if $value.message == "generate"}{$LANG.clientareanavgeneratecontact}{/if}
                                </a>
                                {if $value.message == "generate"}
                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="bottom" title="{$LANG.clientareanavgeneratecontactexplanation}"><i class="fa fa-question"></i></button>
                                {/if}
                            </div>
                        </div>
                    {/if}
                {/if}
            {/foreach}

            </fieldset>
            <fieldset class="onecol" id="{$contactdetail}defaultwhois">
            {if $editRules == 1}
                <div class="control-group">
                    <label class="control-label" for="{$contactdetail}3">{$LANG.domaincontactchoose}</label>
                    <div class="controls">
                        <select class="{$contactdetail}defaultwhois form-control select-inline" name="sel[{$contactdetail}]" id="{$contactdetail}3" onclick="usedefaultwhois(id)">
                            <option value=0>{$values.contact.ticker}{$LANG.domaindefaultcontact}</option>
                            {$values.contact.options}
                        </select>
                    </div>
                </div>
            {/if}
            </fieldset>
        {/foreach}
            </br>
            <p class="textcenter"><input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" /></p>

        </form>

        <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
            <input type="hidden" name="id" value="{$domainid}" />
            <p><input type="submit" value="{$LANG.clientareabacklink}" class="btn" /></p>
        </form>
    </div>

{/if}

{if $currentAction == 'domainrecords'}
 
    {literal}
    <script language="javascript">
    $('#Primary_Sidebar-Domain_Details_Management-Domains_DNS_Records').addClass('active');
    </script>
    {/literal}

    {if $successful}
        {include file="$template/includes/alert.tpl" type="success" msg=$LANG.changessavedsuccessfully textcenter=true}
    {/if}

    {if $errormessage}
        {if $errormessage == 'zonenotfound'}
            {include file="$template/includes/alert.tpl" type="error" errorshtml="{$LANG.zone.notfound}"}
        {else}
            {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
        {/if}
    {/if}


    {include file="$template/includes/alert.tpl" type="info" msg="{$LANG.records.description}"}

    <h3>{$LANG.domainaddonsdnsmanagement}</h3>
    {if $records && $records > 0}
        <table class="table table-list dataTable no-footer dtr-inline">
            <thead>
                <tr>
                    <th>{$LANG.virtualname.name}</th>
                    <th>{$LANG.domaindnsrecordtype}</th>
                    <th>TTL</th>
                    <th>{$LANG.domaindnspriority}</th>
                    <th>{$LANG.virtualname.content}</th>
                    <th>{$LANG.actions}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$records name=records key=record item=values}
                <tr>
                    <td>
                        {if $values.name == ''}
                            -
                        {else}
                            {$values.name}
                        {/if}
                    </td>
                    <td>{$values.type}</td>
                    <td>{$values.ttl}</td>
                    <td>
                        {if $values.prio == ''}
                            -
                        {else}
                            {$values.prio}
                        {/if}
                    </td>
                    <td>{$values.content}</td>
                    <td>
                        {if $values.type != 'SOA' && $values.type != 'NS'}
                            <form class="form-horizontal" role="form" method="post" action="{$smarty.server.PHP_SELF}?action=domainrecords&domainid={$domainid}">
                                <input type="hidden" name="sub" value="delete" />
                                <input type="hidden" name="domainid" value="{$domainid}" />
                                <input type="hidden" name="recordid" value="{$values.id}" />
                                <p class="text-center">
                                    <input type="submit" value="{$LANG.virtualname.delete}" class="btn btn-sm btn-danger" />
                                </p>
                            </form>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <br><br>

        <form class="form-horizontal" role="form" method="post" action="{$smarty.server.PHP_SELF}?action=domainrecords&domainid={$domainid}">
            <input type="hidden" name="sub" value="create" />
            <input type="hidden" name="domainid" value="{$domainid}" />

            <h4>{$LANG.records.create}</h4>

            <div class="form-group">
                <label for="recordname" class="col-xs-4 control-label">{$LANG.virtualname.name}</label>
                <div class="col-xs-3">
                    <input type="text" class="form-control" id="recordname" name="recordname" />
                </div>
                <div class="col-xs-5">
                    . {$domain}
                </div>
            </div>

            <div class="form-group">
                <label for="recordtype" class="col-xs-4 control-label">{$LANG.domaindnsrecordtype}</label>
                <div class="col-xs-6 col-sm-5">
                    <select name="recordtype" id="recordtype" class="field form-control">
                        <option value="A">A</option>
                        <option value="AAAA">AAAA</option>
                        <option value="CNAME">CNAME</option>
                        <option value="MX">MX</option>
                        <option value="TXT">TXT</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="ttl" class="col-xs-4 control-label">TTL</label>
                <div class="col-xs-6 col-sm-5">
                    <select name="ttl" id="ttl" class="field form-control">
                        <option value="7200">7200</option>
                        <option value="60">Un minuto</option>
                        <option value="300">5 minutos</option>
                        <option value="600">10 minutos</option>
                        <option value="900">15 minutos</option>
                        <option value="1800">30 minutos</option>
                        <option value="3600">1 hora</option>
                        <option value="7200">2 horas</option>
                        <option value="14400">4 horas</option>
                        <option value="28800">8 horas</option>
                        <option value="43200">12 horas</option>
                        <option value="57600">16 horas</option>
                        <option value="72000">20 horas</option>
                        <option value="86400">Un día</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="prio" class="col-xs-4 control-label">{$LANG.domaindnspriority}</label>
                <div class="col-xs-6 col-sm-5">
                    <select name="prio" id="prio" class="field form-control">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-xs-4 control-label">{$LANG.virtualname.content}</label>
                <div class="col-xs-6 col-sm-5">
                    <input type="text" class="form-control" id="content" name="content" />
                </div>
            </div>

            <p class="text-center">
                <input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" />
            </p>

        </form>


    {else}
        {include file="$template/includes/alert.tpl" type="info" msg="{$LANG.records.notfound}"}
    {/if}

{/if}

{if $currentAction == 'domainlifecycle'}

    {literal}
    <script language="javascript">
    $('#Primary_Sidebar-Domain_Details_Management-Domains_Lifecycle').addClass('active');
    </script>
    {/literal}

    {if $errormessage}
        {include file="$template/includes/alert.tpl" type="error" errorshtml=$errormessage}
    {/if}


    <h3>{$LANG.domainlifecycle}</h3>

    {if $lifecycle}

        <link rel="stylesheet" type="text/css" href="modules/registrars/virtualname/themes/css/virtualname.css" />

        <ul class="virtualname_tl">
            {if $lifecycle.registration_date}
                <li>
                    <div class="virtualname_tl-badge success">{$lifecycle.registration_date|date_format:"%Y"}</div>
                    <div class="virtualname_tl-panel">
                        <div class="virtualname_tl-heading">
                            <h4 class="virtualname_tl-title">{$LANG.domainregistrationdate}</h4>
                        </div>
                        <div class="virtualname_tl-body">
                            <p>{$lifecycle.registration_date|date_format:"%d/%m/%Y"}</p>
                        </div>
                    </div>
                </li>
            {/if}
            {if $lifecycle.renew_dates}
                {foreach from=$lifecycle.renew_dates item=renew}
                    <li class="virtualname_tl-inverted">
                        <div class="virtualname_tl-badge info">{$renew.date|date_format:"%Y"}</div>
                        <div class="virtualname_tl-panel">
                            <div class="virtualname_tl-heading">
                                <h4 class="virtualname_tl-title">{$LANG.domainrenewdate}</h4>
                            </div>
                            <div class="virtualname_tl-body">
                                <p>{$renew.date|date_format:"%d/%m/%Y"}</p>
                            </div>
                        </div>
                    </li>
                {/foreach}
            {/if}
            {if $lifecycle.expiration_date}
                <li>
                    <div class="virtualname_tl-badge warning">{$lifecycle.expiration_date|date_format:"%Y"}</div>
                    <div class="virtualname_tl-panel">
                        <div class="virtualname_tl-heading">
                            <h4 class="virtualname_tl-title">{$LANG.domainexpirationdate}</h4>
                        </div>
                        <div class="virtualname_tl-body">
                            <p>{$lifecycle.expiration_date|date_format:"%d/%m/%Y"}</p>
                        </div>
                    </div>
                </li>
            {/if}
            {if $lifecycle.redemption_period_date}
                <li class="virtualname_tl-inverted">
                    <div class="virtualname_tl-badge primary">{$lifecycle.redemption_period_date|date_format:"%Y"}</div>
                    <div class="virtualname_tl-panel">
                        <div class="virtualname_tl-heading">
                            <h4 class="virtualname_tl-title">{$LANG.domainredemptionperiod}</h4>
                        </div>
                        <div class="virtualname_tl-body">
                            <p>{$lifecycle.redemption_period_date|date_format:"%d/%m/%Y"}</p>
                        </div>
                    </div>
                </li>
            {/if}
            {if $lifecycle.deletion_period_date}
                <li>
                    <div class="virtualname_tl-badge danger">{$lifecycle.deletion_period_date|date_format:"%Y"}</div>
                    <div class="virtualname_tl-panel">
                        <div class="virtualname_tl-heading">
                            <h4 class="virtualname_tl-title">{$LANG.domaindeletionperiod}</h4>
                        </div>
                        <div class="virtualname_tl-body">
                            <p>{$lifecycle.deletion_period_date|date_format:"%d/%m/%Y"}</p>
                        </div>
                    </div>
                </li>
            {/if}
            {if $lifecycle.release_date}
                <li class="virtualname_tl-inverted">
                    <div class="virtualname_tl-badge">{$lifecycle.release_date|date_format:"%Y"}</div>
                    <div class="virtualname_tl-panel">
                        <div class="virtualname_tl-heading">
                            <h4 class="virtualname_tl-title">{$LANG.domainreleasedate}</h4>
                        </div>
                        <div class="virtualname_tl-body">
                            <p>{$lifecycle.release_date|date_format:"%d/%m/%Y"}</p>
                        </div>
                    </div>
                </li>
            {/if}
        </ul>


    {else}
        {include file="$template/includes/alert.tpl" type="info" msg="{$LANG.virtualname.empty}"}
    {/if}

{/if}

<script type="text/javascript">
    $(".header-lined").prependTo("#rightMenu");
</script>
