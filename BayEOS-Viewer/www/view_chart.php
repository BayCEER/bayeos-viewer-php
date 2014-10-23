<?php 
//Limit Plotting to five series

if(count($_SESSION['clipboard'])>$_SESSION['max_cols']){
	$max_i=$_SESSION['max_cols'];
	add_alert('You have more than '.$_SESSION['max_cols'].
	' series on your clipboard. Only first '.$_SESSION['max_cols'].' will get plotted.
	Change <a href="?tab=Settings" class="alert-link">settings</a> or remove series from your 
	<a href="?tab=Clipboard" class="alert-link">clipboard</a>.','warning');
	echo $GLOBALS['alert'];
} else 
	$max_i=count($_SESSION['clipboard']);

if($max_i==0){
	add_alert("Your clipboard is empty",'warning');
	echo $GLOBALS['alert'];
} else {
	$readonly=!$_SESSION['clipboard'][0][0];
	
//Auto adjust resolution for large intervals
$series=array();
$sec=toEpoch($_SESSION['until'])-toEpoch($_SESSION['from']);
$resByID=array();
$indexByID=array();
for($i=0;$i<count($_SESSION['AgrIntervalle']);$i++){
	$resByID[$_SESSION['AgrIntervalle'][$i][0]]=$_SESSION['AgrIntervalle'][$i][2];
	$indexByID[$_SESSION['AgrIntervalle'][$i][0]]=$i;
}
function checkres($interval,$res,$alert=1){
	$i=0;
	if(! $res) $res=600;
	$max=$_SESSION['max_rows'];
	if($_SESSION['agrint']){
		$res=$GLOBALS['resByID'][$_SESSION['agrint']];
		$i=$GLOBALS['indexByID'][$_SESSION['agrint']];
		$max=$_SESSION['max_rows']/5;
	}
	while(($interval/$res)>$max && $i<(count($_SESSION['AgrIntervalle'])-1)){
		$i++;
		$max=$_SESSION['max_rows']/5;
		$res=$_SESSION['AgrIntervalle'][$i][2];
	}
	if($i>$_SESSION['agrint']){
		$_SESSION['agrint']=$_SESSION['AgrIntervalle'][$GLOBALS['indexByID'][$i]][0];
		$_SESSION['agrfunc']=1;
		if($alert) 
			echo '<div class="alert alert-warning">Estimated rows exceeds '.$_SESSION['max_rows'].' set as maximum
		in <a href="?tab=Settings" class="alert-link">settings</a>.
		Switched to '.$_SESSION['AgrIntervalle'][$GLOBALS['indexByID'][$i]][1].' + Avg</div>';
	}
}

$pathinfo=get_folder_subfolders();

$ids=array();
$names=array();
for($i=0;$i<$max_i;$i++){
	if(isset($pathinfo['subfolders']))
		$_SESSION['clipboard'][$i]['subfolder']=$pathinfo['subfolders'][$i].'/';
	else 
		$_SESSION['clipboard'][$i]['subfolder']='';
	if($_SESSION['chartmulti'])
		$series[$i]=array($_SESSION['clipboard'][$i]);
	else 
		$series[0][$i]=$_SESSION['clipboard'][$i];
	$ids[$i]=$_SESSION['clipboard'][$i][2];
	$names[$i]=$_SESSION['clipboard'][$i][5];
	checkres($sec,$_SESSION['clipboard'][$i]['res'],!isset($_GET['interval']));
}


if($_SESSION['gnuplot']){
?>
<script>
var width=$(window).width();
if(width>1200) width=1140;
else if(width>992) width=940;
else if(width>768) width=720;
else width=width-48;

<?php 
if($_SESSION['chartmulti']){
	for($i=0;$i<$max_i;$i++){ ?>
		document.write('<img src="chart.php?x='+width+'&i=<?php echo $i;?>"><br/>');
<?php }
} else { ?>
document.write('<img src="chart.php?x='+width+'">');
<?php }?>
</script>
<?php } 
else 
{ ?>

<script src="js/d3.min.js"></script>
<script src="js/rickshaw.min.js"></script>
<script>
var currentX=0;
var selectedX= new Object();

function setSelectedTSTable(){
    var keys = [];
	for (var k in selectedX) {
		keys.push(k);
	}
	keys.sort();

	if(keys.length==0){
		$('#selected_ts').html('');
		return;
	}
	$('#selected_ts').html('<div class="col-xs-12"><b>Selected Timestamps:</b></div>');
	for (var i in keys) {
		d=new Date(keys[i]*1000);
		$('#selected_ts').append('<div class="col-md-4 col-xs-6">'+d.toString()+
				' <input type="hidden" name="ts[]" value="'+keys[i]+'">'+
				'<a class="btn btn-xs btn-default" onclick="delete selectedX['+keys[i]+']; setSelectedTSTable(); return;">'+
				'<span class="glyphicon glyphicon-remove"></span></a></div>');
	}
    $('#selected_ts').append('<div class="col-xs-12"><hr/></div>');
}

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
<?php 
$timefilter=xmlrpc_array(array($_SESSION['from'],$_SESSION['until']),'dateTime.iso8601');
if(! $_SESSION['agrfunc'] || ! $_SESSION['agrint'])	$filter_arg=xmlrpc_array($_SESSION['StatusFilter'],'int');
else $filter_arg=xmlrpc_array(array($_SESSION['agrfunc'],$_SESSION['agrint']),'int');

$data=getSeries($ids, $_SESSION['agrfunc'], $_SESSION['agrint'], $timefilter, $filter_arg);
if(! isset($data['datetime'])) $no_data=1;

for($p=0;$p<count($series);$p++){
?>
<div class="row">
<div class="col-md-9">
      <div id="y_axis<?php echo $p;?>"></div>
		<div id="chart<?php echo $p;?>"></div>
		<div id="timeline<?php echo $p;?>"></div>
		<div id="preview<?php echo $p;?>"></div>
</div>	
<div class="col-md-3">
 		<div id="legend<?php echo $p;?>"></div>
</div>
</div>
<script>
<?php 
if(! $readonly){ //set the onClick handler of the chart
if($_SESSION['chartdata']){?>
$('#chart<?php echo $p;?>').on('click', function() { $("#cb"+currentX).prop('checked',true);});
<?php } else { ?>
$('#chart<?php echo $p;?>').on('click', function() { selectedX[currentX]=1; setSelectedTSTable();});
<?php }
} 
?>
	
var graph<?php echo $p;?> = new Rickshaw.Graph({
	element: document.querySelector("#chart<?php echo $p;?>"),
	renderer: '<?php if(! isset($_SESSION['renderer'])) $_SESSION['renderer']='line';
	echo $_SESSION['renderer'];?>',

	interpolation: 'linear',
	min: <?php if(isset($_POST['chart_min']) && is_numeric($_POST['chart_min'])) echo $_POST['chart_min'];
	else echo "'auto'";?>
	<?php if(isset($_POST['chart_max']) && is_numeric($_POST['chart_max'])) echo ',max: '.$_POST['chart_max'];?>,
	series: [
<?php 
if($_SESSION['chartdata']){
	$x=array();
	$y=array();
	$s=array();
}
$unit=array();

$chart_error_messages=array();
for($i=0;$i<count($series[$p]);$i++){
	$ccount=0; //Counts points following directly after on another...	
	$dcount=0; //Counts non NAN points 
	$res=xml_call('ObjektHandler.getLowestRefObjekt',
			array(new xmlrpcval($series[$p][$i][2],'int'),
					new xmlrpcval('mess_einheit','string')));
	$unit[$i]=$res[20];
	
	if($i>0) echo ", ";
	$comma=0;
	echo "{ data: [";
	for($j=0;$j<count($data['datetime']);$j++){
		if($comma){
			echo ",\n";
			$comma=0;
		}
		if(! $_SESSION['interpolate'] && $j>1 && ($data['datetime'][$j]-$data['datetime'][$j-1])>=
				2*($data['datetime'][$j-1]-$data['datetime'][$j-2])){
			echo "{x:".($data['datetime'][$j-1]+($data['datetime'][$j]-$data['datetime'][$j-1])).",y:null},\n";
			$comma=0;
			if($ccount<2) $ccount=0;
		}
		if(! $_SESSION['interpolate'] || ! is_nan($data[($i+$p)][$j])){
			echo "{x:".$data['datetime'][$j].
		",y:".(is_nan($data[($i+$p)][$j])?'null':round($data[($i+$p)][$j],5))."}";
			$comma=1;
			if(! is_nan($data[($i+$p)][$j])){
				$ccount++;
				$dcount++;
			}
			elseif($ccount<2) $ccount=0; 
		}
	}
	if($dcount==0 && $_SESSION['interpolate']) echo "{x:".$data['datetime'][0].',y:null}';
	echo "],
	color: palette.color(),
    name: '".$series[$p][$i]['subfolder'].' <b>'.$series[$p][$i][5].'</b>'.($unit[$i]?" [$unit[$i]]":'')."'}";
	
	if($dcount==0)
		$chart_error_messages[]=' <b>'.$series[$p][$i]['subfolder'].$series[$p][$i][5].'</b>
		does not have data in the selected interval.';
	elseif($ccount<2 && ! $_SESSION['interpolate'] && $_SESSION['renderer']=='line' ) 
		$chart_error_messages[]=' <b>'.$series[$p][$i]['subfolder'].$series[$p][$i][5].'</b>
	 does not show data (different resolution!). Try setting interpolate in chart options.';
}


?>
]
});
graph<?php echo $p;?>.render();

var preview<?php echo $p;?> = new Rickshaw.Graph.RangeSlider( {
	graph: graph<?php echo $p;?>,
	element: document.getElementById('preview<?php echo $p;?>'),
} );
<?php if(! $readonly){ ?>
preview<?php echo $p;?>.slideCallbacks=[set_from_until];
<?php }?>

var hoverDetail<?php echo $p;?> = new Rickshaw.Graph.HoverDetail( {
	graph: graph<?php echo $p;?>,
	xFormatter: function(x) {
		currentX=x;
		return new Date(x * 1000).toString();
	}
} );

var annotator<?php echo $p;?> = new Rickshaw.Graph.Annotate( {
	graph: graph<?php echo $p;?>,
	element: document.getElementById('timeline<?php echo $p;?>')
} );

var legend<?php echo $p;?> = new Rickshaw.Graph.Legend( {
	graph: graph<?php echo $p;?>,
	element: document.getElementById('legend<?php echo $p;?>')

} );

var shelving<?php echo $p;?> = new Rickshaw.Graph.Behavior.Series.Toggle( {
	graph: graph<?php echo $p;?>,
	legend: legend<?php echo $p;?>
} );

var order<?php echo $p;?> = new Rickshaw.Graph.Behavior.Series.Order( {
	graph: graph<?php echo $p;?>,
	legend: legend<?php echo $p;?>
} );

var highlighter<?php echo $p;?> = new Rickshaw.Graph.Behavior.Series.Highlight( {
	graph: graph<?php echo $p;?>,
	legend: legend<?php echo $p;?>
} );

//var ticksTreatment = 'glow';

var xAxis<?php echo $p;?> = new Rickshaw.Graph.Axis.Time( {
	graph: graph<?php echo $p;?>,
	ticksTreatment: 'glow',
	timeFixture: new Rickshaw.Fixtures.Time.Local()
} );

xAxis<?php echo $p;?>.render();

var yAxis<?php echo $p;?> = new Rickshaw.Graph.Axis.Y( {
	graph: graph<?php echo $p;?>,
	tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
	ticksTreatment: 'glow'
} );

yAxis<?php echo $p;?>.render();

</script>
<?php
}
if($no_data) echo '<div class="alert alert-danger">Nothing to plot: Series do not have data in the selected interval.</div>';
else {
	for($i=0;$i<count($chart_error_messages);$i++){
		echo '<div class="alert alert-danger">'.$chart_error_messages[$i].'</div>';
	}
}
} //rickshaw ploting
} // clipboard empty!
//Actionblock for zoom, move + data
echo '
<div class="btn-group">'; //block-action dropdown
echo_saved_cb_dropdown();
echo '
	</div>';
