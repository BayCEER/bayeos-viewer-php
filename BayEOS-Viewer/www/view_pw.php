<form method="post">
<div class="block">
<div class="block-header">Change Password
</div>
<div class="row">
<input type="hidden" name="action" value="password">
<?php 
echo_field("password_old",'Old Password','password','',4);
echo_field("password",'New Password','password','',4);
echo_field("password2",'New Password (confirmation)','password','',4);
?>
</div>
</div>
<div class="block-action">
<?php 
echo_button('Change Password','ok','',"btn btn-primary",'type="submit"');
?>
</div>
</form>
