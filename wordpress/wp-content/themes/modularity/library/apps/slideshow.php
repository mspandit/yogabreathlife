<!-- Begin slideshow -->
<?php
$slideshow_cat = get_option('T_slideshow_cat');
?>
<?php 
		$my_query = new WP_Query("showposts=5&cat=$slideshow_cat"); ?>
<ul id="portfolio">
<?php while ($my_query->have_posts()) : $my_query->the_post();
		$do_not_duplicate = $post->ID; ?>
<li><?php postimage('large'); ?></li>
<?php endwhile; wp_reset_query(); ?>
</ul>