<?php 
if(! count($_SESSION['clipboard'])) echo '<div class="alert alert-warning">Your clipboard is empty.</div>';
else echo_table($_SESSION['clipboard'],"remove");
echo_filter_form("Export");
?>