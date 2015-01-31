<?php get_header(); ?>
<div class="span-<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { echo "15 colborder home"; } else { echo "24 last"; } ?>">
<div class="content">
		<h2>Whoops!  Whatever you are looking for cannot be found.</h2>
	</div>
	</div>
<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { get_sidebar(); } ?>
<!-- Begin Footer -->
<?php get_footer(); ?>