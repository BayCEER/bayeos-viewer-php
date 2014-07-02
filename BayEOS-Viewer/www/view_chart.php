<?php 
//Limit Plotting to five series
if(count($_SESSION['clipboard'])>$_SESSION['max_cols']){
	$max_i=$_SESSION['max_cols'];
	echo '<div class="alert alert-warning">You have more than '.$_SESSION['max_cols'].
	' series on your clipboard. Only first '.$_SESSION['max_cols'].' will get plotted.
	Change <a href="?tab=Settings" class="alert-link">settings</a> or remove series from your 
	<a href="?tab=Clipboard" class="alert-link">clipboard</a>.</div>';
} else 
	$max_i=count($_SESSION['clipboard']);

if($max_i==0){
	add_alert("Your clipboard is empty",'warning');
	echo $GLOBALS['alert'];
} else {

//Auto adjust resolution for large intervals
$series=array();
$sec=toEpoch($_SESSION['until'])-toEpoch($_SESSION['from']);
$resByID=array();
$indexByID=array();
for($i=0;$i<count($_SESSION['AgrIntervalle']);$i++){
	$resByID[$_SESSION['AgrIntervalle'][$i][0]]=$_SESSION['AgrIntervalle'][$i][2];
	$indexByID[$_SESSION['AgrIntervalle'][$i][0]]=$i;
}
function checkres($interval,$res){
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
		echo '<div class="alert alert-warning">Estimated rows exceeds '.$_SESSION['max_rows'].' set as maximum
		in <a href="?tab=Settings" class="alert-link">settings</a>.
		Switched to '.$_SESSION['AgrIntervalle'][$GLOBALS['indexByID'][$i]][1].' + Avg</div>';
	}
}

$pathinfo=get_folder_subfolders();

for($i=0;$i<$max_i;$i++){
	if(isset($pathinfo['subfolders']))
		$_SESSION['clipboard'][$i]['subfolder']=$pathinfo['subfolders'][$i].'/';
	else 
		$_SESSION['clipboard'][$i]['subfolder']='';
	if($_SESSION['chartmulti'])
		$series[$i]=array($_SESSION['clipboard'][$i]);
	else 
		$series[0][$i]=$_SESSION['clipboard'][$i];
	checkres($sec,$_SESSION['clipboard'][$i]['res']);
}

?>

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
		$('#delete_bt').prop("disabled","disabled");
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
    $('#delete_bt').prop("disabled",null);
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
for($p=0;$p<count($series);$p++){
?>
	<div id="chart_container">
        <div id="y_axis<?php echo $p;?>"></div>
		<div id="chart<?php echo $p;?>"></div>
		<div id="legend<?php echo $p;?>"></div>
		<div id="timeline<?php echo $p;?>"></div>
		<div id="preview<?php echo $p;?>"></div>
		</div>	
<script>
<?php if($_SESSION['chartdata']){?>
$('#chart<?php echo $p;?>').on('click', function() { $("#cb"+currentX).prop('checked',true);});
<?php } else { ?>
$('#chart<?php echo $p;?>').on('click', function() { selectedX[currentX]=1; setSelectedTSTable();
//set_from_until('',currentX,currentX);
});
<?php } ?>
	
var graph<?php echo $p;?> = new Rickshaw.Graph({
	element: document.querySelector("#chart<?php echo $p;?>"),
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
for($i=0;$i<count($series[$p]);$i++){	
	$res=xml_call('ObjektHandler.getLowestRefObjekt',
			array(new xmlrpcval($series[$p][$i][2],'int'),
					new xmlrpcval('mess_einheit','string')));
	$unit[$i]=$res[20];
	
	if($i>0) echo ", ";
	echo "{ data: [";
	if(! $_SESSION['agrfunc'] || ! $_SESSION['agrint']){
		$val=xml_call('MassenTableHandler.getRows',
				array(new xmlrpcval($series[$p][$i][2],'int'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['from'],'dateTime.iso8601'),
								new xmlrpcval($_SESSION['until'],'dateTime.iso8601')),'array'),
						new xmlrpcval(array(new xmlrpcval(0,'int'),new xmlrpcval(1,'int'),new xmlrpcval(2,'int')),'array'
						)));
		$val=$val[1]->scalar;
		$step=1;
		if(strlen($val)>20000 && ! $_SESSION['chartdata']) $step=round(strlen($val)/20000);
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
			if($_SESSION['chartdata']){
				$x[]=$tmp[1];
				$y[]=$tmp2[1];
				$tmp2=unpack('c',substr($val,$pos+8,1));
				$s[]=$tmp2[1];
			}
			$pos+=9*$step;
		}
	} else {
		$val=xml_call('AggregationTableHandler.getRows',
				array(new xmlrpcval($series[$p][$i][2],'int'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['from'],'dateTime.iso8601'),
								new xmlrpcval($_SESSION['until'],'dateTime.iso8601')),'array'),
						new xmlrpcval(array(new xmlrpcval($_SESSION['agrfunc'],'int'),new xmlrpcval($_SESSION['agrint'],'int')),'array'
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
    name: '".$series[$p][$i]['subfolder'].' <b>'.$series[$p][$i][5].'</b>'.($unit[$i]?" [$unit[$i]]":'')."'}";
}


?>
]
});
graph<?php echo $p;?>.render();

var preview<?php echo $p;?> = new Rickshaw.Graph.RangeSlider( {
	graph: graph<?php echo $p;?>,
	element: document.getElementById('preview<?php echo $p;?>'),
} );
preview<?php echo $p;?>.slideCallbacks=[set_from_until];

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
if($no_data) echo '<div class="alert alert-danger">At least on series returns no data. Plotting will not work.</div>';

} // clipboard empty!
//Actionblock for zoom, move + data
echo '
<div class="block-action dropdown">';
echo_saved_cb_dropdown();
if($max_i){
echo_button('back','arrow-left',"?move=-1");
echo_button('forward','arrow-right',"?move=+1");
echo_button('Zoom in','zoom-in',"?zoom=+1");
echo_button('Zoom out','zoom-out',"?zoom=-1");
if(count($_SESSION['clipboard'])>1){
	if($_SESSION['chartmulti']) echo_button('Single Plot','resize-small',"?chartmulti=0");
	else echo_button('Multiple Plots','resize-full',"?chartmulti=1");
}
if(! $_SESSION['agrfunc'] || ! $_SESSION['agrint']){
	if($_SESSION['chartdata']) echo_button('Hide Data','arrow-up',"?chartdata=0");
	else echo_button('Show Data','arrow-down',"?chartdata=1");
}
}
echo '
	</div>';

if($max_i){
//Datablock
$readonly=!$_SESSION['clipboard'][0][0];
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
		<th>Time</th>
		<th>Value</th>
		<th>Status</th>
		'.($readonly?'':'<th>
         <!-- Added ID to below select box -->
         <input type="checkbox" name="selectAll" id="selectAllDomainList" />
        </th>').'
		</tr>
		</thead>
		<tbody>
	';
	for($i=0;$i<count($x);$i++){
		echo '<tr><td>'
		.date('Y-m-d H:i:s',$x[$i]).'</td><td>'.$y[$i].'</td><td>'.$status[$s[$i]].'</td>'.
		($readonly?'':'<td><input type="checkbox" id="cb'.$x[$i].'" value="'.$x[$i].'" name="ts[]"></td>').'
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
	 <button id="delete_bt" class="btn btn-primary" type="submit" name="_action_remove" disabled="disabled"
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
}
?>