<?php 
/**********************************************************
 * Actions 
 * 
 * Changes of $_SESSION and all update XML-RPC-Calls
 * Error and Success are reported by $GLOBALS['alert']
 *********************************************************/


/***********************************************************
 * Client Timezone
***********************************************************/
if(isset($_POST['tz']) && $_POST['tz'])
	$_SESSION['tz']=$_POST['tz'];
	
if($_SESSION['tz'])
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
if($_SESSION['tab']=='Chart' && ! count($_SESSION['clipboard'])){
	add_alert("Please add series to your clipboard",'warning');
	$_SESSION['tab']='Folders';
}

/***********************************************************
 * Login Action
 ***********************************************************/

if(isset($_POST['login']) && isset($_POST['password'])){
	$config=parse_ini_file('/etc/bayeos-viewer.ini');
	if(isset($config['serverURL'])) $_SESSION['bayeosurl']=$config['serverURL'];
	else $_SESSION['bayeosurl']='http://'.$_SERVER['SERVER_ADDR'].'/BayEOS-Server/XMLServlet';
	
	$res = xml_call("LoginHandler.createSession",
			array(new xmlrpcval($_POST['login'],'string'),
					new xmlrpcval($_POST['password'],'string')));
	if($res===false) {

	} else {
		$_SESSION['bayeosauth']=base64_encode($res[0].':'.$res[1]);    	
		$_SESSION['from']=date('Ymd',time()-24*3600).'T00:00:00';
		$_SESSION['until']=date('Ymd',time()+24*3600).'T00:00:00';
		$_SESSION['login']=$_POST['login'];
		$_SESSION['breadcrumbs']=array();
		$_SESSION['clipboard']=array();
		$_SESSION['rootids']=array();
		$_SESSION['id']=get_root_id('messung_ordner');
		$_SESSION['breadcrumbs'][]=xml_call('TreeHandler.getNode',array(new xmlrpcval($_SESSION['id'],'int')));
		$_SESSION['tab']='Folders';
		$_SESSION['current_tree']='Folders';
		$_SESSION['IntervalTypes']=xml_call('LookUpTableHandler.getIntervalTypes',array());
		$_SESSION['TimeZones']=xml_call('LookUpTableHandler.getTimeZones',array());
		$_SESSION['AgrFunktionen']=xml_call('LookUpTableHandler.getAgrFunktionen',array());
		$_SESSION['AgrIntervalle']=xml_call('LookUpTableHandler.getAgrIntervalle',array());
		$_SESSION['CRS']=xml_call('LookUpTableHandler.getCRS',array());
		$_SESSION['Status']=xml_call('LookUpTableHandler.getStatus',array());
		$_SESSION['DataTypes']=array('DOUBLE','INTEGER','DATE','BOOLEAN','STRING');
		$_SESSION['agrint']='';
		$_SESSION['agrfunc']='';
		$_SESSION['StatusFilter']=array(0,1,2);
		if($_SESSION['tz']=='Europe/Berlin'){
			$_SESSION['csv_sep']=';';
			$_SESSION['csv_dec']=',';
			$_SESSION['csv_dateformat']='d.m.Y H:i:s';				
		} else {
			$_SESSION['csv_sep']=',';
			$_SESSION['csv_dec']='.';
			$_SESSION['csv_dateformat']='Y-m-d H:i:s';	
		}
		$_SESSION['RefClasses']=array(array('mess_ziel','Target'),
				array('mess_einheit','Unit'),
				array('mess_geraet','Device'),
				array('mess_kompartiment','Compartment'),
				array('mess_ort','Location')//,array('web','Web')
				);
	}
}

/***********************************************************
 * Logout Action
 ***********************************************************/
 
if($_GET['aktion']=='logout'){
	$res = xml_call("LogOffHandler.terminateSession",array());
	if($res===true){
		unset($_SESSION['bayeosauth']);
	}
	unset($_SESSION['bayeosauth']);
}

/***********************************************************
 * ACL Actions
 ***********************************************************/

