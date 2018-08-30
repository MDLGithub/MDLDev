<?php  
ob_start();
    require_once('settings.php'); 
    require_once('config.php'); 
    require_once 'header.php';
print_r($_SESSION);
    if(!isUserLogin()){
        Leave(SITE_URL.'/questionnaire/login/');
    }

    if(isset($_GET['logout'])){
        doLogout();
        Leave(SITE_URL);
    }
     if(isset($_GET['delete-config']) &&$_GET['delete-config'] != '' ){
        deleteUrlConfig($db, $_GET['delete-config']);
        Leave(SITE_URL.'/questionnaire/login/elm/url-configuration.php');
    }
  
    $userID = getThisUserID();
	
    $urlConfgs = getUrlConfigurations($db, $userID);
    $accounts = $db->selectAll('tblaccount');
    $sources = $db->selectAll('tblsource');
    $devices = $db->selectAll('tbldevice', ' WHERE url_flag=1 ORDER BY serial_number ASC ');
   
    $lastConfigData = getLastUrlConfig($db);
    $urlData = $lastConfigData;
   $currentUserId = $_SESSION['user']['id'];
   $urlMain = "https://www.mdlab.com/questionnaire";
   $urlStr = "";
   $generateUrlLink = $urlMain;
   if (isset($_POST['url_config']) && $_POST['url_config']=='1'){
       extract($_POST);
       $urlStr .= ($logo=='gen') ? 'co=gen&' : '';
       $urlStr .= ($ln =='pin') ? 'ln=pin&' : '';
       $urlStr .= ($an!=''&&$an!=0) ? 'an='.$an.'&' : '';
       $urlStr .= ($lc!=''&&$lc!='0') ? 'lc='.$lc.'&' : '';
       $urlStr .= ($dv!=''&&$dv!=0) ? 'dv='.$dv.'&' : '';
    
       $urlStr =  rtrim($urlStr,"&");
       
       $getAccountId = get_field_value($db, 'tblaccount', 'Guid_account', " WHERE account=$an");
       if ($urlStr != ''){		   
            $data = array(
                'currentUserId'=>$currentUserId,
                'geneveda' => ($logo=='gen') ? 1 : 0,
                'account' => ($an!=''&&$an!=0) ? $getAccountId['Guid_account'] : '',
                'location' => ($lc!=''&&$lc!='0') ? $lc : 'W',
                'pin' => ($ln =='pin') ? 1 : 0,
                'device_id' => ($dv!=''&&$dv!=0) ? $dv : ''
            ); 
            
            $checkSettings = validateSettings($db, $data);            
            if($checkSettings=='1'){
                $saveSettings = saveUrlSettings($db, $data);
                if($saveSettings && $saveSettings['status']=='1'){
                    $generateUrlLink .= '/?'.$urlStr;
                }
            }else{
                $generateUrlLink .= '/?'.$urlStr;
            }
            
        }
   }
   $accountProviders = '';
?>


