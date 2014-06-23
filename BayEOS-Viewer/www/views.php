<?php
/************************************************************
 * Views 
 ***********************************************************/
function echo_button($name,$icon,$url='',$class='btn btn-default',$button_attr=''){
	echo "\n";
	if($url) echo '<a href="'.$url.'" class="'.$class.'" >';
	else echo '<button class="'.$class.'" '.$button_attr.'>';
	echo '<span class="glyphicon glyphicon-'.$icon.'"></span> '.$name;
	if($url) echo '</a>';
	else echo '</button>';
		
}

function echo_button_dropdown($name,$icon,$li,$class='btn btn-default',$button_attr=''){
	echo "\n";
	echo '<button type="button" class="'.$class.' dropdown-toggle" data-toggle="dropdown" '.$button_attr.'>';
	echo '<span class="glyphicon glyphicon-'.$icon.'"></span> '.$name.' <span class="caret"></span></button>
	 <ul class="dropdown-menu" role="menu">';
	for($i=0;$i<count($li);$i++){
		echo "\n".'<li><a href="'.$li[$i]['url'].'">'.
		(isset($li[$i]['icon'])?'<span class="glyphicon glyphicon-'.$li[$i]['icon'].'"></span> ':'').
		$li[$i]['name'].'</a></li>';
	}
	echo '</ul>';
}

function get_input($id,$art,$value='',$class='form-control',$options=array()){
	$out='';
	if(isset($options['readonly'])) $readonly=' readonly';
	else $readonly='';
	if(isset($options['selectvalues'])) $_SESSION['SelectValue']=$options['selectvalues'];
	if(isset($options['selectids'])) $_SESSION['SelectID']=$options['selectids'];
	
	switch($art){
		case 'hidden':
			$out.= '<input type="hidden" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars($value).'"'.$readonly.'>';
			break;
		case 'autocomplete':
			$out.='
			<input class="'.$class.'" id="'.$id.'_dp" name="'.$id.'_dp">
			<input id="'.$id.'" name="'.$id.'" type="hidden">
			<script type="text/javascript">
			$(\'#'.$id.'_dp\').autocomplete({source: function(request, response) {
			$.getJSON("search.php", { search: request.term,refclass : '.$options['refclass'].'},
			response);
	},
	select: function(event,ui){
	$(\'#'.$id.'\').val(ui.item.id);
	},
	change: function(event, ui) {
	if(! ui.item){
	this.value=\'\';
	$(\'#'.$id.'\').val(\'\');
	}
	},'.$options['additional_args'].'
	minLength: 1});
	</script>
	';
			break;
		case 'dateTime.iso8601':
			$jquery_datefm='Y-O-D';
			$out.="<input class=\"$class\" id=\"$id\" name=\"$id\"
			value=\"".$value."\"$readonly>"
			.'<script type="text/javascript">
			$(\'#'.$id.'\').datetimeEntry({datetimeFormat: \''.$jquery_datefm.' H:M\',
			spinnerImage: \'js/spinnerOrange.png\'});
			</script>';
			break;
		case 'SelectID':
		case 'RefClasses':
		case 'TimeZones':
		case 'IntervalTypes':
		case 'AgrIntervalle':
		case 'AgrFunktionen':
		case 'Status':
		case 'CRS':
			reset($_SESSION[$art]);
			$out.='<select class="'.$class.'" name="'.$id.'" id="'.$id.'"'.$readonly.'>
			<option value=""></option>';
			while(list($key,$v)=each($_SESSION[$art])){
				$out.="<option value=\"$v[0]\"".($v[0]==$value?" selected":'').">$v[1]</option>";
			}
			$out.="</select>";
			break;
		case 'SelectValue':
		case 'DataTypes':
			reset($_SESSION[$art]);
			$out.='<select class="'.$class.'" name="'.$id.'" id="'.$id.'"'.$readonly.'>';
			while(list($key,$v)=each($_SESSION[$art])){
				$out.="<option value=\"$v\"".($v==$value?" selected":'').">$v</option>";
			}
			$out.="</select>";
			break;
			
		case 'text':
			$out.='<textarea class="'.$class.'" name="'.$id.'" id="'.$id.'"'.$readonly.'>'.htmlspecialchars($value).'</textarea>';
			break;
		case 'boolean':
			$out.= '<input type="checkbox" name="'.$id.'" '.($value?' checked':'').''.$readonly.'>';
			break;
		default:
			$out.= '<input class="'.$class.'" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars($value).'"'.$readonly.'>';
	}
	if(isset($options['old_hidden']))
		$out.='<input type="hidden" name="_old_'.$id.'" value="'.htmlspecialchars($value).'">';

	return $out;
}

function echo_field($id,$name,$art,$value='',$col=6,$options=array()){
	if($col==0){
		echo get_input($id,$art,$value,'form-control',$options);
		return;
	}
	$sm_col=$col;
	if($col<4) $sm_col=$col*2;
	echo '<div class="col-sm-'.$sm_col.' col-lg-'.$col.'">';
	if($art=='boolean'){
		echo '<div class="checkbox">
		<label>';
		echo get_input($id,$art,$value,'form-control',$options);
		echo ' '.$name.'</label>';

	} else {
		echo '<div class="form-group ">
		<label class="control-label" for="'.$id.'">
		'.$name.':</label>
		';
		echo get_input($id,$art,$value,'form-control',$options);
	}
	echo '</div>
	</div>

	';
}




function echo_filter_form($name='Filter',$tfilter=1,$sfilter=1,$csvoptions=1){
	echo '<form action="?filter=1" method="POST" class="form" role="form">';
	if($tfilter){
	echo '<div class="block">
	<div class="block-header">Filter</div>
	<div class="row">
	<input type="hidden" name="setFilter" value=1>
	';
	echo_field("session_from",'From','dateTime.iso8601',toDateFromString($_SESSION['from']),3);
	echo_field("session_until",'Until','dateTime.iso8601',toDateFromString($_SESSION['until']),3);
	echo_field("session_agrfunc",'Aggregation Function','AgrFunktionen',$_SESSION['agrfunc'],3);
	echo_field("session_agrint",'Aggregation Interval','AgrIntervalle',$_SESSION['agrint'],3);
	echo '</div>
	</div>
	';
	}
	if($sfilter){
	echo '
	<div class="block">
	<div class="block-header">
	Status Filter
	</div>
	
	<div class="row">
	<input type="hidden" name="setStatusFilter" value=1>';
	reset($_SESSION['Status']);
	while(list($key,$v)=each($_SESSION['Status'])){
		echo_field("s".$v[0],$v[1],'boolean',in_array($v[0],$_SESSION['StatusFilter']),1);
	}
	reset($_SESSION['Status']);
	echo '</div>	
	</div>';
	}
	if($csvoptions){
	echo '<div class="block">
	<div class="block-header">
	CSV Options
	</div>
	
	<div class="row">
	<input type="hidden" name="setCSVOptions" value=1>
	
	';
	echo_field("csv_dec",'Decimal Point','SelectValue',$_SESSION['csv_dec'],
			3,array('selectvalues'=>array('.',',')));
	echo_field("csv_sep",'Field Separator','SelectValue',$_SESSION['csv_sep'],3,array('selectvalues'=>array(';',',','|','TAB','SPACE')));
	$tz=array($_SESSION['tz'],"Etc/GMT");
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT-'.$i;
	}
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT+'.$i;
	}
	echo_field("csv_tz",'Timezone','SelectValue',$_SESSION['csv_tz'],3,array('selectvalues'=>$tz));
	echo_field("csv_dateformat",'Date Format','SelectValue',$_SESSION['csv_dateformat'],3,
			array('selectvalues'=>array('Y-m-d H:i:s','d.m.Y H:i:s')));
	
	echo '</div>
	</div>';
	}
	echo '
	<div class="block-action">';
	echo_button('Update','ok',"","btn btn-primary"); 
	echo_button('Download CSV','download-alt',"","btn btn-primary",'name="csv"');
	if($tfilter){
		if($name=='Filter')
			echo_button('Clipboard','pushpin',"?tab=Clipboard");
		else
			echo_button('Chart','signal',"","btn btn-primary",'name="chart"');
		echo_button('Add Series','folder-open',"?tab=Folders");
	}
	echo '				
	</div>
	</form>';
}



