<!-- Begin Category Stack Section -->
<div class="double-border"></div>
<div id="category-stack">
<div class="span-15 colborder">
<?php
$cat_1 = get_option('T_category_section_1');
$cat_2 = get_option('T_category_section_2');
$cat_3 = get_option('T_category_section_3');
$cat_4 = get_option('T_category_section_4');
$cat_5 = get_option('T_category_section_5');
?>
<?php $display_categories = array("$cat_1","$cat_2","$cat_3","$cat_4","$cat_5") ;
	foreach ($display_categories as $category) { ?>
<?php query_posts("showposts=1&cat=$category"); ?>
<?php while (have_posts()) : the_post(); ?>
<h3 class="sub"><a href="<?php echo get_category_link($category);?>"><?php single_cat_title(); ?></a></h3>
<div class="span-9 append-1 first">
<?php postimage('thumbnail'); ?>
<h6><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title() ?></a></h6><p class="byline"><?php the_time('M d, Y') ?> | <a href="<?php the_permalink(); ?>">Read </a> | <?php comments_popup_link('Discuss', '1 Comment &#187;', '% Comments &#187;'); ?></p>
<p><?php echo substr(get_the_excerpt(),0,190); ?></p>
<?php endwhile; ?>
</div>

<?php query_posts("showposts=5&offset=1&cat=$category"); ?>
<div class="span-5 more last">
<ul>
<?php while (have_posts()) : the_post(); ?>
<li><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>" class="title"><?php the_title(); ?></a></li>
<?php endwhile; ?>
</ul>
</div>
<div class="double-border"></div>
<?php } ?>
</div>
</div>
<?php get_sidebar(); ?>
<hr />