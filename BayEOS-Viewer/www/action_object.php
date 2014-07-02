<?php
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
	$_POST['parentid']=get_root_id($bayeos_tree_unames[$_SESSION['tab']]);
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
if(isset($_GET['edit']) && is_numeric($_GET['edit']) && isset($_POST["t5"])){
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


?>
