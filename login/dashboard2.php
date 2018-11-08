<?php
//header("Access-Control-Allow-Origin: *");
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
if($role == 'Sales Rep'): 
    $where = array('Guid_user'=>$userID);
    $salesrepRow = getTableRow($db, 'tblsalesrep', $where);
    extract($salesrepRow);
    $photo = $photo_filename;
endif;

$default_account = "";

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
<!-- <script src="assets/eventschedule/js/moment.min.js"></script> -->
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
    .below_avg, .above_avg, .top_performer_avg {
        position: relative;
    }
    .below_avg:before{
        background-image: url(assets/images/below_avg.png);
    }
    .above_avg:before{
        background-image: url(assets/images/above_avg.png);
    }
    .info_block h1 br:first-child {
        display: none;
    }
    .activeButton{ background: #3f628a !important; color: #fff !important;    /*width: 45%;*/ padding: 0; box-shadow: none !important; float: left;}
    tr:first-child > td > .fc-day-grid-event{ min-height: 50px; }
    #piechart svg > g > g:nth-child(4) > g text, #chart svg > g > g:nth-child(4) > g text {
        font-weight: 800 !important;
    }
    .salesrep_dropdown.hide { visibility: visible; }
    p.acc-click { color: #343; }
    .consultant_changed{ visibility: hidden; }
    .consultant_changed:before{ position: absolute;display: inline-block; width: 60px; border-radius: 20px; left: 0; content: "0"; background-color: #fff; visibility: visible; }
    .forcehidden{ display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important; }
    #calendar th{ text-align: center; color: #1c487b;}
    #calendar th.fc-today{ color:  white;}
    #detail, #summary{ /*width: 48%;*/ padding: 2px; font-size: 15px;}
    .top-buttons button.info-button { background: linear-gradient(to bottom, rgba(255,255,255,1) 46%,rgba(224,224,224,1) 64%,rgba(243,243,243,1) 100%); }
    .sales-photo img { max-width: 100px; }
    @media only screen and (min-device-width : 768px) and (max-width : 1024px) 
    and (orientation : portrait) { 
        .top-buttons { /*width: 65%;*/ }
        #detail, #summary{}
        .dropdown_hide{ display: none; }
        .info_block h1{ /*width: 155px;*/ line-height: 26px; text-align:left; padding-left:10px; font-size:20px;}
        .sales-photo img { max-width: 55px; text-align: center; margin-left: 20px; padding: 6px 0px; }
    }
    @media only screen and (min-device-width : 768px) and (max-width : 1024px) 
    and (orientation : landscape) { 

        .top_performer_avg:before {
            top: -4px;
            left: 39px;
           
        }
    }
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
        var salesrep = <?php echo isset($_GET['salerepId']) ? $_GET['salerepId'] : 0; ?>;
        var cursource = 'eventload.php';
        if(salesrep == 0){
            cursource = 'eventload.php';
        }else{
            cursource = 'eventload.php?salerepId='+salesrep;
        }

        // when summary button is clicked
        $('#summary').on('click touchstart', function () {
            if(salesrep == 0)
                var summarycursource = 'ajaxHandlerEvents.php';
            else
                var summarycursource = 'ajaxHandlerEvents.php?salerepId='+salesrep;

            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('addEventSource', summarycursource);
            $('#calendar').fullCalendar('refetchEvents');
            $("#detail").removeClass('activeButton')
            $("#summary").addClass('activeButton')
            
        });

        // when detail button is clicked
        $('#detail').on('click touchstart', function () {
            if(salesrep == 0)
                var detailcursource = 'eventload.php';
            else
                var detailcursource = 'eventload.php?salerepId='+salesrep;
            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            $('#calendar').fullCalendar('addEventSource', detailcursource);
            $('#calendar').fullCalendar('refetchEvents');
            $("#summary").removeClass('activeButton')
            $("#detail").addClass('activeButton')
            
        });
        var state_count1 = 0, count1 = 0;
        var d = new Date();
        var evtsDate = d.getFullYear() + "/" + (d.getMonth()+1) + "/" + d.getDate();
        evtsDate = evtsDate.toString();
        <?php /*if(isset($_GET['salerepId'])): ?>
            if (localStorage.evtsDate) {
                evtsDate = (localStorage.evtsDate).toString();
            }
        <?php endif;*/ ?>


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
            
            eventSources: cursource,
            selectable: true,
            selectHelper: true,
            editable: false, 
            defaultDate: evtsDate,
            viewRender: function(view, element) {
                var currentDate = $('#calendar').fullCalendar('getDate');
                var beginOfWeek = currentDate.startOf('week');
                $("#calendarmonth").html($.fullCalendar.formatDate(beginOfWeek,"MMMM DD"));
                $("#calendaryear").html($.fullCalendar.formatDate(beginOfWeek,"YYYY"));
                $(".salesrep_list").html("<ul><li><a href='<?php echo SITE_URL; ?>/dashboard2.php'>Select All</a></li></ul>");
                //top_stats();
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
                
                if (date.format('DD-MM-YYYY') === today2) {
                   
                    cell.css("background-image", "url('assets/images/active_background.png')");
                    cell.css("background-size", "100% 100%");
                }
            },
            eventClick: function (event)
            {
                var moment = $.datepicker.formatDate('yy-mm-dd', new Date());
                // Get the modal
                var modal = document.getElementById('myModal');
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var modaldate = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
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
                    $("#myModal").delay( 100 ).fadeIn( 400 );
            },
            eventMouseover: function (calEvent, jsEvent) {
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
                
                var modifiedName = (name == "") ? "Health Care Fair" : name;
                
                
                var content = '<div class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable" style="' + borderColor + '">' +
                        '<div class="fc-content evtcontent">' +
                        '<div class="' + icon + '"></div>' +
                        '<div class="fc-title evttitle">';
                    if(event.title == "Health Care Fair")
                        content += '<p class="acc-click" id="acc'+event.accountid+'" >' + modifiedName + '</p></div>';
                    else{
                        content += '<a class="acc-click" id="acc-'+event.accountid+'" href="accounts.php?account_id='+event.accountid+'">' + modifiedName + '</a></div>';
                    }

                    content +=  salesrep ;
                    if (parsedEventTime < parsedNow) {
                        content += '<div class="fc-stats"></div>';
                    }
                    content +='</div>' + '</div>';

                if (event.evtCnt) {

                    var content = '<div class="fc-content evtcontent summarybrca days-' + eventDate + '" style="padding: 0 20px; font-size: 15px; line-height: 16px;">';
                    content += '<div class="numberCircleContainer"><span class="numberCircle">' + event.evtCnt + '</span></div>';
                    content += '<div>Registered <span style="float:right">' + event.registeredCnt + '</span></div>';
                    content += '<div>Completed <span style="float:right">' + event.completedCnt + '</span></div>';
                    content += '<div>Qualified <span style="float:right">' + event.qualifiedCnt + '</span></div>';
                    content += '<div>Submitted <span style="float:right">' + event.submittedCnt + '</span></div>';
                    content += '</div>';
                    return $(content);
                    
                } else {
                   
                    if (parsedEventTime < parsedNow) {
                        
                    var content = '<div class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable days-' + eventDate + '"  style="' + borderColor + '">' +
                                '<div class="fc-content evtcontent">' + '<div class="fc-title evttitle">';
                        if(event.title == "Health Care Fair")
                            content += '<p class="acc-click" id="acc'+event.accountid+'" >' + modifiedName + '</p></div>';
                        else{
                            content += '<a class="acc-click" id="acc-'+event.accountid+'"  href="accounts.php?account_id='+event.accountid+'">' + modifiedName + '</a></div>';
                        }

                        content += salesrep;
                            //content += cmts;
                            if (parsedEventTime < parsedNow) {
                                content += '<div class="fc-stats"></div>';
                            }
                            content += '<div class="' + icon + '"></div>';   
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
                        
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                        element.css('border-color', '#FF6347');
                    } else if (event.salesrep == null && event.title != 'BRCA Day') {
                        
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                        element.css('border-color', '#FF6347');
                    } else {
                        element.css('background-color', '#fff');
                        element.css('color', '#000');
                    }
                }

                var eventData = { action: 'getStates', account: event.account, regitered:28, qualified: 29, completed: 36,  submitted: 1, selectedDate: $.fullCalendar.formatDate(event.start, "Y-M-DD")};
                
                var today = new Date();
                var parsedNow =  new Date(today).getUnixTime();
                var parsedEventTime = new Date(event.start).getUnixTime();        
                    $.ajax({
                        url: "ajaxHandlerEvents.php",
                        type: "POST",
                        data: eventData,
                        success: function (res)
                        {
                            var res = JSON.parse(res);
                            //console.log(res); 
                            var html = '<div class="show-stats"><span class="silhouette"><span>' + res.reg + '</span> <img src="assets/eventschedule/icons/silhouette_icon.png"></span> | <span class="checkmark"><span> '+ res.com + '</span> <img src="assets/eventschedule/icons/checkmark_icon.png"></span> | <span class="dna"><span>'+ res.qua + '</span> <img src="assets/eventschedule/icons/dna_icon.png"></span> | <span class="flask"><span>'+ res.sub +'</span> <img src="assets/eventschedule/icons/flask_icon.png"></span></div>';
                            if(view.name != 'basicDay' && parsedEventTime < parsedNow){
                                element[0].childNodes[0].childNodes[2].innerHTML = html;
                            }
                        }
                    });
            },
            eventAfterAllRender: function (event, element, view) {
                
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");

                var inputparam = {
                    userid: <?php echo $userID; ?>,
                    startdate: start
                };

                $('#topbrcacnt').html('0');
                $('#topeventcnt').html('0');
                $('#topregcnt').html('0');
                $('#topqualcnt').html('0');
                $('#topcomcnt').html('0');
                $('#topsubcnt').html('0');

               
                $.ajax({
                    type : 'POST',
                    data : { userid:<?php echo $userID; ?>, startdate:start, action:'topbrcacount' },//inputparam,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(data){
                        //console.log(data);
                        (data['topbrcacount'] != 0 ) ? $('#topbrcacnt').html(data['topbrcacount']) : 0;
                        (data['topeventcount'] != 0 ) ? $('#topeventcnt').html(data['topeventcount']) : 0;
                        (data['topqualifiedcount'] != 0 ) ? $('#topqualcnt').html(data['topqualifiedcount']) : 0;
                        (data['topregisteredcount'] != 0 ) ? $('#topregcnt').html(data['topregisteredcount']) : 0;
                        (data['topcompletedcount'] != 0 ) ? $('#topcomcnt').html(data['topcompletedcount']) : 0;
                        (data['topsubmittedcount'] != 0 ) ? $('#topsubcnt').html(data['topsubmittedcount']) : 0;
  
                    }
                });
                <?php //endif; ?>

                var startdate = moment(event.start._d).format('YYYY-MM-DD');
                var enddate = moment(event.end._d).format('YYYY-MM-DD');
                
                /*localStorage.setItem('evtsDate', startdate );
                localStorage.setItem('evteDate', enddate );*/

                var events = $('#calendar').fullCalendar('getView');
                var ele_events = events._props.currentEvents;
                var categories = salesrepIds = [];
                $.each(ele_events,function(k, v){
                    salesrepIds.push(v.salesrepid);
                });
                var uniqueIds = salesrepIds.filter(onlyUnique);
                uniqueIds = uniqueIds.toString();

                
                //Bar chart
                <?php if(isset($_GET['salerepId'])):  ?>
                    var chartParams = {ids: uniqueIds, startdate: startdate, enddate: enddate, action: 'getBarChart', showtopPerformer:true};
                <?php else: ?>
                    var chartParams = {ids: uniqueIds, startdate: startdate, enddate: enddate, action: 'getBarChart'};
                <?php endif; ?>
                $.ajax({
                    type: 'POST',
                    url: 'ajaxHandlerEvents.php',
                    data: chartParams,
                    dataType: 'json',
                    success: function(returndata){
                        //returndata = JSON.parse(returndata);
                        console.log(returndata);
                        var chart = $("#chart").data("kendoChart");
                        var catr = returndata.categories;
                        chart.setOptions({
                            series: returndata.series,
                            categoryAxis: {
                                categories: catr},
                            valueAxis:{
                                max:returndata.yaxis
                            },
                        });
                        chart.refresh();
                        /*firstSeries = chart.options.series;
                        firstSeries[0].gap = 5;//parseFloat(5, 10);
                        firstSeries[0].spacing = 5;
                        chart.redraw();
                        console.log(chart);*/
                    },
                });

                //Piechart
                var accounts = [];
                $.each(ele_events,function(k, v){
                    if(v.account!=null)
                        accounts.push(v.account)
                });
                var uniqueAcc = accounts.filter(onlyUnique);
                uniqueAccString = uniqueAcc.toString();
                $.ajax({
                    type : 'POST',
                    data : { acc: uniqueAccString, startdate: startdate, enddate:enddate, action:'piechart' },
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(returndata){
                        var chart = $("#piechart").data("kendoChart");
                        chart.setOptions({
                            series: [returndata],
                        });
                        chart.refresh();
                    }
                });

                <?php if(isset($_GET['salerepId'])): ?>
                    var genid = <?php echo $_GET['salerepId']; ?> 
                    $('.salesrep_list ul').html('<li><a href="<?php echo SITE_URL; ?>/dashboard2.php">Select All</a></li>')
                    $.ajax({
                        url: 'ajaxHandlerEvents.php',
                        type: 'POST',
                        data: { id: genid, sDate: startdate, eDate: enddate, action:'genconValues' },
                        success: function(res){
                            var result = JSON.parse(res);
                            $.each(result, function(k,v){
                                $('.salesrep_list ul').append('<li><a href="<?php echo SITE_URL; ?>/dashboard2.php?salerepId='+v.salesrepid+'">'+v.snames+'</a></li>');
                            });
                        }

                    });
                <?php else: ?>
                    $.get({
                        url:'ajaxHandlerEvents.php', 
                        data:{ srepids:uniqueIds, action:'getconsultant' }, 
                        success: function(res){ 
                            //console.log(res);
                            var result = JSON.parse(res);
                            var arrlen = result['names'].length;
                            var i=0;
                            for(i=0; i<arrlen;i++){
                                $('.salesrep_list ul').append('<li><a href="<?php echo SITE_URL ?>/dashboard2.php?salerepId='+result['ids'][i]+'">'+result['names'][i]+'</a></li>');
                            }
                        } 
                    });
                <?php endif; ?>

                //Table Stats
                <?php if($role == "Sales Rep"):  
                    $record = getSalesRepAccount($db, $Guid_salesrep);
                ?>
                    var string = "<?php echo $record; ?>";
                    var arrayAcc = string.split(',')
                    var z = arrayAcc.filter(function(val) {
                      return uniqueAcc.indexOf(val) != -1;
                    });
                    accIds = z.toString();
                    var params = { acc: accIds, salesreps: <?php echo $Guid_salesrep; ?>, startdate: startdate, enddate:enddate, action:'tableStats' };
                <?php else: ?>
                    accIds = uniqueAccString;
                    var params = { acc: accIds, salesreps: '<?php echo (isset($_GET["salerepId"])) ? $_GET["salerepId"] : "" ?>', startdate: startdate, enddate:enddate, action:'tableStats' };
                <?php endif; ?>
                $.ajax({
                    type : 'POST',
                    data : params,
                    dataType: 'json',
                    url : 'ajaxHandlerEvents.php',
                    success : function(returndata){
                        $("#meregcnt").text(returndata.reg).removeClass();
                        $("#mecomcnt").text(returndata.com).removeClass();
                        $("#mequalcnt").text(returndata.qua).removeClass();
                        $("#mesubcnt").text(returndata.sub).removeClass();
                        $("#mebrcacnt").text(returndata.brca).removeClass();
                        $("#meeventcnt").text(returndata.hcf).removeClass();
                    }
                });

                setTimeout(function(){
                    brcatotal = $("#mebrcacnt").text();
                    brcatop = $("#topbrcacnt").text();
                    $("#mebrcacnt").addClass(statImage(brcatotal, brcatop));
                    hcftotal = $("#meeventcnt").text();
                    hcftop = $("#topeventcnt").text();
                    $("#meeventcnt").addClass(statImage(hcftotal, hcftop));
                    regtotal = $("#meregcnt").text();
                    regtop = $("#topregcnt").text();
                    $("#meregcnt").addClass(statImage(regtotal, regtop));
                    comtotal = $("#mecomcnt").text();
                    comtop = $("#topcomcnt").text();
                    $("#mecomcnt").addClass(statImage(comtotal, comtop));
                    quatotal = $("#mequalcnt").text();
                    quatop = $("#topqualcnt").text();
                    $("#mequalcnt").addClass(statImage(quatotal, quatop));
                    subtotal = $("#mesubcnt").text();
                    subtop = $("#topsubcnt").text();
                    $("#mesubcnt").addClass(statImage(subtotal, subtop));
                }, 5000);
            },
        });
       
        function onlyUnique(value, index, self) { 
            return self.indexOf(value) === index;
        }

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
                        $('#calendar').fullCalendar('refetchEvents');
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
                
                var full_name = $('#modalfull_name_id').val() ? $('#modalfull_name_id').val() : '';
                var street1 = $('#modalstreet1_id').val() ? $('#modalstreet1_id').val() : '';
                var street2 = $('#modalstreet2_id').val() ? $('#modalstreet2_id').val() : '';
                var city = $('#modalcity_id').val() ? $('#modalcity_id').val() : '';
                var state = $('#modalstate_id').val() ? $('#modalstate_id').val() : '';
                var zip = $('#modalzip_id').val() ? $('#modalzip_id').val() : '';
                var modalhealthcareid = $('#modalhealthcareid').val() ? $('#modalhealthcareid').val() : '';
                var modalid = $('#modalid').val();
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
                
                $.ajax({
                    url: "ajaxHandlerEvents.php",
                    type: "POST",
                    data: eventData,
                    success: function (res)
                    {
                        //console.log(res);
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
                    //console.log(result);
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

    $(document).delegate('.rightCircleicon1','click', function(){
        $("#updateEvent").removeClass();
        $("#popup-accounts").hide();
    });

    

    $(document).delegate('a.acc-click', 'click', function(e){
        $("#myModal").addClass("forcehidden");
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
            <?php if(isset($_GET['salerepId']))
                $titleArr = $db->row("SELECT CONCAT(`first_name`,' ',`last_name`) AS genName FROM `tblsalesrep` WHERE `Guid_salesrep`=:id", array('id'=>$_GET['salerepId']));
             ?>
                
            <div class="container"> 
                <div id="stats_header"> 
                <div id="performance_section" class="col-md-8">
                <div class="header week_stats" style="font-weight:bold;">    
                    <p>This Week's Stats</p>
                    <p class="top_performer">&#9726; Top Performer</p>
                    <?php 
                        if($role == 'Sales Rep'):
                            echo "<p class = 'genetic_consultant'>&#9726; Me</p>";
                        elseif(isset($_GET['salerepId'])): 
                            echo "<p class = 'genetic_consultant'>&#9726; ".$titleArr['genName']."</p>";
                        else:
                            echo "<p class = 'genetic_consultant'>&#9726; Genetic Consultant</p>";
                        endif; 
                    ?>
                    
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
                    <div class="col-md-1" style="border-bottom-left-radius:10px;">Health Care Fair</div>
                    <div class="col-md-2"><span id="meeventcnt" style="">0</span><span id="topeventcnt">0</span></div>
                    <div class="col-md-1">Qualified</div>
                    <div class="col-md-2"><span id="mequalcnt" style="">0</span><span id="topqualcnt">0</span></div>
                    <div class="col-md-1">Submitted</div>
                    <div class="col-md-2"><span id="mesubcnt" style="">0</span><span id="topsubcnt">0</span></div>
                </div>
                </div>
             </div>
                <div class="row info_block_row col-md-4 col-sm-12">
                        <div class = "info_block">
                            <?php if($role == 'Sales Rep'): ?>
                                <div class="sales-photo">
                                    <?php 
                                        if($photo != ""){
                                            $photo = SITE_URL."/images/users/".$photo;
                                        } else {
                                            $photo =  SITE_URL."/assets/images/default.png";
                                        }
                                    ?>
                                    <img src="<?php echo $photo ?>">
                                </div>
                            <?php else: ?>
                                <?php 
                                    if(isset($_GET['salerepId'])) :
                                        $sTitle = explode(' ', $titleArr['genName']);
                                        
                                        array_splice( $sTitle, 1, 0, array('<i class="fas fa-angle-down info_block_arrow" onclick="test()" style = "float:right;"></i>') );
                                        //print_r();
                                        $sTitle = implode("<br>",$sTitle);
                                        echo '<h1 class = "col-sm-5">'.$sTitle.'</h1>';
                                    else:
                                        echo '<h1 class "col-sm-5">  All<i class="fas fa-angle-down info_block_arrow" onclick="test()" style = "float:right;"></i> <br>Genetic <br>Consultants</h1>';
                                    endif;

                                ?>
                            <?php endif; ?>

                            <div class = "salesrep_dropdown dropdown_hide">
                                <i class="fas fa-angle-down info_block_arrow" onclick="test()" style = "float:right;"></i>
                                <div class = "salesrep_list">
                                    <ul>
                                        <li><a href='<?php echo SITE_URL; ?>/dashboard2.php'>Select All</a></li>
                                        
                                    </ul>
                                </div>
                            </div>
                        <div class="col-lg-7 col-md-8 col-sm-7 top-buttons">
                        <button type="button" name="Detail" id="detail" class="col-lg-6 col-md-6 col-sm-3 col-md-offset-0 col-sm-offset-1 info-button activeButton" style="">Details</button>
                        <button type="button" name="Summary" id="summary" class="col-lg-6 col-md-6 col-sm-3 info-button" style="">Summary</button>
                        <a href="eventschedule.php" class="col-md-12 col-sm-5 button submit"><strong>Full Calendar</strong></a>   
                        </div>    
                    </div>
                </div>
                </div>
                <div id="calendar"></div>
                <div id = "chart_stats">
                    <div class = "chart_header  col-lg-12 col-md-12">
                            <p class = "stats_date">
                                <span>Stats for Week of</span>
                                <span id="calendarmonth"></span>, 
                                <span id="calendaryear"></span>
                                <span></span>
                            </p> 
                            <a href="mdl-stats.php" target="_blank" class="button submit smaller_button"><strong>View All Stats</strong></a>

                    </div>
                    <div id="piechart"  class="col-md-6 col-sm-12" style="padding:0;"></div>
                    <div id="chart" class="col-md-6 col-sm-12" style="padding:0;"></div>
                    <!-- <div class="overlay"><div>No data available</div></div> -->
                    
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
                    text: "Top Submitting Genetic Consultants"
                },
                sort:{       
                    field :"data",
                    dir:"desc"
                },
                legend: {
                    position: "top",

                },
                seriesDefaults: {
                    type: "column",
                },
                valueAxis: {
                    line: {
                    },
                    minorGridLines: {
                    }
                },
                categoryAxis: {
                    majorGridLines: {
                        visible: false
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
                    width: 580,
                    height: 450
                },
            });

            $("#piechart").kendoChart({
                title: {
                    text: "Top Submitting Accounts"
                },
                chartArea: {
                    width: 620,
                    height: 450
                },
                legend: {
                    position: "left",
                },
                seriesDefaults: {
                    labels: {
                        template: "#if (value > 0) {# #: value #% #}#",
                        position: function(e) { 
                                if(e.percentage < 0.1)
                                    return "outsideEnd";
                                else
                                  return "center";
                              },
                        visible: true,
                        background: "transparent",
                        distance:20
                    }
                },
                tooltip: {
                    template: "#= category # - #= kendo.format('{0:P}', percentage) #"
                },
            });
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

        function labelTemplate (e) { 
            var text = e.value;
            var first = "";
            if ((screen.width<=950))
                first = text.slice(0, text.indexOf(" "));
            else
                first = e.value.split(" ").join("\n");
            return first;
        };

        function test(){
            $(".salesrep_dropdown").toggleClass("dropdown_hide");
            $(".info_block h1").toggleClass("hide");
            $(".info_block_arrow").toggleClass("info_block_arrow_show");
        }
        
        function statImage(total, top){
            var img = "";
            if( parseInt(total) < parseInt(top/2) ){
                img = "below_avg";
            }else if( parseInt(total) < parseInt(top) && parseInt(total) >= parseInt(top/2) ){
                img = "above_avg";
            }else if( parseInt(total) >= parseInt(top) ){
                img = "top_performer_avg";
            }
            return img;
        }
    </script>
<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>