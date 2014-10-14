<?php 
$objekt=xml_call('ObjektHandler.getObjekt',
		array(new xmlrpcval($_GET['edit'],'int'),
				new xmlrpcval($node[4],'string')));

if(isset($_POST['from'])){
	set_post_from_until($_POST['interval']);
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
	$max=500;
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
if(! $agrfunc || ! $agrint)	$filter_arg=xmlrpc_array($_SESSION['StatusFilter'],'int');
else $filter_arg=xmlrpc_array(array($agrfunc,$agrint),'int');
$data=getSeries(array($_GET['edit']), $agrfunc, $agrint, 
		xmlrpc_array(array(toios8601FromEpoch($from),toios8601FromEpoch($until)),'dateTime.iso8601'),
		$filter_arg);

echo "{ data: [";
$comma=0;
for($j=0;$j<count($data['datetime']);$j++){
	if($comma) echo ",\n";
	if($j>1 && ($data['datetime'][$j]-$data['datetime'][$j-1])>=
			2*($data['datetime'][$j-1]-$data['datetime'][$j-2])){
		echo "{x:".($data['datetime'][$j-1]+($data['datetime'][$j]-$data['datetime'][$j-1])).",y:null},\n";
	}
	echo "{x:".$data['datetime'][$j].",y:".round($data[0][$j],5)."}";
	$comma=1;
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

echo_field("from",'From','dateTime.iso8601',date('Y-m-d H:i',$from),3);
echo_field("until",'Until','dateTime.iso8601',date('Y-m-d H:i',$until),3);
echo_field("interval",'Interval','SelectValue',$_POST['interval'],
			3,array('selectvalues'=>array('','today','last 24 hours','last 3 days','last 7 days','last 30 days','yesterday','this week','last week','this month','last month','this year','last year')));
echo '<div class="col-sm-3 col-lg-3"><br/>';
echo_button('Refresh','refresh',"","btn btn-primary",'');
echo_button('to Editor','zoom-in',"","btn btn-primary",' name="ts_to_editor"');
echo '</div>';


$special_view_buttons=array();

?>