<div class="span-8 last">
	<div id="sidebar">
		<?php include (THEMELIB . '/apps/ad-sidebar.php'); ?>
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar') ) : ?>
		<?php endif; ?>	
	</div>
</div>