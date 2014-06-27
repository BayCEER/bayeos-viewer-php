<?php
if(! isset($_GET['view'])) $_GET['view']='';

if(is_numeric($_GET['edit']))
	$node=xml_call('TreeHandler.getNode',array(new xmlrpcval($_GET['edit'],'int')));
else 
	$node=array(0=>1,4=>$_GET['edit']);

$options=array('old_hidden'=>1);
if(! $node[0]) $options['readonly']=1;

echo '<form action="?edit='.$_GET['edit'].'&view='.$_GET['view'].'" method="POST" class="form" role="form" accept-charset="UTF-8">
<div class="block">
<div class="block-header">
'.($node[5]?'Node &quot;'.$node[5].'&quot; - ID: '.$node[2]:'New Node').'
</div>
<div class="row">
';
if(is_numeric($_GET['edit'])){
echo '<ul class="nav nav-tabs">
';
$tabs=array(array('','edit','Details'),
		array('ref','tags','References'),
		array('acl','user','ACL'));
if(isset($GLOBALS['bayeos_has_special_view'][$node[4]]))
	$tabs=array_merge($tabs,$GLOBALS['bayeos_has_special_view'][$node[4]]);
for($i=0;$i<count($tabs);$i++){
	echo '<li'.($_GET['view']==$tabs[$i][0]?' class="active"':'').'><a href="?edit='.$node[2].'&view='.$tabs[$i][0].'">
	<span class="glyphicon glyphicon-'.$tabs[$i][1].'"></span> '.$tabs[$i][2].'</a></li>';
}
echo '</ul>';
}
if($_GET['view']=='ref'){
	if($node[0]){
		echo '<br/><input type="hidden" name="_action_ref_save" value="1">
		'.get_input('refclass','RefClasses','mess_einheit','').'
		'.get_input("newref",'autocomplete','','',array('refclass'=> "$('#refclass').val()")).'
		<input type="submit" value="add" name="addref">';
	}
	$res=xml_call('ObjektHandler.getDirectReferences',array(new xmlrpcval($_GET['edit'],'int')));
	?>
<table class="table table-hover col-sm-12">
	<thead>
		<tr>
			<th>Object</th>
			<th class="hidden-xs">Start</th>
			<th class="hidden-xs">End</th>
		</tr>
	</thead>
	<tbody>
		<?php
		for($i=0;$i<count($res);$i++){
			echo '<tr>
			<td><input type="hidden" name="refids[]" value="'.$res[$i][4].'">
			<input type="hidden" name="refart[]" value="'.$res[$i][6].'">
			<span class="glyphicon glyphicon-'.$GLOBALS['uname_icon_hash'][$res[$i][6]].'"></span> '.$res[$i][1].'</td>
			<td class="hidden-xs">'.get_input("von$i",'dateTime.iso8601',toDate($res[$i][2]),'',$options).'</td>
			<td class="hidden-xs">'.get_input("bis$i",'dateTime.iso8601',toDate($res[$i][3]),'',$options).'</td>';
			if($node[0]) echo '<td><a href="./?edit='.$_GET['edit'].'&refdel='.$res[$i][4].'&refclass='.$res[$i][6].'" class="btn btn-xs btn-default" onClick="return confirm(\'Are you sure?\');">
			<span class="glyphicon glyphicon-remove"></span> delete</a></td>';
			echo '
			</tr>
			';
		}


		$res=xml_call('ObjektHandler.getInheritedReferences',array(new xmlrpcval($_GET['edit'],'int')));
		if(count($res)) echo '<tr><td colspan=3>Inherited References</td></tr>';
		for($i=0;$i<count($res);$i++){
			echo '<tr>
			<td><span class="glyphicon glyphicon-'.$GLOBALS['uname_icon_hash'][$res[$i][6]].'"></span> '.$res[$i][1].'</td>
			<td class="hidden-xs">'.toDate($res[$i][2]).'</td>
			<td class="hidden-xs">'.toDate($res[$i][2]).'</td>
			</tr>
			';
		}
		?>
	</tbody>
</table>
</div></div>
<?php


} elseif($_GET['view']=='acl'){
	$res=xml_call('RightHandler.getRights',array(new xmlrpcval($_GET['edit'],'int')));
	$res=$res[0];
	?>
<table class="table table-hover col-sm-12">
	<thead>
		<tr>
			<th>User/Group</th>
			<th>Read</th>
			<th class="hidden-xs">Write</th>
			<th class="hidden-xs">Exec</th>
			<th class="hidden-xs">Inherit</th>
		</tr>
	</thead>
	<tbody>
		<?php
		function checkbox($name,$value,$editable){
			return '<input type="hidden" name="_old_'.$name.'" value="'.$value.'">
			<input type="checkbox" name="'.$name.'"'.($value?' checked':'').($editable?'':' disabled').'>';
		}
		if($node[0]){
			echo '<tr>
			<td><input type="hidden" name="_action_acl_save" value="1">
			'.get_input("newaclid",'autocomplete','','',array('refclass'=> "'acl'",
					'additional_args'=>'mustMatch: true,')).'
					</td>
					<td>'.checkbox('newacl[1]',1,1).'</td>
					<td class="hidden-xs">'.checkbox('newacl[2]',0,1).'</td>
					<td class="hidden-xs">'.checkbox('newacl[3]',0,1).'</td>
					<td class="hidden-xs">'.checkbox('newacl[4]',1,1).'</td>
					<td><button class="btn btn-xs btn-default" name="_acladd">
					<span class="glyphicon glyphicon-plus"></span> Add
					</button>
					</tr>
					';
		}

		for($i=0;$i<count($res);$i++){
			echo '<tr>
			<td><input type="hidden" name="aclids[]" value="'.$res[$i][7].'">
			<span class="glyphicon glyphicon-'.($res[$i][6]=='gruppe'?'list':'user').'"></span> '.$res[$i][0].'</td>
			<td>'.checkbox('acl'.$res[$i][7].'_1',$res[$i][1],$res[$i][5] && $node[0]).'</td>
			<td class="hidden-xs">'.checkbox('acl'.$res[$i][7].'_2',$res[$i][2],($res[$i][5] && $node[0])).'</td>
			<td class="hidden-xs">'.checkbox('acl'.$res[$i][7].'_3',$res[$i][3],($res[$i][5] && $node[0])).'</td>
			<td class="hidden-xs">'.checkbox('acl'.$res[$i][7].'_4',$res[$i][4],($res[$i][5] && $node[0])).'</td>';
			if($res[$i][5] && $node[0]) echo '<td><a href="./?edit='.$_GET['edit'].'&acldel='.$res[$i][7].'" class="btn btn-xs btn-default" onClick="return confirm(\'Are you sure?\');">
			<span class="glyphicon glyphicon-remove"></span> delete</a></td>';
			echo '</tr>
			';
		}
		?>
	</tbody>
