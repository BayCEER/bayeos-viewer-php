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
		$res=DBQueryParams('select drop_user($1)', array($_GET['del']));
		if($res)
			add_alert('User deleted');
	} elseif(isset($_POST['newrole']) && $_POST['newrole']){
		$res=DBQueryParams('select grant_role($1,$2)', array($_POST['edit'],$_POST['newrole_dp']));
		if($res)
			add_alert('Role granted');
	} elseif(isset($_GET['_roledel'])){
		$res=DBQueryParams('select revoke_role($1,$2)', array($_GET['edit'],$_GET['_roledel']));
		if($res)
			add_alert('Role revoked');
		
	} elseif(isset($_POST['user_loginname']) && $_POST['user_loginname'] && isset($_POST['user_password2'])){
		if($_POST['user_password2']!=$_POST['user_password1'])
			add_alert('Passwords do not match','warning');
		elseif(strlen($_POST['user_password1'])<4)
		add_alert('Password must have at least four characters','warning');
		else{
			$res=DBQueryParams('select create_user($1,$2,$3,$4,$5)', 
					array($_POST['user_loginname'],$_POST['user_password1'],$_POST['user_fullname'],'DB','LOCAL'));
			if($res){
				add_alert('User created');
				$_GET['edit']=$_POST['user_loginname'];
			}
		}
		
	} elseif(isset($_POST['user_loginname']) && $_POST['user_loginname']){
		$res=DBQueryParams('select create_role($1,$2)', 
					array($_POST['user_loginname'],$_POST['user_fullname']));
			if($res)
				add_alert('Role created');
		
		
	} elseif(isset($_POST['user_password2'])){ //Change user...
		if($_POST['user_password2']){
			if($_POST['user_password2']!=$_POST['user_password1'])
				add_alert('Passwords do not match','warning');
			elseif(strlen($_POST['user_password1'])<4)
			add_alert('Password must have at least four characters','warning');
			else{
				$res=DBQueryParams('select set_password($1,$2)',
						array($_POST['edit'],$_POST['user_password1']));
				if($res)
					add_alert('Password changed for user '.$_POST['edit']);
			}
		}
		if($_POST['_old_locked']!=$_POST['locked'] || $_POST['_old_admin']!=$_POST['admin']){
			$res=DBQueryParams('update benutzer set locked=$1,admin=$2 where login=$3',
					array($_POST['locked'],$_POST['admin'],$_POST['edit']));
			if($res)
				add_alert('Settings changed for user '.$_POST['edit']);
				
		}
		
	}
	if(isset($_POST['edit'])) $_GET['edit']=$_POST['edit'];
	if(isset($_SESSION['Benutzer'])) unset($_SESSION['Benutzer']);
	if(isset($_SESSION['Gruppen'])) unset($_SESSION['Gruppen']);
	
}
?>