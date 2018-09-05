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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/eventschedule/css/fullcalendar.css" />
<link rel="stylesheet" href="assets/eventschedule/css/bootstrap-datetimepicker.min.css">
<script src="assets/eventschedule/js/jquery.min.js"></script>
<script src="assets/eventschedule/js/jquery-ui.min.js"></script>
<script src="assets/eventschedule/js/moment.min.js"></script>
<script src="assets/eventschedule/js/fullcalendar.min.js"></script>
<script src="assets/eventschedule/js/bootstrap-datetimepicker.min.js"></script>

<script>

    $(document).ready(function () {
        var cursource = 'eventload.php';
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
            },
            defaultView: 'agendaWeek',
            eventSources: cursource,
            selectable: true,
            selectHelper: true,
            editable: false,
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
                var logo = "";
                var account = "";
                var name = "";
                var salesrep = "";
                if (event.logo)
                    logo = '<div class="fc-logo">' + event.logo + '</div>';
                if (event.account)
                    account = event.account + ' - ';
                if (event.name)
                    name = event.name;
                if (event.salesrep)
                    salesrep = '<div class="fc-salesrep">' + event.salesrep + '</div>';
                var cmts = '';

                var view = $('#calendar').fullCalendar('getView');
                if (view.name == 'agendaWeek' && event.comments)
                    cmts = '<div class="fc-comments">' + event.comments + '</div>';

                var content = '<a class="fc-day-grid-event fc-h-event fc-event fc-start fc-end fc-draggable">' +
                        '<div class="fc-content">' +
                        '<div class="fc-title">' + event.title + '</div>' +
                        logo +
                        '<div class="fc-name">' + account + name + '</div>' +
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
            },

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
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>