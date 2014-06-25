<?php 
/**********************************************************
 * Global Constants
 *********************************************************/

$bayeos_trees=array('Folders','Units','Targets','Locations','Devices','Compartments');
$bayeos_tree_unames=array('Folders'=>'messung_ordner','Units'=>'mess_einheit',
		'Targets'=>'mess_ziel','Locations'=>'mess_ort','Devices'=>'mess_geraet',
		'Compartments'=>'mess_kompartiment');
$bayeos_hasref=array('messung_ordner'=>1,'messung_massendaten'=>1,'messung_labordaten'=>1,
		'data_frame'=>1,'data_column'=>1);
$bayeos_canhavechilds=array('messung_ordner'=>array('messung_ordner','data_frame','messung_massendaten'),
		'data_frame'=>array('data_column'),
		'mess_einheit'=>array('mess_einheit'),'mess_ziel'=>array('mess_ziel'),
		'mess_ort'=>array('mess_ort'),'mess_geraet'=>array('mess_geraet'),
		'mess_kompartiment'=>array('mess_kompartiment'));
$bayeos_has_special_view=array(
		'data_frame'=>array(array('df_editor','zoom-in','Editor'),
			array('df_export','download-alt','Export'))
		);
$uname_name_hash=array('art_objekt'=>'Class',
		'mess_ziel'=>'Target',
		'messung'=>'Series',
		'messung_ordner'=>'Folder',
		'messung_massendaten'=>'Time Series',
		'messung_labordaten'=>'Series',
		'benutzer'=>'User',
		'gruppe'=>'Group',
		'mess_einheit'=>'Unit',
		'mess_geraet'=>'Device',
		'mess_kompartiment'=>'Compartment',
		'mess_ort'=>'Location',
		'mess_einbau'=>'Fitting',
		'data_frame'=>'Data Frame',
		'data_column'=>'Data Column',
		'web_mit'=>'Link',
		'web_pro'=>'Link'
);
$uname_icon_hash=array('art_objekt'=>'inbox',
		'mess_ziel'=>'arrow-right',
		'messung'=>'stats',
		'messung_ordner'=>'folder-close',
		'messung_massendaten'=>'stats',
		'messung_labordaten'=>'stats',
		'benutzer'=>'user',
		'gruppe'=>'group',
		'mess_einheit'=>'tag',
		'mess_geraet'=>'hdd',
		'mess_kompartiment'=>'th-large',
		'mess_ort'=>'home',
		'mess_einbau'=>'wrench',
		'data_frame'=>'list-alt',
		'data_column'=>'stats',
		'web_mit'=>'link',
		'web_pro'=>'link'
);

?>