//Create new ACL:
if(isset($_POST['_action_acl_save']) && is_numeric($_POST['newaclid'])){
	if(xml_call('RightHandler.createRight',
	array(new xmlrpcval($_GET['edit'],'int'),
		new xmlrpcval($_POST['newaclid'],'int'),
		new xmlrpcval($_POST['newacl'][1],'boolean'),
		new xmlrpcval($_POST['newacl'][2],'boolean'),
		new xmlrpcval($_POST['newacl'][3],'boolean'),
		new xmlrpcval($_POST['newacl'][4],'boolean')
		))){
		$_POST['newref']=$node[2];
		add_alert('New ACL created');
	}
	$_GET['view']='acl';
}
//Delete ACL
if(is_numeric($_GET['acldel'])){
	if(xml_call('RightHandler.deleteRight',
			array(new xmlrpcval($_GET['edit'],'int'),
					new xmlrpcval($_GET['acldel'],'int')
			))){
		add_alert('ACL removed');
	}
	$_GET['view']='acl';
}
//Update ACL
if(isset($_POST['_action_acl_save'])){
	$res=0;
	$rights=array(1=>'read',2=>'write',3=>'exec',4=>'inherit');
	for($i=0;$i<count($_POST['aclids']);$i++){
		$id=$_POST['aclids'][$i];
		for($j=1;$j<=4;$j++){
			if(($_POST['acl'.$id.'_'.$j] && !$_POST['_old_acl'.$id.'_'.$j])
					|| (! $_POST['acl'.$id.'_'.$j] && $_POST['_old_acl'.$id.'_'.$j]))
				$res=xml_call('RightHandler.updateRight',
			array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval($id,'int'),
				new xmlrpcval($rights[$j],'string'),
				new xmlrpcval($_POST['acl'.$id.'_'.$j],'boolean')
			));
		}
	}

	if($res){
		add_alert('ACL updated');
		$GLOBALS['alert'].='<div class="alert alert-success">ACL updated</div>';
		$_GET['view']='acl';
	}
}


/***********************************************************
 * Reference Actions
***********************************************************/

//Delete Reference
if(isset($_GET['refdel'])){
	if(xml_call('ObjektHandler.deleteReference',array(new xmlrpcval($_GET['refdel'],'int'),
			new xmlrpcval($_GET['refclass'],'string'))))
		add_alert('Reference deleted');
	$_GET['view']='ref';
}
//Create new reference node:
if(isset($_POST['_action_ref_save']) && ! $_POST['newref'] && $_POST['newref_dp']){
	if($node=xml_call('TreeHandler.newNode',array(new xmlrpcval($_POST['refclass'],'string'),
		new xmlrpcval($_POST['newref_dp'],'string'),
		new xmlrpcval($_SESSION['rootids'][$_POST['refclass']],'int')))){
		$_POST['newref']=$node[2];
		add_alert('New node created');
	}
}
//Create new reference:
if(is_numeric($_POST['newref'])){
	if(xml_call('ObjektHandler.createReference',array(
		new xmlrpcval($_POST['newref'],'int'),
		new xmlrpcval($_GET['edit'],'int'),		
		new xmlrpcval($_POST['refclass'],'string'))))
		add_alert('Reference created');
	$_GET['view']='ref';
}

if(isset($_POST['_action_ref_save']) && ! $_POST['addref']){
	for($i=0;$i<count($_POST['refids']);$i++){
		if($_POST["von$i"]!=$_POST["_old_von$i"] ||
				$_POST["bis$i"]!=$_POST["_old_bis$i"])
			$res=xml_call('ObjektHandler.updateReference',
					array(new xmlrpcval($_POST['refids'][$i],'int'),
							new xmlrpcval($_POST['refart'][$i],'string'),
							new xmlrpcval(toiso8601($_POST["von$i"]),($_POST["von$i"]?'dateTime.iso8601':'null')),
							new xmlrpcval(toiso8601($_POST["bis$i"]),($_POST["bis$i"]?'dateTime.iso8601':'null'))
							));
	}
	if($res){
		add_alert('References updated');
	  	$_GET['view']='ref';
	}
}

/***********************************************************
 * Node Actions
***********************************************************/

//Delete Node
if(isset($_POST['_action_remove']) && is_numeric($_GET['edit'])){
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['edit'],'int')));
	if(xml_call('TreeHandler.deleteNode',array(new xmlrpcval($_GET['edit'],'int')))){
		add_alert('Node '.$_GET['edit'].' deleted');
		unset($_GET['edit']);
	}
	$_GET['id']=$node[3];
}

