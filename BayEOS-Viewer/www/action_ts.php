<?php 
for($i=0;$i<count($_POST['ts']);$i++){
	$ts=$_POST['ts'][$i];
	$res=0;
	if($_POST['ts'.$ts]!=$_POST['_old_ts'.$ts] ||
			$_POST['v'.$ts]!=$_POST['_old_v'.$ts]  ||
			$_POST['s'.$ts]!=$_POST['_old_s'.$ts] ){
		if($_POST['ts'.$ts] && $_POST['v'.$ts]!=''){
			$res=xml_call('MassenTableHandler.updateRow',
					array(new xmlrpcval($_GET['edit'],'int'),
							new xmlrpcval(gmdate('Ymd\TH:i:s',$ts+3600),'dateTime.iso8601'),
							new xmlrpcval($_POST['s'.$ts],'int'),
							new xmlrpcval($_POST['v'.$ts],'double'),
							new xmlrpcval(toiso8601($_POST['ts'.$ts]),'dateTime.iso8601')));
			if($res)
				add_alert('Series point '.$ts.' updated');
		} else {
			$res=xml_call('MassenTableHandler.removeRows',
					array(new xmlrpcval($_GET['edit'],'int'),
							xmlrpc_array(array(gmdate('Ymd\TH:i:s',$ts+3600)),'dateTime.iso8601')));
			if($res)
				add_alert('Series point '.$ts.' deleted');
				
		}
		
	}
	
}

if($_POST['nts'] && $_POST['nv']!=''){
	$res=xml_call('MassenTableHandler.addRow',
			array(new xmlrpcval($_GET['edit'],'int'),
					new xmlrpcval(toiso8601($_POST['nts']),'dateTime.iso8601'),
					new xmlrpcval($_POST['nv'],'double'),
					new xmlrpcval($_POST['ns'],'int')
			));
	if($res)
		add_alert('Series point added');

}

?>