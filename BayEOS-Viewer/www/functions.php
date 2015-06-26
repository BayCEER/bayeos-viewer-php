<?php
session_start();

require_once 'xmlrpc.inc';
$xmlrpc_internalencoding = 'UTF-8';
require_once './constants.php';

$GLOBALS['alert']='';
function add_alert($text,$type='success',$dismissable=TRUE){
	$GLOBALS['alert'].='<div class="alert alert-'.$type.($dismissable?' alert-dismissable">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>':'">').
	$text.'</div>';
}

function set_post_from_until($interval){
	switch($interval){
		case 'last 24 hours':
			$_POST['from']=date('y-m-d H:i',time()-3600*24);
			$_POST['until']=date('y-m-d H:i',time());
			break;
		case 'last 3 days':
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*3);
			$_POST['until']=date('y-m-d H:i',time());
			break;
		case 'last 7 days':
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*7);
			$_POST['until']=date('y-m-d H:i',time());
			break;
		case 'last 30 days':
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*30);
			$_POST['until']=date('y-m-d H:i',time());
			break;
		case 'today':
			$_POST['from']=date('y-m-d 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'yesterday':
			$_POST['from']=date('y-m-d 00:00',time()-3600*24);
			$_POST['until']=date('y-m-d 00:00');
			break;
		case 'this week':
			$weekday=date('N');
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*($weekday-1));
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last week':
			$weekday=date('N');
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*($weekday+6));
			$_POST['until']=date('y-m-d 00:00',time()-3600*24*($weekday-1));
			break;
		case 'this month':
			$_POST['from']=date('y-m-01 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last month':
			$last_month=date('m')-1;
			$year=date('y');
			if($last_month==0){
				$last_month=12;
				$year--;
			}
			$_POST['from']=$year.'-'.$last_month.'-01 00:00';
			$_POST['until']=date('y-m-01 00:00');
			break;
		case 'this year':
			$_POST['from']=date('y-01-01 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last year':
			$_POST['from']=date('y-01-01 00:00',time()-365*3600*24);
			$_POST['until']=date('y-12-31 00:00',time()-365*3600*24);
			break;
				
	}
	
}

function DBQueryParams($query,$params){
	if(! $GLOBALS['conn'])
		$GLOBALS['conn']=pg_connect($_SESSION['dbConnection']);
	if(! $GLOBALS['conn']){
		add_alert('Could not get database connection','danger');
		return false;
	}
	$res=pg_query_params($GLOBALS['conn'],$query, $params);
	if(! $res){
		add_alert(pg_last_error($GLOBALS['conn']),'danger');
	}
	return $res;
}

function updateCookies(){
	setcookie('max_cols',$_SESSION['max_cols'],time()+3600*24*180);
	setcookie('max_rows',$_SESSION['max_rows'],time()+3600*24*180);
	setcookie('gnuplot',$_SESSION['gnuplot'],time()+3600*24*180);
	setcookie('cb2db',$_SESSION['cb2db'],time()+3600*24*180);
	if($_SESSION['cb2db']){
		xml_call('PreferenceHandler.setPreference',
				array(new xmlrpcval('bayeosviewer','string'),
					  new xmlrpcval('cb_saved','string'),
					  new xmlrpcval(serialize($_SESSION['cb_saved']),'string')));
		setcookie('cb_saved',null,-1);
	}
	else
		setcookie('cb_saved',serialize($_SESSION['cb_saved']),time()+3600*24*180);
}

