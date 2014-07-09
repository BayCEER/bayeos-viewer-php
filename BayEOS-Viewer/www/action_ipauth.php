<?php
/******************************************************
 * These actions are run via a direct database
* connection
*
* This works only when locked in as a admin user
*
*****************************************************/

if(isset($_SESSION['dbConnection'])){
	DBQueryParams('select set_user($1)', array($_SESSION['username']));
	
	if(isset($_GET['del'])){
		$res=DBQueryParams('delete from auth_ip where id=$1', array($_GET['del']));
		if($res)
			add_alert('IP Auth entry deleted');
	} elseif(isset($_GET['edit']) && $_POST['network']){
		$res=DBQueryParams('update auth_ip set network=$1,login=$2,access=$3 where id=$4', 
				array($_POST['network'],$_POST['login'],$_POST['access'],$_GET['edit']));
		if($res)
			add_alert('IP Auth entry updated');
		
	} elseif(isset($_POST['network'])){
		$res=DBQueryParams('insert into auth_ip(network,login,access) values($1,$2,$3)', 
				array($_POST['network'],$_POST['login'],$_POST['access']));
		if($res){
			add_alert('IP Auth entry added');
			unset($_GET['edit']);
		}
	}

}
?>