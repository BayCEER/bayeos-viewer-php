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
 *  |-views.php 
 *********************************************************/


require_once './functions.php';
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
	case 'IP Authentication':
		require 'view_ipauth.php';
		break;
	case 'User/Groups':
		require 'view_roles.php';
		break;
	default:
		require 'view_breadcrumbs.php';
		if(isset($_GET['edit']))
			require 'view_object.php';
		else
			require 'view_childs.php';
		
}
require 'view_footer.php';
?>