function get_object_fields($uname){
	switch($uname){
		case 'messung_ordner':
		case 'messung_massendaten':
			$ofields=array(
				array('name'=>'Plan Start','nr'=>15,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>3),
				array('name'=>'Plan End','nr'=>16,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>4),
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string'),
				array('name'=>'Resolution','nr'=>22,'cols'=>4,'type'=>'int','unr'=>2,'default'=>600),
				array('name'=>'Interval Type','nr'=>23,'cols'=>4,'type'=>'IntervalTypes','unr'=>5,'xmltype'=>'int','default'=>0),
				array('name'=>'Time Zone','nr'=>24,'cols'=>4,'type'=>'TimeZones','unr'=>6,'xmltype'=>'int','default'=>1)
			);
		break;
		case 'mess_geraet':
			$ofields=array(
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string'),
				array('name'=>'Serial Number','nr'=>22,'cols'=>4,'type'=>'string','unr'=>2)
			);			
		break;
		case 'mess_einheit':
			$ofields=array(
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string'),
				array('name'=>'Symbol','nr'=>22,'cols'=>4,'type'=>'string','unr'=>2)
			);			
		break;
		case 'mess_ziel':
			$ofields=array(
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string'),
				array('name'=>'Formel','nr'=>22,'cols'=>4,'type'=>'string','unr'=>2)
			);			
		break;
		case 'mess_kompartiment':
			$ofields=array(
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string')
			);				
		break;
		case 'mess_ort':
			$ofields=array(
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>1,'xmltype'=>'string'),
				array('name'=>'x','nr'=>22,'cols'=>3,'type'=>'double','unr'=>2),
				array('name'=>'y','nr'=>23,'cols'=>3,'type'=>'double','unr'=>3),
				array('name'=>'z','nr'=>24,'cols'=>3,'type'=>'double','unr'=>4),
				array('name'=>'CRS','nr'=>25,'cols'=>3,'type'=>'CRS','unr'=>5,'xmltype'=>'int')
				
				);				
		break;
		case 'data_frame':
		case 'data_column':
			$ofields=array(
				array('name'=>'Plan Start','nr'=>15,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>0),
				array('name'=>'Plan End','nr'=>16,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>1),
				array('name'=>'Rec Start','nr'=>17,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>2),
				array('name'=>'Rec End','nr'=>18,'cols'=>6,'type'=>'dateTime.iso8601','unr'=>3),
				array('name'=>'Name','nr'=>13,'cols'=>0,'type'=>'hidden','unr'=>4,'xmltype'=>'string'),
				array('name'=>'Description','nr'=>21,'cols'=>12,'type'=>'text','unr'=>5,'xmltype'=>'string'),
				array('name'=>'Time Zone','nr'=>22,'cols'=>4,'type'=>'TimeZones','unr'=>6,'xmltype'=>'int','default'=>1)
			);
			$_POST['o13']=$_POST['t5']; //Hack! Set Name=Node-Name!!
		break;
	}
	if($uname=='data_column'){
		$ofields[6]=array('name'=>'Column Index','nr'=>22,'cols'=>6,'type'=>'int','unr'=>6,'default'=>1);
		$ofields[7]=array('name'=>'Data Type','nr'=>23,'cols'=>6,'type'=>'DataTypes','unr'=>7,'xmltype'=>'string');
	}
	return $ofields;
}


function getUserGroups($tag,$select=''){
	if(! isset($_SESSION['Benutzer']))
		$_SESSION['Benutzer']=xml_call('LookUpTableHandler.getBenutzer',array());
	if(! isset($_SESSION['Gruppen']))
		$_SESSION['Gruppen']=xml_call('LookUpTableHandler.getGruppen',array());
	$res=array();
	if($select=='' || $select=='Gruppen'){
		for($i=0;$i<count($_SESSION['Gruppen']);$i++){
			if(strstr($_SESSION['Gruppen'][$i][20],$tag))
				$res[]=array('label'=>$_SESSION['Gruppen'][$i][20],'value'=>$_SESSION['Gruppen'][$i][20],'id'=>$_SESSION['Gruppen'][$i][2]);
		}
	}
	if($select=='' || $select=='Benutzer'){
		for($i=0;$i<count($_SESSION['Benutzer']);$i++){
			if(strstr($_SESSION['Benutzer'][$i][20],$tag))
				$res[]=array('label'=>$_SESSION['Benutzer'][$i][20],'value'=>$_SESSION['Benutzer'][$i][20],'id'=>$_SESSION['Benutzer'][$i][2]);
		}
	}
	reset($_SESSION['Benutzer']);
	reset($_SESSION['Gruppen']);
	return($res);
}

