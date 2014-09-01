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

if(! count($_SESSION['clipboard'])){
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

//Extract path and subpath
$pathinfo=get_folder_subfolders();

$filename='bayeos-export.csv';
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
$out='# Exported: '.date('y-m-d H:i').'
# Server: '.$_SESSION['bayeosurl'].'
# Timezone: '.$_SESSION['csv_tz'].'
# Dateformat: '.$_SESSION['csv_dateformat'].'
# Folder: '.$pathinfo['folder'].
(isset($pathinfo['subfolders'])? '
# Subfolders: '.$_SESSION['csv_sep'].implode($_SESSION['csv_sep'], $pathinfo['subfolders']):'').'
# Units: ';
$ids=array();
date_default_timezone_set($_SESSION['csv_tz']);
$head="Datetime";
for($i=0;$i<count($_SESSION['clipboard']);$i++){
	$head.=$_SESSION['csv_sep'].$_SESSION['clipboard'][$i][5];
	$ids[]=$_SESSION['clipboard'][$i][2];
	$res=xml_call('ObjektHandler.getLowestRefObjekt',
			array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
					new xmlrpcval('mess_einheit','string')));
	$out.=$_SESSION['csv_sep'].$res[20];
}
$cols=count($ids);
$out.="\n$head\n";					  
$timefilter=xmlrpc_array(array($_SESSION['csv_from'],$_SESSION['csv_until']),'dateTime.iso8601');
if(! $_SESSION['csv_agrfunc'] || ! $_SESSION['csv_agrint'])	$filter_arg=xmlrpc_array($_SESSION['StatusFilter'],'int');
else $filter_arg=xmlrpc_array(array($_SESSION['csv_agrfunc'],$_SESSION['csv_agrint']),'int');
	
$data=getSeries($ids, $_SESSION['csv_agrfunc'],  $_SESSION['csv_agrint'], $timefilter, $filter_arg);

for($i=0;$i<count($data['datetime']);$i++){
	$out.=date($_SESSION['csv_dateformat'],$data['datetime'][$i]);
	for($j=0;$j<$cols;$j++){
		$out.=$_SESSION['csv_sep'].str_replace('.',$_SESSION['csv_dec'],round($data[$j][$i],5));
	}
	$out.="\n";
}


header("Content-Length: " . strlen($out));
header("Content-type: text/x-csv charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");
echo $out;
exit;


?>