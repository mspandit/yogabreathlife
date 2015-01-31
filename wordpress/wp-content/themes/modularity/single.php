<?php get_header(); ?>
<div class="span-<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { echo "15 colborder home"; } else { echo "24 last"; } ?>">
<div <?php if(function_exists('post_class')) : ?><?php post_class(); ?><?php else : ?>class="post post-<?php the_ID(); ?>"<?php endif; ?>>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<h2><?php the_title(); ?></h2>

<?php include (THEMELIB . '/apps/multimedia.php'); ?>
<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { include (THEMELIB . '/apps/video-medium.php'); } else { include (THEMELIB . '/apps/video.php'); } ?>
<?php the_content(); ?>
</div>
<div class="clear"></div>

<p class="postmetadata alt">
					<small>
						This entry was posted
						<?php { ?>
						on <?php the_time('l, F jS, Y') ?> at <?php the_time() ?>
						and is filed under <?php the_category(', ') ?><?php if (get_the_tags()) the_tags(' and tagged with '); ?>.
						You can follow any responses to this entry through the <?php post_comments_feed_link('RSS 2.0'); ?> feed.

						<?php } edit_post_link('Edit this entry','','.'); ?>

					</small>
				</p>


<div class="nav prev left"><?php next_post_link('%link', '&larr;', TRUE); ?></div>
<div class="nav next right"><?php previous_post_link('%link', '&rarr;', TRUE); ?></div>
<div class="clear"></div>
			<?php endwhile; else : ?>

				<h2 class="center">Not Found</h2>
				<p class="center">Sorry, but you are looking for something that isn't here.</p>
				<?php get_search_form(); ?>

			<?php endif; ?>
<?php comments_template('', true); ?>
<?php include (THEMELIB . '/apps/ad-main.php'); ?>
</div>

<?php $sidebar = get_option('T_sidebar'); if($sidebar == "On") { get_sidebar(); } ?>

<!-- Begin Footer -->
<?php get_footer(); ?>