//Extract common path from clipboard series
function get_folder_subfolders(){
	$folders=array();
	$res=array();
	for($i=0;$i<count($_SESSION['clipboard']);$i++){
		$folders[$i]=explode('/',$_SESSION['clipboard'][$i]['path'][1]);
	}
	$max=count($folders[0])-1;
	for($i=1;$i<count($folders);$i++){
		$j=0;
		while($j<=$max && isset($folders[$i][$j]) && isset($folders[0][$j]) && 
				$folders[$i][$j]==$folders[0][$j])
			$j++;
		$j--;
		if($j<$max) $max=$j;
	}
	
	$res['folder']=implode('/',array_slice($folders[0],0,$max+1));
	$has_subpath=FALSE;
	$subfolders=array();
	for($i=0;$i<count($folders);$i++){
		if(count($folders[$i])>($max+1)){
			$subfolders[$i]=implode('/',array_slice($folders[$i],$max+1));
			$has_subpath=TRUE;
		} else
			$subfolders[$i]='';
	}
	if($has_subpath) $res['subfolders']=$subfolders;
	return $res;
	
}

function get_root_id($uname){
	if(! isset($_SESSION['rootids'][$uname])){
		$res=xml_call("TreeHandler.getRoot",
				array(new xmlrpcval($uname,'string'),
						new xmlrpcval(FALSE,'boolean'),
						new xmlrpcval('week','string'),
						new xmlrpcval(null,'null')));
		$_SESSION['rootids'][$uname]=$res[2];
	}
	return $_SESSION['rootids'][$uname];
}


function ft($t){
	if($t<10) $t="0$t";
	return $t;
}

function xmlrpc_array($values,$type='string'){
	$val=array();
	while(list($key,$value)=each($values)){
		$val[]=new xmlrpcval($value,$type);
	}
	return new xmlrpcval($val,'array');
}

function xml_call($method,$args){

	$request = new xmlrpcmsg($method,$args);

	//echo htmlspecialchars($request->serialize());
	$context = stream_context_create(array('http' => array(
			'method' => "POST",
			'header' => "Content-Type: text/xml charset=UTF-8".
			(isset($_SESSION['bayeosauth'])?"\nAuthentication:".$_SESSION['bayeosauth']:''),
			'content' => $request->serialize()
	)));
	$file = file_get_contents($_SESSION['bayeosurl'],
			false, $context);
	if($file===false){
		$error=error_get_last();
		$GLOBALS['alert'].='<div class="alert alert-danger">XMLRPC-Error: No response from BayEOS-Server using URL '.$_SESSION['bayeosurl'].'<br/>
		<b>This is the error message:</b> '.$error['message'].'</div>';
		return false;
	}
	//echo htmlspecialchars($file);
	$response = xmlrpc_decode($file,'UTF-8');
	if ($response && is_array($response) && xmlrpc_is_fault($response)) {
		$GLOBALS['alert'].='<div class="alert alert-danger">'."xmlrpc $method: $response[faultString] ($response[faultCode])".'</div>';
		return false;
	} else {
		return $response;
	}

}
function toios8601FromEpoch($epoch){
	return(gmdate('Ymd\TH:i:s',$epoch+3600));
}

function toEpoch($isodate){
	$tmp=date_parse($isodate);
	return gmmktime($tmp['hour'],$tmp['minute'],$tmp['second'],$tmp['month'],$tmp['day'],$tmp['year'])-3600;
}