</table>
</div></div>
<?php
} elseif($_GET['view']=='') {
	echo_field("t5",'Name','string',$node[5],4,$options);
	if($node[0] && is_numeric($_GET['edit'])){
		echo_field("parentid","Move to",'autocomplete','',4,
				array('refclass'=> "'".$GLOBALS['bayeos_tree_unames'][$_SESSION['current_tree']]."'",
						'additional_args'=>'mustMatch: true,'));
		echo_field("parentroot",'Move to root folder','boolean','',4);
	}
	echo '</div></div>';
	$ofields=get_object_fields($node[4]);
	if(count($ofields)){
		echo '<div class="block">
		<div class="block-header">
		Object
		</div>
		<div class="row">
		';
		$objekt=xml_call('ObjektHandler.getObjekt',
				array(new xmlrpcval($_GET['edit'],'int'),
						new xmlrpcval($node[4],'string')));
		for($i=0;$i<count($ofields);$i++){
			$value=($ofields[$i]['type']=='dateTime.iso8601'?toDate($objekt[$ofields[$i]['nr']]):$objekt[$ofields[$i]['nr']]);
			if(! $value && isset($ofields[$i]['default'])) $value=$ofields[$i]['default'];
			echo_field("o".$ofields[$i]['nr'],$ofields[$i]['name'],$ofields[$i]['type'],
					$value,$ofields[$i]['cols'],$options);

		}
		echo '</div></div>';
	}
} else {
	require 'view_'.$_GET['view'].'.php';
}
echo '
<div class="block-action">';
if(is_numeric($_GET['edit'])){
	if($node[0]) 
		echo_button('Update','ok','',"btn btn-primary",'type="submit"');
	if($node[0])
		echo ' <button class="btn btn-default" name="_action_remove" onclick="return confirm(\'Are you sure?\');">
	<span class="glyphicon glyphicon-trash"></span> Delete
</button>';
	switch ($node[4]){
		case 'messung_massendaten':
		case 'messung_ordner':
			echo_button('To Clipboard','pushpin','?add='.$node[2]);
			break;
		case 'data_column':
			echo_button('Data Frame','edit','?view=df_editor&edit='.$node[3]);
			break;
	}
	
	
} else 
	echo_button('Save','ok','',"btn btn-primary",'type="submit"');

echo '</div></form>';

?>