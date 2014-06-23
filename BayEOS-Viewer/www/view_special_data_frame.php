<?php 

$res=xml_call('DataFrameHandler.getFrameRows',array(new xmlrpcval($_GET['edit'],'int'),
		new xmlrpcval(null,'null')));

$input_mapper=array('STRING'=>'string','DOUBLE'=>'string','INTEGER'=>'string','BOOLEAN'=>'boolean','DATE'=>'dateTime.iso8601');
$xml_mapper=array('STRING'=>'string','DOUBLE'=>'double','INTEGER'=>'int','BOOLEAN'=>'boolean','DATE'=>'dateTime.iso8601');
$options=array('old_hidden'=>1);
if(! $node[0]) $options['readonly']=1;
?>
<input type="hidden" name="_action_df" value="1">
<table class="table table-hover col-sm-12">
	<thead>
		<tr>
		<th>NR</th>
		<?php 
		for($i=0;$i<count($res[0]);$i++){
			echo '<th>
			<input type="hidden" name="ctyp[]" value="'.$xml_mapper[$res[0][$i][3]].'">
			<input type="hidden" name="cid[]" value="'.$res[0][$i][0].'">
			'.$res[0][$i][2].'</th>';
		}
		
		?>
		</tr>
	</thead>
	<tbody>
		<?php
		$i=1;
		$max=$res[1][count($res[1])-1][0];
		$ri=0;
		while($i<=($max+1)){
			echo '<tr>
			<td><input type="hidden" name="r[]" value="'.$i.'">'.$i.'</td>';
			if($res[1][$ri][0]==$i){
				//Has data!
				$data=$res[1][$ri];
				$ri++;
			} else 
				$data=array();
			for($j=0;$j<count($res[0]);$j++){
				echo '<td>'.get_input('v'.$i.'_'.$res[0][$j][0],
					$input_mapper[$res[0][$j][3]],
						($res[0][$j][3]=='DATE'?toDate($data[$j+1]):$data[$j+1]),'',$options).'</td>';
			}			
			echo '
			</tr>
			';
			$i++;
		}


		?>
	</tbody>
</table>
</div></div>
<?php
	echo '<div class="block">
	<div class="block-header">
	CSV Export
	</div>
	
	<div class="row">';
	echo_field("csv_dec",'Decimal Point','SelectValue',($_SESSION['tz']=="Europe/Berlin"?',':'.'),
			3,array('selectvalues'=>array('.',',')));
	echo_field("csv_sep",'Field Separator','SelectValue',';',3,array('selectvalues'=>array(';',',','|','TAB','SPACE')));
	$tz=array($_SESSION['tz'],"Etc/GMT");
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT-'.$i;
	}
	for($i=1;$i<=12;$i++){
		$tz[]='Etc/GMT+'.$i;
	}
	echo_field("csv_tz",'Timezone','SelectValue',$_SESSION['tz'],3,array('selectvalues'=>$tz));
	echo_field("csv_dateformat",'Date Format','SelectValue',';',3,
			array('selectvalues'=>array('Y-m-d H:i:s','d.m.Y H:i:s')));
	
	
	echo '</div><div class="row"><div class="col-sm-6 col-lg-3"><div class="form-group ">';
	echo_button('Download CSV','download-alt',"","btn btn-primary",'name="csv_df" id="csv_submit"');
	echo '</div></div>
	</div>
	</div>';
	
?>