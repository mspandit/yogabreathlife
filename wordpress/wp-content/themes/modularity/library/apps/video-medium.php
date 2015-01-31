<?php
	$values = get_post_custom_values("video");
	if (isset($values[0])) {
	?>
	
<div id="flashcontent-<?php the_ID(); ?>">

			<strong>You need to <a href="http://www.adobe.com/go/getflashplayer">upgrade your Flash Player to version 9 or newer</a>.</strong>
		</div>

<script type="text/javascript">
			
			var so = new SWFObject("<?php bloginfo('template_directory'); ?>/library/apps/flash/flvPlayer.swf?imagePath=<?php $values = get_post_custom_values("video-thumb"); echo $values[0]; ?>&videoPath=<?php $values = get_post_custom_values("video"); echo $values[0]; ?>&autoStart=false&autoHide=false&autoHideTime=5&hideLogo=true&volAudio=60&newWidth=590&newHeight=332&disableMiddleButton=false&playSounds=true&soundBarColor=0x0066FF&barColor=0x0066FF&barShadowColor=0x91BBFB&subbarColor=0xffffff", "sotester", "590", "332", "9", "#efefef");
			so.addParam("allowFullScreen", "true");
			so.write("flashcontent-<?php the_ID(); ?>");
			
		</script>
		
<div class="clear"></div>
<?php } ?>