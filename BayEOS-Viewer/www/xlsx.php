<?php
/**********************************************************
 * Called for XLSX Exports
*********************************************************/
require './functions.php';
require 'PHPExcel.php';

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
if(! isset($_GET['format'])) $_GET['format']='xlsx';
switch($_GET['format']){
	case 'xlsx':
		$type='Excel2007';
		$mime='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		break;
	case 'xls':
		$type='Excel5';
		$mime='vapplication/nd.ms-excel';
		break;
	case 'pdf':
		$type='PDF';
		$mime='application/pdf';
		break;
	case 'csv':
		$type='CSV';
		$mime='text/x-csv';
		break;
	default:
		header("HTTP/1.0 500 Internal Error");
		header("Status: 500 Internal Error");
		echo "<html><body><h1>Status: 500 Internal Error: Format not supported</h1></body></html>";
		exit();
}

$filename='bayeos-export.'.$_GET['format'];

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("BayEOS-Viewer")
->setLastModifiedBy("BayEOS");

//Extract path and subpath
$pathinfo=get_folder_subfolders();

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
$row=1;
$col=0;
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Exported: '.date('y-m-d H:i'));
$row++;
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Server: '.$_SESSION['bayeosurl']);
$row++;
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Timezone: '.$_SESSION['csv_tz']);
$row++;
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Folder: '.$pathinfo['folder']);
if(isset($pathinfo['subfolders'])){
	$row++;
	$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
	$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Subfolders:');
	for($i=0;$i<count($pathinfo['subfolders']);$i++){
		$col++;
		$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
		$objPHPExcel->getActiveSheet()->setCellValue($cell,$pathinfo['subfolders'][$i]);
	}
	$col=0;
}
$row++;
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'# Units:');
$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row+1);
$objPHPExcel->getActiveSheet()->setCellValue($cell,'Datetime');
for($i=0;$i<count($_SESSION['clipboard']);$i++){
	$col++;
	$ids[]=$_SESSION['clipboard'][$i][2];
	$res=xml_call('ObjektHandler.getLowestRefObjekt',
			array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
					new xmlrpcval('mess_einheit','string')));
	$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
	$objPHPExcel->getActiveSheet()->setCellValue($cell,$res[20]);
	$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row+1);
	$objPHPExcel->getActiveSheet()->setCellValue($cell,$_SESSION['clipboard'][$i][5]);
}
$row+=2;

$timefilter=xmlrpc_array(array($_SESSION['csv_from'],$_SESSION['csv_until']),'dateTime.iso8601');
if(! $_SESSION['csv_agrfunc'] || ! $_SESSION['csv_agrint'])	$filter_arg=xmlrpc_array(array(0,1,2),'int');
else $filter_arg=xmlrpc_array(array($_SESSION['csv_agrfunc'],$_SESSION['csv_agrint']),'int');

function nan($value){
	if(is_nan($value)) return 'NA';
	return $value;
}

if(count($_SESSION['clipboard'])==1){
	if(! $_SESSION['csv_agrfunc'] || !$_SESSION['csv_agrint']){
		$val=xml_call('MassenTableHandler.getRows',
				array(new xmlrpcval($_SESSION['clipboard'][0][2],'int'),
						$timefilter,
						$filter_arg));
		$val=$val[1]->scalar;
		$pos=0;
		while($pos<strlen($val)){
			$col=0;
			$tmp=unpack('N',substr($val,$pos,4));
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$objPHPExcel->getActiveSheet()->getStyle($cell)
			->getNumberFormat()
			->setFormatCode('dd.mm.yyyy hh:mm:ss');
			$t=strptime(date('Y-m-d H:i:s',$tmp[1]),'%Y-%m-%d %H:%M:%S');
			$value=PHPExcel_Shared_Date::PHPToExcel(
					gmmktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],
							$t['tm_mon']+1,$t['tm_mday'],$t['tm_year']+1900));				
			$objPHPExcel->getActiveSheet()->setCellValue($cell,$value);
			$col++;
			$tmp=unpack('N',substr($val,$pos+4,4));
			$t=pack('L',$tmp[1]);
			$tmp=unpack('f',$t);
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$objPHPExcel->getActiveSheet()->setCellValue($cell,nan($tmp[1]));
			$pos+=9;
			$row++;
		}
	} else {
		$val=xml_call('AggregationTableHandler.getRows',
				array(new xmlrpcval($_SESSION['clipboard'][0][2],'int'),
						$timefilter,
						$filter_arg));
		$val=$val[1];
		for($j=0;$j<count($val);$j++){
			$col=0;
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$objPHPExcel->getActiveSheet()->getStyle($cell)
			->getNumberFormat()
			->setFormatCode('dd.mm.yyyy hh:mm:ss');
			$t=strptime(date('Y-m-d H:i:s',$val[$j][0]->timestamp-3600),'%Y-%m-%d %H:%M:%S');
			$value=PHPExcel_Shared_Date::PHPToExcel(
					gmmktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],
							$t['tm_mon']+1,$t['tm_mday'],$t['tm_year']+1900));
			
			$objPHPExcel->getActiveSheet()->setCellValue($cell,$value);
			$col++;
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$objPHPExcel->getActiveSheet()->setCellValue($cell,nan($val[$j][1]));
			$row++;
		}
	}
} else {
	if(!$_SESSION['csv_agrfunc'] || !$_SESSION['csv_agrint']){
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
	while($pos<strlen($val)){
		$col=0;
		$tmp=unpack('N',substr($val,$pos,4));
		$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
		$objPHPExcel->getActiveSheet()->getStyle($cell)
			->getNumberFormat()
			->setFormatCode('dd.mm.yyyy hh:mm:ss');
		$t=strptime(date('Y-m-d H:i:s',$tmp[1]),'%Y-%m-%d %H:%M:%S');
		$value=PHPExcel_Shared_Date::PHPToExcel(
					gmmktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],
							$t['tm_mon']+1,$t['tm_mday'],$t['tm_year']+1900));				
		$objPHPExcel->getActiveSheet()->setCellValue($cell,$value);
		$pos+=4;
		for($j=0;$j<count($ids);$j++){
			$col++;
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$tmp=unpack('N',substr($val,$pos,4));
			$t=pack('L',$tmp[1]);
			$tmp=unpack('f',$t);
			$cell=PHPExcel_Cell::stringFromColumnIndex($col).($row);
			$objPHPExcel->getActiveSheet()->setCellValue($cell,nan($tmp[1]));
			$pos+=4;
		}
		$row++;
	}
}


// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Export');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: '.$mime);
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $type);
$objWriter->save('php://output');
exit;
?>