if($max_i){
echo_button('back','arrow-left',"?move=-1");
echo_button('forward','arrow-right',"?move=+1");
echo_button('Zoom in','zoom-in',"?zoom=+1");
echo_button('Zoom out','zoom-out',"?zoom=-1");
if(count($_SESSION['clipboard'])>1){
	if($_SESSION['chartmulti']) echo_button('Single Plot','resize-small',"?chartmulti=0");
	else echo_button('Multiple Plots','resize-full',"?chartmulti=1");
}
if(! $_SESSION['gnuplot']){
	if($_SESSION['chartdata']) echo_button('Hide Data','arrow-up',"?chartdata=0");
	else echo_button('Show Data','arrow-down',"?chartdata=1");
//	if($_SESSION['interpolate']) echo_button('Show gaps','pause',"?interpolate=0");
//	else echo_button('Interpolate','minus',"?interpolate=1");
}
echo '<div class="btn-group">
<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" >
<span class="glyphicon glyphicon-resize-horizontal"></span> Interval <span class="caret"></span></button>
		 <ul class="dropdown-menu" role="menu">';
$interval=array('today','last 24 hours','last 3 days','last 7 days','last 30 days','yesterday','this week','last week','this month','last month','this year','last year');
for($i=0;$i<count($interval);$i++){
	echo '<li><a href="?interval='.urlencode($interval[$i]).'">'.htmlspecialchars($interval[$i]).'</a></li>';
}
echo '</ul></div>';

}

