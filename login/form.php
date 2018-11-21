<?php
	ob_start();
	require_once('config.php');
	require_once('settings.php');
	require_once('functions.php');
?>
<html>
<head>
    <script>
        /*function PrintDoc() {
            var toPrint = document.getElementById('printarea');
            window.print()
            var popupWin = window.open('', '_blank', 'width=900,height=900,location=no,left=200px');
            popupWin.document.open();
            popupWin.document.write('<html><link rel="stylesheet" type="text/css" href="assets/css/forms.css" /></head><body onload="window.print()">')
            popupWin.document.write(toPrint.innerHTML);
            popupWin.document.write('</html>');
            popupWin.document.close();
        }*/
    </script>
</head>
<body>

<style>

    /* .exact, .form * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    } */

    .c2 th:nth-of-type(2), .c2 td:nth-of-type(2), .c3 th:nth-of-type(3), .c3 td:nth-of-type(3),
    .c4 th:nth-of-type(4), .c4 td:nth-of-type(4), .c5 th:nth-of-type(5), .c5 td:nth-of-type(5) {
        text-align: center;
    }

    .sf4 td:nth-of-type(4) {
        font-size: 2.47mm;
        font-weight: normal;
        max-width: 50mm;
    }

    .columns {
        display: flex;
    }

    .columns > .col {
        flex-grow:1;
    }

    .columns.two > .col {
        width: 50%;
    }

    .dashed {
        border: .42 dashed #000; border-top:0; border-bottom:0;
    }

    .geneveda .dashed {
        border-color: #7a68ae;
    }

    .follow-up p {
        font-size: 3.53mm;
        line-height: 5.29mm;
        margin-top: 7.95mm;
        color: #242424 !important;
    }

    .follow-up .account_logo {
        max-width: 54.18mm;
    }

    .follow-up .lab {
        text-align: right;
        margin-bottom: 10.16mm;
    }

    .follow-up .lab > img {
        max-width: 44.36mm;
        display: inline-block;
    }

    .follow-up .header {
        display: flex;
        padding-bottom: 7.19mm;
    }

    .follow-up .header .col.one {
        width: 76.2mm;
    }

    .follow-up .header .col.two {
        flex-grow: 1;
        text-align: right;
    }

    .follow-up .main {
        border: .67mm solid #cbcbcb; border-left:0; border-right: 0;
        padding-bottom: 7.62mm;
    }

    .form p > .labColor, .lC5 td:nth-of-type(5) {
        color: #7a68ae !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .geneveda .ftable > th {
        background: #7a68ae;
    }

    .pBG_size {
        position: relative;
    }

    .pBG {
        width: 100%;
        height:100%;
        position: absolute;
        left:0;
        top:0;
        z-index:-1;
    }

    .ftable {
        position: relative;
        width: 100%;
        font-size: 2.82mm;
        margin-top: 4.23mm;
        font-weight: 600;
        text-align: left;
    }

    .ftable th, .ftable .pBG {
        height: 5.24mm;
    }

    .ftable th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        vertical-align: middle;
    }

    .ftable tr {
        min-height: 5.24mm;
    }

    .ftable td {
        border-bottom: .16mm solid #4c4c4c;
        padding: 1.35mm 0;
        color: #2b2b2b !important;
    }

    .ftable th:first-child, .ftable td:first-child {
        padding-left: 4.23mm;
    }

    #testing_recommended th:last-child, #testing_recommended td:last-child {
        text-align: center;
        width: 28.61mm;
    }

    #testing_recommended td:last-child {
        border-left: .42mm dashed #7a68ae; border-right: .42mm dashed #7a68ae;
        text-align: center;
    }

    .sTable {
        text-align: left;
        width: 100%;
        table-layout: fixed;
        margin-top: 3.81mm;
    }

    .sTable td, .sTable th  {
        border: .16mm solid #4c4c4c; border-top:0; border-left:0;
        width: 100%;
        font-size: 2.82mm;
        padding-left: 3.13mm;
    }

    .sTable td:last-child, .sTable th:last-child {
        border-right:0;
    }

    .sTable th {
        font-weight: bold;
        padding-top: 2.03mm; padding-bottom: 2.03mm;
    }

    .geneveda .sTable th {
        color: #7a68ae !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .sTable td {
        font-weight: 600;
        color: #2b2b2b !important;
        max-width: 33%;
        padding-top: 1.69mm; padding-bottom: 1.69mm;
    }

    .sTable td:first-child, .sTable th:first-child {
        max-width: 28.36mm;
    }

    #not_completed {
        padding-left: 6.77mm;
    }

    #not_completed .sTable {
        margin-top: 9mm;
    }

    #not_recommended .sTable {
        width: calc(100% - 7.62mm);
    }

    #not_completed th:last-child, #not_recommended th:last-child {
        max-width: 21mm;
    }

    .form footer {
        padding-top: 6.94mm;
        font-size: 8pt;
    }

    .gc_title {
        padding-bottom: 5.41mm;
    }

    .gc_title > h4 {
        font-weight: bold;
        font-size: 10pt;
        padding-bottom: 1.27mm;
    }

    .gc_title > em {
        font-size: 8pt;
        color: #242424 !important;
    }

    .columns .foogo {
        flex-grow: 0;
        padding-right: 4.23mm;
    }

    .foogo > img {
        width: 13.88mm;
    }

    .gc_contact {
        border-top: 0.25mm solid #1c1c1c;
        flex-grow: 1;
        display: flex;
        margin-top: 2.11mm;
        position: relative;
        padding-top: 1.86mm;
    }

    .gc_contact > section {
        flex-grow: 1;
        padding-left: 3.81mm;
    }

    .gc_contact::before {
        content:"";
        position: absolute;
        left:-3.72mm; top:-.2mm;
        z-index:1;
        font-size: inherit;
        display: inline-block;
        width: 3.72mm;
        height: .25mm;
        border-top: 0.25mm solid #1c1c1c;
        transform-origin: bottom right;
        transform: rotate(45deg);
    }

    .gc_contact::after, .gc_contact > section::after {
        content:"";
        position: absolute;
        border-radius: 50%;
        width: 0.84mm;
        height: 0.84mm;
        border: .33mm solid #1c1c1c;
        overflow: hidden;
        right:0;
        top: -.5mm;
    }

    .gc_contact > section::after {
        right: auto;
        left:-2.8mm;
        top:-2.8mm;
    }

    .gc_contact h5 {
        text-transform: uppercase;
        font-weight: 600;
    }

    .gc_contact span {
        padding-left: 3.38mm;
    }

    .gc_contact > * {
        line-height: 11pt;
    }

    .gc_contact li {
        position: relative;
    }

    .gc_contact > ul:last-child {
        margin-left: 12.63mm;
        font-weight: 600;
    }

    .gc_contact img {
        position: absolute;
        max-width: 2.62mm;
        max-height: 2.62mm;
        left: -4mm;
        top: .9mm;
    }

    .gc_contact .gc_email {
        left:-4.8mm;
    }

    .sb {
        font-weight: 600;
    }

    .stat_table {
        text-align: left;
        position: relative;
        font-size: 8pt;
        font-weight: 600;
        color: #242424 !important;
    }

    .follow-up .stat_table {
        display: inline-block;
    }

    .stat_table th {
        border-right: .42mm solid #000;
        padding: 1.69mm 2.03mm;
    }

    .stat_table td {
        border-left: .42mm solid #000;
        border-bottom: .25mm solid #000;
        padding: 2.03mm 0;
    }

    .stat_table td:first-child {
        padding-right: 3.55mm; padding-left: 3.55mm;
    }

    .stat_table tr:last-child > td {
        border-bottom: 0;
    }

    .stat_table td:last-child {
        border-right: .42mm solid #000;
        color: #7a68ae !important;
    }

    .geneveda .stat_table th, .geneveda .stat_table td {
        border-color: #7a68ae;
    }

    .side_head {
        position:absolute !important;
        left:-12mm;
        transform: rotate(-90deg);
        bottom: 12mm;
        text-transform: uppercase;
        color: #9e9e9e !important;
        font-weight: bold;
        letter-spacing: .25mm;
    }

    .follow-up .stat_table .pBG {
        width: 12mm;
        height: 25.4mm;
        top: auto;
        left: auto;
        bottom:0;
        right: 11.5mm;
    }


