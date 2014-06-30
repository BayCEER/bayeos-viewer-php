<?php if(! isset($_GET['search'])) $_GET['search']='';?>
<form accept-charset="UTF-8">
	<input name="search"
		value="<?php echo htmlentities($_GET['search']);?>"> <label><input
		type="checkbox" name="subtreesearch"
		<?php if(isset($_GET['subtreesearch']) && $_GET['subtreesearch']) echo "checked"?>> include subfolders</label>
	<button type="submit" class="btn btn-primary">
		<span class="glyphicon glyphicon-search"></span> Search
	</button>
</form>
<?php 	
if(isset($_GET['search']) && $_GET['search']){
	if($_SESSION['current_tree']=='Folders') $art_filter='messung_%';
	else $art_filter=$GLOBALS['bayeos_tree_unames'][$_SESSION['current_tree']];
	$search=xml_call('TreeHandler.getAllChildren',
			array(new xmlrpcval($_SESSION['id'],'int'),
					new xmlrpcval(false,'boolean'),
					xmlrpc_array(array('mitarbeiter','projekte')),
					new xmlrpcval('**/*'.$_GET['search'].'*','string'),
					new xmlrpcval($art_filter,'string'),
					new xmlrpcval(($_GET['subtreesearch']?-1:0),'int'),
					new xmlrpcval(FALSE,'boolean'),
					new xmlrpcval('week','string'),
					new xmlrpcval(null,'null')
			));
	//This is a hack to match Tree-Index
	$childs=array();
	for($i=0;$i<count($search);$i++){
		$childs[$i]=array(2=>$search[$i][0],4=>$search[$i][9],5=>$search[$i][7],
				6=>$search[$i][5],7=>$search[$i][6]);
		if($_GET['subtreesearch']) $childs[$i]['path']=array($search[$i][1],$search[$i][8]);
	}

} else {
	$childs=xml_call('TreeHandler.getChilds',
			array(new xmlrpcval($_SESSION['id'],'int'),
					new xmlrpcval('messung_massendaten','string'),
					new xmlrpcval(FALSE,'boolean'),
					new xmlrpcval('week','string'),
					($_SESSION['treefilter']?
							xmlrpc_array(array($_SESSION['until'],$_SESSION['from']),'dateTime.iso8601'):
							new xmlrpcval(null,'null'))
			));
}
echo_table($childs);
//print_r($_SESSION);
echo '
    <div class="dropdown">
<div class="block-action">';
$node=$_SESSION['breadcrumbs'][count($_SESSION['breadcrumbs'])-1];
if($node[0]){
	$childs=$GLOBALS['bayeos_canhavechilds'][$node[4]];
	if(count($childs)==1) echo_button('New '.$GLOBALS['uname_name_hash'][$childs[0]],
				'plus','?edit='.$childs[0]);
	else {	
		$li=array();
		for($i=0;$i<count($childs);$i++){
			$li[]=array('name'=>$GLOBALS['uname_name_hash'][$childs[$i]],'url'=>'?edit='.$childs[$i]);
		}
		echo_button_dropdown('New','plus',$li);
	}
}
if( !$_SESSION['treefilter'])
	echo_button('Hide inactive','minus',"?treefilter=1");
else
	echo_button('Show inactive','plus',"?treefilter=0");
echo_button('Details','edit','?edit='.$_SESSION['id']);
echo "\n".'</div></div>'."\n";
?>
