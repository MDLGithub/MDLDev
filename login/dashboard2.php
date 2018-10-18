<?php
ob_start();

require_once('config.php');
require_once('settings.php');
require_once('header.php');

if (!login_check($db)) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    logout();
    Leave(SITE_URL);
}

require_once ('navbar.php');
require_once ('functions_event.php');


$roles = array('Admin', 'Sales Rep', 'Sales Manager');

$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];
if (!in_array($role, $roles)) {
    Leave(SITE_URL . "/no-permission.php");
}

$salesRepDetails = $db->row("SELECT * FROM tblsalesrep WHERE Guid_user=:userid", array('userid' => $userID));

// Account table
$clause = " ORDER BY Guid_account";
$accountdt = $db->selectAll('tblaccount', $clause);

$thisMessage = "";
$error = array();

$roleID = $roleInfo['Guid_role'];

$default_account = "";
/*
$accessRole = getAccessRoleByKey('home');
$roleIDs = unserialize($accessRole['role_ids']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');*/

if (isset($_POST['search']) && (strlen($_POST['from_date']) || strlen($_POST['to_date']))) {
    verify_input($error);
}
?>
<link href="assets/eventschedule/kendoUI/styles/kendo.common.min.css" rel="stylesheet">
<link href="assets/eventschedule/kendoUI/styles/kendo.rtl.min.css" rel="stylesheet">
<link href="assets/eventschedule/kendoUI/styles/kendo.default.min.css" rel="stylesheet">
<link href="assets/eventschedule/kendoUI/styles/kendo.default.mobile.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/eventschedule/css/fullcalendar.css" />
<link rel="stylesheet" href="assets/css/calendar.css" />
<link rel="stylesheet" href="assets/eventschedule/css/bootstrap-datetimepicker.min.css">
<script src="assets/eventschedule/js/jquery.min.js"></script>
<script src="assets/eventschedule/js/jquery-ui.min.js"></script>
<script src="assets/eventschedule/kendoUI/js/jszip.min.js"></script>
<script src="assets/eventschedule/kendoUI/js/kendo.all.min.js"></script>
<script src="assets/eventschedule/js/myweekview.js"></script>
<script src="assets/eventschedule/js/moment.min.js"></script>
<script src="assets/eventschedule/js/fullcalendar.min.js"></script>
<script src="assets/eventschedule/js/bootstrap-datetimepicker.min.js"></script>

