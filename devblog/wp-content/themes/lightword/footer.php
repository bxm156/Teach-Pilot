<div class="clear"></div>
</div>
<div id="footer">
<span class="text">
<?php
$blog_name = '<a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a>';
printf(__('Copyright %s %s %s &middot; Powered by %s <br/>','lightword'),'&copy;',date('Y'),$blog_name,'<a href="http://www.wordpress.org" title="WordPress" target="_blank">WordPress</a>')
;?>
<?php _e('<a href="http://www.lightwordtheme.org/" target="_blank" title="Lightword Theme">Lightword Theme</a> by Andrei Luca','lightword')
;?>
</em>

<a title="<?php _e('Go to top','lightword'); ?>" class="top" href="#top"><?php _e('Go to top','lightword'); ?> &uarr;</a>
</span>
</div>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/menu.js"></script>

<?php wp_footer(); ?>
</div>
</body>
</html>
