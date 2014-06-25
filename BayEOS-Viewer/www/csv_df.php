<?php
/**********************************************************
 * Called for CSV Exports
 *********************************************************/
require './functions.php';

if(! isset($_SESSION['bayeosauth'])){
	header("HTTP/1.0 403 Access Denied");
	header("Status: 403 Access Denied");
	echo "<html><body><h1>Status: 403 Access Denied</h1></body></html>";
	exit();
}

if(! isset($_GET['id'])){
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	echo "<html><body><h1>Status: 404 Not Found</h1></body></html>";
	exit();

}

switch($_SESSION['csv_sep']){
	case 'TAB':
		$_SESSION['csv_sep']="\t";
		break;
	case 'SPACE':
		$_SESSION['csv_sep']=" ";
		break;
}

$filename='bayeos-df-export.csv';
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
$out='# Timezone: '.$_SESSION['csv_tz'].'
# Dateformat: '.$_SESSION['csv_dateformat'].'
# Units: ';
date_default_timezone_set($_SESSION['csv_tz']);
$df=xml_call('DataFrameHandler.getFrameRows',array(new xmlrpcval($_GET['id'],'int'),
		new xmlrpcval(null,'null')));


$head="Nr.";
$cols=count($df[0]);
for($i=0;$i<$cols;$i++){
	$head.=$_SESSION['csv_sep'].$df[0][$i][2];
	$res=xml_call('ObjektHandler.getLowestRefObjekt',
			array(new xmlrpcval($df[0][$i][0],'int'),
					new xmlrpcval('mess_einheit','string')));
	$out.=$_SESSION['csv_sep'].$res[20];
}
$out.="\n$head\n";		
function csv_value($value,$type){
	if(! $value && $type=='BOOLEAN') return 0;
	if(! $value) return '';
	switch($type){
		case 'DATE':
			return date($_SESSION['csv_dateformat'],$value->timestamp-3600);
			break;
		case 'DOUBLE':
			return str_replace('.',$_SESSION['csv_dec'],$value);
			break;
		case 'STRING':
			return '"'.str_replace('"','\\"',$value).'"';
			break;
		default:
			return $value;
	}
}

for($i=0;$i<count($df[1]);$i++){
	$out.=$df[1][$i][0];
	for($j=0;$j<$cols;$j++){
		$out.=$_SESSION['csv_sep'].csv_value($df[1][$i][$j+1],$df[0][$j][3]);
	}
	$out.="\n";
}
header("Content-Length: " . strlen($out));
header("Content-type: text/x-csv charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");
echo $out;
exit;


?>