<?php
/************************************************************
 * Views 
 ***********************************************************/
function get_path($path,$max=30){
	$current=explode("/",$path);
	$out='';
	for($i=count($current)-1;$i>=0;$i--){
		if($current[$i]){
			if(mb_strlen($out)>$max){
				$out='.../'.$out;
				return $out;
			}
			$out=$current[$i].'/'.$out;
		}
	}
	if($current[0]!='.')
		$out='/'.$out;
	return $out;
}

function echo_button($name,$icon,$url='',$class='btn btn-default',$button_attr=''){
	echo "\n";
	if($url) echo '<a href="'.$url.'" class="'.$class.'" >';
	else echo '<button class="'.$class.'" '.$button_attr.'>';
	echo '<span class="glyphicon glyphicon-'.$icon.'"></span> <span class="hidden-xs">'.$name.'</span>';
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
			if(! isset($options['additional_args'])) $options['additional_args']='';
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
		case 'password':
			$out.= '<input class="'.$class.'" type="password" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars($value).'"'.$readonly.'>';			
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


function echo_saved_cb_dropdown(){
	if(count($_SESSION['cb_saved'])){?>
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" ><span class="glyphicon glyphicon-upload"></span> Load <span class="caret"></span></button>
		 <ul class="dropdown-menu" role="menu">
	<?php 
	while(list($key)=each($_SESSION['cb_saved'])){
		echo '<li><a href="?cb_load='.urlencode($key).'">'.htmlspecialchars($key).'</a></li>';
	}
	reset($_SESSION['cb_saved']);
	
	?>
	</ul>
	<?php }
		
}


function echo_filter_form($name='Filter',$tfilter=1,$sfilter=1,$csvoptions=1){
	echo '<form method="POST" class="form" role="form">';
	if($tfilter){
	echo '<div class="block">
	<div class="block-header">Filter</div>
	<div class="row">
	<input type="hidden" name="action" value="filter">
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
		echo_field("s".$v[0],$v[1],'boolean',in_array($v[0],$_SESSION['StatusFilter']),
				(mb_strlen($v[1])>17?round(mb_strlen($v[1])/18+1.5,0):2));
	}
	reset($_SESSION['Status']);
	echo '</div>	
	</div>';
	}
	if($csvoptions){
	echo '<div class="block">
	<div class="block-header">
	Download Options
	</div>
	
	<div class="row">
	<input type="hidden" name="setCSVOptions" value=1>
	
	';
	$tz=array($_SESSION['tz'],"Etc/GMT");
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT-'.$i;
	}
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT+'.$i;
	}
	echo_field("format",'File Format','SelectValue','format',3,
			array('selectvalues'=>array('csv','xlsx','xls')));
	echo_field("csv_tz",'Timezone','SelectValue',$_SESSION['csv_tz'],3,array('selectvalues'=>$tz));
	echo_field("csv_dec",'CSV Decimal Point','SelectValue',$_SESSION['csv_dec'],
			2,array('selectvalues'=>array('.',',')));
	echo_field("csv_sep",'CSV Field Separator','SelectValue',$_SESSION['csv_sep'],2,array('selectvalues'=>array(';',',','|','TAB','SPACE')));
	echo_field("csv_dateformat",'CSV Date Format','SelectValue',$_SESSION['csv_dateformat'],2,
			array('selectvalues'=>array('Y-m-d H:i:s','d.m.Y H:i:s')));
	
	echo '</div>
	</div>';
	}
	echo '
	<div class="block-action">';
	echo_button('Update','ok',"","btn btn-primary"); 
	echo_button('Download','download-alt',"","btn btn-primary",'name="download"');
//	echo_button('XLSX','download-alt',"","btn btn-primary",'name="xlsx"');
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


