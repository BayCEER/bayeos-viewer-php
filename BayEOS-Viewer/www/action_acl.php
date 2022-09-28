<?php 
//Create new ACL:
if($_REQUEST['action']=='acl' && is_numeric($_POST['newaclid'])){
    for($i=0;$i<=4;$i++){
        if(! isset($_POST['newacl'][$i])) $_POST['newacl'][$i]=0;
    }
	if(xml_call('RightHandler.createRight',
			array(new xmlrpcval($_GET['edit'],'int'),
					new xmlrpcval($_POST['newaclid'],'int'),
					new xmlrpcval($_POST['newacl'][1],'boolean'),
					new xmlrpcval($_POST['newacl'][2],'boolean'),
					new xmlrpcval($_POST['newacl'][3],'boolean'),
					new xmlrpcval($_POST['newacl'][4],'boolean')
			))){
		add_alert('New ACL created');
	}
	$_GET['view']='acl';
}
//Delete ACL
if(isset($_GET['acldel']) && is_numeric($_GET['acldel'])){
	if(xml_call('RightHandler.deleteRight',
			array(new xmlrpcval($_GET['edit'],'int'),
					new xmlrpcval($_GET['acldel'],'int')
			))){
		add_alert('ACL removed');
	}
	$_GET['view']='acl';
}
//Update ACL
if($_REQUEST['action']=='acl' && isset($_POST['aclids'])){
	$res=0;
	$rights=array(1=>'read',2=>'write',3=>'exec',4=>'inherit');
	for($i=0;$i<count($_POST['aclids']);$i++){
		$id=$_POST['aclids'][$i];
		for($j=1;$j<=4;$j++){
		    if(! isset($_POST['acl'.$id.'_'.$j])) $_POST['acl'.$id.'_'.$j]=0;
		    if(! isset($_POST['_old_acl'.$id.'_'.$j])) $_POST['_old_acl'.$id.'_'.$j]=0;
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
		$_GET['view']='acl';
	}
}


?>