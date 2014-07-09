<?php
/******************************************************
 * These actions are run via a direct database 
 * connection
 * 
 * This works only when locked in as a admin user
 * 
 *****************************************************/

if(isset($_SESSION['dbConnection'])){
	if(isset($_GET['new']) || isset($_GET['edit'])){
		?>
		<form <?php if(isset($_GET['edit'])) echo ' action="?edit='.$_GET['edit'].'"';?> 
		 method="POST" class="form" role="form" accept-charset="UTF-8">
		<input type="hidden" name="action" value="ipauth">
		<div class="block">
		<div class="block-header">
		<?php echo (isset($_GET['edit'])?'Change IP Auth':'New IP Auth');?>
		</div>
		<div class="row">
		<?php 
		if(isset($_GET['edit'])){
			$r=pg_fetch_row(DBQueryParams('select network,login,access from auth_ip where id=$1', array($_GET['edit'])),0);
		} else $r=array('','','');
		echo_field('network', 'Network', 'string',$r[0],4);
		echo_field('login', 'Login', 'string',$r[1],4);
		echo_field("access",'Access','SelectValue','format',4,
			array('selectvalues'=>array('TRUST','DENY','PASSWORD')));
		?>
			</div>
		
			</div>
		<div class="block-action">
		<button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-ok"></span> 
		<span class="hidden-xs">Save</span></button></div></form>
		<?php 
				
		
		
	} else {
	if(! isset($_GET['offset'])) $_GET['offset']=0;
	$res=DBQueryParams('select id,network,login,access from auth_ip order by network,login',array());
	echo_dbtable($res, array('ID','Network','Login','Access'),'ipauth');
	?>
	<div class="block-action">
	<?php echo_button('new entry', 'plus','?new=1');?>
	</div>
	<?php 
	}	

} else {
?>
<div class="alert alert-warning">Sorry. This function is not working with this BayEOS Servers version.</div>
<?php }?>