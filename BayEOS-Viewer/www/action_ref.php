<?php

//Delete Reference
if(isset($_GET['refdel'])){
	if(xml_call('ObjektHandler.deleteReference',array(new xmlrpcval($_GET['refdel'],'int'),
			new xmlrpcval($_GET['refclass'],'string'))))
		add_alert('Reference deleted');
}
//Create new reference node:
if($_REQUEST['action']=='ref' && ! $_POST['newref'] && $_POST['newref_dp']){
	$res=xml_call('TreeHandler.getAllChildren',
			array(new xmlrpcval(get_root_id($_POST['refclass']),'int'),
					new xmlrpcval(false,'boolean'),
					xmlrpc_array(array('mitarbeiter','projekte')),
					new xmlrpcval('**/'.$_POST['newref_dp'],'string'),
					new xmlrpcval($_POST['refclass'],'string'),
					new xmlrpcval(-1,'int'),
					new xmlrpcval(FALSE,'boolean'),
					new xmlrpcval('week','string'),
					new xmlrpcval(null,'null')
			));
	if(! count($res)){
		if($node=xml_call('TreeHandler.newNode',array(new xmlrpcval($_POST['refclass'],'string'),
			new xmlrpcval($_POST['newref_dp'],'string'),
			new xmlrpcval(get_root_id($_POST['refclass']),'int')))){
			$_POST['newref']=$node[2];
			add_alert('New node created');
		}
	} else 
		$_POST['newref']=$res[0][0];
}
//Create new reference:
if(isset($_POST['newref']) && is_numeric($_POST['newref'])){
	if(xml_call('ObjektHandler.createReference',array(
			new xmlrpcval($_POST['newref'],'int'),
			new xmlrpcval($_GET['edit'],'int'),
			new xmlrpcval($_POST['refclass'],'string'))))
		add_alert('Added reference');
}

if($_REQUEST['action']=='ref' && ! isset($_POST['addref']) ){
	for($i=0;$i<count($_POST['refids']);$i++){
		if($_POST["von$i"]!=$_POST["_old_von$i"] ||
				$_POST["bis$i"]!=$_POST["_old_bis$i"]){
			$res=xml_call('ObjektHandler.updateReference',
					array(new xmlrpcval($_POST['refids'][$i],'int'),
							new xmlrpcval($_POST['refart'][$i],'string'),
							new xmlrpcval(toiso8601($_POST["von$i"]),($_POST["von$i"]?'dateTime.iso8601':'null')),
							new xmlrpcval(toiso8601($_POST["bis$i"]),($_POST["bis$i"]?'dateTime.iso8601':'null'))
					));
			if($res)
				add_alert('Reference '.$_POST['refids'][$i].' updated');
				
		}
	}
	
}

$_GET['view']='ref';

?>