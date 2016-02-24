<?php
/**********************************************************
 * Called by autocomplete
 * 
 * returns JSON
 *********************************************************/
require './functions.php';

if(! isset($_SESSION['bayeosauth'])){
	header("HTTP/1.0 403 Access Denied");
	header("Status: 403 Access Denied");
	echo "<html><body><h1>Status: 403 Access Denied</h1></body></html>";
	exit();
}


$return=array();
if($_GET['refclass']=='acl'){
	$return=getUserGroups($_GET['search']);
} elseif($_GET['refclass']=='roles'){
	$return=getUserGroups($_GET['search'],'Gruppen');
} elseif($_GET['refclass']){
	$path='/';
	//THIS is a hack!!
	if($_GET['refclass']=='messung_%') $parent=get_root_id('messung_ordner');
	else $parent=get_root_id($_GET['refclass']);

	if(substr($_GET['search'],0,1)=='/'){
		$tmp=explode('/',$_GET['search']);
		$search_prefix='';
		$depth=0;
	} else {
		$tmp=explode('/','/'.$_GET['search']);
		$search_prefix='**/';
		$depth=(is_numeric($_GET['depth'])?$_GET['depth']:-1);
		if(is_numeric($_GET['parent'])){
			$parent=$_GET['parent'];
			$path='';
			$search_prefix='';
			$depth=0;
		}
	}
	for($i=1;$i<count($tmp);$i++){
		$res=xml_call('TreeHandler.getAllChildren',
				array(new xmlrpcval($parent,'int'),
						new xmlrpcval(false,'boolean'),
						xmlrpc_array(array('mitarbeiter','projekte')),
						new xmlrpcval($search_prefix.$tmp[$i].($i==(count($tmp)-1)?'*':''),'string'),
						new xmlrpcval($_GET['refclass'],'string'),
						new xmlrpcval($depth,'int'),
						new xmlrpcval(FALSE,'boolean'),
						new xmlrpcval('week','string'),
						new xmlrpcval(null,'null')
		));
		$parent=$res[0][0];
		if($i<(count($tmp)-1)) $path.=$tmp[$i].'/';
	}

	for($i=0;$i<count($res);$i++){
			array_push($return,array('label'=>$path.substr($res[$i][8],2).$res[$i][7],'value'=>$path.substr($res[$i][8],2).$res[$i][7],'id'=>$res[$i][0]));
	}
}
echo(json_encode($return));


?>