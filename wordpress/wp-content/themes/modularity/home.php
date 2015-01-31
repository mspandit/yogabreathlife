<?php get_header(); ?>

<!-- Show the welcome box, slideshow, slider and magazine front only on first page.  Makes for better pagination. -->
<?php if ( $paged < 1 ) { ?>

<?php
$welcomebox = get_option('T_welcomebox');
if($welcomebox == "On") { include (THEMELIB . '/apps/welcomebox.php'); }
?>

<?php
$slideshow = get_option('T_slideshow');
$slideshow_status = get_option('T_slideshow_status');
if($slideshow == "On" && $slideshow_status == "Dynamic") { include (THEMELIB . '/apps/slideshow.php'); }
if($slideshow == "On" && $slideshow_status == "Static") { include (THEMELIB . '/apps/slideshow-static.php'); } ?>

<?php
$slider = get_option('T_slider');
if($slider == "On") { include (THEMELIB . '/apps/slider.php'); }
?>

<?php
$featured = get_option('T_featured');
if($featured == "On") { include (THEMELIB . '/apps/featured.php'); }
?>

<!-- End Better Pagination -->
<?php } ?>

<?php
$blog = get_option('T_blog');
if($blog == "On") { include (THEMELIB . '/apps/blog.php'); }
?>

<?php
$category_section = get_option('T_category_section');
if($category_section == "On") { include (THEMELIB . '/apps/five.php'); }
?>

<!-- Begin Footer -->
<?php get_footer(); ?>