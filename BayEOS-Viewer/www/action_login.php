<?php
$config=parse_ini_file('/etc/bayeos-viewer.ini');
if(isset($config['serverURL'])) $_SESSION['bayeosurl']=$config['serverURL'];
else $_SESSION['bayeosurl']='http://'.$_SERVER['SERVER_ADDR'].'/BayEOS-Server/XMLServlet';

$res = xml_call("LoginHandler.createSession",
		array(new xmlrpcval($_POST['login'],'string'),
				new xmlrpcval($_POST['password'],'string')));
if($res===false) {

} else {
	$_SESSION['username']=$_POST['login'];
	if(isset($config['dbConnection'])){//Checking for Admin-Rights
		$_SESSION['dbConnection']=$config['dbConnection'];
		$dbres=DBQueryParams('select id from benutzer where login=$1 and admin',array($_POST['login']));
		if($dbres && pg_num_rows($dbres)){
			list($_SESSION['userid'])=pg_fetch_row($dbres,0);
		} else {
			unset($_SESSION['dbConnection']);
		}
	}
	
	$_SESSION['bayeosauth']=base64_encode($res[0].':'.$res[1]);
	$_SESSION['from']=date('Ymd',time()-24*3600).'T00:00:00';
	$_SESSION['until']=date('Ymd',time()+24*3600).'T00:00:00';
	$_SESSION['login']=$_POST['login'];
	$_SESSION['breadcrumbs']=array();
	$_SESSION['clipboard']=array();
	$_SESSION['rootids']=array();
	$_SESSION['id']=get_root_id('messung_ordner');
	$_SESSION['breadcrumbs'][]=xml_call('TreeHandler.getNode',array(new xmlrpcval($_SESSION['id'],'int')));
	$_SESSION['tab']='Folders';
	$_SESSION['current_tree']='Folders';
	$_SESSION['IntervalTypes']=xml_call('LookUpTableHandler.getIntervalTypes',array());
	$_SESSION['TimeZones']=xml_call('LookUpTableHandler.getTimeZones',array());
	$_SESSION['AgrFunktionen']=xml_call('LookUpTableHandler.getAgrFunktionen',array());
	$_SESSION['AgrIntervalle']=xml_call('LookUpTableHandler.getAgrIntervalle',array());
	$_SESSION['CRS']=xml_call('LookUpTableHandler.getCRS',array());
	$_SESSION['Status']=xml_call('LookUpTableHandler.getStatus',array());
	$_SESSION['DataTypes']=array('DOUBLE','INTEGER','DATE','BOOLEAN','STRING');
	$_SESSION['agrint']='';
	$_SESSION['agrfunc']='';
	$_SESSION['chartdata']=0;
	$_SESSION['chartmulti']=0;
	$_SESSION['treefilter']=0;
	$_SESSION['interpolate']=0;
	$_SESSION['csv_tz']=$_SESSION['tz'];

	//Cookie settings
	$_SESSION['cb_saved']=array();
	$pref=xml_call('PreferenceHandler.getPreferences',array(new xmlrpcval('bayeosviewer','string')));
	if(isset($_COOKIE['cb_saved'])) $_SESSION['cb_saved']=unserialize($_COOKIE['cb_saved']);
	if(isset($pref['cb_saved'])) $_SESSION['cb_saved']=array_merge($_SESSION['cb_saved'],unserialize($pref['cb_saved']));
	unset($pref);
	ksort($_SESSION['cb_saved']);
	if(isset($_COOKIE['max_rows']) && is_numeric($_COOKIE['max_rows']))
		$_SESSION['max_rows']=$_COOKIE['max_rows'];
	else 
		$_SESSION['max_rows']=5000;
	if(isset($_COOKIE['max_cols']) && is_numeric($_COOKIE['max_cols']))
		$_SESSION['max_cols']=$_COOKIE['max_cols'];
	else
		$_SESSION['max_cols']=5;
	if(isset($_COOKIE['gnuplot'])) $_SESSION['gnuplot']=$_COOKIE['gnuplot'];
	else $_SESSION['gnuplot']=0;
	if(isset($_COOKIE['cb2db'])) $_SESSION['cb2db']=$_COOKIE['cb2db'];
	else $_SESSION['cb2db']=1;
	updateCookies();
	
	
	$_SESSION['StatusFilter']=array(0,1,2);
	if($_SESSION['tz']=='Europe/Berlin'){
		$_SESSION['csv_sep']=';';
		$_SESSION['csv_dec']=',';
		$_SESSION['csv_dateformat']='d.m.Y H:i:s';
	} else {
		$_SESSION['csv_sep']=',';
		$_SESSION['csv_dec']='.';
		$_SESSION['csv_dateformat']='Y-m-d H:i:s';
	}
	$_SESSION['csv_tz']=$_SESSION['tz'];
	$_SESSION['RefClasses']=array(array('mess_ziel','Target'),
			array('mess_einheit','Unit'),
			array('mess_geraet','Device'),
			array('mess_kompartiment','Compartment'),
			array('mess_ort','Location')//,array('web','Web')
	);
}
?>