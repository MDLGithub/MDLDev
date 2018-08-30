<?php 

$showMsg = doLogin($db);

if(isUserLogin()){
    Leave('url-configuration.php');
}
require_once './header.php';
?>

<div class="loginBG">
    <div class="container">
        <?php if( $showMsg && $showMsg != "" ){ ?>	
                <div class="warning">
                  <strong>Warning!</strong> Username or password is not correct.
                </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-4 col-md-offset-7">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-lock"></span> Login</div>
                    <div class="panel-body">
                        <form class="form-horizontal" id="signup" action="" method="post">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">
                                Email</label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control" id="inputEmail3" placeholder="Email" value="admin@mdlab.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">
                                Password</label>
                            <div class="col-sm-9">
                                <input type="password" name="password" class="form-control" id="inputPassword3" placeholder="Password" value="admin" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"/>
                                        Remember me
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-offset-3 col-sm-9">
                                <input class="btn btn-success btn-sm" id="submit" type="submit" value="Sign in" name="login" />

                                     <button type="reset" class="btn btn-default btn-sm">
                                    Reset</button>
                            </div>
                        </div>
                        </form>
                    </div>
                    <div class="panel-footer">
                        Not Registred? <a href="http://www.jquery2dotnet.com">Register here</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once './header.php'; ?>