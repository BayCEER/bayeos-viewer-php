<?php 
/**********************************************************
 * Actions 
 * 
 * Changes of $_SESSION and all update XML-RPC-Calls
 * Error and Success are reported by $GLOBALS['alert']
 *********************************************************/

if(! isset($_REQUEST['action'])) $_REQUEST['action']='';

/***********************************************************
 * Client Timezone
***********************************************************/
if(isset($_POST['tz']) && $_POST['tz'])
	$_SESSION['tz']=$_POST['tz'];
	
if(isset($_SESSION['tz']))
	date_default_timezone_set($_SESSION['tz']);

/***********************************************************
 * Navigation
***********************************************************/

if(isset($_GET['tab'])){
	if(in_array($_GET['tab'],$bayeos_trees) && $_SESSION['current_tree']!=$_GET['tab']){
		$_SESSION['id']=get_root_id($bayeos_tree_unames[$_GET['tab']]);
		$_SESSION['breadcrumbs']=array();
		$_SESSION['breadcrumbs'][]=xml_call('TreeHandler.getNode',array(new xmlrpcval($_SESSION['id'],'int')));
		$_SESSION['current_tree']=$_GET['tab'];
	}
	//Move up if user clicked on Folders comming from a node without childs 
	$last_node=count($_SESSION['breadcrumbs'])-1;
	if(! isset($_GET['edit']) && ! isset($_GET['id']) &&
		! isset($bayeos_canhavechilds[$_SESSION['breadcrumbs'][$last_node][4]]))
		$_GET['id']=$_SESSION['breadcrumbs'][$last_node][3];

	$_SESSION['tab']=$_GET['tab'];
}

/***********************************************************
 * Login Action
 ***********************************************************/

if(isset($_POST['login']) && isset($_POST['password'])){
	require 'action_login.php';
}

/***********************************************************
 * Logout Action
 ***********************************************************/
 
if($_REQUEST['action']=='logout'){
	$res = xml_call("LogOffHandler.terminateSession",array());
	unset($_SESSION['bayeosauth']);
}

/***********************************************************
 * ACL Actions
 ***********************************************************/

if($_REQUEST['action']=='acl' || isset($_GET['acldel']))
	require 'action_acl.php';


/***********************************************************
 * Reference Actions
***********************************************************/
if($_REQUEST['action']=='ref' || isset($_GET['refdel']))
	require 'action_ref.php';


/***********************************************************
 * Node + Object Actions
***********************************************************/

if($_REQUEST['action']=='object')
	require 'action_object.php';


/***********************************************************
 * Massendaten Update + Delete
***********************************************************/
if($_REQUEST['action']=='chartdata')
	require 'action_chartdata.php';

/***********************************************************
 * Massendaten Update + Delete
***********************************************************/
if($_REQUEST['action']=='ts')
	require 'action_ts.php';

/***********************************************************
 * User actions
***********************************************************/
if($_REQUEST['action']=='user')
	require 'action_user.php';

/***********************************************************
 * User actions
***********************************************************/
if($_REQUEST['action']=='auth')
	require 'action_auth.php';


/***********************************************************
 * DataFrame
 ***********************************************************/
if($_REQUEST['action']=='df'){
	$res=0;
	for($i=0;$i<count($_POST['cid']);$i++){
		$rindex=array();
		$values=array();
		$c=$_POST['cid'][$i];
		$t=$_POST['ctyp'][$i];
		for($j=0;$j<count($_POST['r']);$j++){
			$r=$_POST['r'][$j];
			if($t=='boolean' && $_POST['v'.$r.'_'.$c])
				$_POST['v'.$r.'_'.$c]=1;
			if($_POST['v'.$r.'_'.$c]!=$_POST['_old_v'.$r.'_'.$c]){
				$rindex[]=new xmlrpcval($r,'int');
				$v=$_POST['v'.$r.'_'.$c];
				$type=$t;
				if($type=='dateTime.iso8601'){
					$type=($v?'dateTime.iso8601':'null');
					$v=toiso8601($v);
				}
				if(($type=='int' || $type=='double') && ! is_numeric($v)){
					$type='null';
					$v=null;
				}
				$values[]=new xmlrpcval($v,$type);
			}
		}
		if(count($values)){
			$res=xml_call('DataFrameHandler.updateColValues',array(new xmlrpcval($c,'int'),
				new xmlrpcval($rindex,'array'),
				new xmlrpcval($values,'array')
			));
		}
	}
	if($res)
		add_alert('Data frame updated');

}

/***********************************************************
 * Change Password
***********************************************************/
if($_REQUEST['action']=='password'){
	if($_POST['password2']!=$_POST['password'])
		add_alert('Passwords do not match','warning');
	elseif(strlen($_POST['password'])<4)
		add_alert('Password must have at least four characters','warning');
	else{
		$res=xml_call('ToolsHandler.changePassword',
				array(new xmlrpcval($_POST['password_old'],'string'),
					new xmlrpcval($_POST['password'],'string')));
		if($res)
			add_alert('Password changed.');
		
	}
}

