<?php
$themename = "Theme";
$shortname = "T";
$options = array (

        		array(	"name" => "Contact Info",
						"type" => "title"),
				
				array(	"type" => "open"),
				
				array(	"name" => "Email Address",
					    "id" => $shortname."_email",
						"desc" => "Your email address.",
					    "std" => "you@email.com",
					    "type" => "text"),
        
        		array(	"name" => "Phone Number",
					    "id" => $shortname."_phone",
						"desc" => "Your phone number.",
					    "std" => "1-800-867-5309",
					    "type" => "text"),
				
				array(	"type" => "close"),
        		
        		array(	"name" => "Layout and Colors",
						"type" => "title"),
						
				array(	"type" => "open"),
            
        		array(	"name" => "Customize layout and colors",
						"desc" => "If enabled the theme will use the layouts and colors you choose below.",
					    "id" => $shortname."_css",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
              
        		array(	"name" => "Background color",
					    "id" => $shortname."_background_color",
						"desc" => "Your background color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "111111",
					    "type" => "text"),
					    
				array(	"name" => "Page color",
					    "id" => $shortname."_page_color",
						"desc" => "Your page color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "ffffff",
					    "type" => "text"),
					    
				array(	"name" => "Border color",
					    "id" => $shortname."_border_color",
						"desc" => "Your border and box color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "cccccc",
					    "type" => "text"),
					    
				array(	"name" => "Footer color",
					    "id" => $shortname."_footer_color",
						"desc" => "Your footer background color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "000000",
					    "type" => "text"),
        
        		array(	"name" => "Site logo color",
					    "id" => $shortname."_logo_color",
						"desc" => "Your text logo color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "ffffff",
					    "type" => "text"),
        		
        		array(	"name" => "Font color",
					    "id" => $shortname."_font_color",
						"desc" => "Your font color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "222222",
					    "type" => "text"),
					    
       			 array(	"name" => "Link color",
					    "id" => $shortname."_link_color",
						"desc" => "Your link color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "428ce7",
					    "type" => "text"),
        
        		array(	"name" => "Link hover color",
					    "id" => $shortname."_hover_color",
						"desc" => "Your link hover color. Use Hex values and leave out the leading #.  <a href='http://www.colorjack.com/sphere/'>Choose a color scheme.</a>",
					    "std" => "666666",
					    "type" => "text"),
					    
				array(	"type" => "close"),
					    			    		
				array(	"name" => "Sidebar Options",
						"type" => "title"),
						
				array(	"type" => "open"),
            
        		array(	"name" => "Sidebar On/Off",
						"desc" => "If you want a sidebar on the right side of your site, enable this option.  By default, the sidebar is off, making the site a one-column 950px wide theme.",
			    		"id" => $shortname."_sidebar",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"type" => "close"),
			    		
				array(	"name" => "Homepage Options",
						"type" => "title"),
            	
            	array(	"type" => "open"),
            	
        		array(	"name" => "Welcome Box On/Off",
						"desc" => "The welcome box appears just below your masthead and just above all content on the front page only.",
			    		"id" => $shortname."_welcomebox",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
              
        		array(	"name" => "Welcome Title",
					    "id" => $shortname."_welcomebox_title",
              			"desc" => "The title of your welcome message.",
					    "std" => "Howdy folks!",
					    "type" => "text"),
        
        		array(	"name" => "Welcome Message",
						"id" => $shortname."_welcomebox_content",
						"desc" => "Some HTML in the message is okay, including <code>&#60;b&#62;</code>, and <code>&#60;i&#62;</code> tags.",
						"std" => "Hi, this is a quick message to introduce people to your site.  It can be short or long.  You can control virtually every aspect of the homepage design on the Modularity theme options page.",
						"type" => "textarea",
						"options" => array("rows" => "8",
										   "cols" => "70") ),
            
        		array(	"name" => "Slideshow On/Off",
						"desc" => "Enable the Slideshow section below if you want to display a slideshow of images on the homepage.",
			    		"id" => $shortname."_slideshow",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"name" => "Slideshow Static/Dynamic",
						"desc" => "Modularity can look for slideshow images in either a static folder, or it can scan your posts and pull the latest image uploaded using the 'Add Media' button in Wordpress.  By default, the slideshow cycles through 5 images for both static and dynamic slideshows.  If you are using a dynamic gallery, tou can change this number by editing the showposts=5 variable in the /modularity/library/apps/slideshow folder.  If you are using a static gallery, you can simply replace the images located in the /modularity/images/slideshow folder with your own (950 pixels wide max, keep filenames the same).  If you are using a child theme, you will need to create the folder yourself and add images to it (950px wide).",
			    		"id" => $shortname."_slideshow_status",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Static", "Dynamic")),
			    		
			    array(	"name" => "Slideshow Height" ,
						"desc" => "The height of the slideshow in pixels.  Numbers only please!",
						"id" => $shortname."_slideshow_height",
						"std" => "425",
						"type" => "text"),
						
				array(	"name" => "Slideshow Category" ,
						"desc" => "This section only pertains to those using a dynamic slideshow.  Enter the <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>category id</a> of a category in which you post your photos.  If you have enabled slideshow posting and you leave this field blank, all categories will be cycled through the slideshow.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_slideshow_cat",
						"std" => "",
						"type" => "text"),
						
				array(	"name" => "Slider On/Off",
						"desc" => "",
			    		"id" => $shortname."_slider",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"name" => "Slider Category" ,
						"desc" => "This section only pertains to those who have enabled the slider above.  Enter the <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>category id</a> of your slider category.  If you have enabled the slider and you leave this field blank, all categories will be queried.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_slider_cat",
						"std" => "",
						"type" => "text"),
			    		
				array(	"name" => "Featured section On/Off",
						"desc" => "",
			    		"id" => $shortname."_featured",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"name" => "Blog section On/Off",
						"desc" => "",
			    		"id" => $shortname."_blog",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"type" => "close"),
			    		
				array(	"name" => "Category Section Options",
						"type" => "title"),
            	
            	array(	"type" => "open"),
			    		
			    array(	"name" => "Category section On/Off",
						"desc" => "",
			    		"id" => $shortname."_category_section",
			    		"std" => "",
			    		"type" => "select",
			    		"options" => array("Off", "On")),
			    		
			    array(	"name" => "Category section: First category" ,
						"desc" => "The category id of the category that you want to have appear in the category section at the bottom of the homepage.  The category listing moves from left to right.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_category_section_1",
						"std" => "",
						"type" => "text"),

				array(	"name" => "Category section: Second category" ,
						"desc" => "The category id of the category that you want to have appear in the category section at the bottom of the homepage.  The category listing moves from left to right.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_category_section_2",
						"std" => "",
						"type" => "text"),
										   
				array(	"name" => "Category section: Third category" ,
						"desc" => "The category id of the category that you want to have appear in the category section at the bottom of the homepage.  The category listing moves from left to right.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_category_section_3",
						"std" => "",
						"type" => "text"),
						
				array(	"name" => "Category section: Fourth category" ,
						"desc" => "The category id of the category that you want to have appear in the category section at the bottom of the homepage.  The category listing moves from left to right.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_category_section_4",
						"std" => "",
						"type" => "text"),
						
				array(	"name" => "Category section: Fifth category" ,
						"desc" => "The category id of the category that you want to have appear in the category section at the bottom of the homepage.  The category listing moves from left to right.  <a href='http://graphpaperpress.com/wp-content/uploads/2008/12/category-id.jpg'>Help me find my category id's.</a>",
						"id" => $shortname."_category_section_5",
						"std" => "",
						"type" => "text"),
			    		
			    array(	"type" => "close"),
			    
			    array(	"name" => "Advertising Code",
						"type" => "title"),
						
				array(	"type" => "open"),
			    
			    array(	"name" => "Sidebar Advertising Code",
						"id" => $shortname."_sidebar_ad_code",
						"desc" => "Insert your advertising code here or just leave it blank.  If you need advertising code, <a href='http://www.graphpaperpress.com/affiliates/'>try here</a>.  Either way is fine.  The sidebar measures 310 pixels wide.",
						"std" => "",
						"type" => "textarea",
						"options" => array("rows" => "5",
										   "cols" => "40") ),
										   
				array(	"name" => "Main Body Advertising Code",
						"id" => $shortname."_main_ad_code",
						"desc" => "Insert your advertising code here or just leave it blank.  If you need advertising code, <a href='http://www.graphpaperpress.com/affiliates/'>try here</a>.  Either way is fine.  The main body measures 590 pixels wide with the sidebar enabled.  The entire theme measures 950 pixels wide.  This ad will appear beneath the first post on the homepage and at the end of each single post.",
						"std" => "",
						"type" => "textarea",
						"options" => array("rows" => "5",
										   "cols" => "40") ),
										   
				array(	"type" => "close"),
				
        		array(	"name" => "Tracking Code",
						"type" => "title"),
						
				array(	"type" => "open"),
				
				array(	"name" => "Tracking code",
						"id" => $shortname."_tracking_code",
						"desc" => "If you use Google Analytics or need any other tracking script in your footer just copy and paste it here.<br /> The script will be inserted before the closing <code>&#60;/body&#62;</code> tag.",
						"std" => "",
						"type" => "textarea",
						"options" => array("rows" => "5",
										   "cols" => "40") ),
										   
				array(	"type" => "close")
		  );

function mytheme_add_admin() {

    global $themename, $shortname, $options;

    if ( $_GET['page'] == basename(__FILE__) ) {
    
        if ( 'save' == $_REQUEST['action'] ) {

                foreach ($options as $value) {
                    update_option( $value['id'], $_REQUEST[ $value['id'] ] ); }

                foreach ($options as $value) {
                    if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); } else { delete_option( $value['id'] ); } }

                header("Location: themes.php?page=theme-options.php&saved=true");
                die;

        } else if( 'reset' == $_REQUEST['action'] ) {

            foreach ($options as $value) {
                delete_option( $value['id'] ); }

            header("Location: themes.php?page=theme-options.php&reset=true");
            die;

        }
    }

    add_theme_page($themename." Options", "$themename Options", 'edit_themes', basename(__FILE__), 'mytheme_admin');

}

//add_theme_page($themename . 'Header Options', 'Header Options', 'edit_themes', basename(__FILE__), 'headimage_admin');

function headimage_admin(){
	
}

function mytheme_admin() {

    global $themename, $shortname, $options;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
    
?>
<div class="wrap">
<h2><?php echo $themename; ?> settings</h2>

<p><?php _e('For more information about this theme, <a href="http://graphpaperpress.com">visit GraphPaperPress.com</a>. Please visit the <a href="http://graphpaperpress.com/support">GraphPaperPress Forums</a> if you have any questions about this theme.', 'gpp'); ?></p>

<form method="post">

<div id="poststuff" class="dlm">

<?php foreach ($options as $value) { 
    
	switch ( $value['type'] ) {
	
		case "open":
		?>
		
        
		<?php break;
		
		case "close":
		?>
        </table></div></div>
        
        
		<?php break;
		
		case "title":
		?>
		<div class="postbox close">
		<h3><?php echo $value['name']; ?></h3>
			<div class="inside">
        
		<table width="100%" border="0" style="background-color:#ccc; padding:5px 10px;"><tr>
        </tr>
                
        
		<?php break;

		case 'text':
		?>
        
        <tr>
            <td width="20%" rowspan="2" valign="middle"><strong><?php echo $value['name']; ?></strong></td>
            <td width="80%"><input style="width:400px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes( get_settings( $value['id'] ) ); } else { echo $value['std']; } ?>" /></td>
        </tr>

        <tr>
            <td><small><?php echo $value['desc']; ?></small></td>
        </tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>

		<?php 
		break;
		
		case 'textarea':
		?>
        
        <tr>
            <td width="20%" rowspan="2" valign="middle"><strong><?php echo $value['name']; ?></strong></td>
            <td width="80%"><textarea name="<?php echo $value['id']; ?>" style="width:400px; height:200px;" type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( get_settings( $value['id'] ) != "") { echo stripslashes( get_settings(  $value['id'] ) ); } else { echo $value['std']; } ?></textarea></td>
            
        </tr>

        <tr>
            <td><small><?php echo $value['desc']; ?></small></td>
        </tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>

		<?php 
		break;
		
		case 'select':
		?>
        <tr>
            <td width="20%" rowspan="2" valign="middle"><strong><?php echo $value['name']; ?></strong></td>
            <td width="80%"><select style="width:240px;" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"><?php foreach ($value['options'] as $option) { ?><option<?php if ( get_settings( $value['id'] ) == $option) { echo ' selected="selected"'; } elseif ($option == $value['std']) { echo ' selected="selected"'; } ?>><?php echo $option; ?></option><?php } ?></select></td>
       </tr>
                
       <tr>
            <td><small><?php echo $value['desc']; ?></small></td>
       </tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>

		<?php
        break;
            
		case "checkbox":
		?>
            <tr>
            <td width="20%" rowspan="2" valign="middle"><strong><?php echo $value['name']; ?></strong></td>
                <td width="80%"><? if(get_settings($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = ""; } ?>
                        <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />
                        </td>
            </tr>
                        
            <tr>
                <td><small><?php echo $value['desc']; ?></small></td>
           </tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>
            
        <?php 		break;
	
 
} 
}
?>

</div>

<p class="submit">
<input name="save" type="submit" value="Save changes" />    
<input type="hidden" name="action" value="save" />
</p>
</form>
<form method="post">
<p class="submit">
<input name="reset" type="submit" value="Reset" />
<input type="hidden" name="action" value="reset" />
</p>
</form>

			<script type="text/javascript">
			<!--
			jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
			jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
			jQuery('.postbox.close').each(function(){ jQuery(this).addClass("closed"); });
			//-->
			</script>

<?php }  add_action('admin_menu', 'mytheme_add_admin'); ?>