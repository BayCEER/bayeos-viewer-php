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