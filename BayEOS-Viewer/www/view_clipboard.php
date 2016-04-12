<?php 
$clipboard=count($_SESSION['clipboard']);
if(! $clipboard){
	echo '<div class="alert alert-warning">Your clipboard is empty.</div>
	<div class="btn-group">';
	echo_saved_cb_dropdown();
	echo '</div>';
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
<div class="input-group">
<div class="btn-group input-group-btn">
<?php echo_saved_cb_dropdown(); ?>
</div>
<input  class="form-control" name="save_as" value="<?php echo htmlspecialchars($_SESSION['current_clipboard']);?>">
<span class="input-group-btn">
<button class="btn btn-primary" type="submit">
<span class="glyphicon glyphicon-ok"></span> Save</button>
</span> 
</div>
<?php if(! $_SESSION['cb2db']){?>
Note: Settings will get stored in cookies. 
If you did not enable cookies settings will only be valid until logout.
<?php }?>
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