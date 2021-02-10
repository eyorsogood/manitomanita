<?php
/**
 * Header clean template part.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
	if ( ! qed_check( 'is_wordpress_seo_in_use' ) ) {
		printf( '<meta name="description" content="%s">', get_bloginfo( 'description', 'display' ) );
	}
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php acf_form_head(); ?>
	<?php wp_head(); ?>
	<script data-ad-client="ca-pub-8648985139343614" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<body <?php body_class(); ?>>
<div class="loader-overlay" style="background:#1b1b1b;display:none;"><img src="<?php echo 
get_template_directory_uri().'/assets/images/loader.gif'; ?>"></div>
<div class="layout-content">