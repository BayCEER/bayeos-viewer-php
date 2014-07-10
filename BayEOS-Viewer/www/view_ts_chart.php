<?php 
$objekt=xml_call('ObjektHandler.getObjekt',
		array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval($node[4],'string')));

if(isset($_POST['from'])){
	switch($_POST['interval']){
		case 'today':
			$_POST['from']=date('y-m-d 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'yesterday':
			$_POST['from']=date('y-m-d 00:00',time()-3600*24);
			$_POST['until']=date('y-m-d 00:00');
			break;
		case 'this week':
			$weekday=date('N');
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*($weekday-1));
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last week':
			$weekday=date('N');
			$_POST['from']=date('y-m-d 00:00',time()-3600*24*($weekday+6));
			$_POST['until']=date('y-m-d 00:00',time()-3600*24*($weekday-1));
			break;
		case 'this month':
			$_POST['from']=date('y-m-01 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last month':
			$last_month=date('m')-1;
			$year=date('y');
			if($last_month==0){
				$last_month=12;
				$year--;
			}
			$_POST['from']=$year.'-'.$last_month.'-01 00:00';
			$_POST['until']=date('y-m-01 00:00');
			break;
		case 'this year':
			$_POST['from']=date('y-01-01 00:00');
			$_POST['until']=date('y-m-d 00:00',time()+3600*24);
			break;
		case 'last year':
			$_POST['from']=date('y-01-01 00:00',time()-365*3600*24);
			$_POST['until']=date('y-12-31 00:00',time()-365*3600*24);
			break;
					
	}
	$from=toEpoch(toiso8601($_POST['from']));
	$until=toEpoch(toiso8601($_POST['until']));
} else {
	$until=$objekt[18]->timestamp-3600;
	$from=$objekt[17]->timestamp-3600;
}

$res=$objekt[22];
if(! $res) $res=600;
$max=2000;
$resByID=array();
$indexByID=array();
for($i=0;$i<count($_SESSION['AgrIntervalle']);$i++){
	$resByID[$_SESSION['AgrIntervalle'][$i][0]]=$_SESSION['AgrIntervalle'][$i][2];
	$indexByID[$_SESSION['AgrIntervalle'][$i][0]]=$i;
}	

$interval=$until-$from;
$i=0;
$agrint='';
$agrfunc='';

while(($interval/$res)>$max && $i<(count($_SESSION['AgrIntervalle'])-1)){
	$i++;
	$max=800;
	$res=$_SESSION['AgrIntervalle'][$i][2];
}
if($i>0){
	$agrint=$_SESSION['AgrIntervalle'][$GLOBALS['indexByID'][$i]][0];
	$agrfunc=1;
}

?>

<script src="js/d3.min.js"></script>
<script src="js/rickshaw.min.js"></script>
<script>
var currentX=0;
var selectedX= new Object();

function addLeadingZeros(number, length) {
    var num = '' + number;
    while (num.length < length) num = '0' + num;
    return num;
}
function format_date(d){
	var s=d.getFullYear()+'-'+addLeadingZeros(d.getMonth()+1,2)+'-'+addLeadingZeros(d.getDate(),2)+' '+d.toLocaleTimeString();
	return s;
}

function set_from_until(g,from,until){
	if(from!=null)
		$('#from').val(format_date(new Date(from*1000)));
	if(until!=null)
		$('#until').val(format_date(new Date(until*1000)));
	
}



</script>	
<script>
var palette = new Rickshaw.Color.Palette( { scheme: 'colorwheel' } );

</script>
	<div id="chart_container">
        <div id="y_axis"></div>
		<div id="chart"></div>
		<div id="legend"></div>
		<div id="timeline"></div>
		<div id="preview"></div>
		</div>	
<script>
	
var graph = new Rickshaw.Graph({
	element: document.querySelector("#chart"),
	renderer: 'line',
	interpolation: 'linear',
	min: 'auto',
	series: [
<?php 
if($_SESSION['chartdata']){
	$x=array();
	$y=array();
	$s=array();
}
$unit=array();
$no_data=0;
$res=xml_call('ObjektHandler.getLowestRefObjekt',
		array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval('mess_einheit','string')));
$unit[$i]=$res[20];
	
echo "{ data: [";
	if(! $agrfunc || ! $agrint){
$val=xml_call('MassenTableHandler.getRows',
		array(new xmlrpcval($_GET['edit'],'int'),
				xmlrpc_array(array(toios8601FromEpoch($from),toios8601FromEpoch($until)),'dateTime.iso8601'),
				xmlrpc_array(array(0,1,2),'int')));
		$val=$val[1]->scalar;
		$step=1;
		if(strlen($val)>20000) $step=round(strlen($val)/20000);
		$pos=0;
		if(strlen($val)==0) $no_data=1;
		while($pos<strlen($val)){
			if($pos>0) echo ",\n";
			$tmp=unpack('N',substr($val,$pos,4));
			echo "{x:$tmp[1],";
			$tmp2=unpack('N',substr($val,$pos+4,4));
			$t=pack('L',$tmp2[1]);
			$tmp2=unpack('f',$t);
			echo "y:".round($tmp2[1],5)."}";
			$pos+=9*$step;
		}
	} else {
		$val=xml_call('AggregationTableHandler.getRows',
				array(new xmlrpcval($_GET['edit'],'int'),
				xmlrpc_array(array(toios8601FromEpoch($from),toios8601FromEpoch($until)),'dateTime.iso8601'),
				new xmlrpcval(array(new xmlrpcval($agrfunc,'int'),new xmlrpcval($agrint,'int')),'array'
						)));
		$val=$val[1];
		$step=1;
		if(count($val)==0) $no_data=1;
		if(count($val)>2000) $step=round(count($val)/2000);
		for($j=0;$j<count($val);$j+=$step){
			if($j>0) echo ",";
			echo "{x:".($val[$j][0]->timestamp-3600).",y:".round($val[$j][1],5)."}\n";
		}
	}
	echo "],
	color: palette.color(),
    name: '".$objekt[20].' <b>'.$series[$p][$i][5].'</b>'.($unit[$i]?" [$unit[$i]]":'')."'}";
?>
]
});
graph.render();