<style>
    .col-md-1 { width: 10.333333% !important;}
    .container { max-width: 100% !important;}
    #datetimepicker1{ position: relative; width: 172px; }
    #datetimepicker1 input{ width: 100%; }
    #datetimepicker1 img{ position: absolute; top: 8px; right: 5px;}

    #datetimepicker2{ position: relative; /* width: 172px; */}
    #datetimepicker2 input{ width: 100%; }
    #datetimepicker2 img{ position: absolute; top: 8px; right: 5px;}
    textarea.form-control{height: auto !important;}
    .fc-event-container {padding: 5px 0 !important;}

    .fc-event {
        box-shadow:  0 0 .25em !important;
        border-radius:  .625em !important;
        background-color: #fff !important;
        color: #000 !important;
    }
    .fc-axis{display: none !important;}

    /* The Modal (background) */
    .schedulemodal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 10; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .schedulemodal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .evtcontent{ padding: 0px 5px; white-space: pre-wrap !important;}
    .evttitle{font-weight: bold; color: #3a87ad; white-space: nowrap !important; overflow: hidden;text-overflow: ellipsis;}

    .fc-month-view .evttitle, .fc-basicWeek-view .evttitle{width:90%;}
    .fc-basic-view .fc-comments{width: 90%;}
    .fc-comments{white-space: nowrap !important; overflow: hidden;text-overflow: ellipsis;}

    .rightCircleicon1{ position: absolute; width: 20px; height: 20px; right: 0px; top: -1px; background-image: url("assets/eventschedule/images/icon_brca_day.png"); background-repeat: no-repeat;background-size: 20px 20px; pointer-events: visible;}
    .rightCircleicon2{ position: absolute; width: 20px; height: 20px; right: 0px; top: -1px; background-image: url("assets/eventschedule/images/icon_health_fair.png"); background-repeat: no-repeat;background-size: 20px 20px;}
    
    
    select#sidebar_select { border: 1px solid #ccc; border-radius: 20px; width: 100%; padding: 5px 8px;   margin-bottom: 8px;}
    .modalaccounttype.hide { display: none; }
    
</style>
<script>
    
    Date.prototype.getUnixTime = function() { return this.getTime()/1000|0 };
    if(!Date.now) Date.now = function() { return new Date(); }
    Date.time = function() { return Date.now().getUnixTime(); }

    $(document).ready(function () {
        createChart();
        $(".f2").width('95%');
        $("input[name='eventtype']").click(function () {
            var evtType = $(this).val();
            if (evtType == 2) {
                $(".f2").width('100%');
                $("div.accounttype").hide();
                $("div.healthcare").show();
            } else {
                $(".f2").width('95%');
                $("div.accounttype").show();
                $("div.healthcare").hide();
            }
        });

        $("input[name='modaleventtype']").click(function () {
            var modalevtType = $(this).val();
            if (modalevtType == 2) {
                $("div.modalaccounttype").hide();
                $("div.modalhealthcare").show();
            } else {
                $("div.modalaccounttype").show();
                $("div.modalhealthcare").hide();
            }
        });
        var cursource = 'eventload.php';
        $('.salesrep_list li a').click(function () {
            var salesrep = $(this).attr('data-value');
            var salesrepName = $(this).text();

            var salesrepName = $(this).text();
                salesrepName = salesrepName.split(" ");
            var salesrepNameArray = new Array();
            for(var i =0; i < salesrepName.length; i++){
                salesrepNameArray.push(salesrepName[i]);
                if(i == 0){
                    salesrepNameArray.push('<i class="fas fa-angle-down info_block_arrow" style="float:right;"></i>');
                }
                if(i != salesrepName.length-1){
                    salesrepNameArray.push("<br>");
                }
            }
            salesrepName = salesrepNameArray.toString().replace(/\,/g," ");

            //salesrepName = salesrepName.split(" ").join("<br>");
            var reqdashboard = 1;
            var allcursource = 'eventload.php';
            if (salesrep != 0) {
                cursource = 'eventload.php?salerepId=' + salesrep + '&reqdashboard=' + reqdashboard;
            }
            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            if (salesrep == 0) {
                $('#calendar').fullCalendar('addEventSource', allcursource);
            } else {
                $('#calendar').fullCalendar('addEventSource', cursource);
            }
            $('#calendar').fullCalendar('refetchEvents');
            $('.info_block_arrow').removeClass('info_block_arrow_show');
            $(".salesrep_dropdown").addClass("dropdown_hide");
            if(salesrep != 0){
                $(".info_block h1").removeClass('hide').html(salesrepName)
            }else{
                $(".info_block h1").removeClass('hide').html('All<i class="fas fa-angle-down info_block_arrow" style="float:right;"></i><br>Genetic<br>Consultants');
            }
        });

        // when summary button is clicked
        $('#summary').on('click touchstart', function () {
            var summarycursource = 'summaryeventload.php';

            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('addEventSource', summarycursource);
            $('#calendar').fullCalendar('refetchEvents');
            $("#summary").addClass("details_button").removeClass("summary_button");
            $("#detail").addClass("summary_button").removeClass("details_button");
        });

        // when detail button is clicked
        $('#detail').on('click touchstart', function () {
            var detailcursource = 'eventload.php';

            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('addEventSource', detailcursource);
            $('#calendar').fullCalendar('refetchEvents');
            $("#detail").addClass("details_button").removeClass("summary_button");
            $("#summary").addClass("summary_button").removeClass("details_button");
        });
        var state_count1 = 0, count1 = 0;
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
            },
            views: {
                week: {
                    titleFormat: '[Week of ] MMMM D, YYYY',
                    titleRangeSeparator: ' to ',
                }
            },
            defaultView: 'basicWeek',
            handleWindowResize: true,
            contentHeight: 400,            
            //defaultView: 'custom',
            
            eventSources: cursource,
            selectable: true,
            selectHelper: true,
            editable: false,
            viewRender: function(view, element) {
                var currentDate = $('#calendar').fullCalendar('getDate');
                var beginOfWeek = currentDate.startOf('week');
                $("#calendarmonth").html($.fullCalendar.formatDate(beginOfWeek,"MMMM DD"));
                $("#calendaryear").html($.fullCalendar.formatDate(beginOfWeek,"YYYY"));
            },
            dayRender: function (date, cell) {
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1; //January is 0!

                var yyyy = today.getFullYear();
                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                var today2 = dd + '-' + mm + '-' + yyyy;
                //var moment = $('#calendar').fullCalendar('getDate');
                //alert(date.format('DD-MM-YYYY'))
                //if (date._d.getDate() === today.getDate()) {
                //if(moment.format("DD-MM-YYYY") === today ){
                if (date.format('DD-MM-YYYY') === today2) {
                   //cell.css("background", "linear-gradient(135deg, #cfe0e8 15%, #ffffff 25%, #ffffff 50%, #cfe0e8 15%, #cfe0e8 75%, #ffffff 75%, #ffffff 100%)");active_background.png
                    cell.css("background-image", "url('assets/images/active_background.png')");
                    cell.css("background-size", "100% 100%");
                }
            },
            eventClick: function (event)
            {
                var moment = $.datepicker.formatDate('yy-mm-dd', new Date());
                // Get the modal
                var modal = document.getElementById('myModal');
                //var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var modaldate = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                console.log("Moment: "+ moment +", Date: "+modaldate);
                if ( modaldate >= moment ) {
                    $('#updateEvent').find('input, #eventupdate, select').prop("disabled", false);
                    $('#eventupdate').prop("disabled", true);
                    $('input[name="modaleventtype"]').change(function(e){
                        $("#eventupdate").prop("disabled", false);
                    });
                } else {
                    $('#updateEvent').find('input, #eventupdate, select').prop("disabled", true);
                }
                var frmstart = $.fullCalendar.formatDate(event.start, "MM/DD/Y");
                $('#myModal').find('#modaleventstart').val(frmstart);
                $("#modalsalesrepopt").val(event.salesrepid);
                $("#modalaccountopt").val(event.accountid);
                //$("#modalsalesrepopt option:contains(" + event.salesrep + ")").attr('selected', 'selected');
                //$("#modalaccountopt option:contains(" + event.account + ")").attr('selected', 'selected');
                $("#modalcomment").val(event.comments);
                $("#modalfull_name_id").val(event.hltname);
                $("#modalstreet1_id").val(event.street1);
                $("#modalstreet2_id").val(event.street2);
                $("#modalcity_id").val(event.city);
                $("#modalstate_id").val(event.state);
                $("#modalzip_id").val(event.zip);
                $("#modalid").val(event.id);
                $("#modalsalerepid").val(event.salesrepid);
                $("#modalhealthcareid").val(event.healthcareid);

                //var eventID = event.id;
                popup_comment(event.id);
                
                if (event.title == 'BRCA Day') {
                    $('#brcaradio').prop("checked", true);
                    var modalevtType = $(this).val();
                    $("div.modalaccounttype").show();
                    $("div.modalhealthcare").hide();
                } else {
                    $('#healthradio').prop("checked", true);
                    $("div.modalaccounttype").hide();
                    $("div.modalhealthcare").show();
                }
                var today = new Date();
                var currentDate = today.getDate();
                var eventDate = $.fullCalendar.formatDate(event.start, "DD");
                var parsedNow =  new Date(today).getUnixTime();
                var parsedEventTime = new Date(event.start).getUnixTime();
                //if (parsedEventTime >= parsedNow) {
                    //modal.style.display = "block";
                    $("#myModal").delay( 100 ).fadeIn( 400 );
                //}
            },
            eventMouseover: function (calEvent, jsEvent) {
                //alert($(this).find('.silhouette span').html());
                if (!calEvent.evtCnt) {
                    var message = '';
                    if (calEvent.salesrep == null)
                        message += 'SalesRep is not assigned';
                    if (calEvent.salesrep == null && calEvent.account == null)
                        message += ' and ';
                    if (calEvent.account == null && calEvent.title == 'BRCA Day')
                        message += 'Account Number is missing';
                    var mouseOver = "Registered: " + $(this).find('.silhouette span').html() + "<br />";
                    mouseOver += "Completed: " + $(this).find('.checkmark span').html() + "<br />";
                    mouseOver += "Qualified: " + $(this).find('.dna span').html() + "<br />";
                    mouseOver += "Submitted: " + $(this).find('.flask span').html();
                    
                    if (mouseOver != '') {
                        bgclr = '#FF4500';
                        if(message == ""){
                            message = mouseOver;
                            bgclr = '#FFF';
                        }
                        var tooltip = '<div class="tooltipevent" style="padding:20px 20px;min-width:100px;min-height:100px;background:'+ bgclr + ';color:#000;position:absolute;z-index:10001;">' + message + '</div>';
                        $("body").append(tooltip);
                        //$(this).mouseover(function (e) {
                        $(".show-stats").mouseover(function (e) {
                            $(this).css('z-index', 10000);
                            $('.tooltipevent').fadeIn('500');
                            $('.tooltipevent').fadeTo('10', 1.9);
                        }).mousemove(function (e) {
                            $('.tooltipevent').css('top', e.pageY + 10);
                            $('.tooltipevent').css('left', e.pageX + 20);
                        });
                    }
                }

            },

            eventMouseout: function (calEvent, jsEvent) {
                $(this).css('z-index', 8);
                $('.tooltipevent').remove();
            },
            eventRender: function (event, element, view) {
                var today = new Date();
                var currentDate = today.getDate();
                var eventDate = $.fullCalendar.formatDate(event.start, "DD");
                var parsedNow =  new Date(today).getUnixTime();
                var parsedEventTime = new Date(event.start).getUnixTime();

                var time = $.fullCalendar.formatDate(event.start, "hh:mm a");
                var logo = "";
                var account = "";
                var name = "";
                var salesrep = "";
                if (event.logo)
                    logo = '<div class="fc-logo">' + event.logo + '</div>';
                if (event.account)
                    account = event.account + ' - ';

                if (event.salesrep)
                    salesrep = '<div class="fc-salesrep">' + event.salesrep + '</div>';
                var cmts = '';

                var view = $('#calendar').fullCalendar('getView');
                /*if (view.name == 'basicWeek' && event.comments)
                    cmts = '<div class="fc-comments">' + event.comments + '</div>'*/

                var icon = '';
                if (event.title == 'BRCA Day') {
                    icon = 'rightCircleicon1';
                    if (event.name)
                        name = event.name;
                } else {
                    icon = 'rightCircleicon2';
                    if (event.hltname)
                        name = event.hltname;
                }

                var borderColor = 'border: 2px solid #30a844 !important'; // default color
                if (event.color) {
                    borderColor = "border: 2px solid " + event.color + " !important";
                }
                /*
                 var modifiedName = sentenceCase(name.substring(0, 15));
                 if (name.length > 15)
                 modifiedName += "...";
                 */
                /*var modifiedName = sentenceCase(name);*/
                var modifiedName = sentenceCase((name == "") ? "Health Care Fair" : name);
                
                
                var content = '<div class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable" style="' + borderColor + '">' +
                        '<div class="fc-content evtcontent">' +
                        '<div class="' + icon + '"></div>' +
                        '<div class="fc-title evttitle"><a href="accounts.php?account_id='+event.accountid+'">' + modifiedName + '</a></div>' +
                        salesrep + cmts +
                        '</div>' +
                        '</div>';

                if (event.evtCnt) {
                    $("#summary").removeClass("details_button").addClass("summary_button");
                    $("#detail").removeClass("summary_button").addClass("details_button");
                    var content = '<div class="fc-content evtcontent summarybrca days-' + eventDate + '" style="padding: 0 20px; font-size: 15px; line-height: 16px;">';
                    content += '<div class="numberCircleContainer"><span class="numberCircle">' + event.evtCnt + '</span></div>';
                    content += '<div>Registered <span style="float:right">' + event.registeredCnt + '</span></div>';
                    content += '<div>Completed <span style="float:right">' + event.qualifiedCnt + '</span></div>';
                    content += '<div>Qualified <span style="float:right">' + event.completedCnt + '</span></div>';
                    content += '<div>Submitted <span style="float:right">0</span></div>';
                    content += '</div>';
                    return $(content);
                } else {
                    $("#detail").removeClass("details_button").addClass("summary_button");
                    $("#summary").removeClass("summary_button").addClass("details_button");
                    if (parsedEventTime < parsedNow) {
                        
                    var content = '<div class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable days-' + eventDate + '"  style="' + borderColor + '">' +
                                '<div class="fc-content evtcontent">'+                                
                                '<div class="fc-title evttitle"><a id="acc-'+event.accountid+'"  href="accounts.php?account_id='+event.accountid+'">' + modifiedName + '</a></div>' + salesrep + cmts;
                            content += "<div id='state2_count'>"
                            /*if(view.name != 'basicDay'){
                                content += '<div class="states-rep"></div>'
                            }*/
                            content += '</div><div class="' + icon + '"></div>';   
                            content += '</div> </div>';
                            state_count1 += 1;
                    

                        return $(content);
                    } else {
                        return $(content);
                    }
                }


            },
            eventAfterRender: function (event, element, view) {
                if (!event.evtCnt) {
                    if ((event.salesrep == null || event.account == null) && event.title == 'BRCA Day') {
                        //element.css('background-color', '#FF6347');
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                        element.css('border-color', '#FF6347');
                    } else if (event.salesrep == null && event.title != 'BRCA Day') {
                        //element.css('background-color', '#FF6347');
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                        element.css('border-color', '#FF6347');
                    } else {
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                    }
                }

                 var eventData = { action: 'getStates', account: event.account, regitered:28, qualified: 29, completed: 36,  submitted: 1, selectedDate: $.fullCalendar.formatDate(event.start, "Y-M-DD")};                
                    $.ajax({
                        url: "ajaxHandlerEvents.php",
                        type: "POST",
                        data: eventData,
                        success: function (res)
                        {
                            var res = JSON.parse(res);
                            var html = '<div class="show-stats"><span class="silhouette"><span>' + res.reg + '</span> <img src="assets/eventschedule/icons/silhouette_icon.png"></span> | <span class="checkmark"><span> '+ res.qua + '</span> <img src="assets/eventschedule/icons/checkmark_icon.png"></span> | <span class="dna"><span>'+ res.com + '</span> <img src="assets/eventschedule/icons/dna_icon.png"></span> | <span class="flask"><span>'+ res.sub +'</span> <img src="assets/eventschedule/icons/flask_icon.png"></span></div>';
                            if(view.name != 'basicDay'){
                                element[0].childNodes[0].childNodes[2].innerHTML = html;
                            }
                        }
                    });

            },
            eventAfterAllRender: function (event, element, view) {
                //$(".days-06:first").css("display", "block");
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");

                var inputparam = {
                    userid: <?php echo $userID; ?>,
                    startdate: start
                };
                $('#mebrcacnt').html('0');
                $('#meeventcnt').html('0');
                $('#meregcnt').html('0'); 
                $('#mequalcnt').html('0');
                $('#mecomcnt').html('0');

                $('#topbrcacnt').html('0');
                $('#topeventcnt').html('0');
                $('#topregcnt').html('0');
                $('#topqualcnt').html('0');
                $('#topcomcnt').html('0');
                
                $.ajax({
                    type : 'POST',
                    data : { userid:<?php echo $userID; ?>, startdate:start, action:'mebrcacount' },//inputparam,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.mebrcacount) $('#mebrcacnt').html(v.mebrcacount);
                        });
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : { userid:<?php echo $userID; ?>, startdate:start, action:'topbrcacount' },//inputparam,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.topbrcacount) $('#topbrcacnt').html(v.topbrcacount);
                            $('#mebrcacnt').removeClass('increase decrease gold_stat gold_stat_star');
                            
                            if($('#mebrcacnt').html() < $('#topbrcacnt').html()) $('#mebrcacnt').addClass('decrease');
                            if($('#mebrcacnt').html() > $('#topbrcacnt').html()) $('#mebrcacnt').addClass('increase');
                            if($('#mebrcacnt').html() === $('#topbrcacnt').html()){
                                $('#mebrcacnt').addClass('gold_stat gold_stat_star');
                                $('#topbrcacnt').addClass('gold_stat');
                            }    
                        });
                    },
                    error: function(){
                        $('#mebrcacnt').removeClass('increase decrease gold_stat gold_stat_star');
                        if($('#mebrcacnt').html() != 0 && $('#topbrcacnt').html() != 0){
                            if($('#mebrcacnt').html() < $('#topbrcacnt').html()) $('#mebrcacnt').addClass('decrease');
                            if($('#mebrcacnt').html() > $('#topbrcacnt').html()) $('#mebrcacnt').addClass('increase');
                            if($('#mebrcacnt').html() === $('#topbrcacnt').html()){
                                $('#mebrcacnt').addClass('gold_stat gold_stat_star');
                                $('#topbrcacnt').addClass('gold_stat');
                            }
                        }    
                    }
                });
                
                $.ajax({
                    type : 'POST',
                    data : { userid:<?php echo $userID; ?>, startdate:start, action:'meeventcount' },//inputparam,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.meeventcount) $('#meeventcnt').html(v.meeventcount);
                        });
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'topeventcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.topeventcount) $('#topeventcnt').html(v.topeventcount);
                            
                            $('#meeventcnt').removeClass('increase decrease gold_stat gold_stat_star');
                            if($('#meeventcnt').html() < $('#topeventcnt').html()) $('#meeventcnt').addClass('decrease');
                            if($('#meeventcnt').html() > $('#topeventcnt').html()) $('#meeventcnt').addClass('increase');
                            if($('#meeventcnt').html() === $('#topeventcnt').html()){
                                $('#meeventcnt').addClass('gold_stat gold_stat_star');
                                $('#topeventcnt').addClass('gold_stat');
                            }    
                        });
                    },
                    error: function(){
                        $('#meeventcnt').removeClass('increase decrease gold_stat gold_stat_star');
                        if($('#meeventcnt').html() != 0 && $('#topeventcnt').html() != 0){
                            if($('#meeventcnt').html() < $('#topeventcnt').html()) $('#meeventcnt').addClass('decrease');
                            if($('#meeventcnt').html() > $('#topeventcnt').html()) $('#meeventcnt').addClass('increase');
                            if($('#meeventcnt').html() === $('#topeventcnt').html()){
                                $('#meeventcnt').addClass('gold_stat gold_stat_star');
                                $('#topeventcnt').addClass('gold_stat');
                            }
                        }    
                    }
                });
                
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'meregisteredcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.meregisteredcount) $('#meregcnt').html(v.meregisteredcount);
                        });
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'mequalifiedcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.mequalifiedcount) $('#mequalcnt').html(v.mequalifiedcount);
                        });
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'mecompletedcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.mecompletedcount) $('#mecomcnt').html(v.mecompletedcount);
                        });
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'topregisteredcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.topregisteredcount) $('#topregcnt').html(v.topregisteredcount);
                            $('#meregcnt').removeClass('increase decrease gold_star');
                            if($('#meregcnt').html() < $('#topregcnt').html()) $('#meregcnt').addClass('decrease');
                            if($('#meregcnt').html() > $('#topregcnt').html()) $('#meregcnt').addClass('increase');
                            if($('#meregcnt').html() === $('#topregcnt').html()){
                                $('#meregcnt').addClass('gold_stat gold_stat_star');
                                $('#topregcnt').addClass('gold_stat');
                            }    
                        });
                    },
                    error: function(){ 
                        $('#meregcnt').removeClass('increase decrease gold_star');
                        if($('#meregcnt').html() != 0 && $('#topregcnt').html() != 0){
                            if($('#meregcnt').html() < $('#topregcnt').html()) $('#meregcnt').addClass('decrease');
                            if($('#meregcnt').html() > $('#topregcnt').html()) $('#meregcnt').addClass('increase');
                            if($('#meregcnt').html() === $('#topregcnt').html()){
                                $('#meregcnt').addClass('gold_stat gold_stat_star');
                                $('#topregcnt').addClass('gold_stat');
                            }    
                        }   
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'topqualifiedcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.topqualifiedcount) $('#topqualcnt').html(v.topqualifiedcount);
                            
                            $('#mequalcnt').removeClass('increase decrease gold_stat gold_stat_star');
                            if($('#mequalcnt').html() < $('#topqualcnt').html()) $('#mequalcnt').addClass('decrease');
                            if($('#mequalcnt').html() > $('#topqualcnt').html()) $('#mequalcnt').addClass('increase');
                            if($('#mequalcnt').html() === $('#topqualcnt').html()){
                                $('#mequalcnt').addClass('gold_stat gold_stat_star');
                                $('#topqualcnt').addClass('gold_stat');
                            }    
                        });
                    },
                    error: function(){ 
                        $('#mequalcnt').removeClass('increase decrease gold_star');
                        if($('#mequalcnt').html() != 0 && $('#topqualcnt').html() != 0){
                            if($('#mequalcnt').html() < $('#topqualcnt').html()) $('#mequalcnt').addClass('decrease');
                            if($('#mequalcnt').html() > $('#topqualcnt').html()) $('#mequalcnt').addClass('increase');
                            if($('#mequalcnt').html() === $('#topqualcnt').html()){
                                $('#mequalcnt').addClass('gold_stat gold_stat_star');
                                $('#topqualcnt').addClass('gold_stat');
                            }    
                        }   
                    }
                });
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'topcompletedcount.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(v.topcompletedcount) $('#topcomcnt').html(v.topcompletedcount);
                            
                            $('#mecomcnt').removeClass('increase decrease gold_star');
                            if($('#mecomcnt').html() < $('#topcomcnt').html()) $('#mecomcnt').addClass('decrease');
                            if($('#mecomcnt').html() > $('#topcomcnt').html()) $('#mecomcnt').addClass('increase');
                            if($('#mecomcnt').html() === $('#topcomcnt').html()){
                                $('#mecomcnt').addClass('gold_stat gold_stat_star');
                                $('#topcomcnt').addClass('gold_stat');
                            }    
                        });
                    },
                    error: function(){ 
                        $('#mecomcnt').removeClass('increase decrease gold_star');
                        if($('#mecomcnt').html() != 0 && $('#topcomcnt').html() != 0){
                            if($('#mecomcnt').html() < $('#topcomcnt').html()) $('#mecomcnt').addClass('decrease');
                            if($('#mecomcnt').html() > $('#topcomcnt').html()) $('#mecomcnt').addClass('increase');
                            if($('#mecomcnt').html() === $('#topcomcnt').html()){
                                $('#mecomcnt').addClass('gold_stat gold_stat_star');
                                $('#topcomcnt').addClass('gold_stat');
                            }    
                        }   
                    }
                });
                
                $.ajax({
                    type : 'POST',
                    data : inputparam,
                    dataType: 'json',
                    url : 'topgenetic.php',
                    success : function(returndata){
                        //console.log(JSON.stringify(returndata));
                    var chart = $("#chart").data("kendoChart");
                    var catr = returndata.categories;
                    //console.log(returndata.series)
                    chart.setOptions({
                        series: returndata.series,
                        categoryAxis: {
                            categories: catr},
                        valueAxis:{
                            max:returndata.yaxis
                        }
                    });
                    chart.refresh();
                    }
                });
                
                $.ajax({
                    type : 'POST',
                    data : { userid:<?php echo $userID; ?>, startdate:start, action:'piechart' },//inputparam,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(returndata){
                        console.log(returndata)
                        //console.log(JSON.stringify(returndata))
                    var chart = $("#piechart").data("kendoChart");
                    chart.setOptions({
                        series: [returndata],
                    });
                    chart.refresh();
                    }
                });
                
                
                
                
            },
        });

        // Whenever the user clicks on the "save" button
        var clickEventType=((document.ontouchstart!==null)?'click':'touchstart');
        $('#eventsave').bind(clickEventType, function () {
            var errorMsg = "";
            if ($("#salesrepopt").val() == "") {
                errorMsg = "Please select Genetic Consultant"
            }
            if ($("input[name='eventtype']:checked").val() == 1 && $('#accountopt').val() == 0) {
                if (errorMsg)
                    errorMsg += "\n";
                errorMsg += "Please select Account";
            }
            if (errorMsg) {
                alert(errorMsg);
                return false;
            }

            var title = $("input[name='eventtype']:checked").parent('label').text();
            if ($('#eventstart').val() && ($('#salerepid').val() || $('#accountopt').val() != 0)) {
                var start = dateFormat($('#eventstart').val(), "yyyy-mm-dd");
                var end = dateFormat($('#eventstart').val(), "yyyy-mm-dd");
                var accountId = $('#accountopt').val();
                var salesrepId = $('#salerepid').val() ? $('#salerepid').val() : 0;
                var comments = $('#comment').val();
                var full_name = $('#full_name_id').val() ? $('#full_name_id').val() : '';
                var street1 = $('#street1_id').val() ? $('#street1_id').val() : '';
                var street2 = $('#street2_id').val() ? $('#street2_id').val() : '';
                var city = $('#city_id').val() ? $('#city_id').val() : '';
                var state = $('#state_id').val() ? $('#state_id').val() : '';
                var zip = $('#zip_id').val() ? $('#zip_id').val() : '';

                var eventData = {
                    title: title,
                    start: start,
                    end: end,
                    salesrepId: salesrepId,
                    accountId: accountId,
                    comments: comments,
                    full_name: full_name,
                    street1: street1,
                    street2: street2,
                    city: city,
                    state: state,
                    zip: zip
                };
                $.ajax({
                    url: "eventinsert.php",
                    type: "POST",
                    data: eventData,
                    success: function ()
                    {
                        //$('#calendar').fullCalendar('renderEvent', eventData, true); // stick? = true
                        $('#calendar').fullCalendar('refetchEvents');
                        //alert("Added Successfully");
                    }
                })

            } else {
                return false;
            }
        });

         $("#modalcomment, #modalhealthcareComment, #modalfull_name_id, #modalstreet1_id, #modalstreet2_id, #modalcity_id, #modalstate_id, #modalzip_id").bind("keyup change", function(e) {
            $(this).addClass('updated');
            if($(this).val() != '')
                $('button#eventupdate').prop('disabled', false);
            else
                $('button#eventupdate').prop('disabled', true);
        })

        // Whenever the user clicks on the "update" button
        $('#eventupdate').bind(clickEventType, function () {

            var current_time = get_date();
            var commentid = "";
            if($(this).hasClass("edited")){
                $(this).removeClass("edited")
                commentid = $('#eventupdate').attr('data-commentid');
            }
            var errorMsg = "";
            if ($("#modalsalesrepopt").val() == "0") {
                errorMsg = "Please select Genetic Consultant"
            }
            if ($("input[name='modaleventtype']:checked").val() == 1 && $('#modalaccountopt').val() == 0) {
                if (errorMsg)
                    errorMsg += "\n";
                errorMsg += "Please select Account";
            }
            if (errorMsg) {
                alert(errorMsg);
                return false;
            }
            var title = $("input[name='modaleventtype']:checked").parent('label').text();
            if ($('#modaleventstart').val() && ($('#modalsalerepid').val() || $('#modalaccountopt').val() != 0)) {
                /*
                var start = dateFormat($('#modaleventstart').val(), "yyyy-mm-dd");
                var end = dateFormat($('#modaleventstart').val(), "yyyy-mm-dd");
                
                var start = $('#modaleventstart').val();
                var end = $('#modaleventstart').val();
            
                */
                    
                var start = moment($('#modaleventstart').val()).format("YYYY-MM-DD");
                var end = moment($('#modaleventstart').val()).format("YYYY-MM-DD");
                var accountId = $('#modalaccountopt').val();
                var salesrepId = $('#modalsalerepid').val() ? $('#modalsalerepid').val() : 0;
                var action ="" , comments = "";
                var radioValue = $("input[name='modaleventtype']:checked").val();

                if(radioValue == 2 ){
                    comments = $("#modalhealthcareComment").val();
                    action = 'healthEventupdate'
                }else{
                    comments = $("#modalcomment").val();
                    action = 'eventupdate'
                }
                
                //var action = ($('#modalcomment').val()) ? 'eventupdate' : 'healthEventupdate';
                var full_name = $('#modalfull_name_id').val() ? $('#modalfull_name_id').val() : '';
                var street1 = $('#modalstreet1_id').val() ? $('#modalstreet1_id').val() : '';
                var street2 = $('#modalstreet2_id').val() ? $('#modalstreet2_id').val() : '';
                var city = $('#modalcity_id').val() ? $('#modalcity_id').val() : '';
                var state = $('#modalstate_id').val() ? $('#modalstate_id').val() : '';
                var zip = $('#modalzip_id').val() ? $('#modalzip_id').val() : '';
                var modalhealthcareid = $('#modalhealthcareid').val() ? $('#modalhealthcareid').val() : '';
                var modalid = $('#modalid').val();
                // var userid = $('#update_commenterid').val();
                var userid = $('#update_commenterid').val();
                var eventData = {
                    modaltitle: title,
                    modalstart: start,
                    modalend: end,
                    modalsalesrepId: salesrepId,
                    modalaccountId: accountId,
                    modalcomments: comments,
                    full_name: full_name,
                    street1: street1,
                    street2: street2,
                    city: city,
                    state: state,
                    zip: zip,
                    modalid: modalid,
                    userid: userid,
                    modalhealthcareid: modalhealthcareid,
                    commentid: commentid,
                    updated_date : current_time,
                    action: action
                };
                console.log(eventData);
                //return false;

                $.ajax({
                    url: "ajaxHandlerEvents.php",
                    type: "POST",
                    data: eventData,
                    success: function (res)
                    {
                        console.log(res);
                        $('#calendar').fullCalendar('refetchEvents');
                        popup_comment(modalid);
                        $('#modalcomment').val('');
                        $("#eventupdate").prop('disabled',true);
                        $("#modalhealthcareComment").val('')
                    }
                })

            } else {
                return false;
            }
        });

        // cancel update
        $('#eventcancel').bind(clickEventType, function () {
            var modal = document.getElementById('myModal');
            modal.style.display = "none";
        });

        // Whenever the user clicks on the "delete" button
        $('#eventdelete').bind(clickEventType, function () {
            var modalid = $('#modalid').val();
            if (confirm("Are you sure you want to remove it?"))
            {
                var id = modalid;
                $.ajax({
                    url: "eventdelete.php",
                    type: "POST",
                    data: {id: id},
                    success: function ()
                    {
                        $('#calendar').fullCalendar('refetchEvents');
                        var modal = document.getElementById('myModal');
                        modal.style.display = "none";
                        //alert("Event Removed");
                    }
                })
            }

        });

        $('#salesrepopt').on('change', function () {
            $('#salerepid').val(this.value);
        });
        $('#modalsalesrepopt').on('change', function () {
            var selec = $('#modalaccountopt option:selected').val();
            $('#modalaccountopt option').remove();
            $('#modalaccountopt').html('<option value="0">Account</option>');
            $.ajax({
                type : 'POST',
                data : 'salerepId='+ this.value,
                dataType: 'json',
                url : 'accountselection.php',
                success : function(data){
                    $.each(data, function(k, v) {
                        if(selec == v.id) var selected = 'selected';
                        if(v.id) $('#modalaccountopt').append('<option value="' + v.id + '" '+ selected + '>' + v.name + '</option>');
                    });


                }
            });
            $('#modalsalerepid').val(this.value);
        });
        
        $('#modalaccountopt').on('change', function () {
                var selec = $('#modalsalesrepopt option:selected').val();
                $('#modalsalesrepopt option').remove();
                $('#modalsalesrepopt').html('<option value="">Genetic Consultant</option>');
                $.ajax({
                    type : 'POST',
                    data : 'accountId='+ this.value,
                    dataType: 'json',
                    url : 'salesrepselection.php',
                    success : function(data){
                        $.each(data, function(k, v) {
                            if(selec == v.id) var selected = 'selected';
                            if(v.id) $('#modalsalesrepopt').append('<option value="' + v.id + '" '+ selected + '>' + v.name + '</option>');
                        });


                    }
                });
                
                var accountName =  $('#modalaccountopt option:selected').text();
                var accountIdArr = accountName.split("-");
                var accountId = accountIdArr[0];
                if(accountId != 'Account'){
                    var ajaxUrl = baseUrl+'/ajaxHandler.php';
                    $.ajax( ajaxUrl , {
                        type: 'POST',
                        data: {
                           get_account_info: '1',
                           account_id: accountId
                        },
                        success: function(response) {
                            var result = JSON.parse(response);
                            var accountData = result['accountInfo'];
                            var providers = result['providers']
                            if(providers.length == 0){
                                if(!confirm("No Provider in this Account. Do you want to continue?")){
                                    $("#modalaccountopt").val('0');
                                }    
                            }    
                        },
                        error: function() {
                            alert('0');
                        }
                    });
                } 
        });

        var dateFormat = function () {
            var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                    timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                    timezoneClip = /[^-+\dA-Z]/g,
                    pad = function (val, len) {
                        val = String(val);
                        len = len || 2;
                        while (val.length < len)
                            val = "0" + val;
                        return val;
                    };

            // Regexes and supporting functions are cached through closure
            return function (date, mask, utc) {
                var dF = dateFormat;

                // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
                if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
                    mask = date;
                    date = undefined;
                }

                // Passing date through Date applies Date.parse, if necessary
                date = date ? new Date(date) : new Date;
                if (isNaN(date))
                    throw SyntaxError("invalid date");

                mask = String(dF.masks[mask] || mask || dF.masks["default"]);

                // Allow setting the utc argument via the mask
                if (mask.slice(0, 4) == "UTC:") {
                    mask = mask.slice(4);
                    utc = true;
                }

                var _ = utc ? "getUTC" : "get",
                        d = date[_ + "Date"](),
                        D = date[_ + "Day"](),
                        m = date[_ + "Month"](),
                        y = date[_ + "FullYear"](),
                        H = date[_ + "Hours"](),
                        M = date[_ + "Minutes"](),
                        s = date[_ + "Seconds"](),
                        L = date[_ + "Milliseconds"](),
                        o = utc ? 0 : date.getTimezoneOffset(),
                        flags = {
                            d: d,
                            dd: pad(d),
                            ddd: dF.i18n.dayNames[D],
                            dddd: dF.i18n.dayNames[D + 7],
                            m: m + 1,
                            mm: pad(m + 1),
                            mmm: dF.i18n.monthNames[m],
                            mmmm: dF.i18n.monthNames[m + 12],
                            yy: String(y).slice(2),
                            yyyy: y,
                            h: H % 12 || 12,
                            hh: pad(H % 12 || 12),
                            H: H,
                            HH: pad(H),
                            M: M,
                            MM: pad(M),
                            s: s,
                            ss: pad(s),
                            l: pad(L, 3),
                            L: pad(L > 99 ? Math.round(L / 10) : L),
                            t: H < 12 ? "a" : "p",
                            tt: H < 12 ? "am" : "pm",
                            T: H < 12 ? "A" : "P",
                            TT: H < 12 ? "AM" : "PM",
                            Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                            o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                            S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                        };

                return mask.replace(token, function ($0) {
                    return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
                });
            };
        }();

