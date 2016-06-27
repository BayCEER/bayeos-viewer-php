<?php 
if(! isset($config['customBootstrapHome'])) $config['customBootstrapHome']='';
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BayEOS Server</title>


<meta name="layout" content="main">
<link rel="stylesheet" href="css/signin.css">


<link rel="stylesheet" href="<?php echo $config['customBootstrapHome'];?>css/bootstrap.min.css">
<link rel="stylesheet" href="js/rickshaw.min.css">
<link rel="stylesheet" href="css/main.css">
<script src="js/jstz.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="/javascript/jquery/jquery.min.js"></script>
<script	src="/javascript/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet"
	href="/javascript/jquery-ui/css/smoothness/jquery-ui.min.css">
<script src="js/jquery.datetimepicker.full.min.js"></script>
<!-- <script type="text/javascript"
	src="/javascript/jquery-ui/jquery.mousewheel.js"></script>-->
<style type="text/css">
@import "js/jquery.datetimepicker.min.css";
</style>
<script src="js/clipboard.min.js"></script>


</head>
<body>
	<div id="wrap">
	<?php if(isset($config['customHeaderInclude']) && is_readable($config['customHeaderInclude']))
		include $config['customHeaderInclude'];
	?>
		<nav class="navbar navbar-default<?php 
		if(isset($config['customNavbarInverse']) && $config['customNavbarInverse']) echo " navbar-inverse";?>">
			<div class="container">
				<div class="navbar-header">
					<?php if(isset($_SESSION['bayeosauth'])){?>
					<button type="button" class="navbar-toggle" data-toggle="collapse"
						data-target=".navbar-ex1-collapse">
						<span class="sr-only">Toggle navigation</span> <span
							class="icon-bar"></span> <span class="icon-bar"></span> <span
							class="icon-bar"></span>
					</button>
				<?php }
				$HOMELINK="./?tab=Folders".(isset($_SESSION['homefolder'])?'&id='.$_SESSION['homefolder']:'');
				?>

					<a href="<?php echo $HOMELINK;?>" class="navbar-brand"><strong>BayEOS</strong> Server</a>
				</div>
				<div class="collapse navbar-collapse navbar-ex1-collapse">
					<?php if(isset($_SESSION['bayeosauth'])){?>
					<ul class="nav navbar-nav">
						<?php 
						$nav=array('Folders'=>array('icon'=>'folder-open'),
								'Clipboard'=>array('icon'=>'pushpin'),
								'Chart'=>array('icon'=>'signal'),
								'Admin'=>array('icon'=>'wrench',
										'dropdown'=>array(
												'Settings'=>array('icon'=>'wrench'),
												'Change Password'=>array('icon'=>'refresh'),
												'sep1'=>1,
												'Units'=>array('icon'=>'tag'),
												'Targets'=>array('icon'=>'arrow-right'),
												'Devices'=>array('icon'=>'hdd'),
												'Locations'=>array('icon'=>'home'),
												'Compartments'=>array('icon'=>'th-large')
										)));
						if($_SESSION['dbConnection']){
							$nav['Admin']['dropdown']['sep2']=1;
							$nav['Admin']['dropdown']['Authentication']=array('icon'=>'cog');
							$nav['Admin']['dropdown']['User/Roles']=array('icon'=>'user');
						}
						if(count($_SESSION['bookmarks'])){
							$nav['Bookmarks']['icon']='bookmark';
							while(list($key,$value)=each($_SESSION['bookmarks'])){
								$nav['Bookmarks']['dropdown'][$key]=array('url'=>'?tab=Folders&id='.$value);
							}
						}
						
						while(list($key,$value)=each($nav)){
							if(isset($value['dropdown'])){
								echo '
								<li class="dropdown"><a href="#" class="dropdown-toggle"
								data-toggle="dropdown"><span class="glyphicon glyphicon-'.$value['icon'].'"></span>
								'.$key.'<b class="caret"></b></a>
								<ul class="dropdown-menu">
								';
								while(list($key2,$value2)=each($value['dropdown'])){
									if(strstr($key2,'sep'))
										echo '<li class="divider"></li>';
									else { 
										if(isset($value2['url'])) $url=$value2['url'];
										else $url='./?tab='.urlencode($key2);
										echo '
									<li'.($key2==$_SESSION['tab']?' class="active"':'').'>
									<a href="'.$url.'"><span class="glyphicon glyphicon-'.$value2['icon'].'"></span> '.$key2.'</a>
									</li>';
									}
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
						<p class="navbar-text hidden-sm">
 						Signed in as
							<?php echo $_SESSION['login'];?>
 							</p>
						<a href="?action=logout" class="btn btn-default navbar-btn" title="logout">
							<span class="glyphicon glyphicon-log-out"></span> 
							<span class="hidden-sm">Logout</span>
						</a>
						<?php }?>
					</div>
				</div>

			</div>


		</nav>
		<div class="container">
<?php 	
echo $GLOBALS['alert'];
$GLOBALS['alert']='';

?>