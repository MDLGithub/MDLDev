<?php
ob_start();
require_once('settings.php');
require_once('config.php');
require_once('header.php');
require_once ('navbar.php');

if (!isUserLogin()) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    doLogout();
    Leave(SITE_URL);
}

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

// Salesrep table
$clause = " ORDER BY Guid_salesrep";
$salesrep = $db->selectAll('tblsalesrep', $clause);

$thisMessage = "";
?>
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">-->
<link rel="stylesheet" href="assets/eventschedule/css/fullcalendar.css" />
<link rel="stylesheet" href="assets/eventschedule/css/bootstrap-datetimepicker.min.css">
<script src="assets/eventschedule/js/jquery.min.js"></script>
<script src="assets/eventschedule/js/jquery-ui.min.js"></script>
<script src="assets/eventschedule/js/moment.min.js"></script>
<script src="assets/eventschedule/js/fullcalendar.min.js"></script>
<script src="assets/eventschedule/js/bootstrap-datetimepicker.min.js"></script>
<style>
    .col-md-1 { width: 9.333333% !important;}
    .container { max-width: 1358px !important;}
    #datetimepicker1{ position: relative; width: 172px; }
    #datetimepicker1 input{ width: 100%; }
    #datetimepicker1 img{ position: absolute; top: 8px; right: 5px;}
    
    #datetimepicker2{ position: relative; width: 172px; }
    #datetimepicker2 input{ width: 100%; }
    #datetimepicker2 img{ position: absolute; top: 8px; right: 5px;}
    
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
    .evtcontent{ padding: 5px 5px; white-space: pre-wrap !important;}
    .evttitle{font-weight: bold; color: #3a87ad; white-space: pre-wrap !important;}
    
    .rightCircleicon1{ position: absolute; width: 20px; height: 20px; right: 0px; top: -1px; background-image: url("assets/eventschedule/images/sample-icon1.png"); background-repeat: no-repeat;background-size: 20px 20px;}
    .rightCircleicon2{ position: absolute; width: 20px; height: 20px; right: 0px; top: -1px; background-image: url("assets/eventschedule/images/sample-icon2.png"); background-repeat: no-repeat;background-size: 20px 20px;}
</style>
<script>

    $(document).ready(function () {

        $("input[name='eventtype']").click(function () {
            var evtType = $(this).val();
            if (evtType == 2) {
                $("div.accounttype").hide();
                $("div.healthcare").show();
            } else {
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
        $('#salesrepfilter,#accountfilter').change(function () {
            var salesrep = 0;
            var account = 0;
            salesrep = $('#salesrepfilter option:selected').val();
            account = $('#accountfilter option:selected').val();
            var allcursource = 'eventload.php';
            if (salesrep != 0 || account != 0) {
                cursource = 'eventload.php?salerepId=' + salesrep + '&accountId=' + account;
            }

            $('#calendar').fullCalendar('removeEventSources');
            $('#calendar').fullCalendar('refetchEvents');
            if (salesrep == 0 && account == 0) {
                $('#calendar').fullCalendar('addEventSource', allcursource);
            } else {
                $('#calendar').fullCalendar('addEventSource', cursource);
            }
            $('#calendar').fullCalendar('refetchEvents');

        });

        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            eventSources: cursource,
            selectable: true,
            selectHelper: true,
            editable: false,
            eventClick: function (event)
            {
                var moment = $.datepicker.formatDate('yy-mm-dd', new Date());
                // Get the modal
                var modal = document.getElementById('myModal');
                //var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var thisdate = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                if (moment <= thisdate) {
                    $('#updateEvent').find('input, textarea, button, select').prop("disabled", false);
                } else {
                    $('#updateEvent').find('input, textarea, button, select').prop("disabled", true);
                    ;
                }
                $('#myModal').find('#modaleventstart').val(start);
                $("#modalsalesrepopt option:contains(" + event.salesrep + ")").attr('selected', 'selected');
                $("#modalaccountopt option:contains(" + event.account + ")").attr('selected', 'selected');
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
                modal.style.display = "block";
            },
            eventMouseover: function (calEvent, jsEvent) {
                var message = '';
                if (calEvent.salesrep == null)
                    message += 'SalesRep is not assigned';
                if (calEvent.salesrep == null && calEvent.account == null)
                    message += ' and ';
                if (calEvent.account == null)
                    message += 'Account Number is missing';
                if (message != '') {
                    var tooltip = '<div class="tooltipevent" style="padding:20px 20px;min-width:100px;min-height:100px;background:#FF4500;color:#000;position:absolute;z-index:10001;">' + message + '</div>';
                    $("body").append(tooltip);
                    $(this).mouseover(function (e) {
                        $(this).css('z-index', 10000);
                        $('.tooltipevent').fadeIn('500');
                        $('.tooltipevent').fadeTo('10', 1.9);
                    }).mousemove(function (e) {
                        $('.tooltipevent').css('top', e.pageY + 10);
                        $('.tooltipevent').css('left', e.pageX + 20);
                    });
                }
            },

            eventMouseout: function (calEvent, jsEvent) {
                $(this).css('z-index', 8);
                $('.tooltipevent').remove();
            },
            eventRender: function (event, element, view) {
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
                    salesrep = '<div class="fc-salesrep">' + event.salesrep + ' | ' + time + '</div>';
                var cmts = '';

                var view = $('#calendar').fullCalendar('getView');
                if (view.name == 'agendaWeek' && event.comments)
                    cmts = '<div class="fc-comments">' + event.comments + '</div>'
                
                var icon = '';
                if(event.title == 'BRCA Day'){
                    icon = 'rightCircleicon1';
                    if (event.name) name = event.name;
                }
                else{
                    icon = 'rightCircleicon2';
                    if(event.hltname) name = event.hltname;
                }

                
                
                var content = '<a class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable">' +
                        '<div class="fc-content evtcontent">' +
                        '<div class="'+ icon + '"></div>' + 
                        '<div class="fc-title evttitle">' + name + '</div>' +
                         salesrep + cmts +
                        '</div>' +
                        '</a>';

                return $(content);
            },
            eventAfterRender: function (event, element, view) {
                if (event.salesrep == null || event.account == null) {
                    element.css('background-color', '#FF6347');
                    element.css('border-color', '#FF6347');
                }
                else{
                    element.css('background-color', '#fff');
                    element.css('color', '#000');
                }
            },

        });

        // Whenever the user clicks on the "save" button
        $('#eventsave').on('click', function () {
            if ($("input[name='eventtype']:checked").val() == 1 && $('#accountopt').val() == 0) {
                alert("Please select Account");
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
                    city:city,
                    state:state,
                    zip:zip
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

        // Whenever the user clicks on the "update" button
        $('#eventupdate').on('click', function () {
            if ($("input[name='modaleventtype']:checked").val() == 1 && $('#modalaccountopt').val() == 0) {
                alert("Please select Account");
                return false;
            }
            var title = $("input[name='modaleventtype']:checked").parent('label').text();
            if ($('#modaleventstart').val() && ($('#modalsalerepid').val() || $('#modalaccountopt').val() != 0)) {
                var start = dateFormat($('#modaleventstart').val(), "yyyy-mm-dd");
                var end = dateFormat($('#modaleventstart').val(), "yyyy-mm-dd");
                var accountId = $('#modalaccountopt').val();
                var salesrepId = $('#modalsalerepid').val() ? $('#modalsalerepid').val() : 0;
                var comments = $('#modalcomment').val();
                var full_name = $('#modalfull_name_id').val() ? $('#modalfull_name_id').val() : '';
                var street1 = $('#modalstreet1_id').val() ? $('#modalstreet1_id').val() : '';
                var street2 = $('#modalstreet2_id').val() ? $('#modalstreet2_id').val() : '';
                var city = $('#modalcity_id').val() ? $('#modalcity_id').val() : '';
                var state = $('#modalstate_id').val() ? $('#modalstate_id').val() : '';
                var zip = $('#modalzip_id').val() ? $('#modalzip_id').val() : '';
                var modalhealthcareid = $('#modalhealthcareid').val() ? $('#modalhealthcareid').val() : '';
                var modalid = $('#modalid').val();
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
                    city:city,
                    state:state,
                    zip:zip,
                    modalid: modalid,
                    modalhealthcareid: modalhealthcareid
                };

                $.ajax({
                    url: "eventupdate.php",
                    type: "POST",
                    data: eventData,
                    success: function ()
                    {
                        //$('#calendar').fullCalendar('renderEvent', eventData, true); // stick? = true
                        $('#calendar').fullCalendar('refetchEvents');
                        //alert("Updated Successfully");
                    }
                })

            } else { alert("not")
                return false;
            }
        });
        
        // cancel update
        $('#eventcancel').on('click', function () {
            var modal = document.getElementById('myModal');
            modal.style.display = "none";
        });

        // Whenever the user clicks on the "delete" button
        $('#eventdelete').on('click', function () {
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
            $('#modalsalerepid').val(this.value);
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
</script>
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
                    <li class="active">Event Schedule</li>   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller event-schedule">
            <div class="container">  
                <form id='createEvent'>
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <div class="row">
                                <div class='col-md-2'>
                                    <div class="form-group">
                                        <div class='input-group date' id='datetimepicker1'>
                                            <input type='text' id="eventstart" class="form-control" placeholder="Event Date" />
                                            <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($role == 'Admin' || $role == 'Sales Manager') { ?>
                                    <div class='col-md-2'>
                                        <div class="form-group">
                                            <select class="form-control" id="salesrepopt">
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
                                <input type="hidden" id="salerepid" value="<?php echo $salesRepDetails['Guid_salesrep']; ?>">
                                <div class='col-md-2'>
                                    <div class="form-group">
                                        <div class="eventtype">
                                            <label><input type="radio" name="eventtype" value="1" checked>BRCA Day</label>
                                        </div>
                                        <div class="eventtype">
                                            <label><input type="radio" name="eventtype" value="2">Health Care Fair</label>
                                        </div>
                                    </div>
                                </div> 
                                <div class='col-md-2 accounttype'>
                                    <div class="form-group">
                                        <select class="form-control" id="accountopt">
                                            <option value="0">Account</option>
                                            <?php
                                            foreach ($accountdt as $acct) {
                                                ?>
                                                <option value='<?php echo $acct['Guid_account']; ?>'><?php echo $acct['account'] . ' - ' . ucfirst(strtolower($acct['name'])); ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class='col-md-2 accounttype'> 
                                    <div class="form-group">
                                        <textarea class="form-control" rows="5" id="comment" placeholder="Comments"></textarea>
                                    </div> 
                                </div>    
                                <button type="submit" id="eventsave" class="btn btn-default">Save</button> 
                            </div>
                            <div class="row healthcare" style="display: none;">
                                <div class='col-md-4'>
                                    <div class="form-group"> <!-- Full Name -->
                                        <input type="text" class="form-control" id="full_name_id" name="full_name" placeholder="Full Name">
                                    </div>	
                                </div>    
                                <div class='col-md-4'>
                                    <div class="form-group"> <!-- Street 1 -->
                                        <input type="text" class="form-control" id="street1_id" name="street1" placeholder="Street address, P.O. box, company name, c/o">
                                    </div>					
                                </div>    
                                <div class='col-md-4'>    
                                    <div class="form-group"> <!-- Street 2 -->
                                        <input type="text" class="form-control" id="street2_id" name="street2" placeholder="Apartment, suite, unit, building, floor, etc.">
                                    </div>	
                                </div>    
                                <div class='col-md-4'>
                                    <div class="form-group"> <!-- City-->
                                        <input type="text" class="form-control" id="city_id" name="city" placeholder="City">
                                    </div>									
                                </div>    
                                <div class='col-md-4'>
                                    <div class="form-group"> <!-- State Button -->
                                        <select class="form-control" id="state_id" name="state">
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
                                </div>    
                                <div class='col-md-4'>
                                    <div class="form-group"> <!-- Zip Code-->
                                        <input type="text" class="form-control" id="zip_id" name="zip" placeholder="zip code">
                                    </div>	
                                </div>    
                            </div>  
                        </div>
                    </div>
                </form>  
                <div class="filterby">
                    <div>                
                        <div>
                            <div>
                                <div class="row">
                                    <div class="col-md-1"><label>Filter by:</label></div>
                                    <div class='col-md-2'>
                                        <div class="form-group">
                                            <select class="form-control" id="salesrepfilter">
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
                                    <div class='col-md-2'>
                                        <div class="form-group">
                                            <select class="form-control" id="accountfilter">
                                                <option value="0">Account</option>
                                                <?php
                                                foreach ($accountdt as $acct) {
                                                    ?>
                                                    <option value='<?php echo $acct['Guid_account']; ?>'><?php echo $acct['account'] . ' - ' . ucfirst(strtolower($acct['name'])); ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="calendar"></div>

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
                                    <div class='col-md-2 modalaccounttype'>
                                        <div class="form-group">
                                            <select class="form-control" id="modalaccountopt">
                                                <option value="0">Account</option>
                                                <?php
                                                foreach ($accountdt as $acct) {
                                                    ?>
                                                    <option value='<?php echo $acct['Guid_account']; ?>'><?php echo $acct['account'] . ' - ' . ucfirst(strtolower($acct['name'])); ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class='col-md-2 modalaccounttype'> 
                                        <div class="form-group">
                                            <textarea class="form-control" rows="5" id="modalcomment" placeholder="Comments"></textarea>
                                        </div> 
                                    </div>  
                                    <button type="submit" id="eventupdate" class="btn btn-default">Update</button>
                                    <button type="submit" id="eventcancel" class="btn btn-default">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="eventdelete" style="border-radius: 2em !important; margin: 7px 0;">Delete</button>
                                </div>
                                <div class="row modalhealthcare" style="display: none;">
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- Full Name -->
                                            <input type="text" class="form-control" id="modalfull_name_id" name="modalfull_name" placeholder="Full Name">
                                        </div>	
                                    </div>    
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- Street 1 -->
                                            <input type="text" class="form-control" id="modalstreet1_id" name="modalstreet1" placeholder="Street address, P.O. box, company name, c/o">
                                        </div>					
                                    </div>    
                                    <div class='col-md-4'>    
                                        <div class="form-group"> <!-- Street 2 -->
                                            <input type="text" class="form-control" id="modalstreet2_id" name="modalstreet2" placeholder="Apartment, suite, unit, building, floor, etc.">
                                        </div>	
                                    </div>    
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- City-->
                                            <input type="text" class="form-control" id="modalcity_id" name="modalcity" placeholder="City">
                                        </div>									
                                    </div>    
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- State Button -->
                                            <select class="form-control" id="modalstate_id" name="modalstate">
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
                                    </div>    
                                    <div class='col-md-4'>
                                        <div class="form-group"> <!-- Zip Code-->
                                            <input type="text" class="form-control" id="modalzip_id" name="modalzip" placeholder="zip code">
                                        </div>	
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
        $('#eventstart').datepicker({
            dateFormat: "yy-mm-dd",
            showOn: 'both',
            buttonImageOnly: true,
            buttonImage: 'http://jqueryui.com/resources/demos/datepicker/images/calendar.gif'
        });
        $('#modaleventstart').datepicker({
            dateFormat: "yy-mm-dd",
            showOn: 'both',
            buttonImageOnly: true,
            buttonImage: 'http://jqueryui.com/resources/demos/datepicker/images/calendar.gif'
        });
    });
</script>
<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>