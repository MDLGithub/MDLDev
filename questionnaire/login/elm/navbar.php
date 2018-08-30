
 <!-- Static navbar -->
      <nav class="navbar navbar-default">
	<div class="container-fluid">
	  <div class="navbar-header">
	    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
	      <span class="sr-only">Toggle navigation</span>
	      <span class="icon-bar"></span>
	      <span class="icon-bar"></span>
	      <span class="icon-bar"></span>
	    </button>
	    <a class="navbar-brand" href="#">mdLab</a>
	  </div>
	  <div id="navbar" class="navbar-collapse collapse">
	    <ul class="nav navbar-nav navbar-right">
		<li><a>Hello, Admin.</a></li>
		<li class="dropdown">
		    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Settings <span class="caret"></span></a>
		    <ul class="dropdown-menu">
			<li><a href="<?php echo baseUrl(); ?>/dev/login/elmira/url-configuration.php">URL Configuration</a></li>
						<li><a href="<?php echo baseUrl(); ?>/dev/login">BRCAcare&reg; Portal</a></li>
		       <!-- <li><a href="<?php echo baseUrl(); ?>/dev/login/elmira/account-config.php">Account Configuration</a></li>
			<li><a href="<?php echo baseUrl(); ?>/dev/login/elmira/devices.php">Devices</a></li>-->
			<li role="separator" class="divider"></li>
			<li><a href="<?php echo baseUrl(); ?>/dev/login">LOGOUT</a></li>
		    </ul>
		</li>
	    </ul>
	  </div><!--/.nav-collapse -->
	</div><!--/.container-fluid -->
      </nav>