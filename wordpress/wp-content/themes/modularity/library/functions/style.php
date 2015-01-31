<?php
require_once( dirname(__FILE__) . '../../../../../../wp-config.php');
require_once( dirname(__FILE__) . '../../../functions.php');
header("Content-type: text/css"); 
?>
<?php //Load Variables
  $background_color = get_option('T_background_color');
  $page_color = get_option('T_page_color');
  $border_color = get_option('T_border_color');
  $logo_color = get_option('T_logo_color');
  $font_color = get_option('T_font_color');
  $link_color = get_option('T_link_color');
  $hover_color = get_option('T_hover_color');
  $footer_color = get_option('T_footer_color');
?> 
<?php
$css = get_option('T_css');
//echo $css; exit;
if($css == "On") { ?>
/*Base Colors
------------------------------------------------------------ */
body { background: #<?php echo $background_color; ?>; }
.container, .sliderGallery { background: #<?php echo $page_color; ?>; }
div.colborder, div.border { border-right: 1px solid #<?php echo $border_color; ?>; }
.box,.postmetadata,.nav,h3#comments,h3#respond,#commentform,#sidebar ul li a:hover,ul.txt li:hover {background: #<?php echo $border_color; ?>; }
#sidebar ul li a,ul.txt li { border-bottom: 1px solid #<?php echo $border_color; ?>; }
hr {background: #<?php echo $border_color; ?>; color: #<?php echo $border_color; ?>; }
#footer-wrap {background: #<?php echo $footer_color; ?>;}

/*Font Color
------------------------------------------------------------ */
#masthead h4 a {color: #<?php echo $logo_color; ?>}
body,p,h1,h2,h3,h4,h5,h6,h1 a,h2 a,h3 a,h4 a,h5 a,h6 a {color: #<?php echo $font_color; ?>}
h3.sub,h2.sub {border-bottom: 1px solid #<?php echo $border_color; ?>; }
.welcomebox h2, .welcomebox h6, .welcomebox p {color:#<?php echo $footer_color; ?>}

/*Links 
------------------------------------------------------------ */
a:link, a:visited { color: #<?php echo $link_color; ?>; }

/*Hover 
------------------------------------------------------------ */
a:hover, a:focus { color: #<?php echo $hover_color; ?>; }

<?php } ?>