<!-- Begin Five Category Section -->
<div id="category-section">
<?php
$cat_1 = get_option('T_category_section_1');
$cat_2 = get_option('T_category_section_2');
$cat_3 = get_option('T_category_section_3');
$cat_4 = get_option('T_category_section_4');
$cat_5 = get_option('T_category_section_5');
?>
<?php $display_categories = array("$cat_1","$cat_2","$cat_3","$cat_4","$cat_5") ; $i = 0;
	foreach ($display_categories as $category) { $i++; ?>
<?php query_posts("showposts=1&cat=$category"); ?>
<div class="column span-4 post-<?php the_ID(); ?><?php if ($i < 5) { ?> colborder<?php  } ?><?php if ($i == 5 ) { ?> last<?php $i==0; } ?>">
<?php while (have_posts()): the_post();?>
<h3 class="sub"><a href="<?php echo get_category_link($category);?>"><?php single_cat_title(); ?></a></h3>
<?php postimage('thumbnail'); ?>
<h6><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title() ?></a></h6>
<p class="byline"><?php the_time('M d, Y') ?> | <?php comments_popup_link('Discuss', '1 Comment', '% Comments'); ?></p>
<p><?php echo substr(get_the_excerpt(),0,100); ?></p>
<?php endwhile;?>
<h6 class="sub"><a href="<?php echo get_category_link($category);?>">More in <?php single_cat_title(); ?></a></h6>
<ul>
<?php query_posts("showposts=5&offset=1&cat=$category"); ?>
<?php while (have_posts()) : the_post(); ?>
<li><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>" class="title"><?php the_title(); ?></a></li>
<?php endwhile; ?>
</ul>
</div>
<?php } ?>
</div>
<div class="clear"></div>
