<?php
/******************************************************
 * These actions are run via a direct database
* connection
*
* This works only when locked in as a admin user
*
*****************************************************/
if(isset($_SESSION['dbConnection'])){
	switch($_GET['view']){
		case 'ip':
			$table='auth_ip';
			$name='IP Auth';
			$insert_query='insert into auth_ip(network,login,access) values($1,$2,$3)';
			$update_query='update auth_ip set network=$1,login=$2,access=$3 where id=$4';
			break;
		case 'ldap':
			$table='auth_ldap';
			$name='LDAP Auth';
			$insert_query='insert into auth_ldap(name,host,dn,ssl,port) values($1,$2,$3,$4,$5)';
			$update_query='update auth_ldap set name=$1,host=$2,dn=$3,ssl=$4,port=$5 where id=$6';
			break;
				
	}
	
	DBQueryParams('select set_user($1)', array($_SESSION['username']));
	if(! isset($_POST['ssl'])) $_POST['ssl']=0;
	
	if(isset($_GET['del'])){
		$res=DBQueryParams('delete from '.$table.' where id=$1', array($_GET['del']));
		if($res)
			add_alert($name.' entry deleted');
	} elseif(isset($_GET['edit']) && ($_POST['network']||$_POST['name'])){
		$res=DBQueryParams($update_query, 
				($_GET['view']=='ip'?
						array($_POST['network'],$_POST['login'],$_POST['access'],$_GET['edit']):
						array($_POST['name'],$_POST['host'],$_POST['dn'],$_POST['ssl'],$_POST['port'],$_GET['edit'])));
		if($res)
			add_alert($name.' entry updated');
		
	} elseif(isset($_POST['network']) || isset($_POST['name'])){
		$res=DBQueryParams($insert_query,($_GET['view']=='ip'?
				array($_POST['network'],$_POST['login'],$_POST['access']):
				array($_POST['name'],$_POST['host'],$_POST['dn'],$_POST['ssl'],$_POST['port'])));
		if($res){
			add_alert($name.' entry added');
			unset($_GET['edit']);
		}
	}

}
?>