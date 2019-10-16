<!--
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.8.X
// * @copyright Copyright (c) 2019, Virtualname
// * @version 1.1.19
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: Clients Custom Domain Contacts
// *************************************************************************-->

{if $currentAction == 'details'}

        <script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>
        {include file="$template/pageheader.tpl" title=$LANG.clientareanavdetails}
        {include file="$template/clientareadetailslinks.tpl"}

        {if $successful}
        <div class="alert alert-success">
            <p>{$LANG.changessavedsuccessfully}</p>
        </div>
        {/if}

        {if $errormessage}
        <div class="alert alert-error">
            <p class="bold">{$LANG.clientareaerrors}</p>
            <ul>
                {$errormessage}
            </ul>
        </div>
        {/if}

        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?action=details">

        <br />

        <fieldset class="control-group">
        <div class="control-group">

        <div class="control-group">
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

        <div class="col2half">
            <div class="control-group">
                <label class="control-label" for="firstname">{$LANG.clientareafirstname}</label>
                <div class="controls">
                    <input type="text" name="firstname" id="firstname" value="{$clientfirstname}"{if in_array('firstname',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="lastname">{$LANG.clientarealastname}</label>
                <div class="controls">
                    <input type="text" name="lastname" id="lastname" value="{$clientlastname}"{if in_array('lastname',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="companyname">{$LANG.clientareacompanyname}</label>
                <div class="controls">
                    <input type="text" name="companyname" id="companyname" value="{$clientcompanyname}"{if in_array('companyname',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="email">{$LANG.clientareaemail}</label>
                <div class="controls">
                    <input type="text" name="email" id="email" value="{$clientemail}"{if in_array('email',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="blank">&nbsp;</label>
                <div class="controls">
                    &nbsp;
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="paymentmethod">{$LANG.paymentmethod}</label>
                <div class="controls">
                    <select name="paymentmethod" id="paymentmethod">
                    <option value="none">{$LANG.paymentmethoddefault}</option>
                    {foreach from=$paymentmethods item=method}
                    <option value="{$method.sysname}"{if $method.sysname eq $defaultpaymentmethod} selected="selected"{/if}>{$method.name}</option>
                    {/foreach}
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="billingcontact">{$LANG.defaultbillingcontact}</label>
                <div class="controls">
                    <select name="billingcid" id="billingcontact" style='max-width: 100%;'>
                    <option value="0">{$LANG.usedefaultcontact}</option>
                    {foreach from=$contacts item=contact}
                    <option value="{$contact.id}"{if $contact.id eq $billingcid} selected="selected"{/if}>{$contact.name}</option>
                    {/foreach}
                    </select>
                </div>
            </div>

        </div>
        <div class="col2half">

            <div class="control-group">
                <label class="control-label" for="address1">{$LANG.clientareaaddress1}</label>
                <div class="controls">
                    <input type="text" name="address1" id="address1" value="{$clientaddress1}"{if in_array('address1',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="address2">{$LANG.clientareaaddress2}</label>
                <div class="controls">
                    <input type="text" name="address2" id="address2" value="{$clientaddress2}"{if in_array('address2',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="city">{$LANG.clientareacity}</label>
                <div class="controls">
                    <input type="text" name="city" id="city" value="{$clientcity}"{if in_array('city',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="state">{$LANG.clientareastate}</label>
                <div class="controls">
                    <input type="text" name="state" id="state" value="{$clientstate}"{if in_array('state',$uneditablefields)} readonly class="disabled"{/if} />
                    <input type="hidden" name="stateHidden" id="inputStateHidden" value="{$clientstate}" class="form-control"/>
                    <select name="state" id="stateselect"></select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="postcode">{$LANG.clientareapostcode}</label>
                <div class="controls">
                    <input type="text" name="postcode" id="postcode" value="{$clientpostcode}"{if in_array('postcode',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="country">{$LANG.clientareacountry}</label>
                <div class="controls">
                    {$clientcountriesdropdown}
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="phonenumber">{$LANG.clientareaphonenumber}</label>
                <div class="controls">
                    <input type="text" name="phonenumber" id="phonenumber" value="{$clientphonenumber}"{if in_array('phonenumber',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>


            <div class="control-group" {if $hideicnumber} style='display:none;' {/if}>
                <label class="control-label" for="clientidentificationnumber">{$LANG.clientIdentificationNumber}</label>
                <div class="controls">
                    <input type="text" name="clientidentificationnumber" id="clientidentificationnumber" value="{$clientidentificationnumber}"{if in_array('clientidentificationnumber',$uneditablefields)} readonly class="disabled"{/if} />
                </div>
            </div>

            <div class="control-group" {if $hideicnumber} style='display:none;' {/if}>
                <label class="control-label" for="legal_form">{$LANG.legal_form_contact}</label>
                <div class="controls">
                    {$clientlegalforms}
                </div>
            </div>

        </div>
        </div>

        {if $customfields}
        {foreach from=$customfields key=num item=customfield}
            <div class="control-group">
                <label class="control-label" for="customfield{$customfield.id}">{$customfield.name}</label>
                <div class="controls">
                    {$customfield.input} {$customfield.description}
                </div>
            </div>
        {/foreach}
        {/if}

        </fieldset>

        {if $showMarketingEmailOptIn}
            <div class="marketing-email-optin">
                <h4>{lang key='emailMarketing.joinOurMailingList'}</h4>
                <p>{$marketingEmailOptInMessage}</p>
                <input type="checkbox" name="marketingoptin" value="1"{if $marketingEmailOptIn} checked{/if} class="no-icheck toggle-switch-success" data-size="small" data-on-text="{lang key='yes'}" data-off-text="{lang key='no'}">
            </div>
        {/if}

        <div class="form-actions">
            <input type="hidden" name="customerid" id="customerid" value="{$clientsdetails.userid}" />
            <input class="btn btn-primary" type="submit" name="save" id="save" value="{$LANG.clientareasavechanges}" />
            {if $clientvirtualnamevalidation != 1}
                <input class="btn btn-success" type="submit" name="linked" id="linked" value="{$LANG.clientarealinkedcontact}" />
            {else}
                <input class="btn btn-danger" type="submit" name="unlinked" id="unlinked" value="{$LANG.clientareaunlinkedcontact}" />
            {/if}
            <input class="btn" type="reset" value="{$LANG.cancel}" />
        </div>

        </form>
{/if}

{if $currentAction == 'contacts'}
    {if $contactid}
        <script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>
        {include file="$template/pageheader.tpl" title=$LANG.clientareanavcontacts}
        {include file="$template/clientareadetailslinks.tpl"}
        {if $successful}
            <div class="alert alert-success">
                <p>{$LANG.changessavedsuccessfully}</p>
            </div>
        {/if}
        {if $errormessage}
            <div class="alert alert-error">
                <p class="bold">{$LANG.clientareaerrors}</p>
                    <ul>
                            {$errormessage}
                    </ul>
            </div>
        {/if}
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

        <form method="post" class="form-inline" action="{$smarty.server.PHP_SELF}?action=contacts">
            <div class="alert alert-block alert-info">
                <p>{$LANG.clientareachoosecontact}:&nbsp;
                    <select name="contactid" onchange="submit()">
                        {foreach item=contact from=$contacts}
                            <option value="{$contact.id}"{if $contact.id eq $contactid} selected="selected"{/if}>{$contact.name} - {$contact.email}</option>
                        {/foreach}
                        <option value="new">{$LANG.clientareanavaddcontact}</option>&nbsp;
                    </select>
                    <input class="btn" type="submit" value="{$LANG.go}" />
                </p>
            </div>
        </form>
        <br />
        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?action=contacts&id={$contactid}">
            <fieldset class="control-group" style="margin:0;">
                <div class="control-group">
                    <div class="control-group">
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

                    <div class="col2half">
                        <div class="control-group">
                            <label class="control-label" for="firstname">{$LANG.clientareafirstname}</label>
                            <div class="controls">
                                <input type="text" name="firstname" id="firstname" value="{$contactfirstname}" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="lastname">{$LANG.clientarealastname}</label>
                            <div class="controls">
                                <input type="text" name="lastname" id="lastname" value="{$contactlastname}" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="companyname">{$LANG.clientareacompanyname}</label>
                                <div class="controls">
                                    <input type="text" name="companyname" id="companyname" value="{$contactcompanyname}" />
                                </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="email">{$LANG.clientareaemail}</label>
                                <div class="controls">
                                    <input type="text" name="email" id="email" value="{$contactemail}" />
                                </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="billingcontact">{$LANG.subaccountactivate}</label>
                            <div class="controls">
                                <label class="checkbox">
                                    <input type="checkbox" name="subaccount" id="subaccount"{if $subaccount} checked{/if} /> {$LANG.subaccountactivatedesc}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col2half">
                        <div class="control-group">
                            <label class="control-label" for="address1">{$LANG.clientareaaddress1}</label>
                            <div class="controls">
                               <input type="text" name="address1" id="address1" value="{$contactaddress1}" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="address2">{$LANG.clientareaaddress2}</label>
                            <div class="controls">
                                <input type="text" name="address2" id="address2" value="{$contactaddress2}" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="city">{$LANG.clientareacity}</label>
                            <div class="controls">
                                <input type="text" name="city" id="city" value="{$contactcity}" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="state">{$LANG.clientareastate}</label>
                            <div class="controls">
                                <input type="text" name="state" id="state" value="{$contactstate}" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="postcode">{$LANG.clientareapostcode}</label>
                            <div class="controls">
                                <input type="text" name="postcode" id="postcode" value="{$contactpostcode}" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="country">{$LANG.clientareacountry}</label>
                            <div class="controls">
                                {$countriesdropdown}
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="phonenumber">{$LANG.clientareaphonenumber}</label>
                            <div class="controls">
                                <input type="text" name="phonenumber" id="phonenumber" value="{$contactphonenumber}" />
                            </div>
                        </div>
                        <div class="control-group" {if $hideicnumber} style='display:none;' {/if}>
                            <label class="control-label" for="identificationnumber">{$LANG.clientIdentificationNumber}</label>
                                <div class="controls">
                                    <input type="text" name="identificationnumber" id="identificationnumber" value="{$contactidentificationnumber}" />
                                    <input type="text" name="tax_id" id="tax_id" value="{$contactidentificationnumber}" />
                                </div>
                        </div>
                        <div class="control-group" {if $hideicnumber} style='display:none;' {/if}>
                            <label class="control-label" for="legal_form">{$LANG.legal_form_contact}</label>
                            <div class="controls">
                                {$legalforms}
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <div id="subaccountfields" class="well{if !$subaccount} hide{/if}">
                <fieldset>
                    <div class="control-group">
                        <label class="control-label" for="password">{$LANG.clientareapassword}</label>
                        <div class="controls">
                            <input type="password" name="password" id="password" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password2">{$LANG.clientareaconfirmpassword}</label>
                        <div class="controls">
                           <input type="password" name="password2" id="password2" />
                        </div>
                    </div>
                    <!--<div class="control-group">
                        <label class="control-label" for="passstrength">{$LANG.pwstrength}</label>
                        <div class="controls">
                        </div>
                    </div>-->
                    <div class="control-group">
                        <label class="full control-label">{$LANG.subaccountpermissions}</label>
                        <div class="controls">
                            <ul class="inputs-list">
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" value="profile"{if in_array('profile',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsprofile}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permcontacts" value="contacts"{if in_array('contacts',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermscontacts}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permproducts" value="products"{if in_array('products',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsproducts}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permmanageproducts" value="manageproducts"{if in_array('manageproducts',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsmanageproducts}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permdomains" value="domains"{if in_array('domains',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsdomains}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permmanagedomains" value="managedomains"{if in_array('managedomains',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsmanagedomains}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="perminvoices" value="invoices"{if in_array('invoices',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsinvoices}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permtickets" value="tickets"{if in_array('tickets',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermstickets}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permaffiliates" value="affiliates"{if in_array('affiliates',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsaffiliates}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permemails" value="emails"{if in_array('emails',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsemails}</span>
                                    </label>
                                </li>
                                <li class="col2half">
                                    <label class="checkbox">
                                        <input type="checkbox" name="permissions[]" id="permorders" value="orders"{if in_array('orders',$permissions)} checked{/if} />
                                        <span>{$LANG.subaccountpermsorders}</span>
                                    </label>
                                </li>
                            </ul>
                         </div>
                    </div>
                </fieldset>
            </div>
            <fieldset>
                <div class="control-group">
                    <label class="control-label">{$LANG.clientareacontactsemails}</label>
                    <div class="controls">
                        <ul class="inputs-list">
                            <li>
                                <label class="full control-label">
                                    <input type="checkbox" name="generalemails" id="generalemails" value="1"{if $generalemails} checked{/if} />
                                    <span>{$LANG.clientareacontactsemailsgeneral}</span>
                                </label>
                            </li>
                            <li>
                                <label class="full control-label">
                                    <input type="checkbox" name="productemails" id="productemails" value="1"{if $productemails} checked{/if} />
                                    <span>{$LANG.clientareacontactsemailsproduct}</span>
                                </label>
                            </li>
                            <li>
                                <label class="full control-label">
                                    <input type="checkbox" name="domainemails" id="domainemails" value="1"{if $domainemails} checked{/if} />
                                    <span>{$LANG.clientareacontactsemailsdomain}</span>
                                </label>
                            </li>
                            <li>
                                <label class="full control-label">
                                    <input type="checkbox" name="invoiceemails" id="invoiceemails" value="1"{if $invoiceemails} checked{/if} />
                                    <span>{$LANG.clientareacontactsemailsinvoice}</span>
                                </label>
                            </li>
                            <li>
                                <label class="full control-label">
                                    <input type="checkbox" name="supportemails" id="supportemails" value="1"{if $supportemails} checked{/if} />
                                    <span>{$LANG.clientareacontactsemailssupport}</span>
                                </label>
                            </li>
                         </ul>
                    </div>
                </div>
            </fieldset>
            <div class="form-actions">
                <input class="btn btn-primary" type="submit" name="submit" value="{$LANG.clientareasavechanges}" />
                {if $contactvnamevalidation != 1}
                    <input class="btn btn-success" type="submit" name="linked" id="linked" value="{$LANG.clientarealinkedcontact}" />
                {/if}
                <input class="btn" type="reset" value="{$LANG.cancel}" />
                <input class="btn btn-danger" type="button" value="{$LANG.clientareadeletecontact}" onclick="deleteContactVNAME()" />
                <input type="hidden" name="contactid" id="contactid" value="{$contactid}" />
            </div>
        </form>
    {else}
        {include file="$template/clientadetailsdata.tpl"}
    {/if}
{/if}

{if $currentAction == 'addcontact'}
        <script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>

        {if $successful}
        <div class="alert alert-success">
            <p>{$LANG.changessavedsuccessfully}</p>
        </div>
        {/if}

        {if $errormessage}
        <div class="alert alert-error">
            <p class="bold">{$LANG.clientareaerrors}</p>
            <ul>
                {$errormessage}
            </ul>
        </div>
        {/if}

        <script type="text/javascript">
        {literal}
        jQuery(document).ready(function(){
            jQuery("#subaccount").click(function () {
                if (jQuery("#subaccount:checked").val()!=null) {
                    jQuery("#subaccountfields").slideDown();
                } else {
                    jQuery("#subaccountfields").slideUp();
                }
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

        <form method="post" class="form-inline" action="{$smarty.server.PHP_SELF}?action=contacts">
        <div class="alert alert-block alert-success">
        <p><strong>{$LANG.clientareachoosecontact}:</strong>&nbsp; <select name="contactid" onchange="submit()">
            {foreach item=contact from=$contacts}
                <option value="{$contact.id}">{$contact.name} - {$contact.email}</option>
            {/foreach}
            <option value="new" selected="selected">{$LANG.clientareanavaddcontact}</option>
            </select>&nbsp; <input class="btn" type="submit" value="{$LANG.go}" /></p>
        </div>
        </form>

        <br />

        <form class="form-horizontal" method="post" action="{$smarty.server.PHP_SELF}?action=addcontact">
        <input type="hidden" name="submit" value="true" />

        <fieldset class="control-group" style="margin:0;">

        <div class="control-group">
        <div class="col2half">

            <div class="control-group">
                <label class="control-label" for="firstname">{$LANG.clientareafirstname}</label>
                <div class="controls">
                    <input type="text" name="firstname" id="firstname" value="{$contactfirstname}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="lastname">{$LANG.clientarealastname}</label>
                <div class="controls">
                    <input type="text" name="lastname" id="lastname" value="{$contactlastname}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="companyname">{$LANG.clientareacompanyname}</label>
                <div class="controls">
                    <input type="text" name="companyname" id="companyname" value="{$contactcompanyname}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="email">{$LANG.clientareaemail}</label>
                <div class="controls">
                    <input type="text" name="email" id="email" value="{$contactemail}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="billingcontact">{$LANG.subaccountactivate}</label>
                <div class="controls">
                    <label class="checkbox">
                    <input type="checkbox" name="subaccount" id="subaccount"{if $subaccount} checked{/if} /> {$LANG.subaccountactivatedesc}
                    </label>
                </div>
            </div>

        </div>
        <div class="col2half">

            <div class="control-group">
                <label class="control-label" for="address1">{$LANG.clientareaaddress1}</label>
                <div class="controls">
                    <input type="text" name="address1" id="address1" value="{$contactaddress1}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="address2">{$LANG.clientareaaddress2}</label>
                <div class="controls">
                    <input type="text" name="address2" id="address2" value="{$contactaddress2}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="city">{$LANG.clientareacity}</label>
                <div class="controls">
                    <input type="text" name="city" id="city" value="{$contactcity}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="state">{$LANG.clientareastate}</label>
                <div class="controls">
                    <input type="text" name="state" id="state" value="{$contactstate}" />
                    <select name="state" id="stateselect"></select>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="postcode">{$LANG.clientareapostcode}</label>
                <div class="controls">
                    <input type="text" name="postcode" id="postcode" value="{$contactpostcode}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="country">{$LANG.clientareacountry}</label>
                <div class="controls">
                    {$countriesdropdown}
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="phonenumber">{$LANG.clientareaphonenumber}</label>
                <div class="controls">
                    <input type="text" name="phonenumber" id="phonenumber" value="{$contactphonenumber}" />
                </div>
            </div>

            <div class="control-group" {if $hideicnumber} style='display:none;' {/if}>
                <label class="control-label" for="identificationnumber">{$LANG.clientIdentificationNumber}</label>
                    <div class="controls">
                        <input type="text" name="identificationnumber" id="identificationnumber" value="{$contactidentificationnumber}" />
                        <input type="text" name="tax_id" id="tax_id" value="{$contactidentificationnumber}" />
                    </div>
            </div>

            <div class="control-group" style='display:none;'>
                <label class="control-label" for="country">{$LANG.legal_form_contact}</label>
                <div class="controls">
                    {$legalforms}
                </div>
            </div>

        </div>
        </div>

        </fieldset>

        <div id="subaccountfields" class="well{if !$subaccount} hide{/if}">

        <fieldset>

            <div class="control-group">
                <label class="control-label" for="password">{$LANG.clientareapassword}</label>
                <div class="controls">
                    <input type="password" name="password" id="password" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="password2">{$LANG.clientareaconfirmpassword}</label>
                <div class="controls">
                    <input type="password" name="password2" id="password2" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="passstrength">{$LANG.pwstrength}</label>
                <div class="controls">
                    {include file="$template/pwstrength.tpl"}
                </div>
            </div>

            <div class="control-group">
                <label class="full control-label">{$LANG.subaccountpermissions}</label>
                <div class="controls">
                    <ul class="inputs-list">
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" value="profile"{if in_array('profile',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsprofile}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permcontacts" value="contacts"{if in_array('contacts',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermscontacts}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permproducts" value="products"{if in_array('products',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsproducts}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permmanageproducts" value="manageproducts"{if in_array('manageproducts',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsmanageproducts}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permdomains" value="domains"{if in_array('domains',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsdomains}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permmanagedomains" value="managedomains"{if in_array('managedomains',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsmanagedomains}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="perminvoices" value="invoices"{if in_array('invoices',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsinvoices}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permtickets" value="tickets"{if in_array('tickets',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermstickets}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permaffiliates" value="affiliates"{if in_array('affiliates',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsaffiliates}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permemails" value="emails"{if in_array('emails',$permissions)} checked{/if} />
                                <span>{$LANG.subaccountpermsemails}</span>
                            </label>
                        </li>
                        <li class="col2half">
                            <label class="checkbox">
                                <input type="checkbox" name="permissions[]" id="permorders" value="orders"{if in_array('orders',$permissions)} checked{/if} />
                                {$LANG.subaccountpermsorders}
                            </label>
                        </li>
                    </ul>
                </div>
            </div>

        </fieldset>

        </div>

        <fieldset>

            <div class="control-group">
                <label class="control-label">{$LANG.clientareacontactsemails}</label>
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" name="generalemails" id="generalemails" value="1"{if $generalemails} checked{/if} />
                        {$LANG.clientareacontactsemailsgeneral}
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="productemails" id="productemails" value="1"{if $productemails} checked{/if} />
                        {$LANG.clientareacontactsemailsproduct}
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="domainemails" id="domainemails" value="1"{if $domainemails} checked{/if} />
                        {$LANG.clientareacontactsemailsdomain}
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="invoiceemails" id="invoiceemails" value="1"{if $invoiceemails} checked{/if} />
                        {$LANG.clientareacontactsemailsinvoice}
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="supportemails" id="supportemails" value="1"{if $supportemails} checked{/if} />
                        {$LANG.clientareacontactsemailssupport}
                    </label>
                </div>
            </div>

        </fieldset>

        <div class="form-actions">
            <input class="btn btn-primary" type="submit" name="submit" value="{$LANG.clientareasavechanges}" />
            <input class="btn" type="reset" value="{$LANG.cancel}" />
        </div>

        </form>
{/if}

{if $currentAction == 'domaincontacts'}

    {include file="$template/pageheader.tpl" title=$LANG.managing|cat:" "|cat:$domain}

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
    </script>
    {/literal}

    <div id="tabs">
        <ul class="nav nav-tabs">
            <li id="tab1nav"><a href="clientarea.php?action=domaindetails&id={$domainid}">{$LANG.information}</a></li>
            <li id="tab2nav"><a href="#tab2">{$LANG.domainsautorenew}</a></li>
            <li id="tab3nav"><a href="#tab3">{$LANG.domainnameservers}</a></li>
            <li id="tab4nav"><a href="#tab4">{$LANG.domainregistrarlock}</a></li>
            <li class="dropdown active">
                <a data-toggle="dropdown" href="#" class="dropdown-toggle">{$LANG.domainmanagementtools}<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="./clientareadata.php?action=domaincontacts&amp;domainid={$domainid}" class="active">{$LANG.domaincontactinfo}</a></li>
                    <li><a href="./clientarea.php?action=domainregisterns&amp;domainid={$domainid}">{$LANG.domainregisterns}</a></li>
                    <li class="divider"></li>
                    {if $getepp}
                        <li><a href="./clientarea.php?action=domaingetepp&amp;domainid={$domainid}">{$LANG.domaingeteppcode}</a></li>
                    {/if}
                </ul>
            </li>
        </ul>
    </div>


    <div class="alert alert-block alert-info">
        <p>{$LANG.domainname}: <strong>{$domain}</strong></p>
    </div>

    {if $error}
        <div class="alert alert-error">
            <p class="bold textcenter">{$error}</p>
        </div>
    {/if}

    {if $successmessage == "linked"}
        <div class="alert alert-success">
            <p class="bold textcenter">{$LANG.changessavedsuccessfully}</p>
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
                            <a href='.{$value.href}' class="{$contactdetail}customwhois btn btn-primary" />
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
                    <select class="{$contactdetail}defaultwhois form-control select-inline" name="sel[{$contactdetail}]" id="{$contactdetail}3" onclick="usedefaultwhois(id)" style='max-width: 80%;'>
                        <option value=0>{$values.contact.ticker}{$LANG.domaindefaultcontact}</option>
                        {$values.contact.options}
                    </select>
                </div>
            </div>
        {/if}
        </fieldset>
    {/foreach}

    <p class="textcenter"><input type="submit" value="{$LANG.clientareasavechanges}" class="btn btn-primary" /></p>

    </form>

    <form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
        <input type="hidden" name="id" value="{$domainid}" />
        <p>
            <input type="submit" value="{$LANG.clientareabacklink}" class="btn" />
        </p>
    </form>
{/if}
