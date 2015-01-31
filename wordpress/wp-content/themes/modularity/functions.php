<?php

// Path constants
define('THEMELIB', TEMPLATEPATH . '/library');

// Get Theme Options Page
if(is_admin()) :
require_once (THEMELIB . '/functions/theme-options.php');
endif;

// Get Post Thumbnails and Images
include(THEMELIB . '/functions/post-images.php');

// Filter Comments
include(THEMELIB . '/functions/comments-filter.php');

// Load widgets
include(THEMELIB . '/functions/widgets.php');

// Produces an avatar image with the hCard-compliant photo class for author info
include(THEMELIB . '/functions/author-info-avatar.php');

// Remove the WordPress Generator 
function gpp_remove_generators() { return ''; }  
add_filter('the_generator','gpp_remove_generators');

function theme_wp_head() { ?><link href="<?php bloginfo('template_directory'); ?>/library/functions/style.php" rel="stylesheet" type="text/css" /><?php } 
if(get_option('T_css')=="On")
add_action('wp_head', 'theme_wp_head'); 

?>