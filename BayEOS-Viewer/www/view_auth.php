<?php
/******************************************************
 * These actions are run via a direct database 
 * connection
 * 
 * This works only when locked in as a admin user
 * 
 *****************************************************/
if(! isset($_GET['view'])) $_GET['view']='ip';
echo '<ul class="nav nav-tabs">';
$tabs=array(array('ip','cloud','IP Auth'),
		array('ldap','tasks','LDAP Auth'));


for($i=0;$i<count($tabs);$i++){
	if($_GET['view']==$tabs[$i][0]){
		$name=$tabs[$i][2];
		$ci=$i;
	}
	echo '<li'.($_GET['view']==$tabs[$i][0]?' class="active"':'').'><a href="?view='.$tabs[$i][0].'">
	<span class="glyphicon glyphicon-'.$tabs[$i][1].'"></span> '.$tabs[$i][2].'</a></li>';
}
echo '</ul>';




if(isset($_SESSION['dbConnection'])){
	if(isset($_GET['new']) || isset($_GET['edit'])){
		?>
		<form  action="?view=<?php echo $_GET['view']; if(isset($_GET['edit'])) echo '&edit='.$_GET['edit'];?>"
		 method="POST" class="form" role="form" accept-charset="UTF-8">
		<input type="hidden" name="action" value="auth">
		<div class="block">
		<div class="block-header">
		<?php echo (isset($_GET['edit'])?'Change ':'New ').$name;?>
		</div>
		<div class="row">
		<?php 
		if(isset($_GET['edit'])){
			$r=pg_fetch_row(DBQueryParams(($_GET['view']=='ip'?
					'select network,login,access from auth_ip where id=$1':
					'select name,host,dn,ssl,port from auth_ldap where id=$1'), array($_GET['edit'])),0);
		} else $r=array('','','','','');
		if($_GET['view']=='ip'){
			echo_field('network', 'Network', 'string',$r[0],4);
			echo_field('login', 'Login', 'string',$r[1],4);
			echo_field("access",'Access','SelectValue','format',4,
			array('selectvalues'=>array('TRUST','DENY','PASSWORD')));
		} else {
			echo_field('name', 'Name', 'string',$r[0],3);
			echo_field('host', 'Host', 'string',$r[1],3);
			echo_field('dn', 'dn', 'string',$r[2],6);
			echo_field('ssl', 'SSL', 'boolean',$r[3],3);
			echo_field('port', 'PORT', 'string',$r[4],3);	
		}
		?>
			</div>
		
			</div>
		<div class="block-action">
		<button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-ok"></span> 
		<span class="hidden-xs">Save</span></button></div></form>
		<?php 
		if($_GET['view']=='ip')
			echo 'Please note that BayEOS-Viewer connects the XML-RPC interface <b>'.$_SESSION['bayeosurl'].'</b> via the IP-Address of the server <b>'.$_SERVER['SERVER_ADDR'].'</b>. Adding this
			address to trusted IP-Addresses will bypass all password authentication.';
		else
			echo '<p>For LDAPS a key store is necessary. You must import the certificate of your
			LDAP Server into the default store of the server JRE. The
			default keystore for your JRE can be found in
			/usr/lib/jvm/jre/lib/security. The key can be imported by the
			keytool utility:</p>
			<pre class="programlisting">keytool -import -alias myAlias -file myCertificate.crt -keystore cacerts -storepass changeit</pre>
			Please restart the Tomcat Server after the import.';
		
		
		
	} else {
	if(! isset($_GET['page'])) $_GET['page']=1;
	if($_GET['view']=='ip'){
		$res=DBQueryParams('select id,network,login,access from auth_ip order by network,login',array());
		echo_dbtable($res, array('ID','Network','Login','Access'),'auth','&view='.$_GET['view']);
	} else {
		$res=DBQueryParams('select id,name,host,dn,case when ssl then \'yes\' else \'no\' end,
				port from auth_ldap order by name',array());
		echo_dbtable($res, array('ID','Name','Host','DN','SSL','Port'),'auth','&view='.$_GET['view']);
	}
	?>
	<div class="block-action">
	<?php echo_button('new entry', 'plus','?new=1&view='.$_GET['view']);?>
	</div>
	<?php 
	
	}	

} else {
?>
<div class="alert alert-warning">Sorry. This function is not working with this BayEOS Servers version.</div>
<?php }?>