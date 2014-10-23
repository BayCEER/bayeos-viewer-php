<?php
//Set StatusFilter
if(isset($_POST['setStatusFilter'])){
	$_SESSION['StatusFilter']=array();
	while(list($key,$v)=each($_SESSION['Status'])){
		if(isset($_POST['s'.$v[0]])) $_SESSION['StatusFilter'][]=$v[0];
	}
	reset($_SESSION['Status']);
}

//Set CSV Options
if(isset($_POST['setCSVOptions'])){
	$_SESSION['csv_dec']=$_POST['csv_dec'];
	$_SESSION['csv_sep']=$_POST['csv_sep'];
	$_SESSION['csv_tz']=$_POST['csv_tz'];
	$_SESSION['csv_dateformat']=$_POST['csv_dateformat'];
}

//DF-Export
if(isset($_POST['csv_df'])){
	header('Location: ./csv_df.php?id='.$_GET['edit']);
	exit();
}

//TS-Export
if(isset($_POST['download'])){
	if(isset($_POST['session_from'])) $_SESSION['csv_from']=toiso8601($_POST['session_from']);
	if(isset($_POST['session_until'])) $_SESSION['csv_until']=toiso8601($_POST['session_until']);
	$_SESSION['csv_agrint']=$_POST['session_agrint'];
	$_SESSION['csv_agrfunc']=$_POST['session_agrfunc'];
	if($_POST['format']=='csv') header('Location: ./csv.php');
	else header('Location: ./xlsx.php?format='.$_POST['format']);
	exit();
} else {
	if(isset($_POST['session_from'])) $_SESSION['from']=toiso8601($_POST['session_from']);
	if(isset($_POST['session_until'])) $_SESSION['until']=toiso8601($_POST['session_until']);
	$_SESSION['agrint']=$_POST['session_agrint'];
	$_SESSION['agrfunc']=$_POST['session_agrfunc'];
}

if(isset($_POST['chart']))	
	$_SESSION['tab']='Chart';

if(isset($_POST['renderer'])){
	$_SESSION['renderer']=$_POST['renderer'];
	$_SESSION['interpolate']=$_POST['interpolate'];
	if($_SESSION['renderer']!='line')
		$_SESSION['interpolate']=0;
}
?>