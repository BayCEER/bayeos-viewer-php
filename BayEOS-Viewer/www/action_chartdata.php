<?php
$status=array();
while(list($k,$v)=each($_SESSION['Status'])){
	$status[$v[0]]=$v[1];
}
reset($_SESSION['Status']);
if(isset($_POST['ts'])){
	$ts=array();
	for($i=0;$i<count($_POST['ts']);$i++){
		$ts[]=gmdate('Ymd\TH:i:s',$_POST['ts'][$i]+3600);
	}
	$ts=xmlrpc_array($ts,'dateTime.iso8601');
	for($i=0;$i<count($_SESSION['clipboard']);$i++){
		if(isset($_POST['_action_remove'])){
			$res=xml_call('MassenTableHandler.removeRows',
					array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),$ts));
			if($res)
				add_alert('Deleted data points of series <b>'.$_SESSION['clipboard'][$i][5].'</b> 
						for selected timestamps');

		} elseif($_POST['status']!='') {
			$res=xml_call('MassenTableHandler.updateRows',
					array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),$ts,
							new xmlrpcval($_POST['status'],'int')));
			if($res)
				add_alert('Status of series <b>'.$_SESSION['clipboard'][$i][5].'</b> set to <b>'.$status[$_POST['status']].'</b>
						for selected timestamps');

		}
	}
} elseif(isset($_POST['from']) &&
		$_POST['from'] && $_POST['until']){
	//Note: We set the from 1 second back to have the border 'inclusive'
	$from = new xmlrpcval(toios8601FromEpoch(toEpoch(toiso8601($_POST['from']))-1),'dateTime.iso8601');
	$until= new xmlrpcval(toiso8601($_POST['until']),'dateTime.iso8601');
	for($i=0;$i<count($_SESSION['clipboard']);$i++){
		if(isset($_POST['_action_remove'])){
			$res=xml_call('MassenTableHandler.removeRowsByInterval',
					array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
							$from,
							$until));
			if($res)
				add_alert('Deleted data points of series <b>'.$_SESSION['clipboard'][$i][5].'</b>
						 for interval '.$_POST['from'].' to '.$_POST['until']);
				
		} elseif($_POST['status']!=''){
			$res=xml_call('ToolsHandler.updateRows',
					array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
							new xmlrpcval('messung_massendaten','string'),
							$from,
							$until,
							new xmlrpcval($_POST['status'],'int')));
			if($res)
				add_alert('Status of series <b>'.$_SESSION['clipboard'][$i][5].'</b> set to <b>'.$status[$_POST['status']].'</b>
						for interval '.$_POST['from'].' to '.$_POST['until']);
		}
	}

}



?>