/***********************************************************
 * Settings
***********************************************************/
if(isset($_GET['cb_del']) && isset($_SESSION['cb_saved'][$_GET['cb_del']])){
	unset($_SESSION['cb_saved'][$_GET['cb_del']]);
	updateCookies();
	add_alert('Deleted saved clipboard');
}

if($_REQUEST['action']=='settings'){
	$_SESSION['max_cols']=$_POST['max_cols'];
	$_SESSION['max_rows']=$_POST['max_rows'];
	$_SESSION['gnuplot']=$_POST['gnuplot'];
	updateCookies();
	add_alert('Settings saved');
	
}
if($_REQUEST['action']=='settings_clipboard' && $_POST['save_as']){
	$ids=array();
	for($i=0;$i<count($_SESSION['clipboard']);$i++){
		$ids[]=$_SESSION['clipboard'][$i][2];
	}
	$_SESSION['cb_saved'][$_POST['save_as']]=$ids;
	updateCookies();
	add_alert('Clipboard saved as '.$_POST['save_as']);
}

/***********************************************************
 * Clipboard
 ***********************************************************/
//Clipboard load
if(isset($_GET['cb_load'])){
	if(is_array($_SESSION['cb_saved'][$_GET['cb_load']])){
		$_SESSION['clipboard']=array();
		for($i=0;$i<count($_SESSION['cb_saved'][$_GET['cb_load']]);$i++){
			addToClipboard($_SESSION['cb_saved'][$_GET['cb_load']][$i],0);
		}
	}
}

//Clipboard add
if(isset($_GET['add']) && is_numeric($_GET['add'])){
	if(! addToClipboard($_GET['add'])){ //Folder
		$childs=xml_call('TreeHandler.getAllChildren',
					array(new xmlrpcval($_GET['add'],'int'),
							new xmlrpcval(false,'boolean'),
							xmlrpc_array(array('mitarbeiter','projekte')),
							new xmlrpcval('**/*','string'),
							new xmlrpcval('messung_massendaten','string'),
							new xmlrpcval(1,'int'),
							new xmlrpcval(FALSE,'boolean'),
							new xmlrpcval('week','string'),
							new xmlrpcval(null,'null')
					));
		if(count($childs)==0)
			add_alert('No timeseries found in folder','warning');
		else {
			$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['add'],'int')));
			for($i=0;$i<count($childs);$i++){
				addToClipboard($childs[$i][0]);
			}
		}
	}
	//No chart Data for new clipboard
	$_SESSION['chartdata']=0;
}

//Clipboard remove
if(isset($_GET['remove'])){
	if($_GET['remove']=='all')
		$_SESSION['clipboard']=array();
	elseif(is_numeric($_GET['remove'])){
		$tmp=$_SESSION['clipboard'];
		$_SESSION['clipboard']=array();
		for($i=0;$i<count($tmp);$i++){
			if($tmp[$i][2]!=$_GET['remove']) $_SESSION['clipboard'][]=$tmp[$i];
		}
	}	
}



/***********************************************************
 * Filter
 ***********************************************************/
if($_REQUEST['action']=='filter')
	require 'action_filter.php';


if(isset($_GET['zoom'])){
	$step=toEpoch($_SESSION['until'])-toEpoch($_SESSION['from']);
	$_SESSION['until']=toios8601FromEpoch(toEpoch($_SESSION['until'])-$_GET['zoom']*round($step/4));
	$_SESSION['from']=toios8601FromEpoch(toEpoch($_SESSION['from'])+$_GET['zoom']*round($step/4));
}
if(isset($_GET['move'])){
	$step=toEpoch($_SESSION['until'])-toEpoch($_SESSION['from']);
	$_SESSION['until']=toios8601FromEpoch(toEpoch($_SESSION['until'])+$_GET['move']*round($step/3));
	$_SESSION['from']=toios8601FromEpoch(toEpoch($_SESSION['from'])+$_GET['move']*round($step/3));
	
}

//Tree Filter On/OFF
if(isset($_GET['treefilter'])) $_SESSION['treefilter']=$_GET['treefilter'];
if(isset($_GET['chartmulti'])) $_SESSION['chartmulti']=$_GET['chartmulti'];
if(isset($_GET['chartdata'])) $_SESSION['chartdata']=$_GET['chartdata'];
if(isset($_SESSION['chartdata']) && $_SESSION['chartdata'] && count($_SESSION['clipboard'])>1){
	add_alert('Time series show data only works with <b>ONE</b> selected series!','warning');
	$_SESSION['chartdata']=0;
}


?>