<?php get_header(); ?>

<!-- Show the welcome box, slideshow, slider and magazine front only on first page.  Makes for better pagination. -->
<?php if ( $paged < 1 ) { ?>

<?php
$welcomebox = get_option('T_welcomebox');
if($welcomebox == "On") { include (THEMELIB . '/apps/welcomebox.php'); }
?>


				<div class="entry">
										
<div id="flashcontent-welcome">
		<strong>You need to <a href="http://www.adobe.com/go/getflashplayer">upgrade your Flash Player to version 9 or newer</a>.</strong>
		</div>

<script type="text/javascript">
			
			var so = new SWFObject("http://www.yogabreathlife.com/wp-content/themes/modularity/library/apps/flash/flvPlayer.swf?imagePath=http://www.yogabreathlife.com/wp-content/uploads/2009/04/yblthumb590X332.jpg&videoPath=http://www.yogabreathlife.com/wp-content/uploads/2009/04/milindwelfinal_f8fs_360k.flv&autoStart=false&autoHide=false&autoHideTime=5&hideLogo=true&volAudio=60&newWidth=590&newHeight=332&disableMiddleButton=false&playSounds=false&soundBarColor=0x0066FF&barColor=0x0066FF&barShadowColor=0x91BBFB&subbarColor=0xffffff", "Greeting Video", "590", "332", "9", "#efefef");
			so.addParam("allowFullScreen", "true");
			so.write("flashcontent-welcome");
			
		</script>

<div class="clear"></div>
					<p>Hello and welcome to our site.</p>
									</div>



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
if($category_section == "On") { include (HIGH_DEF_PATH . '/apps/category-stack.php'); }
?>

<!-- Begin Footer -->
<?php get_footer(); ?>