function echo_table($childs,$action="add"){
	$with_path=isset($childs[0]['path']);
	?>	
	</div><div class="container">
		<table class="table table-hover col-sm-12">
		<thead>
		<tr>
		<th>Name</th>
		<?php if($with_path){?>		
		<th class="hidden-xs">Folder</th>
		<?php }?>
		<th class="hidden-xs">Records from</th>
		<th class="hidden-xs">Records until</th>
		<?php if($action=='remove'){?>
		<th><a href="./?remove=all" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-remove"></span> Remove all</a></th>
		<?php }?>
		</tr>
		</thead>
		<tbody>
	<?php 	
		for($i=0;$i<count($childs);$i++){
			echo '<tr><td>
			<span class="glyphicon glyphicon-'.$GLOBALS['uname_icon_hash'][$childs[$i][4]].'">
			</span> '.(isset($GLOBALS['bayeos_canhavechilds'][$childs[$i][4]])?
					'<a href="?id='.$childs[$i][2].(strstr($childs[$i][4],'messung_')?'&tab=Folders':'').'">'.$childs[$i][5].'</a>':$childs[$i][5]).'
					</td>
					'.($with_path?'<td class="hidden-xs">
			<a href="?id='.$childs[$i]['path'][0].'">'.$childs[$i]['path'][1].'</a>
							</td>':'').'
			<td class="hidden-xs">'.toDate($childs[$i][6]).'</td>
			<td class="hidden-xs">'.toDate($childs[$i][7]).'</td>
			<td class="link">
			';
			if($childs[$i][4]=='messung_massendaten' || $childs[$i][4]=='messung_ordner'){
			switch($action){
				case 'add':
					echo '<a href="./?add='.$childs[$i][2].'" class="btn btn-xs btn-default">
	         		<span class="glyphicon glyphicon-pushpin"></span> To Clipboard</a>';
					break;
				case 'remove':
					echo '<a href="./?remove='.$childs[$i][2].'" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-remove"></span> Remove</a>';
					break;
						
			}
			}
			echo '<a href="./?edit='.$childs[$i][2].(strstr($childs[$i][4],'messung_')?'&tab=Folders':'').'" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-edit"></span> Details</a>';
			echo '	
			</td>
			</tr>
			';
			
		}
	?>
		 </tbody>
	     </table> 
	 </div>  
	<div class="container">	
	<?php 
		
}
?>