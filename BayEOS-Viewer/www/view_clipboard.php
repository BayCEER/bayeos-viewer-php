<?php 
$clipboard=count($_SESSION['clipboard']);
if(! $clipboard){
	echo '<div class="alert alert-warning">Your clipboard is empty.</div>
	';
	echo_saved_cb_dropdown(); 
}
else {
	echo_table($_SESSION['clipboard'],"remove");
?>
<form method="POST" class="form" role="form">
<div class="block">
<div class="block-header">Save clipboard selection as</div>
<div class="row">
<input type="hidden" name="action" value="settings_clipboard">
<div class="col-sm-12"><div class="form-group ">
<div class="dropdown">
<?php echo_saved_cb_dropdown(); ?>
<input name="save_as">
<button class="btn btn-primary" type="submit">
<span class="glyphicon glyphicon-ok"></span> Save</button> 
Note: Settings will get stored in cookies. 
If you did not enable cookies settings will only be valid until logout.
</div>
</div>
</div>
</div>
</div>
</form>
<br/>
<?php 
echo_filter_form("Export");
}
?>