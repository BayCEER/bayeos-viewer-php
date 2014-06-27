<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BayEOS Server</title>


<meta name="layout" content="main">
<link rel="stylesheet" href="css/signin.css">


<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="js/rickshaw.min.css">
<link rel="stylesheet" href="css/main.css">
<script type="text/javascript" src="js/jstz.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="/javascript/jquery/jquery.min.js"></script>
<script type="text/javascript"
	src="/javascript/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet"
	href="/javascript/jquery-ui/css/smoothness/jquery-ui.min.css">
<script type="text/javascript" src="js/jquery.datetimeentry.pack.js"></script>
<script type="text/javascript"
	src="/javascript/jquery-ui/jquery.mousewheel.js"></script>
<style type="text/css">
@import "js/jquery.datetimeentry.css";
</style>


</head>
<body>
	<div id="wrap">
		<nav class="navbar navbar-default">
			<div class="container">
				<div class="navbar-header">
					<?php if(isset($_SESSION['bayeosauth'])){?>
					<button type="button" class="navbar-toggle" data-toggle="collapse"
						data-target=".navbar-ex1-collapse">
						<span class="sr-only">Toggle navigation</span> <span
							class="icon-bar"></span> <span class="icon-bar"></span> <span
							class="icon-bar"></span>
					</button>
				<?php }?>

					<a href="./" class="navbar-brand"><strong>BayEOS</strong> Server</a>
				</div>
				<div class="collapse navbar-collapse navbar-ex1-collapse">
					<?php if(isset($_SESSION['bayeosauth'])){?>
					<ul class="nav navbar-nav">
						<?php 
						$nav=array('Folders'=>array('icon'=>'folder-open'),
								'Clipboard'=>array('icon'=>'pushpin'),
								'Chart'=>array('icon'=>'signal'),
								'Admin'=>array('icon'=>'wrench',
										'dropdown'=>array('Units'=>array('icon'=>'tag'),
												'Targets'=>array('icon'=>'arrow-right'),
												'Devices'=>array('icon'=>'hdd'),
												'Locations'=>array('icon'=>'home'),
												'Compartments'=>array('icon'=>'th-large'),
												'sep'=>1,
												'User/Groups'=>array('icon'=>'user'),
												'IP Authentication'=>array('icon'=>'cloud'),
												'Settings'=>array('icon'=>'wrench')
										)));
						while(list($key,$value)=each($nav)){
							if(isset($value['dropdown'])){
								echo '
								<li class="dropdown"><a href="#" class="dropdown-toggle"
								data-toggle="dropdown"><span class="glyphicon glyphicon-'.$value['icon'].'"></span>
								'.$key.'<b class="caret"></b></a>
								<ul class="dropdown-menu">
								';
								while(list($key2,$value2)=each($value['dropdown'])){
									if($key2=='sep')
										echo '<li class="divider"></li>';
									else 
										echo '
									<li'.($key2==$_SESSION['tab']?' class="active"':'').'>
									<a href="./?tab='.$key2.'"><span class="glyphicon glyphicon-'.$value2['icon'].'"></span> '.$key2.'</a>
									</li>';

								}
								echo '</ul>
								</li>';
							} else
								echo '
								<li'.($key==$_SESSION['tab']?' class="active"':'').'>
								<a href="./?tab='.$key.'"><span class="glyphicon glyphicon-'.$value['icon'].'"></span> '.$key.'</a>
								</li>';
						}
							
						?>
					</ul>
					<?php }?>

					<div class="navbar-right">
						<?php if(isset($_SESSION['bayeosauth'])){
							?>
						<p class="navbar-text">
						<span class="hidden-sm">
						Signed in as
							<?php echo $_SESSION['login'];?>
						</span>
							</p>
						<a href="?action=logout">
							<button type="button" class="btn btn-default navbar-btn">Logout</button>
						</a>
						<?php }?>
					</div>
				</div>

			</div>


		</nav>
		<div class="container">
			<?php 	
echo $GLOBALS['alert'];
?>