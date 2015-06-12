<form method="POST" class="form" role="form">
<div class="block">
<div class="block-header">Chart settings - Larger values may slow down plotting!</div>
<div class="row">
<input type="hidden" name="action" value="settings">
<?php
echo_field("max_cols",'Maximum number of series','INT',$_SESSION['max_cols'],3);
echo_field("max_rows",'Maximum number of datapoints','INT',$_SESSION['max_rows'],3);
echo_field("gnuplot",'Render plot as image (Check if normal chart plotting does not work on your device)','boolean',$_SESSION['gnuplot'],3);
echo_field("cb2db",'Save clipboards on server','boolean',$_SESSION['cb2db'],3);
?>
</div>
</div>
<div class="block">
<div class="block-header">Saved Clipboards</div>
<div class="row">
<table class="table table-hover col-sm-6">
<?php 
while(list($key)=each($_SESSION['cb_saved'])){
	echo '<tr><td>'.$key.'<td>
	<a href="?cb_load='.urlencode($key).'&tab=Clipboard" class="btn btn-xs btn-default">
	<span class="glyphicon glyphicon-upload"></span> load</a>
	
	<a href="?cb_del='.urlencode($key).'" 
	class="btn btn-xs btn-default" onClick="return confirm(\'Are you sure?\');">
			<span class="glyphicon glyphicon-remove"></span> delete</a><td></td></tr>';
}
reset($_SESSION['cb_saved']);
?>
</table>
</div>
</div>
<div class="block-action">
<button class="btn btn-primary" type="submit">
<span class="glyphicon glyphicon-ok"></span> Update</button> 
</div>
</form>
Note: Settings will get stored in cookies. 
If you did not enable cookies settings will only be valid until logout.