function echo_pagination($max,$current=1,$qs='',$step=10){
	if($max<=$step) return;
	echo '<ul class="pagination">';
	if($current<1) $current=1;
	if($current>ceil($max/$step)) $current=ceil($max/$step);
	
	$pag=array($current);
	$current_step=1;
	$last=$current;
	$pag_count=0;
	while(($current-$current_step)>=1){
		$pag_count++;
		if($max/$step>12 || $pag_count>2){
			$new=ceil(($current-$current_step)/$current_step)*$current_step;
			$current_step*=10;
		} else {
			$new=round($current-$current_step);
			$current_step++;
			if($pag_count>1)
				$current_step=10;
		}
		if($new!=$last){
			$pag[]=$new;
			$last=$new;
		}
		
	}
	$current_step=1;
	$last=$current;
	$pag_count=0;
	while(($current+$current_step)<=ceil($max/$step)){
		$pag_count++;
		if($max/$step>12 || $pag_count>2){
			$new=floor(($current+$current_step)/$current_step)*$current_step;
			$current_step*=10;
		} else {
			$new=round($current+$current_step);
			$current_step++;
			if($pag_count>1)
				$current_step=10;
		}
		if($new!=$last){
			$pag[]=$new;
			$last=$new;
		}
	}
	sort($pag);
	
	
	echo '<li'.($current>1?'':' class="disabled"').'><a href="?page=1'.$qs.'">&laquo;</a></li>';
		
	echo '<li'.($current>1?'':' class="disabled"').'><a href="?page='.($current-1).$qs.'">&lsaquo;</a></li>';
	for($i=0;$i<count($pag);$i++){
		echo '<li'.($current==$pag[$i]?' class="active"':'').'><a href="?page='.$pag[$i].$qs.'">'.$pag[$i].'</a></li>';
	}
	echo '<li'.(($current)>=ceil($max/$step)?' class="disabled"':'').'><a href="?page='.($current+1).$qs.'">&rsaquo;</a></li>';
	echo '<li'.(($current)>=ceil($max/$step)?' class="disabled"':'').'><a href="?page='.ceil($max/$step).$qs.'">&raquo;</a></li>';
	echo '</ul>';
}

function echo_table($childs,$action="add",$qs=''){
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
		<th>Records until</th>
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
			<a href="?tab=Folders&id='.$childs[$i]['path'][0].'">'.get_path($childs[$i]['path'][1],40).'</a>
							</td>':'').'
			<td class="hidden-xs">'.toDate($childs[$i][6]).'</td>
			<td>'.toDate($childs[$i][7]).'</td>
			<td class="link">
			';
			if($childs[$i][4]=='messung_massendaten' || $childs[$i][4]=='messung_ordner'){
			switch($action){
				case 'add':
					echo '<a href="./?add='.$childs[$i][2].$qs.'" class="btn btn-xs btn-default">
	         		<span class="glyphicon glyphicon-pushpin"></span> <span class="hidden-xs">To Clipboard</span></a>';
					break;
				case 'remove':
					echo '<a href="./?remove='.$childs[$i][2].$qs.'" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-remove"></span> <span class="hidden-xs">Remove</span></a>';
					break;
						
			}
			}
			echo '<a href="./?edit='.$childs[$i][2].(strstr($childs[$i][4],'messung_')?'&tab=Folders':'').'" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">Details</span></a>';
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

function echo_dbtable($res,$header,$action='',$qs='',$icon='',$edit=array('del','edit'),$step=10){
	echo_pagination(pg_num_rows($res),$_GET['page'],$qs,$step);
	
	?>
	
	<table class="table table-hover col-sm-12">
	<thead>
	<tr>
	<?php for($i=0;$i<count($header);$i++){?>
	<th><?php echo $header[$i];?></th>
	<?php }?>
	</tr>
	</thead>
	<tbody>
	
	<?php 
			for($i=($_GET['page']-1)*10;$i<min(pg_num_rows($res),$_GET['page']*10);$i++){
				$r=pg_fetch_row($res,$i);
				echo '<tr><td>'.($icon?'<span class="glyphicon glyphicon-'.$icon.'"></span> ':'').$r[0].'</td>';
				for($j=1;$j<count($header);$j++){
					echo '<td>'.$r[$j].'</td>';
				}
				echo '<td>'.(in_array('del',$edit)?'
						<a href="./?action='.$action.'&del='.urlencode($r[0]).$qs.'" class="btn btn-xs btn-default" 
				 onClick="return confirm(\'Do you really want to delete this item?\');">
						<span class="glyphicon glyphicon-remove"></span> <span class="hidden-xs">Delete</span></a>':'').
				 (in_array('edit',$edit)?'<a href="./?edit='.urlencode($r[0]).$qs.'" class="btn btn-xs btn-default">
						<span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">Edit</span></a>':'').'
				</td></tr>';
			}
			
	?>
		</tbody>
	</table>
<?php 		
}

?>