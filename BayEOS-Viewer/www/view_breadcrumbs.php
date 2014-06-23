<?php 
if(is_numeric($_GET['edit'])) $_GET['id']=$_GET['edit'];
if(is_numeric($_GET['id']))	$_SESSION['id']=$_GET['id'];
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
while($i<count($_SESSION['breadcrumbs'])){
	if($_SESSION['breadcrumbs'][$i][2]==$_SESSION['id'])
		echo '<li class="active">'.$_SESSION['breadcrumbs'][$i][5].'</li>';
	else
		echo '<li><a href="?id='.$_SESSION['breadcrumbs'][$i][2].'">'.$_SESSION['breadcrumbs'][$i][5].'</a></li>';
	$i++;
}
echo '</ol>';


?>