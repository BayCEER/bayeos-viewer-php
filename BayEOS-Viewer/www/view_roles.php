<div class="col-lg-12">
<ul class="nav nav-tabs">
<?php 
if(isset($_GET['new'])) $_GET['view']=$_GET['new'];
if(! isset($_GET['view'])) $_GET['view']='';
if($_GET['view']==''){
	$icon='user';
	$name='user';
} else {
	$icon='list';
	$name='role';
}
if(! isset($_GET['page'])) $_GET['page']=1;

$tabs=array(array('','user','User'),
		array('roles','list','Roles'));

for($i=0;$i<count($tabs);$i++){
	echo '<li'.($_GET['view']==$tabs[$i][0]?' class="active"':'').'><a href="?view='.$tabs[$i][0].'">
	<span class="glyphicon glyphicon-'.$tabs[$i][1].'"></span> '.$tabs[$i][2].'</a></li>';
}
?>
</ul>
<?php if(isset($_SESSION['dbConnection'])){
	if(isset($_GET['edit']) || isset($_GET['new'])){
		
?>
<form action="?view=<?php echo $_GET['view'];?>" method="POST" class="form" role="form" accept-charset="UTF-8">
<input type="hidden" name="action" value="user">
<div class="block">
<div class="block-header">
<?php echo (isset($_GET['edit'])?'Change user':'New '.$name);?>
</div>
<div class="row">
<?php 
if(isset($_GET['new'])){
	echo_field('user_loginname', 'Login', 'string');
	echo_field('user_fullname', 'Full Name', 'string');
} else 
	echo '<input type="hidden" name="edit" value="'.$_GET['edit'].'">';
if($_GET['view']==''){
	echo_field('user_password1', 'Password', 'password');
	echo_field('user_password2', 'Password (confirmation)', 'password');
	if(isset($_GET['edit'])){
		$res=DBQueryParams('select case when admin then 1 end,case when locked then 1 end 
				from benutzer where login=$1', array($_GET['edit']));
		$r=pg_fetch_row($res,0);
		echo_field('locked', 'Locked', 'boolean',$r[1],6,array('old_hidden'=>1));
		echo_field('admin', 'Admin', 'boolean',$r[0],6,array('old_hidden'=>1));
	} 
	
}
if(isset($_GET['edit'])){
	echo '</div>
	</div>
	<div class="block">
<div class="block-header">Granted Roles</div>
<div class="row">
	';
	$res=DBQueryParams('select g.login from benutzer g, benutzer_gr bg, benutzer b
			where g.id=bg.id_gruppe and b.id=bg.id_benutzer and b.login=$1', array($_GET['edit']));
	?>
	<table class="table table-hover col-sm-12">
		<thead>
			<tr>
				<th>Roles</th>
			</tr>
		</thead>
		<tbody>
			<?php
			echo '<tr>
				<td>
				'.get_input("newrole",'autocomplete','','',array('refclass'=> "'roles'",
						'additional_args'=>'mustMatch: true,')).'
						</td>
						<td><button class="btn btn-xs btn-default" name="_roleadd">
						<span class="glyphicon glyphicon-plus"></span> Add
						</button>
						</tr>
						';
			
	
			for($i=0;$i<pg_num_rows($res);$i++){
				$r=pg_fetch_array($res,$i);
				echo '<tr>
				<td><span class="glyphicon glyphicon-list"></span> '.$r[0].'</td>
				<td><a href="./?action=user&edit='.$_GET['edit'].'&_roledel='.urlencode($r[0]).'" class="btn btn-xs btn-default" onClick="return confirm(\'Are you sure?\');">
				<span class="glyphicon glyphicon-remove"></span> delete</a></td>';
				echo '</tr>
				';
			}
			?>
		</tbody>
	</table>
<?php 		
}

?>	
	</div>

	</div>
<div class="block-action">
<button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-ok"></span> 
<span class="hidden-xs">Save</span></button></div></form>
<?php 
		
	} else {
	$qs="&view=$_GET[view]";
	
	
if($res=DBQueryParams('select b.login,
			case when b.locked then \'yes\' else \'no\' end,
			case when b.admin then \'yes\' else \'no\' end
			from benutzer b, objekt o, art_objekt ao
			where b.id=o.id and o.id_art=ao.id and ao.uname=$1 order by 1', 
			array(($_GET['view']==''?'benutzer':'gruppe')))){

		echo_dbtable($res, array('Name','Locked','Admin'),'user',"&view=$_GET[view]",$icon);
?>
<div class="block-action">
<?php 	
} else 
	echo $GLOBALS['alert'];
echo_button('new '.$name, 'plus','?new='.$_GET['view']);
?>
</div>
<?php 
	}
} else {
	if(! isset($_SESSION['Benutzer']))
		$_SESSION['Benutzer']=xml_call('LookUpTableHandler.getBenutzer',array());
	if(! isset($_SESSION['Gruppen']))
		$_SESSION['Gruppen']=xml_call('LookUpTableHandler.getGruppen',array());
	
	$tmp=($_GET['view']=='roles'?$_SESSION['Gruppen']:$_SESSION['Benutzer']);
	
	echo_pagination(count($tmp),$_GET['page'],"&view=$_GET[view]");
	
?>
	<table class="table table-hover col-sm-12">
	<thead>
	<tr>
	<th>Name</th>
	</tr>
	</thead>
	<tbody>
<?php 
for($i=($_GET['page']-1)*10;$i<min(count($tmp),$_GET['page']*10);$i++){
	echo '<tr><td><span class="glyphicon glyphicon-'.$icon.'"></span> '.$tmp[$i][20].'</td></tr>
	';
}

?>
	</tbody>
</table>
<?php }?>
</div>
