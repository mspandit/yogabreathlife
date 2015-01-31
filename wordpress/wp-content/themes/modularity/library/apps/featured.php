<!-- Begin featured -->
<div id="featured-section">
<div class="span-15 colborder home">
<h3 class="sub">Latest </h3>
	<?php 
		$my_query = new WP_Query("showposts=1"); ?>
	<?php while ($my_query->have_posts()) : $my_query->the_post();
		$do_not_duplicate = $post->ID; ?>
			<div <?php if(function_exists('post_class')) : ?><?php post_class(); ?><?php else : ?>class="post post-<?php the_ID(); ?>"<?php endif; ?>>
				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry">
					<?php include (THEMELIB . '/apps/multimedia.php'); ?>
					<?php include (THEMELIB . '/apps/video-medium.php'); ?>
					<?php the_content(); ?>
				</div>
				<div class="clear"></div>
				<p class="postmetadata"><?php the_time('M d, Y') ?> | Categories: <?php if (the_category(', '))  the_category(); ?> <?php if (get_the_tags()) the_tags('| Tags: '); ?> | <?php comments_popup_link('Leave A Comment &#187;', '1 Comment &#187;', '% Comments &#187;'); ?> <?php edit_post_link('Edit', '| ', ''); ?> </p>
		</div>
	<?php endwhile; wp_reset_query(); ?>
	<div class="clear"></div>
	
<?php include (THEMELIB . '/apps/ad-main.php'); ?>

</div>
<div class="span-8 last">
<h3 class="sub">Previously</h3>
<?php $i == 0; ?>
	<?php 
		$my_query = new WP_Query("showposts=3&offset=1"); ?>
	<?php while ($my_query->have_posts()) : $my_query->the_post(); $i++;
		$do_not_duplicate = $post->ID; ?>
			<div <?php if(function_exists('post_class')) : ?><?php post_class(); ?><?php else : ?>class="post post-<?php the_ID(); ?>"<?php endif; ?>>
			<?php postimage('thumbnail'); ?>
			<h6><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title() ?></a></h6>
			<p class="byline"><?php the_time('M d, Y') ?> | <a href="<?php the_permalink(); ?>">Read </a> | <?php comments_popup_link('Discuss', '1 Comment', '% Comments'); ?></p>
			<p><?php echo substr(get_the_excerpt(),0,100); ?></p>
			</div>
			<?php if ($i < 3) { ?>
			<hr />
			<?php  } ?>
	<?php endwhile; ?>
	<?php $i == 0; ?>
	<?php include (THEMELIB . '/apps/ad-sidebar.php'); ?>
	</div>
</div>
<hr />