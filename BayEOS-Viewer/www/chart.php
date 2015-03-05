<?php 
/**********************************************************
 * Produces a gnuplot
 * 
 * could be used for devices that do not support rickshaw plot
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

$prefix=tempnam("/dev/shm/",'bayeos');
if(! isset($_GET['x'])) $_GET['x']=640;
if($_GET['x']<400) $_GET['x']=400;
$_GET['y']=round($_GET['x']/2);
if($_GET['y']>350) $_GET['y']=350;

if(isset($_GET['i'])){
	$min_i=$_GET['i'];
	$max_i=$_GET['i']+1;
} else {
	$min_i=0;
	$max_i=count($_SESSION['clipboard']);
}

if($_GET['x']<500) $font=9;
elseif($_GET['x']<600) $font=10;
else $font=11;

header('Content-type: image/png');
$gp='set terminal png truecolor font "arial,'.$font.'" size '.$_GET['x'].','.$_GET['y'].'
		set output "'.$prefix.'.png"
		set autoscale
		set xdata time
		set timefmt "%Y-%m-%d %H:%M"
		#set timefmt "%H:%M"
		set style data lines
		set ylabel ""
		set xlabel "Time"
		#set format x "%d.%m."
		set format x "%H:%M"
		plot ';

$datafiles=array();
for($i=$min_i;$i<$max_i;$i++){
	
	$datafiles[$i]="$prefix.$i.txt";
	$f=fopen($datafiles[$i],'w');
	if($_SESSION['agrfunc']=='' || $_SESSION['agrint']==''){
		$val=xml_call('MassenTableHandler.getRows',
		array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
				new xmlrpcval(array(new xmlrpcval($_SESSION['from'],'dateTime.iso8601'),
						new xmlrpcval($_SESSION['until'],'dateTime.iso8601')),'array'),
				new xmlrpcval(array(new xmlrpcval(0,'int'),new xmlrpcval(1,'int'),new xmlrpcval(2,'int')),'array'
				)));
		$val=$val[1]->scalar;
		$pos=0;
		$step=1;
		if(strlen($val)>5000) $step=round(strlen($val)/5000);
		while($pos<strlen($val)){
			$tmp=unpack('N',substr($val,$pos,4));
			fwrite($f,date('Y-m-d H:i',$tmp[1]));
			$tmp=unpack('N',substr($val,$pos+4,4));
			$t=pack('L',$tmp[1]);
			$tmp=unpack('f',$t);
			fwrite($f," $tmp[1]\n");
			$pos+=9*$step;

		}
	} else {
		$val=xml_call('AggregationTableHandler.getRows',
				array(new xmlrpcval($_SESSION['clipboard'][$i][2],'int'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['from'],'dateTime.iso8601'),
								new xmlrpcval($_SESSION['until'],'dateTime.iso8601')),'array'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['agrfunc'],'int'),new xmlrpcval($_SESSION['agrint'],'int')),'array'
						)));
		$val=$val[1];
		$step=1;
		if(count($val)>500) $step=round(count($val)/500);
		for($j=0;$j<count($val);$j+=$step){
			fwrite($f,date('Y-m-d H:i',$val[$j][0]->timestamp-3600)." ".$val[$j][1]."\n");
		}	
	}
	fclose($f);
	$gp.=($i>$min_i?', ':'').'"'.$datafiles[$i].'" using 1:3 axes x1y1 title "'.$_SESSION['clipboard'][$i]['subfolder'].' '.$_SESSION['clipboard'][$i][5].'" with lines lt '.($i+1);
}
$f=fopen($prefix.'.gp','w');
fwrite($f,$gp."\n");
fclose($f);

system('cat '.$prefix.'.gp | gnuplot');
readfile($prefix.'.png');

//exit();
unlink($prefix.'.png');
unlink($prefix.'.gp');

for($i=0;$i<count($datafiles);$i++){
	unlink($datafiles[$i]);
}
unlink($prefix);
exit();


?>