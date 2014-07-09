<?php 
if(isset($_POST['offset'])) $_GET['offset']=$_POST['offset'];
if(! isset($_GET['offset'])) $_GET['offset']=0;
$step=10;
if($_GET['offset']<0) $_GET['offset']=0;

$nr=array();
for($i=($_GET['offset']+1);$i<=($_GET['offset']+$step);$i++){
	$nr[]=$i;
}

$res=xml_call('DataFrameHandler.getFrameRows',array(new xmlrpcval($_GET['edit'],'int'),
		xmlrpc_array($nr,'int')
		));
$input_mapper=array('STRING'=>'string','DOUBLE'=>'string','INTEGER'=>'string','BOOLEAN'=>'boolean','DATE'=>'dateTime.iso8601');
$xml_mapper=array('STRING'=>'string','DOUBLE'=>'double','INTEGER'=>'int','BOOLEAN'=>'boolean','DATE'=>'dateTime.iso8601');
$options=array('old_hidden'=>1);
if(! $node[0]) $options['readonly']=1;
$max=$res[1][count($res[1])-1][0];
echo_pagination($max+100,$_GET['offset'],"&view=df_editor&edit=$_GET[edit]",$step);
?>
<input type="hidden" name="offset" value="<?php echo $_GET['offset'];?>">
<input type="hidden" name="action" value="df">
<table class="table table-hover col-sm-12">
	<thead>
		<tr>
		<th>NR</th>
		<?php 
		for($i=0;$i<count($res[0]);$i++){
			echo '<th>
			<input type="hidden" name="ctyp[]" value="'.$xml_mapper[$res[0][$i][3]].'">
			<input type="hidden" name="cid[]" value="'.$res[0][$i][0].'">
			'.$res[0][$i][2].'
			<a href="?edit='.$res[0][$i][0].'" class="btn btn-xs btn-default" >
			<span class="glyphicon glyphicon-edit"></span> edit</a>
			</th>';
		}
		
		?>
		<th><a href="?edit=data_column" class="btn btn-xs btn-default" >
		<span class="glyphicon glyphicon-plus"></span> New Column</a></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i=$_GET['offset']+1;
		$ri=0;
		while($i<=($_GET['offset']+$step)){
			echo '<tr>
			<td><input type="hidden" name="r[]" value="'.$i.'">'.$i.'</td>';
			while(isset($res[1][$ri]) && $res[1][$ri][0]<$i) $ri++;
			if($res[1][$ri][0]==$i){
				//Has data!
				$data=$res[1][$ri];
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

