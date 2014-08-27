<?php 
if(isset($_POST['page'])) $_GET['page']=$_POST['page'];
if(! isset($_GET['page'])) $_GET['page']=1;
if($_GET['page']<1) $_GET['page']=1;
$step=10;
$objekt=xml_call('ObjektHandler.getObjekt',
		array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval($node[4],'string')));
$step=10;

if(isset($_POST['from'])){
	set_post_from_until($_POST['interval']);
	$min_ts=toEpoch(toiso8601($_POST['from']));	
	$max_ts=toEpoch(toiso8601($_POST['until']));
} elseif(isset($_GET['min_ts'])){
	$min_ts=$_GET['min_ts'];
	$max_ts=$_GET['max_ts'];	
} else {
	$max_ts=$objekt[18]->timestamp-3600;
	$min_ts=$objekt[17]->timestamp-3600;
}
$from=$min_ts+($_GET['page']-1)*$step*$objekt[22];
$until=$from+$step*$objekt[22];


$max=round(($max_ts-$min_ts)/$objekt[22]);
?> 

<script>
function addLeadingZeros(number, length) {
    var num = '' + number;
    while (num.length < length) num = '0' + num;
    return num;
}
function format_date(d){
	var s=d.getFullYear()+'-'+addLeadingZeros(d.getMonth()+1,2)+'-'+addLeadingZeros(d.getDate(),2)+' '+d.toLocaleTimeString();
	return s;
}

function set_from_until(from,until){
	if(from!=null)
		$('#from').val(format_date(new Date(from*1000)));
	if(until!=null)
		$('#until').val(format_date(new Date(until*1000)));
	
}

$(function() {
$( "#slider-range" ).slider({
range: true,
min: <?php echo $min_ts;?>,
max: <?php echo $max_ts;?>,
values: [ <?php echo $min_ts;?>, <?php echo $max_ts;?> ],
slide: function( event, ui ) {
	set_from_until(ui.values[0],ui.values[1]);
}

});
});
</script>
<br/>
<div id="slider-range"></div>
<div class="row">
<?php 
echo_field("from",'From','dateTime.iso8601',date('Y-m-d H:i',$min_ts),3);
echo_field("until",'Until','dateTime.iso8601',date('Y-m-d H:i',$max_ts),3);
echo_field("interval",'Interval','SelectValue',$_POST['interval'],
			3,array('selectvalues'=>array('','today','yesterday','this week','last week','this month','last month','this year','last year')));
echo '<div class="col-sm-3 col-lg-3"><br/>';
echo_button('Refresh','refresh',"","btn btn-primary",'');
echo_button('to Chart','signal',"","btn btn-primary",' name="ts_to_chart"');
echo '</div>';
?>
</div>
<?php 
echo_pagination($max,$_GET['page'],"&view=ts_editor&edit=$_GET[edit]&min_ts=$min_ts&max_ts=$max_ts");

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