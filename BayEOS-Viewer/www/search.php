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
	$res=xml_call('TreeHandler.getAllChildren',
				array(new xmlrpcval(get_root_id($_GET['refclass']),'int'),
						new xmlrpcval(false,'boolean'),
						xmlrpc_array(array('mitarbeiter','projekte')),
						new xmlrpcval('**/'.$_GET['search'].'*','string'),
						new xmlrpcval($_GET['refclass'],'string'),
						new xmlrpcval(-1,'int'),
						new xmlrpcval(FALSE,'boolean'),
						new xmlrpcval('week','string'),
						new xmlrpcval(null,'null')
				));

	for($i=0;$i<count($res);$i++){
			array_push($return,array('label'=>$res[$i][8].$res[$i][7],'value'=>$res[$i][8].$res[$i][7],'id'=>$res[$i][0]));
	}
}
echo(json_encode($return));


?>