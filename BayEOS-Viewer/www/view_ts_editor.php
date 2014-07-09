<?php 
if(isset($_POST['page'])) $_GET['page']=$_POST['page'];
if(! isset($_GET['page'])) $_GET['page']=1;
if($_GET['page']<1) $_GET['page']=1;
$step=10;
$objekt=xml_call('ObjektHandler.getObjekt',
		array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval($node[4],'string')));
$step=10;
$max=$objekt[18]->timestamp-3600;
$min=$objekt[17]->timestamp-3600;

$from=$min+($_GET['page']-1)*$step*$objekt[22];
$until=$from+$step*$objekt[22];


$max=round(($max-$min)/$objekt[22]);



echo_pagination($max,$_GET['page'],"&view=ts_editor&edit=$_GET[edit]");

$val=xml_call('MassenTableHandler.getRows',
		array(new xmlrpcval($_GET['edit'],'int'),
				xmlrpc_array(array(toios8601FromEpoch($from),toios8601FromEpoch($until)),'dateTime.iso8601'),
				xmlrpc_array(array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16),'int')));
$val=$val[1]->scalar;
$pos=0;
$options=array('old_hidden'=>1);
if(! $node[0]) $options['readonly']=1;

echo '<input type="hidden" name="page" value="'.$_GET['page'].'">
<input type="hidden" name="action" value="ts">
<table class="table table-hover col-sm-12">
	<thead>
		<tr>
		<th>Timestamp</th>
		<th>Value</th>
		<th>Status</th>
		</tr>
	</thead>
	<tbody>
	';

while($pos<strlen($val)){
	$tmp=unpack('N',substr($val,$pos,4));
	$ts=$tmp[1];
	echo '<tr><td><input type="hidden" name="ts[]" value="'.$ts.'">';
	echo get_input('ts'.$ts, 'dateTime.iso8601',date('Y-m-d H:i',$ts),'',$options);
	echo '</td><td>';
	$tmp=unpack('N',substr($val,$pos+4,4));
	$t=pack('L',$tmp[1]);
	$tmp=unpack('f',$t);
	echo get_input('v'.$ts, 'float',$tmp[1],'',$options);
	echo '</td><td>';
	$tmp=unpack('c',substr($val,$pos+8,1));
	echo get_input('s'.$ts, 'Status',$tmp[1],'',$options);
	echo '</td></tr>';
	$pos+=9;
}

if($node[0]){
	echo '<tr><td colspan=3>New data point:</td></tr>';
	echo '<tr><td>';
	echo get_input('nts', 'dateTime.iso8601','','');
	echo '</td><td>';
	echo get_input('nv', 'float','','');
	echo '</td><td>';
	echo get_input('ns', 'Status','','');
	echo '</td></tr>';
}

echo '</tbody>
</table>';




?>