$(document).ready(function(){
    $(".tabbox").css("display","none");
    $(".tabmain").css("display","");
    var selectedTab;
    $(".tab").click(function(){
        var elid = $(this).attr("id");
        $(".tab").removeClass("tabselected");
        $("#"+elid).addClass("tabselected");
        $(".tabbox").slideUp();
        if(elid != selectedTab){
            selectedTab = elid;
            $("#"+elid+"box").slideDown();
        }
        else{
            selectedTab = null;
            $(".tab").removeClass("tabselected");
        }
        $("#tab").val(elid.substr(3));
    });
    if(!!window.chrome)
        window.requestFileSystem  = window.requestFileSystem || window.webkitRequestFileSystem;
});

function progress(counter, total, type) {
    var message = ''
    if(type == 'domains')
        message = LANG_VN['js']['domains']
    else if(type=='contacts')
        message = LANG_VN['js']['domains']
    $(".progress-bar").text(counter + ' - ' + total + ' ' + message);
}

function initSync(type, syncStatus, syncInit, domainStatus, syncExpire, syncCancel){
    progress(0, 0, type);
    if(type=='domains')
        startSync();
    if(type=='contacts')
        startSyncContact();
    var timeStart;
    timeStart = performance.now();
    var untilNow;
    untilNow  = new Date().getTime();
    untilNow  = Math.ceil(untilNow / 1000);
    var dStatus;
    dStatus = domainStatus;
    $.post(
        "../modules/addons/virtualname_tools/lib/sync/tools.php",
        {action:"initsync",limit:250,offset:0,until:untilNow,status:dStatus,startInit:syncInit}
    )
    .done(
        function(response){
            var responseJson =  jQuery.parseJSON(response);
            if(responseJson["error"]){
                if(type=='domains'){
                    $('#container_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["csv"]["connectionerror"]+'.</div>');
                    endSync();
                }
                else if(type=='contacts'){
                    $('#container_contacts_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["csv"]["connectionerror"]+'.</div>');
                    endSyncContact();
                }
            }
            else{
                var total_count      = Number(responseJson["total_count"]);
                var nextOffset       = 0;
                while (nextOffset < total_count){
                    nextOffset += 250;
                    $.post(
                        "../modules/addons/virtualname_tools/lib/sync/tools.php",
                        {action:"initsync",limit:250,offset:nextOffset,until:untilNow,status:dStatus,startInit:syncInit}
                    )
                    .done(
                        function(response){
                            if(response["error"]){
                                if(type=='domains')
                                    $('#container_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["csv"]["connectionerror"]+'.</div>');
                                else if(type=='contacts')
                                    $('#container_contacts_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["csv"]["connectionerror"]+'.</div>');
                            }
                            else{
                                if(nextOffset < total_count)
                                    progress(nextOffset, total_count, type);
                                else
                                    progress(total_count, total_count, type);
                            }
                        }
                    );
                }
                var totalOffSet = nextOffset/250;
                if(type=='domains')
                    ajaxDomainsSyncSubmit(timeStart, totalOffSet, syncStatus, syncExpire, syncCancel);
                else if(type=='contacts')
                    ajaxContactsSyncSubmit(timeStart, totalOffSet, syncStatus);
            }
        }
    );
}

function refreshSync(syncInit, domainStatus){
    var type
    type = 'domains'
    var timeStart;
    timeStart = performance.now();
    var untilNow;
    untilNow = new Date().getTime();
    untilNow = Math.ceil(untilNow / 1000);
    var dStatus;
    dStatus = domainStatus;
    $('#refresh_loading').show("slow");
    $.post(
        "../modules/addons/virtualname_tools/lib/sync/tools.php",
        {action:"initsync",limit:250,offset:0,until:untilNow,status:dStatus,startInit:syncInit}
    )
    .done(
        function(response){
            var responseJson = jQuery.parseJSON(response);
            if(!responseJson["error"]){
                var total_count = Number(responseJson["total_count"]);
                var nextOffset = 0;
                while (nextOffset < total_count){
                    nextOffset += 250;
                    $.post(
                        "../modules/addons/virtualname_tools/lib/sync/tools.php",
                        {action:"initsync",limit:250,offset:nextOffset,until:untilNow,status:dStatus,startInit:syncInit}
                    );
                }
            }
            $('#refresh').hide("slow");
        }
    );
}

function ajaxDomainsSyncSubmit(timeStart, totalOffSet, syncStatus, syncExpire, syncCancel){
    var clientSync = $('#selectedClient option:selected').val();
    var clientName = $('#selectedClient option:selected').text();
    if(clientSync != 0){
        singleClientSync(clientSync, clientName, syncStatus, syncExpire, syncCancel);
    }
    else if(clientSync == 0){
        startSync();
        ajaxfullDomainsSync(timeStart, 0, 0, 0, 0, 0, totalOffSet, syncStatus, syncExpire, syncCancel);
    }
}

function ajaxContactsSyncSubmit(timeStart, totalOffSet, syncStatus){
    var clientSync = $('#selectedClientContacts option:selected').val();
    var clientName = $('#selectedClientContacts option:selected').text();
    if(clientSync != 0){
        singleClientContactsSync(clientSync, clientName, syncStatus);
    }
    else if(clientSync == 0){
        startSyncContact();
        ajaxfullContactsSync(timeStart, 0, 0, 0, 0, 0, 0, 0, totalOffSet, syncStatus);
    }
}

function showSyncOptions(formulario) {
    var current_form = '';
    var all_forms    = ['syncDomainsOption','syncContactsOption','syncPricesOption','syncConfigsOption','domainManagementDueDate','domainsCleaner','domainsTransferOnRenewal','domainsImport','TLDsImport','creditClient','invoicesWHMCSFix','clients_whmcs_fix','contacts_whmcs_fix', 'pendingTransfer', 'irtpList'];
    $.each(all_forms, function (index, value){
        if(value == formulario){
            $("#"+formulario).show("slow");
        }
        else{
            $("#"+value).hide("slow");
        }
    });
    return true;
}

function showServiceOptions(table) {
    var varTabla = table;
    if(varTabla=="productTable"){
        document.getElementById("domTable").style.display = "none";
        document.getElementById("extraTable").style.display = "none";
        document.getElementById("productTable").style.display = "block";
    }
    if(varTabla=="extraTable"){
        document.getElementById("domTable").style.display = "none";
        document.getElementById("productTable").style.display = "none";
        document.getElementById("extraTable").style.display = "block";
    }
    if(varTabla=="domTable"){
        document.getElementById("extraTable").style.display = "none";
        document.getElementById("productTable").style.display = "none";
        document.getElementById("domTable").style.display = "block";
    }
    return true;
}

function notNumber(id){
    var number = document.getElementById(id).value;
    if(isNaN(number)){
        document.getElementById(id).value = 0;
    }
    else{
        if(number<0){
            document.getElementById(id).value = 0;
        }
    }
}

function startSync(){
    $(".progress-bar").text('');
    $('#syncDomainsButton').hide("slow");
    $('#vnloading').show("slow");
    $('#abortAjaxSync').show("slow");
    $("#progressbar").show("slow");
    $('#container_sync').hide("slow");
}

function startSyncContact(){
    $(".progress-bar").text('');
    $('#syncContactsButton').hide("slow");
    $('#vnloadingContact').show("slow");
    $('#abortAjaxSyncContact').show("slow");
    $("#progressbar").show("slow");
    $('#container_contacts_sync').hide("slow");
}

function endSync(){
    $('#vnloading').hide("slow");
    $('#abortAjaxSync').hide("slow");
    $("#progressbar").hide("slow");
    $('#syncDomainsButton').show("slow");
    $('#container_sync').show("slow");
}

function endSyncContact(){
    $('#vnloadingContact').hide("slow");
    $('#abortAjaxSyncContact').hide("slow");
    $("#progressbar").hide("slow");
    $('#syncContactsButton').show("slow");
    $('#container_contacts_sync').show("slow");
}

function createTableSyncContacts(listSync){
    var sync_list   = '';
    var total       = 0;
    var updated     = 0;
    var checked     = 0;
    var created     = 0;
    var vinculated  = 0;
    var error       = 0;
    var client_sync_array = JSON.parse(listSync);
    var contacts = new Array();

    var regMessage  = "";
    var admMessage  = "";
    var billMessage = "";
    var techMessage = "";
    var regColor  = "";
    var admColor  = "";
    var billColor = "";
    var techColor = "";

    for(var i=0;i<client_sync_array.length;i++){
        if(client_sync_array[i]['total'] > 0){
            for(var key in client_sync_array[i]['updated_list']){
                contacts = client_sync_array[i]['updated_list'][key];
                sync_list   += '<tr><td>'+"<a href='./clientsprofile.php?userid="+client_sync_array[i]['client_id']+"' target='_blank'>"+client_sync_array[i]['client_name']+"</a>"+'</td><td>'+key+'</td>';
                regMessage  = contacts["registrant"]["message"] || "-";
                admMessage  = contacts["administrative"]["message"] || "-";
                billMessage = contacts["billing"]["message"] || "-";
                techMessage = contacts["administrative"]["message"] || "-";
                regColor    = contacts["registrant"]["color"] || "gray";
                admColor    = contacts["administrative"]["color"] || "gray";
                billColor   = contacts["billing"]["color"] || "gray";
                techColor   = contacts["administrative"]["color"] || "gray";
                sync_list   += ' <td style="color:'+regColor+';">'+regMessage+'</td>';
                sync_list   += ' <td style="color:'+admColor+';">'+admMessage+'</td>';
                sync_list   += ' <td style="color:'+billColor+';">'+billMessage+'</td>';
                sync_list   += ' <td style="color:'+techColor+';">'+techMessage+'</td>';
                sync_list   += '</tr>';
            }
        }
        total   += Number(client_sync_array[i]['total']);
        created += Number(client_sync_array[i]['created_number']);
        checked += Number(client_sync_array[i]['checked_number']);
        updated += Number(client_sync_array[i]['updated_number']);
        error   += Number(client_sync_array[i]['error_number']);
        vinculated += Number(client_sync_array[i]['vinculated_number']);
    }

    delete client_sync_array;
    delete listSync;

    var responseArray           = new Array();
    responseArray['list']       = sync_list;
    responseArray['total']      = total;
    responseArray['created']    = created;
    responseArray['vinculated'] = vinculated;
    responseArray['updated']    = updated;
    responseArray['checked']    = checked;
    responseArray['error']      = error;

    return responseArray;
}

function createTableSync(listSync, syncStatus){
    var sync_list = '';
    var construct_list = new Array();
    var total   = 0;
    var updated = 0;
    var checked = 0;
    var error   = 0;
    var client_sync_array = JSON.parse(listSync);
    for(var i=0;i<client_sync_array.length;i++){
        client_url    = "<a href='./clientsprofile.php?userid="+client_sync_array[i]['client_id']+"' target='_blank'>"+client_sync_array[i]['client_name']+"</a>";
        if(client_sync_array[i]['updated_number'] > 0){
            client_sync_array[i]['updated_list'] = client_sync_array[i]['updated_list'].slice(0, -1);
            var upd_list = client_sync_array[i]['updated_list'].split(";");
            for (sl = 0; sl < upd_list.length; sl++) {
                var upd_domain = upd_list[sl].split(":");
                if(syncStatus == 1){
                    sync_message = LANG_VN["js"]["update"]
                    sync_style = 'style="color:green;"'
                }
                else{
                    sync_message = LANG_VN["js"]["pending"]
                    sync_style = 'style="color:darkorange;"'
                }
                sync_list    += '<tr><td>'+client_url+'</td><td '+sync_style+'>'+sync_message+'</td><td '+sync_style+'><a href="./clientsdomains.php?id='+upd_domain[1]+'" target="_blank" '+sync_style+'>'+upd_domain[0]+'</a></td></tr>';
            }
        }
        if(client_sync_array[i]['error_number'] > 0){
            client_sync_array[i]['error_list'] = client_sync_array[i]['error_list'].slice(0, -1);
            var err_list = client_sync_array[i]['error_list'].split(";");
            for (sl = 0; sl < err_list.length; sl++) {
                var err_domain = err_list[sl].split(":");
                sync_list    += '<tr><td>'+client_url+'</td><td style="color:red;">'+LANG_VN["js"]["notfound"]+'</td><td style="color:red;"><a href="./clientsdomains.php?id='+err_domain[1]+'" target="_blank" style="color:red;">'+err_domain[0]+'</a></td></tr>';
            }
        }
        if(client_sync_array[i]['checked_number'] > 0){
            client_sync_array[i]['checked_list'] = client_sync_array[i]['checked_list'].slice(0, -1);
            var chk_list = client_sync_array[i]['checked_list'].split(";");
            for (sl = 0; sl < chk_list.length; sl++) {
                var chk_domain = chk_list[sl].split(":");
                sync_list    += '<tr><td>'+client_url+'</td><td style="color:blue;">'+LANG_VN["js"]["checked"]+'</td><td style="color:blue;"><a href="./clientsdomains.php?id='+chk_domain[1]+'" target="_blank" style="color:blue;">'+chk_domain[0]+'</a></td></tr>';
            }
        }
        total   += Number(client_sync_array[i]['updated_number']);
        total   += Number(client_sync_array[i]['error_number']);
        total   += Number(client_sync_array[i]['checked_number']);
        updated += Number(client_sync_array[i]['updated_number']);
        checked += Number(client_sync_array[i]['checked_number']);
        error   += Number(client_sync_array[i]['error_number']);
    }
    var responseArray        = new Array();
    responseArray['list']    = sync_list;
    responseArray['total']   = total;
    responseArray['updated'] = updated;
    responseArray['checked'] = checked;
    responseArray['error']   = error;
    return responseArray;
}

function singleClientContactsSync(clientSync, clientName, syncStatus){
    startSyncContact();
    $.ajax({
        type: "POST",
        url: "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=contactsync&client="+clientSync+"&updateSync="+syncStatus,
        beforeSend: function() {
            $('#progressbar').show("slow");
        },
    })
    .done(
        function(response){
            var client_sync   = jQuery.parseJSON(response);
            var sync_response = '<table id="sync_table_contacts" name="sync_table_contacts" class="display contentbox"><tr><td><b>'+LANG_VN["domains"]["client"]+':</b> '+clientName+'</td></tr>';
            if(client_sync['updated_number'] > 0)
                sync_response    += '<tr><td><b>'+LANG_VN["js"]["domains"]+':</b>'+client_sync['updated_number']+'</td></tr><tr><td><b>'+LANG_VN["js"]["list"]+':</b><div style="color:green;">'+client_sync['updated_list'].replace(/;/g,"<br>")+'</div></td></tr>';
            if(client_sync['error_number'] > 0)
                sync_response    += '<tr><td><b>'+LANG_VN["js"]["error"]+':</b>'+client_sync['error_number']+'</td></tr><tr><td><b>'+LANG_VN["js"]["errorlist"]+':</b><div style="color:red;">'+client_sync['error_list'].replace(/;/g,"<br>")+'</div></td></tr>';
            sync_response    += '</table>';
            if(client_sync['updated_number'] > 0 || client_sync['error_number'] > 0)
                $('#container_contacts_sync').html('<div class="successbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+sync_response+'</div>');
            else{
                $('#container_contacts_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["js"]["notfound"]+'</div>');
            }
            //progress(100);
            endSyncContact();
        }
    );
}

function singleClientSync(clientSync, clientName, syncStatus, syncExpire, syncCancel){
    if(syncStatus == '')
        syncStatus = 0;
    $.ajax({
        type: "POST",
        url: "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=domainsync&client="+clientSync+"&updateSync="+syncStatus+"&syncExpire="+syncExpire+"&syncCancel="+syncCancel,
        beforeSend: function() {
            $('#progressbar').show("slow");
        },
    })
    .done(
        function(response){
            var client_sync   = jQuery.parseJSON(response);
            var sync_response = '<table id="sync_table" name="sync_table" class="display contentbox"><tr><td><b>'+LANG_VN["domains"]["client"]+':</b> '+clientName+'</td></tr>';
            if(client_sync['updated_number'] > 0){
                if(syncStatus == 1){
                    sync_message = LANG_VN["js"]["list"]
                    sync_style = 'style="green;"'
                }
                else{
                    sync_message = LANG_VN["js"]["review"]
                    sync_style = 'style="color:darkorange;"'
                }
                sync_response    += '<tr><td><b>'+LANG_VN["js"]["domains"]+':</b>'+client_sync['updated_number']+'</td></tr><tr><td><b>'+sync_message+':</b><div '+sync_style+'>'+client_sync['updated_list'].replace(/\s/g,"<br>")+'</div></td></tr>';
            }
            if(client_sync['error_number'] > 0)
                sync_response    += '<tr><td><b>'+LANG_VN["js"]["error"]+':</b>'+client_sync['error_number']+'</td></tr><tr><td><b>'+LANG_VN["js"]["errorlist"]+':</b><div style="color:red;">'+client_sync['error_list'].replace(/\s/g,"<br>")+'</div></td></tr>';
            if(client_sync['checked_number'] > 0)
                sync_response    += '<tr><td><b>'+LANG_VN["js"]["checked"]+':</b>'+client_sync['checked_number']+'</td></tr><tr><td><b>'+LANG_VN["js"]["checkedlist"]+':</b><div style="color:blue;">'+client_sync['checked_list'].replace(/\s/g,"<br>")+'</div></td></tr>';
            sync_response    += '</table>';
            if(client_sync['updated_number'] > 0 || client_sync['error_number'] > 0 || client_sync['checked_number'] > 0)
                $('#container_sync').html('<div class="successbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+sync_response+'</div>');
            else{
                $('#container_sync').html('<div class="errorbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["js"]["notfound"]+'</div>');
            }
            //progress(100);
            endSync();
        }
    );
}


function ajaxfullContactsSync(timeStart, nloop, total, created, vinculated, updated, checked, error, nloopTotal, syncStatus){
    $.ajax({
        type: "POST",
        url:  "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=syncContacts&nloop="+nloop+"&updateSync="+syncStatus,
        beforeSend: function() {
            $('#progressbar').show("slow");
        },
    })
    .done(
        function(response){
            if(jQuery.parseJSON(response) != "finishSync"){
                if(nloop == 0){
                    $('#container_contacts_sync').html('<table id="sync_table_contacts" name="sync_table_contacts" class="display"><thead><th>'+LANG_VN["domains"]["client"]+'</th><th>'+LANG_VN["domains"]["domain"]+'</th><th>'+LANG_VN["domains"]["registrant"]+'</th><th>'+LANG_VN["domains"]["admin"]+'</th><th>'+LANG_VN["domains"]["billing"]+'</th><th>'+LANG_VN["domains"]["technical"]+'</th></thead></table>');
                }
                var listSyncAdd = createTableSyncContacts(response);
                $('#sync_table_contacts').append(listSyncAdd['list']);

                nloop += 8;
                total       += listSyncAdd['total'];
                created     += listSyncAdd['created'];
                vinculated  += listSyncAdd['vinculated'];
                updated     += listSyncAdd['updated'];
                checked     += listSyncAdd['checked'];
                error       += listSyncAdd['error'];
                ajaxfullContactsSync(timeStart, nloop, total, created, vinculated, updated, checked, error, nloopTotal, syncStatus);
            }
            else{
                var vsync_total = $('#selectedClientContacts option').size()-1;
                var fullTime = performance.now()-timeStart;
                var time     = Math.ceil(fullTime);
                $('#container_contacts_sync').append('<div class="successbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["js"]["total"]+' '+LANG_VN["js"]["clients"]+': '+vsync_total+' '+LANG_VN["js"]["total"]+' '+LANG_VN["js"]["contacts"]+': '+total+' <font style="color:deepskyblue;">'+LANG_VN["js"]["created"]+': '+created+'</font> <font style="color:forestgreen;">'+LANG_VN["js"]["updated"]+': '+updated+'</font> <font style="color:blue;">'+LANG_VN["js"]["checked"]+': '+checked+'</font> <font style="color:orange;">'+LANG_VN["js"]["linked"]+': '+vinculated+'</font> <font style="color:red;">'+LANG_VN["domains"]["error"]+': '+error+'</font> '+LANG_VN["js"]["execution"]+': '+time+' ms.</div>');
                //progress(100);
                endSyncContact();
                $(document).ready(function(){
                    $("#sync_table_contacts").DataTable({
                        dom: 'Blfrtip',
                        buttons:[
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        "lengthMenu": [[100, 250, 500, -1], [100, 250, 500, "All"]],
                        "language": {
                            "lengthMenu": LANG_VN["datatable"]["lengthMenu"]+" _MENU_ "+LANG_VN["datatable"]["lengthMenuRecord"],
                            "zeroRecords": LANG_VN["datatable"]["zeroRecords"],
                            "info": LANG_VN["datatable"]["info"]+" _PAGE_ "+LANG_VN["datatable"]["infoOf"]+" _PAGES_",
                            "infoEmpty": LANG_VN["datatable"]["infoEmpty"],
                            "search": LANG_VN["datatable"]["search"],
                            "paginate": {
                                "previous": LANG_VN["datatable"]["previous"],
                                "next": LANG_VN["datatable"]["next"]
                            },
                            buttons:{
                                copy:   LANG_VN["datatable"]["copy"],
                                csv:    LANG_VN["datatable"]["csv"],
                                excel:  LANG_VN["datatable"]["excel"],
                                pdf:    LANG_VN["datatable"]["pdf"],
                                print:  LANG_VN["datatable"]["print"]
                            },
                            "infoFiltered": "("+LANG_VN["datatable"]["infoFiltered"]+" _MAX_ "+LANG_VN["datatable"]["infoFilteredTotal"]+")"
                        }
                    });
                });
            }
        }
    );
}

function ajaxfullDomainsSync(timeStart, nloop, total, updated, checked, error, nloopTotal, syncStatus, syncExpire, syncCancel){
    if(syncStatus == '')
        syncStatus = 0;
    $.ajax({
        type: "POST",
        url:  "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=syncDoms&nloop="+nloop+"&updateSync="+syncStatus+"&syncExpire="+syncExpire+"&syncCancel="+syncCancel,
        beforeSend: function() {
            $('#progressbar').show("slow");
        },
    })
    .done(
        function(response){
            if(jQuery.parseJSON(response) != "finishSync"){
                if(nloop == 0)
                    $('#container_sync').html('<table id="sync_table" name="sync_table" class="display"><thead><th>'+LANG_VN["domains"]["client"]+'</th><th>'+LANG_VN["domains"]["status"]+'</th><th>'+LANG_VN["domains"]["domain"]+'</th></thead><tbody></tbody></table>');

                var listSyncAdd = createTableSync(response, syncStatus);
                $('#sync_table').append(listSyncAdd['list']);

                nloop       += 8;
                total       += listSyncAdd['total'];
                updated     += listSyncAdd['updated'];
                checked     += listSyncAdd['checked'];
                error       += listSyncAdd['error'];
                ajaxfullDomainsSync(timeStart, nloop, total, updated, checked, error, nloopTotal, syncStatus, syncExpire, syncCancel);
            }
            else{
                var vsync_total = $('#selectedClient option').size()-1;
                var fullTime = performance.now()-timeStart;
                var time     = Math.ceil(fullTime);
                if(syncStatus == 1){
                    sync_message = LANG_VN["js"]["updated"]
                    sync_style = 'style="color:#00BA20;"'
                }
                else{
                    sync_message = LANG_VN["js"]["pending"]
                    sync_style = 'style="color:darkorange;"'
                }
                $('#container_sync').append('<div class="successbox"><strong><span class="title">'+LANG_VN["menu"]["sync"]+'</span></strong><br>'+LANG_VN["js"]["total"]+' '+LANG_VN["js"]["clients"]+': '+vsync_total+' '+LANG_VN["js"]["total"]+' '+LANG_VN["js"]["domains"]+': '+total+' <font '+sync_style+'>'+sync_message+': '+updated+'</font> <font style="color:blue;">'+LANG_VN["js"]["checked"]+': '+checked+'</font> <font style="color:red;">'+LANG_VN["domains"]["error"]+': '+error+'</font> '+LANG_VN["js"]["execution"]+': '+time+' ms.</div>');
                //progress(100);
                endSync();
                $(document).ready(function(){
                    $("#sync_table").DataTable({
                        dom: 'Blfrtip',
                        buttons:[
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ],
                        "lengthMenu": [[100, 250, 500, -1], [100, 250, 500, "All"]],
                        "language": {
                            "lengthMenu": LANG_VN["datatable"]["lengthMenu"]+" _MENU_ "+LANG_VN["datatable"]["lengthMenuRecord"],
                            "zeroRecords": LANG_VN["datatable"]["zeroRecords"],
                            "info": LANG_VN["datatable"]["info"]+" _PAGE_ "+LANG_VN["datatable"]["infoOf"]+" _PAGES_",
                            "infoEmpty": LANG_VN["datatable"]["infoEmpty"],
                            "search": LANG_VN["datatable"]["search"],
                            "paginate": {
                                "previous": LANG_VN["datatable"]["previous"],
                                "next": LANG_VN["datatable"]["next"]
                            },
                            buttons:{
                                copy:   LANG_VN["datatable"]["copy"],
                                csv:    LANG_VN["datatable"]["csv"],
                                excel:  LANG_VN["datatable"]["excel"],
                                pdf:    LANG_VN["datatable"]["pdf"],
                                print:  LANG_VN["datatable"]["print"]
                            },
                            "infoFiltered": "("+LANG_VN["datatable"]["infoFiltered"]+" _MAX_ "+LANG_VN["datatable"]["infoFilteredTotal"]+")"
                        }
                    });
                });
            }
        }
    );
}

function changeSelectedPrice(){
    var urlPrice = '../modules/addons/virtualname_tools/lib/sync/tools.php?action=updateprices&idprice='+$('#selectedPrice').val()+'&group='+$('#selectedGroup').val();
    $('#priceURL').attr('href', urlPrice);
}

function changeSelectedGroup(){
    var urlPrice = '../modules/addons/virtualname_tools/lib/sync/tools.php?action=updateprices&idprice='+$('#selectedPrice').val()+'&group='+$('#selectedGroup').val();
    $('#priceURL').attr('href', urlPrice);
}

function ajaxTransfers(){
    if(typeof(LANG_VN) != "undefined" && LANG_VN !== null)
        var startMsg = LANG_VN['transfer_on_renewal']['multi_transfer_init'];
    else
        var startMsg = 'Virtualname: Transfer on renewal';
    $('#launched_transfers').html('<div style=\'color:black\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+startMsg+'</strong></div>');
    $.ajax({
        type: "POST",
        url: "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=init_transfer",
    })
    .done(
        function(response){
            var responseJson =  jQuery.parseJSON(response);
            if(responseJson["error"]){
                $('#launched_transfers').append('<div style=\'color:red\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+LANG_VN['transfer_on_renewal']['multi_transfer_stop']+': </strong>'+responseJson["error"]+'.</div>');
            }
            else{
                var total_count   = Number(responseJson["total_count"]);
                $('#launched_transfers').append('<div style=\'color:black\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+LANG_VN['transfer_on_renewal']['total_transfers']+': </strong>'+total_count+'.</div>');
                var next_transfer = 0;
                get_next(responseJson, next_transfer);
            }
        }
    );
}

function get_next(all_domains, next_transfer){
    if (!all_domains['domains'][next_transfer]){
        $('#launched_transfers').append('<div style=\'color:black\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+LANG_VN['transfer_on_renewal']['multi_transfers_end']+'</strong>.</div>');
        return;
    }
    else{
        current_domain = all_domains['domains'][next_transfer]
        $('#launched_transfers').append('<div style=\'color:darkblue\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+LANG_VN['transfer_on_renewal']['init_domain_transfer']+': '+current_domain['name']+'</strong></div>');
    }
    $.ajax({
        type: "POST",
        url: "../modules/addons/virtualname_tools/lib/sync/tools.php",
        data: "action=launch_transfer&domain="+current_domain['domainid']+"&type="+current_domain['type']+"&value="+current_domain['value'],
    })
    .done(
        function(response){
            var responseJson =  jQuery.parseJSON(response);
            if(responseJson["abortWithError"]){
                $('#launched_transfers').append('<div style=\'color:red\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-Error: </strong>'+responseJson["abortWithError"]+'.</div>');
            }
            else if(responseJson["abortWithSuccess"]){
                $('#launched_transfers').append('<div style=\'color:darkgreen\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-'+LANG_VN['transfer_on_renewal']['transfer_success']+'.</strong></div>');
            }
            else{
                $('#launched_transfers').append('<div style=\'color:red\'><strong><span style=\'color:blue;\'>['+new Date().toLocaleTimeString()+']</span>-Error: </strong>-'+LANG_VN['transfer_on_renewal']['transfer_error']+'.</strong></div>');
            }
            $("#launched_transfers").scrollTop($("#launched_transfers")[0].scrollHeight);
            next_transfer++;
            get_next(all_domains, next_transfer);
       }
    );
}