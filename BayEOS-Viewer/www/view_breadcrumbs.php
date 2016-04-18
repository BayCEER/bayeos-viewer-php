<?php 
//ID-Search via User-Input
if(isset($_GET['search']) && is_numeric($_GET['search']) && $_GET['stype']==2){
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['search'],'int')));
	if($node){
		if(! isset($GLOBALS['bayeos_canhavechilds'][$node[4]]) || $node[4]=='data_frame')
			 $_GET['edit']=$node[2];
		else $_GET['id']=$node[2];
	}
}
//Search via autocomplete
if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['id'],'int')));
	if($node && (! isset($GLOBALS['bayeos_canhavechilds'][$node[4]])||$node[4]=='data_frame')){
		$_GET['edit']=$node[2];
	}
}

if(isset($_GET['edit']) && is_numeric($_GET['edit'])) $_GET['id']=$_GET['edit'];
if(isset($_GET['id']) && is_numeric($_GET['id']))	$_SESSION['id']=$_GET['id'];
$i=0;
$found=0;
$count=count($_SESSION['breadcrumbs']);
while($i<$count){
	if($_SESSION['breadcrumbs'][$i][2]==$_SESSION['id']){
		$i++;
			
		while($i<$count){
			unset($_SESSION['breadcrumbs'][$i]);
			$i++;
		}
		$found=1;
		break;
	}
	$i++;
}
if(! $found){
	$_SESSION['breadcrumbs'][]=xml_call('TreeHandler.getNode',array(new xmlrpcval($_SESSION['id'],'int')));
	$last=count($_SESSION['breadcrumbs'])-1;
	if($last>0 &&
			$_SESSION['breadcrumbs'][$last][3]!=$_SESSION['breadcrumbs'][($last-1)][2]){
		//There is a gap!
		$node=$_SESSION['breadcrumbs'][$last];
		$tmp=array($node);
		while($node[3]>0){
			$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($node[3],'int')));
			$tmp[]=$node;
		}
		$_SESSION['breadcrumbs']=array_reverse($tmp);
	}
}

$i=0;
echo '<ol class="breadcrumb">';
$_SESSION['currentpath']='';
$cb_path='';
while($i<count($_SESSION['breadcrumbs'])){
	$_SESSION['currentpath'].='/'.$_SESSION['breadcrumbs'][$i][5];
	if($_SESSION['breadcrumbs'][$i][2]==$_SESSION['id'])
		echo '<li class="active">'.$_SESSION['breadcrumbs'][$i][5].'</li>';
	else
		echo '<li><a href="?id='.$_SESSION['breadcrumbs'][$i][2].'">'.$_SESSION['breadcrumbs'][$i][5].'</a></li>';
	if($i>0) $cb_path.='/'.$_SESSION['breadcrumbs'][$i][5];
	$i++;
}
if($cb_path) 
	echo '
	
<button id="cb-btn" class="pull-right btn btn-xs btn-default hidden-xs" data-clipboard-text="'.$cb_path.'" title="copy path to clipboard">
<span class="glyphicon glyphicon-copy"></span> <span class="hidden-sm">Copy </span>Path
</button>
<script>
new Clipboard(document.getElementById(\'cb-btn\'));
</script>
	<button id="cb-btn-id" class="pull-right btn btn-xs btn-default hidden-xs" data-clipboard-text="'.$_SESSION['id'].'" title="copy ID to clipboard">
	<span class="glyphicon glyphicon-copy"></span> <span class="hidden-sm">Copy </span>ID
	</button>
	<script>
	new Clipboard(document.getElementById(\'cb-btn-id\'));
	</script>
	';

echo '</ol>
';

?>