function toDateFromString($isodate){
	$tmp=date_parse($isodate);
	$timestamp=gmmktime($tmp['hour'],$tmp['minute'],$tmp['second'],$tmp['month'],$tmp['day'],$tmp['year']);
	return ($timestamp?date('Y-m-d H:i',$timestamp-3600):'');
}

function toDate($isodate){
	return( (is_object($isodate) && $isodate->timestamp?date('Y-m-d H:i',$isodate->timestamp-3600):'') );
}

function toiso8601($date){
	if(! $date) return NULL;
	$tmp=date_parse($date);
	return(gmdate('Ymd\TH:i:s',mktime($tmp['hour'],$tmp['minute'],$tmp['second'],$tmp['month'],$tmp['day'],$tmp['year'])+3600));
}

function getName($id){
	if(! isset($GLOBALS['treehash'][$id])){
		$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($id,'int')));
		$GLOBALS['treehash'][$id]=array($node[3],$node[5]);
	} 
	return $GLOBALS['treehash'][$id];
}

function getPath($id_parent){
	if($_SESSION['id']==$id_parent) return $_SESSION['currentpath'];
	$path='';
	while($id_parent){
		list($id_parent,$name)=getName($id_parent);
		$path='/'.$name.$path;
		if($id_parent==$_SESSION['id']) return $_SESSION['currentpath'].$path;
	}
	return $path;
}

function addToClipboard($node,$alert=1){
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
			$node['path']=array($node[3],getPath($node[3]));
			$_SESSION['clipboard'][]=$node;
			if($alert) 
				add_alert($node[5].' (ID '.$node[2].') added to your
					<a href="?tab=Clipboard" class="alert-link">clipboard</a>');
			return 1;
	}
	return 0;
}

function getSeries($ids,$aggfunc,$aggint,$timefilter,$filter_arg){
	$res=array();
	if(! is_array($ids)) $ids=array($ids);
	if(count($ids)==1){
		if(! $aggfunc || !$aggint){
			$val=xml_call('MassenTableHandler.getRows',
					array(new xmlrpcval($ids[0],'int'),
							$timefilter,
							$filter_arg));
			$val=$val[1]->scalar;
			$pos=0;
			$i=0;
			while($pos<strlen($val)){
				$tmp=unpack('N',substr($val,$pos,4));
				$res['datetime'][$i]=$tmp[1];
				$tmp=unpack('N',substr($val,$pos+4,4));
				$t=pack('L',$tmp[1]);
				$tmp=unpack('f',$t);
				$res[0][$i]=$tmp[1];				
				$tmp=unpack('c',substr($val,$pos+8,1));
				$res['status'][$i]=$tmp[1];
				$pos+=9;
				$i++;
			}
		} else {
			$val=xml_call('AggregationTableHandler.getRows',
					array(new xmlrpcval($ids[0],'int'),
							$timefilter,
							$filter_arg));
			$val=$val[1];
			for($j=0;$j<count($val);$j++){
				$res['datetime'][$j]=$val[$j][0]->timestamp-3600;
				$res[0][$j]=$val[$j][1];
			}
		}
	} else {
		if(!$aggfunc || !$aggint){
			$func='MassenTableHandler.getMatrix';
		}
		else {
			$func='AggregationTableHandler.getMatrix';
		}
		$val=xml_call($func,
				array(xmlrpc_array($ids,'int'),
						$timefilter,
						$filter_arg,
						new xmlrpcval(false,'boolean')));
		$val=$val[1]->scalar;
		$pos=0;
		$i=0;
		$cols=count($ids);
		while($pos<strlen($val)){
			$tmp=unpack('N',substr($val,$pos,4));
			$pos+=4;
			$res['datetime'][$i]=$tmp[1];
			for($j=0;$j<$cols;$j++){
				$tmp=unpack('N',substr($val,$pos,4));
				$t=pack('L',$tmp[1]);
				$tmp=unpack('f',$t);
				$res[$j][$i]=$tmp[1];
				$pos+=4;
			}
			$i++;
		}
	}
	return $res;
}

?>