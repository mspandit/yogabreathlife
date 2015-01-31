<?php get_header(); ?>
<div class="span-<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { echo "15 colborder home"; } else { echo "24 last"; } ?>">
<div class="content">
<?php if (have_posts()) : ?>

	<h2>Search Results</h2>

	<div class="navigation">
		<div><?php next_posts_link('&laquo; Older Entries') ?></div>
		<div><?php previous_posts_link('Newer Entries &raquo;') ?></div>
	</div>

<?php while (have_posts()) : the_post(); ?>
<div <?php if(function_exists('post_class')) : ?><?php post_class(); ?><?php else : ?>class="post post-<?php the_ID(); ?>"<?php endif; ?>>
<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title() ?></a></h2>
<?php postimage('thumbnail'); ?>
<?php the_excerpt(); ?>
<p class="postmetadata alt quiet"><?php the_time('M d, Y') ?> @ <?php the_time() ?> | <?php comments_popup_link('Have your say &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
</div>
<div class="clear"></div>
<?php endwhile; ?>

<div class="clear"></div>

	<div class="navigation">
		<div><?php next_posts_link('&laquo; Older Entries') ?></div>
		<div><?php previous_posts_link('Newer Entries &raquo;') ?></div>
	</div>

<?php else : ?>

	<h2>No posts found.</h2>
	<h6>Use the search tap at top right to start a new search.</h6>

<?php endif; ?>

</div>
</div>

<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { get_sidebar(); } ?>

<?php get_footer(); ?>