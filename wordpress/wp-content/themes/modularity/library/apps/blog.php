<!-- Begin blog -->
<div id="blog-section">
<div class="span-<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { echo "15 colborder home"; } else { echo "24 last"; } ?>">
<h3 class="sub">Blog</h3>
	<?php if (have_posts()) : ?>
	<?php $i == 0; ?>
		<?php while (have_posts()) : the_post(); $i++; ?>
			<div <?php if(function_exists('post_class')) : ?><?php post_class(); ?><?php else : ?>class="post post-<?php the_ID(); ?>"<?php endif; ?>>
				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry">
					<?php include (THEMELIB . '/apps/multimedia.php'); ?>
					<?php include (THEMELIB . '/apps/video.php'); ?>
					<?php the_content(); ?>
					<?php if ($i == 1) { ?>
					<?php include (THEMELIB . '/apps/ad-main.php'); ?>
					<?php  } ?>
				</div>
				<div class="clear"></div>
				<p class="postmetadata"><?php the_time('M d, Y') ?> | Categories: <?php if (the_category(', '))  the_category(); ?> <?php if (get_the_tags()) the_tags('| Tags: '); ?> | <?php comments_popup_link('Leave A Comment &#187;', '1 Comment &#187;', '% Comments &#187;'); ?> <?php edit_post_link('Edit', '| ', ''); ?> </p>
			</div>
		<div class="clear"></div>
		<?php endwhile; ?>

		<div class="nav">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>
		<div class="clear"></div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
		<?php get_search_form(); ?>

	<?php endif; ?>
	<?php $i == 0; ?>
</div>
</div>
<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { get_sidebar(); } ?>
<hr />