</style>

<input type="button" value="Print" class="btn" onclick="window.print()"/>

<section class="form follow-up geneveda" id="printarea">
    <header class="header">
        <div class="col one">
            <figure class="account_logo"><img src="https://www.mdlab.com/dev/images/practice/22230-LEXINGTON%20OBGYN%20ASSOCIATES.png" alt="Lexington"></figure>

            <p>Thank you for selecting Geneveda to provide Hereditary Breast and Ovarian Cancer (HBOC) Screening for your patients. This report was prepared on <strong><?=date("F j, Y");?></strong>.</p>
        </div>

        <div class="col two">
            <figure class="lab"><img src="https://www.mdlab.com/dev/images/logo_geneveda.png" alt="Geneveda"></figure>

            <table class="stat_table c2 c3">
                <thead>
                <tr>
                    <th><h4 class="side_head">Summary</h4></th>
                    <th><img class="pBG" src="assets/images/swatch_gen_green.png" alt="">Today</th>
                    <th>Total</th>
                </tr>
                </thead>

                <tbody>
                <?php
                if(isset($_POST['today'])){
                    $account = $_POST['account'];
                    $guid_account = $_POST['guid_account'];
                    $_POST['today'] = date("Y-m-d");
                    $today = $_POST['today'];

                    //print_r($_POST);

                    echo get_status_state($db, '0', array('Guid_account'=>$guid_account), array('account_id'=>$guid_account,'status_table'=>'1'), $today);
                }
                ?>

                </tbody>
            </table>
        </div>
    </header>

    <div class="main">
        <p>Based upon the cited clinical policy testing guidelines, testing is <strong class="labColor">recommended</strong> for the following patients:</p>

        <?php
        /*$statuses = array('28' => 'Registered Paient', '36' => 'Completed Questionnaire', '16' => 'Insufficient Informatin' , '29' => 'Medically Qualified' );
        $filterUrlStr = "";
        $content = '';
        foreach ($statuses as $key => $status) {
            $stats1 = get_stats_info($db, $key, FALSE, $searchData);
            $stats2 = get_stats_info_today($db, $key, FALSE, $searchData, $today);
            $content .= "<tr class='parent'>";
            $content .= "<td class='text-left'><span>".$status."</span></td>";
            $content .= '<td><a>'.$stats2['count'].'</a></td>';
            $content .= '<td><a>'.$stats1['count'].'</a></td>';
            $content .= "</tr>";
        } */
        //return $content;

        $specimen = get_stats_info_today(
            $db, 1,
            FALSE,
            array(
                'Guid_account'=>$guid_account,
                'to_date'=>$_POST['to_date'],
                'from_date'=>$_POST['from_date']
            ),
            $today
        );
        //print_r( $specimen );
        ?>

        <table id="testing_recommended" class="ftable c4 c5 c6 lC5">
            <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Guideline Applied</th>
                <th>Guideline Met</th>
                <th>Date Collected</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($specimen['info'])):
                //echo '<pre>';print_r($specimen['info']);echo '</pre>';
                //exit;

                foreach ($specimen['info'] as $key => $value) {
                    $patient_id = $value['Guid_patient'];
                    $sql = "SELECT `firstname_delete`,`lastname_delete`,`dob` FROM `tblpatient` WHERE `Guid_patient`=$patient_id";
                    $test = $db->query($sql, array('patient_id' => $value['Guid_patient']));

                    $Guid_user = $value['Guid_user'];
                    $Qqualify = "SELECT * FROM `tblqualify` WHERE `Guid_user`=$Guid_user";
                    $Rqualify = $db->query($Qqualify);
                    $Guid_qualify = $Rqualify[0]['Guid_qualify'];

                    $insurance = $db->query(
                        "SELECT 
                          `insurance`, `Guid_qualify`, `guideline_met` 
                        FROM 
                          `tblcancerquestion` req 
                        LEFT JOIN 
                          `tblunknownans` wnans 
                        ON 
                          wnans.Guid_question=req.Guid_question 
                        AND 
                          `Guid_qualify`='$Guid_qualify' 
                        ORDER BY 
                          `Date_created` DESC LIMIT 0,1"
                    );

                    echo "<tr>";
                    echo "<td>".$test[0]['firstname_delete']."</td>";
                    echo "<td>".$test[0]['lastname_delete']."</td>";
                    echo "<td>".$test[0]['dob']."</td>";
                    echo "<td>".$insurance[0]['insurance']."</td>";
                    echo "<td>".$insurance[0]['guideline_met']."</td>";
                    echo "<td>".$value['Date_created']."</td>";
                    echo "</tr>";
                }
            endif;
            ?>
            </tbody>
        </table>

        <p><strong>Insufficient information</strong> was available to determine if the following patients met clinical policy testing guidelines:</p>
        <?php $unknown = get_stats_info_today(
            $db,
            31,
            FALSE,
            array(
                'Guid_account'=>$guid_account,
                'to_date'=>$_POST['to_date'],
                'from_date'=>$_POST['from_date']
            ),
            $today
        ); ?>
        <table class="ftable sf4">
            <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Information Needed</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(!empty($unknown['info'])):
                //echo '<pre>';print_r($unknown['info']);echo '</pre>';
                //exit;

                foreach ($unknown['info'] as $key => $value) {
                    $patient_id = $value['Guid_patient'];
                    $sql = "SELECT `firstname_delete`,`lastname_delete`,`dob` FROM `tblpatient` WHERE `Guid_patient`=$patient_id";
                    $test = $db->query($sql, array('patient_id' => $value['Guid_patient']));

                    //echo '<pre>';print_r($unknown['info']);echo '</pre>';
                    //exit;
                    $Guid_user = $value['Guid_user'];
                    $Qqualify = "SELECT * FROM `tblqualify` WHERE `Guid_user`=$Guid_user";
                    $Rqualify = $db->query($Qqualify);
                    $Guid_qualify = $Rqualify[0]['Guid_qualify'];
                    //echo '<pre>';print_r($Guid_qualify);echo '</pre>';
                    //exit;

                    $sqlSSQualify = "SELECT ssq.* FROM tbl_ss_qualify ssq WHERE ssq.Guid_qualify=:Guid_qualify  ORDER BY Date_created DESC";
                    $ssQualifyResult = $db->query($sqlSSQualify, array('Guid_qualify'=>$Guid_qualify));
                    //echo '<pre>';print_r($ssQualifyResult);echo '</pre>';
                    //exit;

                    foreach ($ssQualifyResult as $k=>$v){
                        $Guid_qualify = $v['Guid_qualify'];
                        $Date_created = $v['Date_created'];
                        //$qFam = $db->query("SELECT * FROM `tblqualifyfam` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
                        //$queryPers = "SELECT * FROM `tbl_ss_qualifypers` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created";
                        //$qPers = $db->query($queryPers, array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
                        $qAns = $db->query("SELECT * FROM `tbl_ss_qualifyans` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));

                        $qualifyedClass = "";
                        if($v['qualified'] == 'No'){
                            $qualifyedClass = "mn no";
                        } elseif ($v['qualified'] == 'Yes') {
                            $qualifyedClass = "mn yes";
                        }


                        $personal = "<label>Personal: </label> ";
                        $family = "<label>Family: </label> ";
                        $out = '';
                        if(!empty($qAns)){
                            $ansPersonal = "";
                            $ansFam = "";
                            foreach ($qAns as $k=>$v) {
                                $ansPersonalType =  $v['cancer_personal'];
                                if(strpos(trim($ansPersonalType), ' ') == false){
                                    $ansPersonalType .=  " Cancer";
                                }
                                $ansPersonal .= $ansPersonalType;
                                if($v['age_personal'] && $v['age_personal']!=""){
                                    $ansPersonal .= " (Age ". $v['age_personal']."); ";
                                }
                                if($v['age_personal']==""&&$v['age_personal']==""){
                                    $ansPersonal = "No Cancer History";
                                }


                                $ansFamType =  $v['cancer_type'];
                                if(strpos(trim($ansFamType), ' ') == false){
                                    $ansFamType .=  " Cancer";
                                }
                                $ansFam .= $v['relative'].", ".$ansFamType;
                                if($v['age_relative'] && $v['age_relative']!=""){
                                    $ansFam .= " (Age ". $v['age_relative']."); ";
                                }
                                if($v['cancer_type']==""&&$v['relative']==""){
                                    $ansFam = "No Cancer History";
                                }

                            }
                            $ansPersonal = rtrim($ansPersonal,'; ');
                            $ansFam = rtrim($ansFam,'; ');


                            $out .= $personal.$ansPersonal;
                            $out .= $family.$ansFam;
                        } else {
                            $out .= $personal." No Cancer History ";
                            $out .= $family." No Cancer History ";
                        }


                    }

                    echo "<tr>";
                    echo "<td>".$test[0]['firstname_delete']."</td>";
                    echo "<td>".$test[0]['lastname_delete']."</td>";
                    echo "<td>".$test[0]['dob']."</td>";
                    echo "<td>".$out."</td>";
                    echo "</tr>";
                }
            endif;
            ?>
            </tbody>
        </table>

        <div id="not_recommended" class="columns two">
            <div class="col">
                <p>The following patients were also screened and based upon the information provided, screening for BRCA, HBOC, and/or Lynch Syndrome is <strong>not</strong> recommended:</p>
                <?php $notqual = get_stats_info_today(
                    $db,
                    30,
                    FALSE,
                    array(
                        'Guid_account'=>$guid_account,
                        'to_date'=>$_POST['to_date'],
                        'from_date'=>$_POST['from_date']
                    ),
                    $today
                ); ?>
                <table class="sTable">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>DOB</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($notqual['info'])):
                        foreach ($notqual['info'] as $key => $value) {
                            $patient_id = $value['Guid_patient'];
                            $sql = "SELECT `firstname_delete`,`lastname_delete`,`dob` FROM `tblpatient` WHERE `Guid_patient`=$patient_id";
                            $test = $db->query($sql, array('patient_id' => $value['Guid_patient']));

                            echo "<tr>";
                            echo "<td>".$test[0]['firstname_delete']."</td>";
                            echo "<td>".$test[0]['lastname_delete']."</td>";
                            echo "<td>".$test[0]['dob']."</td>";
                            echo "</tr>";
                        }
                    endif;
                    ?>
                    </tbody>
                </table>
            </div>

            <div id="not_completed" class="col">
                <p>The following patients initiated, but did not complete the questionnaire:</p>
                <?php $incomplete = get_stats_info_today(
                    $db,
                    16,
                    FALSE,
                    array(
                        'Guid_account'=>$guid_account,
                        'to_date'=>$_POST['to_date'],
                        'from_date'=>$_POST['from_date']
                    ),
                    $today
                ); ?>
                <table class="sTable">
                    <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>DOB</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(!empty($incomplete['info'])):
                        foreach ($incomplete['info'] as $key => $value) {
                            $patient_id = $value['Guid_patient'];
                            $sql = "SELECT `firstname_delete`,`lastname_delete`,`dob` FROM `tblpatient` WHERE `Guid_patient`=$patient_id";
                            $test = $db->query($sql, array('patient_id' => $value['Guid_patient']));

                            echo "<tr>";
                            echo "<td>".$test[0]['firstname_delete']."</td>";
                            echo "<td>".$test[0]['lastname_delete']."</td>";
                            echo "<td>".$test[0]['dob']."</td>";
                            echo "</tr>";
                        }
                    endif;
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p>Access your physician portal any time at <span class="labColor">https://www.mdlab.com/questionnaire/login</span>. Please contact me for any additional details or if you do not have access to your portal.</p>

    <footer>
        <section class="gc_title">
            <h4>Brandon Franklin, PhD</h4>
            <em>Specialty Sales Consultant, Division of Genetics and Oncology</em>
        </section>

        <div class="columns">
            <figure class="foogo col"><img src="assets/images/icon_print.svg" alt=""></figure>

            <div class="gc_contact col">
                <section>
                    <h5>Medical Diagnostic Laboratories, L.L.C.</h5>
                    <span>www.<b class="sb">mdlab</b>.com</span>
                </section>

                <ul>
                    <li><img src="images/icon_address.svg" alt="">2439 Kuser Rd</li>
                    <li>Hamilton, NJ 08690</li>
                </ul>

                <ul>
                    <li><img src="images/icon_phone.svg" alt="">888.414.3237</li>
                    <li><img class="gc_email" src="images/icon_email.svg" alt="">bfranklin@mdlab.com</li>
                </ul>
            </div>
        </div>
    </footer>
</section>
</body>
</html>
