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
	if(in_array($_GET['tab'],$bayeos_trees)){
		$_SESSION['id']=get_root_id($bayeos_tree_unames[$_GET['tab']]);
		$_SESSION['breadcrumbs']=array();
		$_SESSION['breadcrumbs'][]=xml_call('TreeHandler.getNode',array(new xmlrpcval($_SESSION['id'],'int')));
		$_SESSION['current_tree']=$_GET['tab'];
	}
	$_SESSION['tab']=$_GET['tab'];
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

//move to editor
if(isset($_POST['ts_to_editor']))
	$_GET['view']='ts_editor';
if(isset($_POST['ts_to_chart']))
	$_GET['view']='ts_chart';

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
/*
if(isset($_GET['cb_del']) && isset($_SESSION['cb_saved'][$_GET['cb_del']])){
	unset($_SESSION['cb_saved'][$_GET['cb_del']]);
	updateCookies();
	add_alert('Deleted saved clipboard');
	$_GET['stab']='clipboards';
}

if(isset($_GET['bm_del']) && isset($_SESSION['bookmarks'][$_GET['bm_del']])){
	unset($_SESSION['bookmarks'][$_GET['bm_del']]);
	updateCookies();
	add_alert('Deleted bookmark '.$_GET['bm_del']);
	$_GET['stab']='bookmarks';
}
*/
if($_REQUEST['action']=='settings'){
	$keys=array('max_cols','max_rows','gnuplot','cb2db');
	for($i=0;$i<count($keys);$i++){
		if($_SESSION[$keys[$i]]!=$_POST[$keys[$i]]){
			$_SESSION[$keys[$i]]=$_POST[$keys[$i]];
			$_GET['stab']='chart';
		}
		
	}
	
	if(is_array($_POST['cb_key'])){
		for($i=0;$i<count($_POST['cb_key']);$i++){
			if($_POST['cb_key'][$i]!=$_POST['cb_key_new'][$i]){
				$value=$_SESSION['cb_saved'][$_POST['cb_key'][$i]];
				unset($_SESSION['cb_saved'][$_POST['cb_key'][$i]]);
				$_SESSION['cb_saved'][$_POST['cb_key_new'][$i]]=$value;
				$_GET['stab']='clipboards';
			}
		}
	}
	if(is_array($_POST['cb_del'])){
		for($i=0;$i<count($_POST['cb_del']);$i++){
			unset($_SESSION['cb_saved'][$_POST['cb_del'][$i]]);
		}
		$_GET['stab']='clipboards';
	}
	
	
	
	if(is_array($_POST['bm_key'])){
		for($i=0;$i<count($_POST['bm_key']);$i++){
			if($_POST['bm_key'][$i]!=$_POST['bm_key_new'][$i]){
				$value=$_SESSION['bookmarks'][$_POST['bm_key'][$i]];
				unset($_SESSION['bookmarks'][$_POST['bm_key'][$i]]);
				$_SESSION['bookmarks'][$_POST['bm_key_new'][$i]]=$value;
				$_GET['stab']='bookmarks';
			}
			
		}
	}
	if(is_array($_POST['bm_del'])){
		for($i=0;$i<count($_POST['bm_del']);$i++){
			unset($_SESSION['bookmarks'][$_POST['bm_del'][$i]]);
		}
		$_GET['stab']='bookmarks';
	}
	ksort($_SESSION['bookmarks']);
	ksort($_SESSION['cb_saved']);
	updateCookies();
	add_alert('Settings saved');
	
}
if($_REQUEST['action']=='settings_clipboard' && $_POST['save_as']){
	$ids=array();
	for($i=0;$i<count($_SESSION['clipboard']);$i++){
		$ids[]=$_SESSION['clipboard'][$i][2];
	}
	$_SESSION['cb_saved'][$_POST['save_as']]=$ids;
	ksort($_SESSION['cb_saved']);
	updateCookies();
	add_alert('Clipboard saved as '.$_POST['save_as']);
	$_SESSION['current_clipboard']=$_POST['save_as'];
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
	$_SESSION['current_clipboard']=$_GET['cb_load'];
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
	if($_GET['remove']=='all'){
		$_SESSION['clipboard']=array();
		$_SESSION['current_clipboard']='';
	}
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
if(isset($_GET['interval'])){
	set_post_from_until($_GET['interval']);
	$_SESSION['from']=toiso8601($_POST['from']);
	$_SESSION['until']=toiso8601($_POST['until']);
	$_SESSION['agrint']='';
	$_SESSION['agrfunc']='';
	
}

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
if(isset($_GET['interpolate'])) $_SESSION['interpolate']=$_GET['interpolate'];

/*if(isset($_SESSION['chartdata']) && $_SESSION['chartdata'] && count($_SESSION['clipboard'])>1){
	add_alert('Time series show data only works with <b>ONE</b> selected series!','warning');
	$_SESSION['chartdata']=0;
}*/

//set home
if(isset($_GET['action']) && $_GET['action']=='sethome'){
	if($_SESSION['cb2db'])
		xml_call('PreferenceHandler.setPreference',
		array(new xmlrpcval('bayeosviewer','string'),
				new xmlrpcval('homefolder','string'),
				new xmlrpcval($_GET['id'],'string')));
	else 
		setcookie('homefolder',$_GET['id'],time()+3600*24*180);
		
	$_SESSION['homefolder']=$_GET['id'];
	add_alert('New homefolder saved. You can jump to your home folder by clicking on BayEOS Server on the top left.');
	
}

//set bookmark
if(isset($_GET['action']) && $_GET['action']=='setbookmark'){
	$_GET['stab']='bookmarks';
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['id'],'int')));
	if(in_array($_GET['id'],$_SESSION['bookmarks'])){
		add_alert('&quot;'.$node[5].'&quot; is already in your bookmarks','warning');
		unset($_GET['action']);
	}
}
if(isset($_GET['action']) && $_GET['action']=='setbookmark'){
	$c=1;
	$name=$node[5];
	while(isset($_SESSION['bookmarks'][$name])){
		$name=$node[5].'_'.$c;
		$c++;
	}
	$_SESSION['bookmarks'][$name]=$_GET['id'];
	ksort($_SESSION['bookmarks']);
	updateCookies();
	add_alert('Added folder to your bookmarks');

}

?>