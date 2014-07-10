<div class="col-sm-12"><h5>CSV-Export:</h5></div>
<input type="hidden" name="action" value="filter">
<input type="hidden" name="setCSVOptions" value=1>
<?php
echo_field("csv_dec",'Decimal Point','SelectValue',$_SESSION['csv_dec'],
			3,array('selectvalues'=>array('.',',')));
echo_field("csv_sep",'Field Separator','SelectValue',$_SESSION['csv_sep'],3,
		array('selectvalues'=>array(';',',','|','TAB','SPACE')));
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

$special_view_buttons=array(
		array('Download CSV','download-alt',"","btn btn-primary",'name="csv_df" id="csv_submit"'));
?>