//Move Node - Find root node
if(isset($_POST['parentroot']) && $_POST['parentroot']){
	$class='messung_ordner';
	if(! $_SESSION['rootids'][$class]){
		$res=xml_call("TreeHandler.getRoot",
				array(new xmlrpcval($_GET['refclass'],'string'),
						new xmlrpcval(FALSE,'boolean'),
						new xmlrpcval('week','string'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['from'],'dateTime.iso8601'),
								new xmlrpcval($_SESSION['until'],'dateTime.iso8601')),'array')));
		$_SESSION['rootids'][$class]=$res[2];
	}
	$_POST['parentid']=$_SESSION['rootids'][$class];
} 
//Move Node
if(isset($_POST['parentid']) && is_numeric($_POST['parentid'])){
	if(xml_call('TreeHandler.moveNode',array(new xmlrpcval($_GET['edit'],'int'),
			new xmlrpcval($_POST['parentid'],'int')))){
		add_alert('Node moved to '.$_POST['parentid'].'');
		$_SESSION['breadcrumbs']=array($_SESSION['breadcrumbs'][0]);
	}
}

//Rename Node:
if(isset($_POST["t5"]) && $_POST["t5"] && $_POST["t5"]!=$_POST["_old_t5"] && is_numeric($_GET['edit'])){
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['edit'],'int')));
	if(xml_call('TreeHandler.renameNode',array(new xmlrpcval($_GET['edit'],'int'),
			new xmlrpcval($node[4],'string'),
			new xmlrpcval($_POST["t5"],'string'))))
		add_alert('Node renamed');
}

//New Node
if(isset($_POST["t5"]) && $_POST["t5"] && isset($_GET['edit']) && ! is_numeric($_GET['edit'])){
	$node=xml_call('TreeHandler.newNode',array(new xmlrpcval($_GET['edit'],'string'),
			new xmlrpcval($_POST["t5"],'string'),
			new xmlrpcval($_SESSION['id'],'int')));
	if($node){
		add_alert('Node created');
		$_GET['edit']=$node[2];//set edit for Display
	}
}

/***********************************************************
 * Object Actions
***********************************************************/

//Save Object:
if(is_numeric($_GET['edit']) && isset($_POST["t5"])){
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['edit'],'int')));
	$changed=($_POST["t5"]!=$_POST["_old_t5"]);
	$ofields=get_object_fields($node[4]);
	for($i=0;$i<count($ofields);$i++){
		if($_POST['o'.$ofields[$i]['nr']]!=$_POST['_old_o'.$ofields[$i]['nr']]){
			$i=count($ofields);
			$changed=1;
		}
	}
	if($changed){
		$values=array(new xmlrpcval($_POST["t5"],'string'));
		for($i=0;$i<count($ofields);$i++){
			$value=$_POST["o".$ofields[$i]['nr']];
			$type=(isset($ofields[$i]['xmltype'])?$ofields[$i]['xmltype']:$ofields[$i]['type']);
			if($type=='dateTime.iso8601'){
				$type=($value?'dateTime.iso8601':'null');
				$value=toiso8601($value);
			}
			if(($type=='int' || $type=='double') && ! $value)
				$type='null';
			
			$values[$ofields[$i]['unr']]=new xmlrpcval($value,$type);
		}
		$res=xml_call('ObjektHandler.updateObjekt',
				array(new xmlrpcval($_GET['edit'],'int'),
						new xmlrpcval($node[4],'string'),
						new xmlrpcval($values,'array')));
		if($res)
			add_alert('Object updated');
	}
	
}	

/***********************************************************
 * Massendaten Update + Delete
***********************************************************/

if(isset($_GET['action']) && $_GET['action']=='chartdata'){
	$ts=array();
	for($i=0;$i<count($_POST['ts']);$i++){
		$ts[]=gmdate('Ymd\TH:i:s',$_POST['ts'][$i]+3600);
	}
	$ts=xmlrpc_array($ts,'dateTime.iso8601');
	
	if(isset($_POST['_action_remove'])){
		$res=xml_call('MassenTableHandler.removeRows',
				array(new xmlrpcval($_SESSION['clipboard'][0][2],'int'),$ts));
		if($res)
			add_alert('Series points deleted');
		
	} else {
		$res=xml_call('MassenTableHandler.updateRows',
				array(new xmlrpcval($_SESSION['clipboard'][0][2],'int'),$ts,
						new xmlrpcval($_POST['status'],'int')));
		if($res)
			add_alert('Series status updated');
		
	}
}

/***********************************************************
 * DataFrame
 ***********************************************************/
