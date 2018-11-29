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
<script src="assets/eventschedule/js/moment.min.js"></script>
<script src="assets/eventschedule/js/bootstrap-datetimepicker.min.js"></script>


<style>

        .form-container{
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-content: center;
            height: 100%;
        }

        #accordion {
            list-style: none;
            margin: 0;
            padding: 0;
            height: 500px;
            overflow: hidden;
        }

        #accordion::after {
            content: '';
            position: absolute;
            background: rgb(28,72,123);
            width: 30px;
            height: 500px;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        #accordion .form-info-container{
            height: 100%;
            background: grey;
            background: url("assets/images/form_grey_background.png");
            box-shadow: inset 0px 0px 21px 0px rgba(74, 74, 74, 0.11);
            border-bottom-left-radius: 10px;
            position: relative;

        }

        #accordion .save{
            background: linear-gradient(to bottom, rgba(255,255,255,1) 46%,rgba(224,224,224,1) 64%,rgba(243,243,243,1) 100%);
            border: 0;
            outline: 0;
            cursor: pointer;
            border: 1px solid #b7b7b7;
            box-shadow: inset 0 0 30px rgba(255,255,255,.8), 0 1px 6px rgba(0,0,0,.31);
            padding: .857em 2.2em .857em 4em;
            border-radius: 2em;
            width:20px;
            float:right;
        }

        #accordion .page-count{
            float:right;
        }

        .form-info-container .buttons{
                display: block;
                width: 100%;
                height: 50px;
                float: right;
                /* bottom: 0; */
                position: absolute;
                /* bottom: 0; */
                /* right: 50px; */
                top: 430px;
        }

        #accordion .form-info{
            padding:20px;
            margin-left:50px;
        }

        #accordion .form-info strong{
            font-size: 30px;
            font-family: "Open Sans";
            color: rgb(28, 72, 123);
            font-weight: bold;
            line-height: 1.333;
        }
         
        #accordion li {
            float: left;
            display: block;
            height: 100%;
            width: 50px;
            /*padding: 15px 0 0 0;*/
            overflow: hidden;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            line-height: 1.5em;
        }
         
        #accordion li img {
            border: none;
            float: left;
            margin: -15px 15px 0 0;
        }
         
        #accordion li.active {
            width: 80%;
        }

        #forms{
            width: 270px;
            height: 62px;
            box-shadow: 0px 0px 2.08px 1.92px rgba(0, 0, 0, 0.15);
            background: linear-gradient( 90deg, rgb(255,255,255) 0%, rgb(227,255,255) 0%, rgb(218,223,227) 0%, rgb(255,255,255) 100%);
            display: inline-block;
            position: relative;
            top: 15px;
        }

        #forms h2{
            font-size: 30px;
            font-family: "Open Sans";
            color: rgb(71, 71, 71);
            font-weight: bold;
            font-style: italic;
            line-height: 1.333;
        }

        #form-details{
            background: linear-gradient( rgb(218,223,227) 15%, rgb(255,255,255) 100%);
            width: 145px;
            height: 75px;
            border-radius: 10px 10px 0px 0px;
            box-shadow: 0px 0px 2.08px 1.92px rgba(0, 0, 0, 0.2);
            display:inline-block;
        }

        #form-details h2{
            font-size: 30px;
            font-family: "Open Sans";
            color: rgb(28, 72, 123);
            font-weight: bold;
            line-height: 1.2;
            text-align: center;
        }

        #form-bar{
            width: 5%;
            min-width:50px;
            /*background: linear-gradient( 90deg, rgb(218,223,227) 15%, rgb(255,255,255) 100%);*/
            background: url("assets/images/form_bar_background.png") no-repeat;
            border-radius:10px 0 0 10px;
            background-size:cover;
            display: block;
            border: none;
            border-right: 1px solid #fff;
            float: left;
            /*margin: -15px 15px 0 0;*/
            height: 100%;
            box-shadow: 4.988px 0.349px 4px 0px rgba(0, 0, 0, 0.2);
            position:relative;
            z-index:1;
        }

        #form-bar h2{
            writing-mode: vertical-rl;
            font-size: 22px;
            font-family: "Open Sans";
            color: rgb(28, 72, 123);
            font-weight: bold;
            line-height: 1.2;
            transform: rotate(180deg);
            height: 100%;
            text-indent:10px;
            padding-right:10px;
        }

        .f2 input, .f2 select, .f2 textarea{
            background:white;
        }

</style>

<script>

    
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
            <a href="<?php echo QUESTIONNAIRE_URL; ?>" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller event-schedule">
            <div class="container form-container" style="margin:auto"> 

                <div>
                    <div id = "form-details">
                        <h2>Details</h2>
                    </div>
                    <div id = "forms">
                        <h2>Forms</h2>
                    </div>
                <ul id="accordion">
                  <li>
                    <div id = "form-bar">
                        <h2>Patient Demographics</h2>
                    </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Patient Demographics</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                        <div class = "buttons">
                            <div class = "page-count"><p>Page 1 of 4</p></div>
                            <div class = "save">Save</div>
                        </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>Insurance</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info">
                        <strong>Insurance</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>Test</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info">
                        <strong>Test</strong><br/>
                            <div class="f2 required col-md-4">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>GC</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info">
                        <strong>GC</strong><br/>
                            <div class="f2 required col-md-4">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                   </div>
                </li>

              </ul>

          </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
$(document).ready(function(){
 
    activeItem = $("#accordion li:first");
    $(activeItem).addClass('active');
 
    $("#accordion li").click(function(){
        $(activeItem).animate({width: "50px"}, {duration:300, queue:false});
        $(this).animate({width: "80%"}, {duration:300, queue:false});
        activeItem = this;
    });
 
});

</script>

<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>