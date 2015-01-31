<!-- Begin slider -->
<?php
$slider_cat = get_option('T_slider_cat');
?>
<script type="text/javascript" charset="utf-8">
        $(window).ready(function () {
  $('div.sliderGallery').each(function () {
    var ul = $('ul', this);
    var productWidth = ul.innerWidth() - $(this).outerWidth();

    var slider = $('.slider', this).slider({ 
      handle: '.handle',
      minValue: 0, 
      maxValue: productWidth, 
      slide: function (ev, ui) {
        ul.css('left', '-' + ui.value + 'px');
      }, 
      stop: function (ev, ui) {
        ul.animate({ 'left' : '-' + ui.value + 'px' }, 500, 'linear');
      }
    });
  });
});
    </script>

<div id="slider-section">
<div class="sliderGallery">
<?php 
		$my_query = new WP_Query("showposts=10&cat=$slider_cat"); ?>
<ul class="items">
	<?php while ($my_query->have_posts()) : $my_query->the_post();
		$do_not_duplicate = $post->ID; ?>

	<li class="post-<?php the_ID(); ?> slider-item"><?php postimage('thumbnail'); ?><span class="slider-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title() ?></a></span></li>
      	<?php endwhile; wp_reset_query(); ?>
</ul>
  <div class="slider">
    <!-- the handler to action the slide -->
    <div class="handle"></div>
    <?php 
		$my_query = new WP_Query("showposts=10&cat=$slider_cat"); ?>
    <?php $i == 0; ?>
    <?php while ($my_query->have_posts()) : $my_query->the_post();
		$do_not_duplicate = $post->ID; ?>
	<?php $i++; ?>
    <span class="slider-<?php echo ($i); ?>"><?php echo ($i); ?></span>
    <?php endwhile; wp_reset_query(); ?>
  </div>
</div>
</div>
<hr />