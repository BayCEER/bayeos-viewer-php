<?php 
if(! isset($_SESSION['Benutzer']))
	$_SESSION['Benutzer']=xml_call('LookUpTableHandler.getBenutzer',array());
if(! isset($_SESSION['Gruppen']))
	$_SESSION['Gruppen']=xml_call('LookUpTableHandler.getGruppen',array());
?>
<table class="table table-hover col-sm-12">
<thead>
<tr>
<th>Name</th>
</tr>
</thead>
<tbody>
<?php 
for($i=0;$i<count($_SESSION['Gruppen']);$i++){
	echo '<tr><td><span class="glyphicon glyphicon-list"></span> '.$_SESSION['Gruppen'][$i][20].'</td></tr>
	';
}
for($i=0;$i<count($_SESSION['Benutzer']);$i++){
	echo '<tr><td><span class="glyphicon glyphicon-user"></span> '.$_SESSION['Benutzer'][$i][20].'</td></tr>
	';
}
reset($_SESSION['Benutzer']);
reset($_SESSION['Gruppen']);

?>
	</tbody>
</table>

