<div class="ad-sidebar">
<?php
	$main_ad_code = get_option("T_main_ad_code");
	$main_ad_code = stripslashes($main_ad_code);
	if($main_ad_code != "") { echo $main_ad_code; }
?>
</div>