var preview = new Rickshaw.Graph.RangeSlider( {
	graph: graph,
	element: document.getElementById('preview'),
} );
preview.slideCallbacks=[set_from_until];

var hoverDetail = new Rickshaw.Graph.HoverDetail( {
	graph: graph,
	xFormatter: function(x) {
		currentX=x;
		return new Date(x * 1000).toString();
	}
} );

var annotator = new Rickshaw.Graph.Annotate( {
	graph: graph,
	element: document.getElementById('timeline')
} );

var legend = new Rickshaw.Graph.Legend( {
	graph: graph,
	element: document.getElementById('legend')

} );

var shelving = new Rickshaw.Graph.Behavior.Series.Toggle( {
	graph: graph,
	legend: legend
} );

var order = new Rickshaw.Graph.Behavior.Series.Order( {
	graph: graph,
	legend: legend
} );

var highlighter = new Rickshaw.Graph.Behavior.Series.Highlight( {
	graph: graph,
	legend: legend
} );

//var ticksTreatment = 'glow';

var xAxis = new Rickshaw.Graph.Axis.Time( {
	graph: graph,
	ticksTreatment: 'glow',
	timeFixture: new Rickshaw.Fixtures.Time.Local()
} );

xAxis.render();

var yAxis = new Rickshaw.Graph.Axis.Y( {
	graph: graph,
	tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
	ticksTreatment: 'glow'
} );

yAxis.render();

</script>
<?php

if($no_data) echo '<div class="alert alert-danger">Selection returns no data. Plotting will not work.</div>';

echo_field("from",'From','dateTime.iso8601',date('Y-m-d H:i',$from),4);
echo_field("until",'Until','dateTime.iso8601',date('Y-m-d H:i',$until),4);
echo_field("interval",'Interval','SelectValue',$_POST['interval'],
			4,array('selectvalues'=>array('','today','yesterday','this week','last week','this month','last month','this year','last year')));


$special_view_buttons=array(
		array('Refresh','refresh',"","btn btn-primary",''));

?>