if($max_i){
//Datablock
if($_SESSION['chartdata']){
	$status=array();
	while(list($k,$v)=each($_SESSION['Status'])){
		$status[$v[0]]=$v[1];
	}
	reset($_SESSION['Status']);
	echo '<form action="?action=chartdata" method="POST" class="form" role="form">
	<div class="block">
	<div class="block-header">Chart Data</div>
	<div class="row">
	<table class="table table-hover col-sm-12" id="domainTable">
		<thead>
		<tr>
		<th>Time</th>';
	for($j=0;$j<count($names);$j++){
		echo "<th>$names[$j]</th>";
	}
	if(count($ids)==1) echo '<th>Status</th>';
	echo '
		'.($readonly?'':'<th>
         <!-- Added ID to below select box -->
         <input type="checkbox" name="selectAll" id="selectAllDomainList" />
        </th>').'
		</tr>
		</thead>
		<tbody>
	';
	for($i=0;$i<count($data['datetime']);$i++){
		echo '<tr><td>'
		.date('Y-m-d H:i:s',$data['datetime'][$i]).'</td>';
		for($j=0;$j<count($ids);$j++){
			echo '<td>'.round($data[$j][$i],4).'</td>';
		}
		if(count($ids)==1) echo '<td>'.$status[$data['status'][$i]].'</td>';
		echo ($readonly?'':'<td><input type="checkbox" id="cb'.$data['datetime'][$i].'" value="'.$data['datetime'][$i].'" name="ts[]"></td>').'
		</tr>';
	}
	echo '</tbody></table>
	<script>
	$(\'#selectAllDomainList\').click (function () {
     var checkedStatus = this.checked;
    $(\'#domainTable tbody tr\').find(\'td:last :checkbox\').each(function () {
        $(this).prop(\'checked\', checkedStatus);
     });
});
	
    $(document).ready(function() {
      $(\'#domainTable tbody tr\').click(function(event) {
        if (event.target.type !== \'checkbox\') {
          $(\':checkbox\', this).trigger(\'click\');
        }
      });
    });

</script>
	</div></div>
'.($readonly?'':'<div class="block-action">'.get_input("status","Status",'','').'
	<button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-ok"></span> Set Status</button>
	 <button class="btn btn-primary" type="submit" name="_action_remove" onclick="return confirm(\'Are you sure?\');">
	<span class="glyphicon glyphicon-trash"></span> Delete Selected</button>
</div>').'
	</form>';
		
} elseif(! $readonly) {
	//Status tool
	echo '
	
	<form action="?action=chartdata" method="POST" class="form" role="form">';
	echo '<div class="block">
		<div class="block-header">Set Status or Delete data points</div>
		<div class="row" id="selected_ts">
		</div>
		<div class="row">
		';
		echo_field("from",'From (inclusive &ge;)','dateTime.iso8601','',3);
		echo_field("until",'Until (inclusive &le;)','dateTime.iso8601','',3);
		echo_field("status",'Status','Status',"-1",3);
		echo '	<div class="col-sm-6 col-lg-3">
		<br/>
		<button class="btn btn-primary" type="submit" id="set_status">
		<span class="glyphicon glyphicon-ok"></span> Set Status</button>
	 <button id="delete_bt" class="btn btn-primary" type="submit" name="_action_remove"
	  onclick="return confirm(\'Do you really want to delete data points for the selected timestamps?\');">
	<span class="glyphicon glyphicon-trash"></span> Delete</button>
		</div>
		
		</div>
		</div>
		</form>
		<br/>
		';
	
}


echo_filter_form();

echo '<p>Note: If plotting does not work for you, your device may not support the plot library. In this case you 
can change your <a href="?tab=Settings">settings</a> to <b>render plot as image</b>.</p>';

}
?>