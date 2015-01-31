<!-- Begin welcomebox -->
<?php
$welcomebox = get_option('T_welcomebox');
$welcomebox_title = get_option('T_welcomebox_title');
$welcomebox_content = get_option('T_welcomebox_content');
if($welcomebox == "On") { ?>
<div class="welcomebox entry">
<h3 class="sub"><?php echo stripslashes($welcomebox_title); ?></h3>
<h2><?php echo stripslashes($welcomebox_content); ?></h2>
</div>
<?php } ?>