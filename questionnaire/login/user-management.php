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
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}
$users = getUsersAndRoles($db);
$thisMessage="";
?>
<main class="full-width">
    <?php 
    
    if(isset($_GET['update']) ){ 
        $thisMessage = "Changes have been saved";
    }
    if($thisMessage != ""){  ?>
    <section id="msg_display" class="show success">
        <h4><?php echo $thisMessage;?></h4>
    </section>
    <?php } ?> 
    <div class="box full visible ">  
        <section id="palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">User Management</li>                   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller">  
            <div class="row">                   
                <div class="col-md-12">
                    <a class="add-new-device" href="<?php echo SITE_URL; ?>/user-management.php?action=add">
                        <span class="fas fa-plus-circle" aria-hidden="true"></span> Add
                    </a>
                </div>                 
            </div>               
            <div class="row">
                <div class="col-md-12">
                    <table id="dataTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th  class="actions">ID</th>
                                <th>Email</th>
                                <!--<th>Type</th>-->
                                <th>Role</th>
                                <th class="noFilter actions text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user){ ?>
                            <tr>
                                <td><?php echo $user['Guid_user']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <!--<td><?php //echo $user['user_type']; ?></td>-->
                                <td><?php echo $user['role']; ?></td>
                                <td class="text-center">                                        
                                    <a href="<?php echo SITE_URL; ?>/user-management.php?action=update&id=<?php echo $user['Guid_user']; ?>">
                                        <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                    </a>                                       
                                    <a onclick="javascript:confirmationDeleteUser($(this));return false;" href="<?php echo SITE_URL; ?>/user-management.php?delete=<?php echo $user['Guid_user']; ?>&id=<?php echo $user['Guid_user']; ?>">
                                        <span class="far fa-trash-alt" aria-hidden="true"></span> 
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>           
        </div>
    </div>
</main>


