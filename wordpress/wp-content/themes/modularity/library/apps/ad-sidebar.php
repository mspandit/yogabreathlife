<div class="ad-sidebar">
<?php
	$sidebar_ad_code = get_option("T_sidebar_ad_code");
	$sidebar_ad_code = stripslashes($sidebar_ad_code);
	if($sidebar_ad_code != "") { echo $sidebar_ad_code; }
?>
</div>