// Some common format strings
        dateFormat.masks = {
            "default": "ddd mmm dd yyyy HH:MM:ss",
            shortDate: "m/d/yy",
            mediumDate: "mmm d, yyyy",
            longDate: "mmmm d, yyyy",
            fullDate: "dddd, mmmm d, yyyy",
            shortTime: "h:MM TT",
            mediumTime: "h:MM:ss TT",
            longTime: "h:MM:ss TT Z",
            isoDate: "yyyy-mm-dd",
            isoTime: "HH:MM:ss",
            isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
            isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
        };

// Internationalization strings
        dateFormat.i18n = {
            dayNames: [
                "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
                "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
            ],
            monthNames: [
                "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
                "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
            ]
        };

// For convenience...
        Date.prototype.format = function (mask, utc) {
            return dateFormat(this, mask, utc);
        };

        // Get the modal
        var modal = document.getElementById('myModal');

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

    });

    function sentenceCase(str) {
        if ((str === null) || (str === ''))
            return false;
        else
            str = str.toString();

        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }
    function popup_comment(eventID){
        $.ajax({
            type : 'POST',
            data : { action: 'getComment', eventid: eventID },
            url : 'ajaxHandlerEvents.php',
            success : function(res){

                var result = JSON.parse(res);
                //console.log(result);
                $(".comments-log").html("<label for='modalcomment'>Comments History: </label>");
                $("#modalcomment").val('');
                var count = 0;
                var commentstext = "";
                if(result.length != 0){
                    console.log(result);
                    var current_user = $("#update_commenterid").val();
                    for( count = 0; count < result.length; count++){
                        var comment_date = moment(new Date(result[count]['created_date'])).format('DD MMM YYYY, h:mm a');
                        commentstext += "<div class='commentlogss' id='"+result[count]['id']+"'><p>";
                        if(result[count]['repfname'] != null){
                            commentstext += "<strong>"+ result[count]['repfname'] + " " + result[count]['replname'] + " (" + comment_date + ") </strong>";
                        }else{
                            commentstext += "<strong>"+ result[count]['adminfname'] + " " + result[count]['adminlname'] + " (" + comment_date + ") </strong>";
                        }
                        if(current_user == result[count]['user_id'])
                            commentstext += "<span style='float:right; margin-right:5px;'><a class='fas fa-pencil-alt edit' href='#'></a> <a href='#' class='fa fa-times del'></a></span></p>";
                        else
                            commentstext += "</p>";

                        commentstext += "<p class='comments'>"+result[count]['comments']+"</p></div>";
                    }
                    $(".comments-log").append(commentstext);
                }
            }
        });
    }

    $(document).delegate('.del','click',function(){
        var parent = $(this).parent().parent().parent();
        var id = parent.attr("id");
        $.ajax({
            url: 'ajaxHandlerEvents.php',
            type: 'POST',
            data: {action:"commentDelete", commentid:id},
            success: function(res){
                var result = JSON.parse(res);
                if(result == true){ 
                    parent.html("Deleted..");
                    $(parent).fadeOut(2000);
                }
            }
        })
    });
    $(document).delegate('.edit','click',function(){
        var parent = $(this).parent().parent().parent();
        var id = parent.attr("id");
        var text = parent.find('.comments').text();
        $("#modalcomment").val(text);
        $("#modalhealthcareComment").val(text);
        $("#eventupdate").addClass('edited').attr("data-commentid",id);
    });
    