if(isset($_POST['csv_df'])){
	$_SESSION['csv_dec']=$_POST['csv_dec'];
	$_SESSION['csv_sep']=$_POST['csv_sep'];
	$_SESSION['csv_tz']=$_POST['csv_tz'];
	$_SESSION['csv_dateformat']=$_POST['csv_dateformat'];
	header('Location: ./csv_df.php?id='.$_GET['edit']);
}

if(isset($_POST['_action_df'])){
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
 * Clipboard
 ***********************************************************/

//Clipboard add
if(isset($_GET['add']) && is_numeric($_GET['add'])){
	function addToClipboard($node){
		for($i=0;$i<count($_SESSION['clipboard']);$i++){
			if($_SESSION['clipboard'][$i][2]==$node){
				add_alert($_SESSION['clipboard'][$i][5]. "(ID $node) is already on your 
						<a href=\"?tab=Clipboard\" class=\"alert-link\">clipboard</a>",'warning');
				return 1;
			}
		}
		
		$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($node,'int')));
		if($node[4]=='messung_massendaten'){		
			$object=xml_call('ObjektHandler.getObjekt',array(new xmlrpcval($node[2],'int'),
				new xmlrpcval('messung_massendaten','string')));
			$node['res']=$object[22];
			$node['path']=array((isset($_GET['folder_id'])?$_GET['folder_id']:$_SESSION['id']),
					$_SESSION['currentpath'].$_GET['subpath']);
			$_SESSION['clipboard'][]=$node;
			add_alert($node[5].' (ID '.$node[2].') added to your
					<a href="?tab=Clipboard" class="alert-link">clipboard</a>');
			return 1;
		}
		return 0;
	}	

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
			$_GET['subpath'].='/'.$node[5];
			for($i=0;$i<count($childs);$i++){
				addToClipboard($childs[$i][0]);
			}
		}
	}
	$_SESSION['chartdata']=0;
}

//Clipboard remove
if(isset($_GET['remove']) && $_GET['remove']=='all'){
	$_SESSION['clipboard']=array();
}

if(isset($_GET['remove']) && is_numeric($_GET['remove'])){
	$tmp=$_SESSION['clipboard'];
	$_SESSION['clipboard']=array();
	for($i=0;$i<count($tmp);$i++){
		if($tmp[$i][2]!=$_GET['remove']) $_SESSION['clipboard'][]=$tmp[$i];
	}
}


/***********************************************************
 * Filter
 ***********************************************************/
//Set StatusFilter 
if(isset($_POST['setStatusFilter'])){
	$_SESSION['StatusFilter']=array();
	while(list($key,$v)=each($_SESSION['Status'])){
		if(isset($_POST['s'.$v[0]])) $_SESSION['StatusFilter'][]=$v[0];
	}
	reset($_SESSION['Status']);
}

if(isset($_POST['setCSVOptions'])){
	$_SESSION['csv_dec']=$_POST['csv_dec'];
	$_SESSION['csv_sep']=$_POST['csv_sep'];
	$_SESSION['csv_tz']=$_POST['csv_tz'];
	$_SESSION['csv_dateformat']=$_POST['csv_dateformat'];
}

if(isset($_POST['setFilter'])){
	if(isset($_POST['csv'])){
		if(isset($_POST['session_from'])) $_SESSION['csv_from']=toiso8601($_POST['session_from']);
		if(isset($_POST['session_until'])) $_SESSION['csv_until']=toiso8601($_POST['session_until']);
		$_SESSION['csv_agrint']=$_POST['session_agrint'];
		$_SESSION['csv_agrfunc']=$_POST['session_agrfunc'];
		header('Location: ./csv.php');
		exit();
	} else {
		if(isset($_POST['session_from'])) $_SESSION['from']=toiso8601($_POST['session_from']);
		if(isset($_POST['session_until'])) $_SESSION['until']=toiso8601($_POST['session_until']);
		$_SESSION['agrint']=$_POST['session_agrint'];
		$_SESSION['agrfunc']=$_POST['session_agrfunc'];
	}
	if(isset($_POST['chart'])){
		$_SESSION['tab']='Chart';
	}
	
}
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
if($_SESSION['chartdata'] && count($_SESSION['clipboard'])>1){
	add_alert('Time series editing only works with <b>ONE</b> selected series!','warning');
	$_SESSION['chartdata']=0;
}


?>