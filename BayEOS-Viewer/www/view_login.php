
<form class="form-signin" action="./<?php 
$qs=array();
if(isset($_GET['id'])) $qs[]="id=$_GET[id]";
if(isset($_GET['edit'])) $qs[]="edit=$_GET[edit]";
if(isset($_GET['tab'])) $qs[]="tab=$_GET[tab]";
if(isset($_GET['view'])) $qs[]="view=$_GET[view]";
if(count($qs)) echo '?'.implode("&",$qs);
unset($qs);
?>" method="POST" role="form"
	id="loginForm" autocomplete="off">
	<p class="lead strong">Please sign in</p>
	<script type="text/javascript">
	var tz = jstz.determine(); // Determines the time zone of the browser client
    document.write('<input type="hidden" name="tz" value="'+tz.name()+'">');
	</script>
	<input id="username" name="login" class="form-control" autofocus=""
		placeholder="Username" type="text"> <input id="password"
		name="password" class="form-control" placeholder="Password"
		type="password">
	<button class="btn btn-lg btn-primary btn-block" type="submit"
		value="Login">Sign in</button>
</form>