<div class="container">
      <!-- Static navbar -->
      <?php require_once 'navbar.php';?> 

      <div class="url_config_box">
        <div class="row">
          <div class="col-md-12 text-center">
            <h2 class="page-title-1">URL Configuration</h2><br/>
          </div>
        </div>
        <div class="row">
          <div class="col-md-5">
            <h4>Use Previous Settings</h4>
            
            <table class="table ">
                
                <thead class="thead-dark">
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Logo</th>
                        <th>Account</th>
                        <th>Location</th>
                        <th>Login</th>
                        <th>Action</th>
                    </tr>
                </thead>                
            
            <?php 
            if (isset($_POST['url_config']) && $_POST['url_config']=='1'){ 
                $userID = getThisUserID();
                $urlConfgs = getUrlConfigurations($db, $userID);
            }
                $i = 1;
                foreach ($urlConfgs as $k=>$v){ 
                    $cntlogo = ($v['geneveda']==0) ? 'MDL': 'Geneveda';
                    $cntAccount = $v['account']."-".$v['name'];
                    $cntDesc = $v['description'];
                    $cntPin = $v['pin'] ? "Pin" : 'Pass';
            ?>
                <tr>
                    <td>
                        <input type="radio" id="<?php echo $v['id']; ?>" class="url_config" name="url_config" > 
                    </td>
                    <td ><?php echo $i; ?></td>
                    <td><?php echo $cntlogo;?></td>
                    <td><?php echo ($cntAccount && $cntAccount !='-') ? $cntAccount : "N/A";?></td>
                    <td><?php echo $cntDesc ? $cntDesc : "";?></td>
                    <td><?php echo $cntPin; ?> </td>
                    
                    <td class="text-center"> 
                        <a onclick="javascript:confirmationDelete($(this));return false;" href="?delete-config=<?php echo $v['id']?>&id=<?php echo $i; ?>">
                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                        </a>
                    </td>
                </tr>
           
            <?php 
                $i++;                
                } 
            ?>
                
                
            </table>
          </div>
 
          <div class="col-md-7">
            <h4>Select Settings</h4>
            <div class="row">
              <div class="col-md-6">
                <form method="POST" id="url-config-settings">
                  <fieldset>
                    <div class="radio">                        
                      <label class="genevedaLogo">
                          <input id="geneveda" name="logo" value="gen" type="radio" checked="" >
                          <span class="image" ></span>
                      </label>                       
                    </div>
                    <div class="radio">
                        <label class="mdlLogo">
                            <input id="mdl" name="logo" value="" type="radio" <?php echo (isset($_POST['logo']) && $_POST['logo'] != 'gen') ? 'checked' : '';?>  >
                            <span class="image" ></span>
                        </label>                        
                    </div>
                    <div class="form-group">
                       <select name="an" id="account" class="form-control">
                           <option value="0">Account</option>
                        <?php
                            foreach ($accounts as $k=>$v){
                                $isAccount = ( isset($an) && ($an==$v['account']) ) ? ' selected' : '';
                            //$accountActive = ($lastConfigData['account']==$v['Guid_account']) ? $v : '';
                            //$accountProviders = ($lastConfigData['account']==$v['Guid_account']) ? get_active_providers($db, $v['account']) : '';
                           
                        ?>
                            <option <?php echo $isAccount; ?> value="<?php echo $v['account'];?>" ><?php echo $v['account']." - ".$v['name'];?></option>
                        <?php } ?>
                      </select>
                    </div>                      
                    <div class="form-group">
                       <select name="lc" id="location" class="form-control">
                          <option value="0">Originating Location</option>
                        <?php
                            foreach ($sources as $k=>$v){
                            $isLocation = ( isset($lc) && $lc==$v['code']) ? ' selected' : '';
                            
                        ?>
                            <option <?php echo $isLocation; ?> value="<?php echo $v['code'];?>" ><?php echo $v['description']; ?></option>
                        <?php } ?>
                      </select>
                    </div>
                    <div class="radio">
                      <label>
                          <input type="radio" name="ln" id="pin" value="pin" checked="" >
                            Pin
                      </label>
                    </div>
                    <div class="radio">
                      <label>
                        <input type="radio" name="ln" id="pass" value="" <?php echo (isset($ln) && $ln=='')? " checked" : ""; ?> >
                        Password
                      </label>
                    </div>

                     <div class="form-group">
                        <select name="dv" id="deviceId" class="form-control">
                          <option>Device ID</option>
                            <?php 
                            foreach ($devices as $k=>$v){ 
                                $isDevice = ( isset($_POST['dv']) && $_POST['dv'] == $v['id']) ? ' selected' : '';
                            ?>
                            <option <?php echo $isDevice; ?> value="<?php echo $v['id'];?>" ><?php echo $v['serial_number']." - ".$v['device_type'];?></option>
                        <?php } ?>
                      </select>
                    </div>
                      
                    <button name="url_config" value="1" type="submit" class="btn btn-info">Generate URL</button>                  
                  </fieldset>
              </div>
              <div class="col-md-6">
                  <div class="row">
                      <div class="col-md-6">
                          <p id="officeLogo">
                              <?php 
                                if( isset($_POST['an']) && $_POST['an'] != "0"){
                                    $thisAccountId = $_POST['an'];
                                    $thisAccount = get_field_value($db, 'tblaccount', 'account', " WHERE account=$thisAccountId");
                                    $accountActive = getAcount($db, $thisAccount['account']);
                                    $accountActive = $accountActive[0];
									
                                    if($accountActive!=''){
                                         echo "<img src='/images/practice/".$accountActive['logo']."' />"; 
                                    }
                                }                             
                              ?>
                          </p>
                      </div>
                      <div class="col-md-6">
                          <h5 id="officeAddressLabel" class="addressTitle">
                              <?php  if(isset($accountActive) && $accountActive!=''){ echo "Address"; }?>
                          </h5>
                          <p id="officeAddress">
                              <?php 
                              if(isset($accountActive) && $accountActive!=''){
                                  echo $accountActive['address']."<br/>".$accountActive['city'].", ".$accountActive['state']." ".$accountActive['zip'];
                              }
                              ?>
                          </p>
                      </div>
                  </div>
                  <h5 id="physiciansListLabel">
                      <?php  if(isset($accountActive) && $accountActive!=''){ echo '<p class="providersTitle">Health Care Providers</p>'; }?>
                  </h5>
                  <p id="physiciansList">
                    <?php 
                        if(isset($accountActive) && $accountActive!=''){
                            $accountProviders = get_active_providers($db, 'id', $thisAccount['account']);
                            if($accountProviders !=''){
                                foreach ($accountProviders as $k=>$v){
                                    echo "<p>".$v['name'].'</p>';
                                }
                            }
                        }
                    ?>
                  </p>
              </div>
            </div>
            
          </form>
          </div>


        </div>

          <div class="row">
              <div class="col-md-2"></div>
              <div class="col-md-8 text-center">
                  <br/>
                  <?php if(isset($_POST['url_config']) && $generateUrlLink!=''){ ?>
                  <a id="urlLink" target="_blank" href="<?php echo $generateUrlLink; ?>"> 
                    Link: 
                    <?php echo $generateUrlLink ?>
                  </a>
                  <?php } ?>
              </div>
          </div>
    </div>


    </div>

<?php require_once 'footer.php';?>