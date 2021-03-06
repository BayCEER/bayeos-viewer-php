<?php 
/**********************************************************
 * Main Application
 * 
 * Application Layout:
 * index.php
 *  |-functions.php
 *  |  |- xmlrpc.inc
 *  |  |- constants.php
 *  |-actions.php
 *  |  |- action_login.php
 *  |  |- ...
 *  |-views.php
 *     |- view_header.php
 *     |- ... 
 *********************************************************/


require_once './functions.php';
/***********************************************************
 * Login Action
***********************************************************/

if(isset($_POST['login']) && isset($_POST['password'])){
	require 'action_login.php';
}
if(isset($_SESSION['bayeosauth']))
	require_once './actions.php';
require_once './views.php';


//Views:
require 'view_header.php';
//Not Authenticated	
if(! isset($_SESSION['bayeosauth'])){
	require 'view_login.php';
	require 'view_footer.php';
}

//Normal Application
switch ($_SESSION['tab']){
	case 'Clipboard':
		require 'view_clipboard.php';
		break;
	case 'Chart':
		require 'view_chart.php';
		break;
	case 'Authentication':
		require 'view_auth.php';
		break;
	case 'Change Password':
		require 'view_pw.php';
		break;
	case 'User/Roles':
		require 'view_roles.php';
		break;
	case 'Settings':
		require 'view_settings.php';
		break;
		
	default:
		require 'view_breadcrumbs.php';
		if(isset($_GET['edit'])) //note view_breadcrumbs.php will set $_GET['edit'] in some cases!
			require 'view_object.php';
		else
			require 'view_childs.php';
		
}
require 'view_footer.php';
?>