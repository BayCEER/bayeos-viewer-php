<?php 
if(isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['search'])) unset($_GET['search']);
if(! isset($_GET['search'])) $_GET['search']='';

if($_SESSION['current_tree']=='Folders') $art_filter='messung_%';
else $art_filter=$GLOBALS['bayeos_tree_unames'][$_SESSION['current_tree']];

?>
<form accept-charset="UTF-8">
<?php 

?>
		<input id="search" name="search" value="<?php echo htmlentities($_GET['search']);?>">
			<input id="id" name="id" type="hidden">
			<script type="text/javascript">
			$('#search').autocomplete({source: function(request, response) {
			$.getJSON("search.php", { search: request.term,refclass : '<?php echo $art_filter;?>',depth : 0, parent : <?php echo $_SESSION['id'];?>},
			response);
	},
	select: function(event,ui){
	$('#id').val(ui.item.id);
	},
	change: function(event, ui) {
	if(! ui.item){

	$('#id').val('');
	}
	},mustMatch: false,
	minLength: 1});
	</script>	<!--<input name="search"
		value="<?php echo htmlentities($_GET['search']);?>">--> IN 
	<select name="stype">
	<option value="0">folder</option>
	<option value="1"<?php if($_GET['stype']==1) echo " selected";?>>subfolders</option>
	<option value="2"<?php if($_GET['stype']==2) echo " selected";?>>ID</option>
	</select>
	<button type="submit" class="btn btn-primary">
		<span class="glyphicon glyphicon-search"></span> Search
	</button>
</form>
<?php 	
if(isset($_GET['search']) && $_GET['search'] && $_GET['stype']<2){
	$qs='&search='.urlencode($_GET['search']).'&stype='.$_GET['stype'];
	$search=xml_call('TreeHandler.getAllChildren',
			array(new xmlrpcval($_SESSION['id'],'int'),
					new xmlrpcval(false,'boolean'),
					xmlrpc_array(array('mitarbeiter','projekte')),
					new xmlrpcval('**/*'.$_GET['search'].'*','string'),
					new xmlrpcval($art_filter,'string'),
					new xmlrpcval(($_GET['stype']?-1:0),'int'),
					new xmlrpcval(FALSE,'boolean'),
					new xmlrpcval('week','string'),
					new xmlrpcval(null,'null')
			));
	//This is a hack to match Tree-Index
	$childs=array();
	for($i=0;$i<count($search);$i++){
		$childs[$i]=array(2=>$search[$i][0],4=>$search[$i][9],5=>$search[$i][7],
				6=>$search[$i][5],7=>$search[$i][6]);
		if($_GET['stype']) $childs[$i]['path']=array($search[$i][1],$search[$i][8]);
	}

} else {
	$qs='';
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
echo_table($childs,'add',$qs);
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