</script>
<?php
// Salesrep table
$clause = " ORDER BY Guid_salesrep";
$salesrep = $db->selectAll('tblsalesrep', $clause);
?>
<main class="full-width">
    <?php if ($thisMessage != "") { ?>
        <section id="msg_display" class="show success">
            <h4><?php echo $thisMessage; ?></h4>
        </section>
    <?php } ?>    
    <div class="box full visible ">  
        <section id="palette_top">
            <h4>             
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <!-- <li class="active">Event Schedule</li>  -->  
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller event-schedule">
            <div class="container"> 
                <div id="stats_header"> 
                <div id="performance_section">
                <div class="header week_stats" style="font-weight:bold;">    
                    <p>This Week's Stats</p>
                    <p class="top_performer">&#9726; Top Performer</p>
                    <p class = "genetic_consultant">&#9726; Genetic Consultant</p>
                </div>
                <div id="performance_chart">
                <div class="row">
                    <div class="col-md-1" style="border-top-left-radius:10px;">BRCA Days</div>
                    <div class="col-md-2"><span id="mebrcacnt" style="">0</span><span id="topbrcacnt">0</span></div>
                    <div class="col-md-1">Registered</div>
                    <div class="col-md-2"><span id="meregcnt" style="">0</span><span id="topregcnt">0</span></div>
                    <div class="col-md-1">Completed</div>
                    <div class="col-md-2"><span id="mecomcnt" style="">0</span><span id="topcomcnt">0</span></div>
                </div>
                <div class="row">
                    <div class="col-md-1" style="border-bottom-left-radius:10px;">Events</div>
                    <div class="col-md-2"><span id="meeventcnt" style="">0</span><span id="topeventcnt">0</span></div>
                    <div class="col-md-1">Qualified</div>
                    <div class="col-md-2"><span id="mequalcnt" style="">0</span><span id="topqualcnt">0</span></div>
                    <div class="col-md-1">Submitted</div>
                    <div class="col-md-2"><span id="mesubcnt" style="">0</span><span id="topsubcnt">0</span></div>
                </div>
                </div>
             </div>
                <div class="row info_block_row">
                        <div class = "info_block">
                            <h1>
                            All<i class="fas fa-angle-down info_block_arrow" style = "float:right;"></i>
                            <br>Genetic
                            <br>Consultants</h1> 
                            <div class = "salesrep_dropdown dropdown_hide">
                                <i class="fas fa-angle-down info_block_arrow" style = "float:right;"></i>
                                <div class = "salesrep_list">
                                    <?php
                                        $clause = "  ORDER BY first_name, last_name";
                                        $salesrep = $db->selectAll('tblsalesrep', $clause);
                                    ?>
                                    <ul>
                                        <li><a data-value="0">Reset</a></li>
                                        <?php 
                                            foreach ($salesrep as $srole) {
                                                if ($srole['first_name']) {
                                                    ?>
                                                    <li><?php echo "<a data-value='".$srole['Guid_salesrep']."'>".$srole['first_name'] . " " . $srole['last_name']."</a>"; ?></li>
                                                    <?php
                                                }
                                            }
                                        ?>
                                        <!-- <li><a href = "#">Name</a></li>
                                        <li><a href = "#">Name</a></li> -->
                                    </ul>
                                </div>
                            </div>
                        <div class="col-lg-7 col-md-5 top-buttons">
                        <button type="button" name="Detail" id="detail" class="info-button details_button">Details</button>
                        <button type="button" name="Summary" id="summary" class="info-button summary_button">Summary</button>
                        <a href="eventschedule.php" class="button submit"><strong>Full Calendar</strong></a>   
                        </div>    
                    </div>
                </div>
                </div>
                <div id="calendar"></div>
                <div id = "chart_stats">
                    <div class = "chart_header  col-lg-12 col-md-12">
                            <p class = "stats_date">
                                <span>Stats for Week of</span>
                                <span id="calendarmonth"></span>
                                <span id="calendaryear"></span>
                                <span></span>
                            </p> 
                             <!-- <i class="fas fa-angle-down stats_dropdown_arrow"></i>
                            <div class = "stats_dropdown dropdown_hide">
                                <form id="filter_form" class = "stats_dropdown_flex" action="" method="post"> 
                                    <?php //if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                            
                                    <div class = "dropdown_container">
                                        <div class="f2">
                                       

                                            <div class="group">                       
                                                <input readonly class="datepicker" type="text" id="from_date" name="from_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['from_date']) && strlen($_POST['from_date'])) ? $_POST['from_date'] : ""; ?>" placeholder="From Date">

                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                        <?php //} ?>
                                        <?php
                                        $date_error = "";

                                        if (isset($_POST['search'])) {
                                            if (isset($error['to_date'])) {
                                                $date_error = " error";
                                            } elseif (strlen($_POST['to_date'])) {
                                                $date_error = " valid";
                                            }
                                        }
                                        ?>
                                        <?php //if(isFieldVisibleByRole($roleIDs['to_date']['view'], $roleID)) {?>
                                            <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['to_date'])) && (strlen($_POST['to_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
                                                <label class="dynamic" for="to_date"><span>To Date</span></label>

                                                <div class="group">                       
                                                    <input readonly class="datepicker" type="text" id="to_date" name="to_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['to_date']) && strlen($_POST['to_date'])) ? $_POST['to_date'] : ""; ?>" placeholder="To Date" max="<?php echo date('Y-m-d'); ?>">

                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php //} ?>
                                    
                                    <div class = "dropdown_container">     
                                        <div class="f2">
                                            <!--<label class="dynamic" for="to_date"><span>Current Week</span></label>

                                            <div class="group">                       
                                                <input readonly type="text" id="to_date" name="to_date" value="" placeholder="Current Week">

                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="f2">
                                            <!--<label class="dynamic" for="to_date"><span>Month</span></label>

                                            <div class="group">                       
                                                <input readonly type="text" id="to_date" name="to_date" value="" placeholder="Month">

                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class = "dropdown_container">
                                        <div class="f2">
                                            <!--<label class="dynamic" for="to_date"><span>Quarter</span></label>

                                            <div class="group">                       
                                                <input readonly type="text" id="to_date" name="to_date" value="" placeholder="Quarter">

                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="f2">
                                            <!--<label class="dynamic" for="to_date"><span>Year</span></label>

                                            <div class="group">                       
                                                <input readonly type="text" id="to_date" name="to_date" value="" placeholder="Year">

                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                <a class = "stats_filter" href="#"></a>
                            </form>
                        </div> -->

                            <a href="mdl-stats.php" target="_blank" class="button submit smaller_button"><strong>View All Stats</strong></a>

                    </div>
                    <div id="piechart"  class="col-md-6 col-sm-12" ></div>
                    <div id="chart"  class="col-md-6 col-sm-12" style="padding:0;"></div>
                </div>
                </div>
            </div>
            <!-- The Modal -->
            <div id="myModal" class="schedulemodal">

                <!-- Modal content -->
                <div class="schedulemodal-content">
                    <span class="close">&times;</span>
                    <form id='updateEvent'>
                        <input type="hidden" name="modalid" id="modalid" value="">
                        <input type="hidden" name="modalhealthcareid" id="modalhealthcareid" value="">
                        <div class="panel-primary">
                            <div class="panel-body">
                                <div class="row">
                                    <div class='col-md-2'>
                                        <div class="form-group">
                                            <div class='input-group date' id='datetimepicker2'>
                                                <input type='text' id="modaleventstart" class="form-control" placeholder="Event Date" />
                                                <span class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($role == 'Admin' || $role == 'Sales Manager') { ?>
                                        <div class='col-md-2'>
                                            <div class="form-group">
                                                <select class="form-control" id="modalsalesrepopt">
                                                    <option value="0">Genetic Consultant</option>
                                                    <?php
                                                    foreach ($salesrep as $srole) {
                                                        if ($srole['first_name']) {
                                                            ?>
                                                            <option value='<?php echo $srole['Guid_salesrep']; ?>'><?php echo $srole['first_name'] . " " . $srole['last_name']; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($role == 'Sales Rep') { ?>
                                        <div class='col-md-2'>
                                            <div class="form-group">
                                                <span><?php
                                                    echo $salesRepDetails['first_name'] . " " . $salesRepDetails['last_name'];
                                                    ?>
                                                </span>    
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <input type="hidden" id="modalsalerepid" value="<?php echo $salesRepDetails['Guid_salesrep']; ?>">
                                    <div class='col-md-2'>
                                        <div class="form-group">
                                            <div class="modaleventtype">
                                                <label><input type="radio"  id="brcaradio" name="modaleventtype" value="1" checked>BRCA Day</label>
                                            </div>
                                            <div class="modaleventtype">
                                                <label><input type="radio"  id="healthradio" name="modaleventtype" value="2">Health Care Fair</label>
                                            </div>
                                        </div>
                                    </div> 
                                    <div class='col-md-5 modalaccounttype'>
                                        <div class="form-group">
                                            <select class="form-control" id="modalaccountopt">
                                                <option value="0">Account</option>
                                                <?php
                                                foreach ($accountdt as $acct) {
                                                    ?>
                                                    <option value='<?php echo $acct['Guid_account']; ?>'><?php echo $acct['account'] . ' - ' . ucwords(strtolower($acct['name'])); ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div></div>
                                <div class="row modalaccounttype">
                                    
                                    <div class="col-md-6">
                                        <div class="comments-log">
                                            
                                        </div>
                                    </div>

                                    <div class='col-md-6'> 
                                        <div class="form-group">
                                            <label for="modalcomment" style="font-size: 15px;">Add Comments: </label>
                                            <textarea class="form-control" rows="10" id="modalcomment" placeholder="Comments"></textarea>
                                        </div> 
                                    </div>  

                                </div>
                                <div class="row modalhealthcare" style="display: none;">
                                    <div class='col-md-4'>
                                        <div class="comments-log">
                                            
                                        </div>
                                    </div>   
                                    <div class='col-md-4'>
                                        <div class="form-group">
                                            <textarea class="form-control" rows="12" id="modalhealthcareComment" placeholder="Comments"></textarea>
                                        </div>
                                    </div>    
                                    
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- Full Name -->
                                            <input type="text" class="form-control" id="modalfull_name_id" name="modalfull_name" placeholder="Full Name">
                                        </div>
                                        <div class="form-group"> <!-- Street 1 -->
                                            <input type="text" class="form-control" id="modalstreet1_id" name="modalstreet1" placeholder="Street address, P.O. box, company name, c/o">
                                        </div>
                                        <div class="form-group"> <!-- Street 2 -->
                                            <input type="text" class="form-control" id="modalstreet2_id" name="modalstreet2" placeholder="Apartment, suite, unit, building, floor, etc.">
                                        </div>
                                        <div class="form-group"> <!-- City-->
                                            <input type="text" class="form-control" id="modalcity_id" name="modalcity" placeholder="City">
                                        </div>  
                                        <div class="form-group"> <!-- State Button -->
                                            <select class="form-control" id="modalstate_id" name="modalstate">
                                                <option value="">State</option>
                                                <option value="AL">Alabama</option>
                                                <option value="AK">Alaska</option>
                                                <option value="AZ">Arizona</option>
                                                <option value="AR">Arkansas</option>
                                                <option value="CA">California</option>
                                                <option value="CO">Colorado</option>
                                                <option value="CT">Connecticut</option>
                                                <option value="DE">Delaware</option>
                                                <option value="DC">District Of Columbia</option>
                                                <option value="FL">Florida</option>
                                                <option value="GA">Georgia</option>
                                                <option value="HI">Hawaii</option>
                                                <option value="ID">Idaho</option>
                                                <option value="IL">Illinois</option>
                                                <option value="IN">Indiana</option>
                                                <option value="IA">Iowa</option>
                                                <option value="KS">Kansas</option>
                                                <option value="KY">Kentucky</option>
                                                <option value="LA">Louisiana</option>
                                                <option value="ME">Maine</option>
                                                <option value="MD">Maryland</option>
                                                <option value="MA">Massachusetts</option>
                                                <option value="MI">Michigan</option>
                                                <option value="MN">Minnesota</option>
                                                <option value="MS">Mississippi</option>
                                                <option value="MO">Missouri</option>
                                                <option value="MT">Montana</option>
                                                <option value="NE">Nebraska</option>
                                                <option value="NV">Nevada</option>
                                                <option value="NH">New Hampshire</option>
                                                <option value="NJ">New Jersey</option>
                                                <option value="NM">New Mexico</option>
                                                <option value="NY">New York</option>
                                                <option value="NC">North Carolina</option>
                                                <option value="ND">North Dakota</option>
                                                <option value="OH">Ohio</option>
                                                <option value="OK">Oklahoma</option>
                                                <option value="OR">Oregon</option>
                                                <option value="PA">Pennsylvania</option>
                                                <option value="RI">Rhode Island</option>
                                                <option value="SC">South Carolina</option>
                                                <option value="SD">South Dakota</option>
                                                <option value="TN">Tennessee</option>
                                                <option value="TX">Texas</option>
                                                <option value="UT">Utah</option>
                                                <option value="VT">Vermont</option>
                                                <option value="VA">Virginia</option>
                                                <option value="WA">Washington</option>
                                                <option value="WV">West Virginia</option>
                                                <option value="WI">Wisconsin</option>
                                                <option value="WY">Wyoming</option>
                                            </select>                   
                                        </div>
                                        <div class="form-group"> <!-- Zip Code-->
                                            <input type="text" class="form-control" id="modalzip_id" name="modalzip" placeholder="zip code">
                                        </div>
                                    </div> 
                                    
                                   <!--  <input type="hidden" id="current_user" name="userid" value="<?php echo $userID; ?>">  -->
                                    <input type="hidden" id="update_commenterid" name="userid" value="<?php echo $userID; ?>">
                                    <input type="hidden" id="update_date_updated" name="update_date" value="<?php echo date("Y-m-d H:i:s"); ?>">
                                </div>  
                                <div class="row">
                                    <div class='col-md-10'>
                                        <button type="button" id="eventupdate" class="btn btn-primary">Update</button>
                                        <button type="button" id="eventcancel" class="btn btn-danger">Cancel</button>
                                        <button type="button" class="btn btn-danger" id="eventdelete" style="border-radius: 2em !important; margin: 7px 0;">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form> 
                </div>

            </div>
        </div>
    </div>
</main>
<script>
    $(function () {
        $('#modaleventstart').datepicker({
            dateFormat: "mm/dd/yy",
            showOn: 'both',
            buttonImageOnly: true,
            buttonImage: 'assets/eventschedule/images/calendar.gif'
        });
    });
</script>
<script>
        function createChart() {
            $("#chart").kendoChart({
                title: {
                    text: "Top Genetic Consultants"
                },
                legend: {
                    //visible: false
                },
                seriesDefaults: {
                    type: "column",
                    stack: true
                },
                valueAxis: {
                    //max: 500,
                    line: {
                       //visible: false
                    },
                    minorGridLines: {
                        //visible: true
                    }
                },
                categoryAxis: {
                    //categories: [1952, 1956, 1960, 1964, 1968],
                    majorGridLines: {
                        //visible: false
                    },
                    labels: {
                                template: labelTemplate
                            }
                },
                tooltip: {
                    visible: true,
                    template: "#= series.name #: #= value #"
                },
                chartArea: {
                    width: 600,
                    height: 230
                },
            });
            
            $("#piechart").kendoChart({
                title: {
                    text: "Top Accounts"
                },
                chartArea: {
                    width: 600,
                    height: 250
                },
                legend: {
                    position: "left",
                },
                seriesDefaults: {
                    labels: {
                        //template: "#= kendo.format('{0:P}', percentage)#",
                        template: "#if (value > 0) {# #: value #% #}#",
                        position: "center",
                        visible: true,
                        background: "transparent"
                    }
                },
                tooltip: {
                    //visible: true,
                    template: "#= category # - #= kendo.format('{0:P}', percentage) #"
                },
            });

            function labelTemplate (e) { 
                var text = e.value;
                var first = "";
                if ((screen.width<=950))
                    first = text.slice(0, text.indexOf(" "));
                else
                    first = e.value.split(" ").join("\n");
                return first;
            };
        }
        function get_date(){
            var d = new Date();
            var hr = d.getHours();
            var min = d.getMinutes();
            if (min < 10) {
                min = "0" + min;
            }
            var sec = d.getSeconds();
            var date = d.getDate();
            var month = d.getMonth()+1;
            var year = d.getFullYear();
            var current_time = year + "-" + month + "-" + date + " " +hr + ":" + min + ":" + sec ;
            return current_time;
        }
    </script>
<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>