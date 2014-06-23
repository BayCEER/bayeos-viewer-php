
<form class="form-signin" action="./" method="POST" role="form"
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
