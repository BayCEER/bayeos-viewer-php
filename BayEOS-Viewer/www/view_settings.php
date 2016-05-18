<?php 
if(! isset($_GET['stab'])) $_GET['stab']='clipboards';
?>
<form method="POST" class="form" role="form" action="?action=settings">
<div class="block">
<div class="block-header">Settings</div>
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
<li<?php if($_GET['stab']=='clipboards') echo ' class="active"';?>><a href="#clipboards" data-toggle="tab">Clipboards</a></li>
<li<?php if($_GET['stab']=='bookmarks') echo ' class="active"';?>><a href="#bookmarks" data-toggle="tab">Bookmarks</a></li>
<li<?php if($_GET['stab']=='chart') echo ' class="active"';?>><a href="#chart" data-toggle="tab">Miscellaneous</a></li>
</ul>

<div id="my-tab-content" class="tab-content">

<div class="tab-pane<?php if($_GET['stab']=='clipboards') echo ' active';?>" id="clipboards">
<div class="row">
<table class="table table-hover col-sm-6">
<?php 
while(list($key)=each($_SESSION['cb_saved'])){
	echo '<tr><td>'.get_input('cb_key[]','hidden',$key).get_input('cb_key_new[]','',$key).'<td>
	<input type="checkbox" name="cb_del[]" value="'.htmlspecialchars($key).'" id="cb_del_'.htmlspecialchars($key).'">
	<label for="cb_del_'.htmlspecialchars($key).'">delete</label> 
	</td><td>
	<a href="?cb_load='.htmlspecialchars($key).'&tab=Clipboard" class="btn btn-xs btn-default">
	<span class="glyphicon glyphicon-upload"></span> load</a>
	<td></td></tr>';
}
reset($_SESSION['cb_saved']);
?>
</table>
</div>
</div>

<div class="tab-pane<?php if($_GET['stab']=='bookmarks') echo ' active';?>" id="bookmarks">
<div class="row">
<table class="table table-hover col-sm-6">
<?php 
reset($_SESSION['bookmarks']);
if(! isset($_GET['id'])) $_GET['id']=0;
while(list($key,$value)=each($_SESSION['bookmarks'])){
	echo '<tr'.($_GET['id']==$value?' class="success"':'').'><td>'.get_input('bm_key[]','hidden',$key).get_input('bm_key_new[]','',$key).'<td>
	<input type="checkbox" name="bm_del[]" value="'.htmlspecialchars($key).'" id="bm_del_'.htmlspecialchars($key).'">
	<label for="bm_del_'.htmlspecialchars($key).'">delete</label> 
	</td><td>
	<a href="?id='.htmlspecialchars($value).'&tab=Folders" class="btn btn-xs btn-default">
	<span class="glyphicon glyphicon-share-alt"></span> goto</a>
	<td></td></tr>'."\n";
}
//$_SESSION['bookmarks']=$tmp;
reset($_SESSION['bookmarks']);
?>
</table>
</div>
</div>

<div class="tab-pane<?php if($_GET['stab']=='chart') echo ' active';?>" id="chart">
<div class="row">
<input type="hidden" name="action" value="settings">
<?php
echo_field("max_cols",'Maximum number of series','INT',$_SESSION['max_cols'],3);
echo_field("max_rows",'Maximum number of datapoints','INT',$_SESSION['max_rows'],3);
echo_field("gnuplot",'Render plot as image (Check if normal chart plotting does not work on your device)','boolean',$_SESSION['gnuplot'],3);
echo_field("cb2db",'Save clipboards and bookmarks on server','boolean',$_SESSION['cb2db'],3);
?>
</div>
</div>


</div>
</div>

<div class="block-action">
<button class="btn btn-primary" type="submit">
<span class="glyphicon glyphicon-ok"></span> Update</button> 
</div>
</form>
Note: <?php if($_SESSION['cb2db']) echo 'Some ';?>settings will get stored in cookies. 
If you did not enable cookies settings will only be valid until logout.