<?php 
    $first_name = "";
    $last_name = "";
    $photo_filename = "";
    
    if(isset($_POST['save_user'])){
        extract($_POST);
        $userData['email'] = $email;
        if($_POST['password'] != ""){
            $userData['password'] = encode_password($password);
        }        
        if($Guid_role != ""){
            if($Guid_role=='1'){
                $userData['user_type'] = 'admin';
            } elseif ($Guid_role=='2') {
                $userData['user_type'] = 'provider';
            } elseif ($Guid_role=='3') {
                $userData['user_type'] = 'patient';
            } elseif ($Guid_role=='4') {
                $userData['user_type'] = 'salesrep';
            } elseif ($Guid_role=='5') {
                $userData['user_type'] = 'salesmgr';
            }            
        }
        
        if($Guid_user == ""){ //insert User
//            $userData['user_type'] = 'provider';
//            $userData['Date_created'] = date('Y-m-d H:i:s');        
//            $inserUser = insertIntoTable($db, 'tbluser', $userData);
//            $data['Guid_user']= $inserUser['insertID'];
        }else{ //update
            $userData['Date_modified'] = date('Y-m-d H:i:s');
            $whereUser = array('Guid_user'=>$Guid_user);
            $updateUser = updateTable($db, 'tbluser', $userData, $whereUser);
            
            
            saveUserRole($db, $Guid_user, $Guid_role);
            $fName = isset($_POST['first_name']) ? $_POST['first_name'] : "";
            $lName = isset($_POST['last_name']) ? $_POST['last_name'] : "";
            if($Guid_role=='3'){
                $userDetails = array('firstname'=>$fName, 'lastname'=>$lName);                    
            } else {
                $userDetails = array('first_name'=>$fName, 'last_name'=>$lName);
                if($_FILES["photo_filename"]["name"] != ""){
                    $fileName = $_FILES["photo_filename"]["name"];        
                    $userDetails['photo_filename'] = $fileName;
                    $uploadMsg = uploadFile('photo_filename', 'images/users/');
                }
            }
            saveUserDetails($db, $Guid_user, $Guid_role, $userDetails);
            
            Leave(SITE_URL."/user-management.php?update");
            
        }
    }

    if(isset($_GET['action']) && $_GET['action'] !="" ){ 
        $userID = $_GET['id'];
        $user = getUserAndRole($db, $userID);        
        $allRoles = $db->selectAll('tblrole', ' ORDER BY role ASC');  
        if($user['role']){
            $userDetails = getUserDetails($db, $user['role'], $userID);
            if($userDetails){
                extract($userDetails);
            }
        }else{
            
        }
        
    
?>
<div id="patient-info-box" class="modalBlock">
    <div class="contentBlock">
        <a class="close" href="<?php echo SITE_URL."/user-management.php"; ?>">X</a>        
        <h2 class="text-center">User Info</h2>
        <form action="" method="POST" enctype="multipart/form-data"> 
            <div class="row">
                
                <input type="hidden" name="Guid_user" value="<?php echo $user['Guid_user']; ?>" />
                
                <div class="col-md-6">
                    <?php if(!$user['role']==NULL) { ?>
                    <div class="f2 <?php echo ($first_name!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="first_name"><span>First Name</span></label>
                        <div class="group">
                            <input name="first_name" value="<?php echo $first_name; ?>" type="text" class="form-control" id="first_name" placeholder="First Name">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                    <div class="f2 <?php echo ($last_name!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="last_name"><span>Last Name</span></label>
                        <div class="group">
                            <input name="last_name" value="<?php echo $last_name; ?>" type="text" class="form-control" id="last_name" placeholder="Last Name">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="f2 <?php echo ($user['email']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="email"><span>Email</span></label>
                        <div class="group">
                            <input name="email" value="<?php echo $user['email']; ?>" type="text" class="form-control" id="email" placeholder="Email">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                    <div class="f2">
                        <label class="dynamic" for="password"><span>Password</span></label>
                        <div class="group">
                            <input name="password" type="password" class="form-control" id="password" placeholder="Password">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                     
                    <div class="f2 <?php echo ($user['role']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="reason_not"><span>User Roles</span></label>
                        <div class="group">
                            <select id="user_type" name="Guid_role" class="no-selection">
                                <option value="">User Roles</option>
                                <?php foreach ($allRoles as $role){ ?>
                                    <option <?php echo ($user['role']==$role['role']) ? " selected": ""; ?> value="<?php echo $role['Guid_role']; ?>"><?php echo $role['role']; ?></option>   
                                <?php } ?>
                            </select>
                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>          
                    <?php if(!$user['role']==NULL && $role != 'Patient') { //For patients we dont have photo field in DB ?>
                    <div class="row">
                        <div class="col-md-10">
                            <div class="f2 <?php echo ($photo_filename!="")?"valid show-label":"";?>">
                                <label class="dynamic" for="photo"><span>Photo</span></label>
                                <div class="group">
                                    <input id="file" value="<?php echo $photo_filename; ?>" name="photo_filename" class="form-control pT-5" type="file" placeholder="Photo"/>
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                            </div>                    
                        </div>
                        <?php $image = (!isset($photo_filename) || $photo_filename=="")?"/assets/images/default.png":"/images/users/".$photo_filename; ?>
                        <div id="profile-pic" class="col-md-2 pT-30">
                            <img id="image" width="40" src="<?php echo SITE_URL.$image; ?>" />
                        </div>
                    </div>    
                    <?php } ?>
                </div>
                
            </div>
            
            <div class="row actionButtons">
                <div class="col-md-12 pT-20">
                    <button name="save_user" type="submit" class="btn-inline">Save</button>
                </div>
            </div>
        </form>   
    </div>    
</div>
<?php } ?>

<?php require_once('scripts.php');?>
<script type="text/javascript">  
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            //lengthChange: false,
            "pageLength": 50,
            "aoColumnDefs": [
              { 
                  
                  //"bSortable": false, 
                  "aTargets": [ '3' ] } 
            ]
        });   
    }
</script>
<?php require_once('footer.php');?>