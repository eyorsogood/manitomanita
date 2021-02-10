<?php
/**
 * Shortcode [accordion] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string $content
 * @var string $style
 * @var string $accordion_id
 * @var string $css_class
 * @var string $view
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

if ( ! $content ) {
	return;
}

if ( $css_class ) {
	$css_class = ' ' . $css_class;
}
if ( 'with-border' === $style ) {
	$css_class .= ' accordion--with-border';
} elseif ( 'theme-default' === $style ) {
	$css_class .= ' accordion--theme-default';
}

printf(
	'<div class="panel-group accordion%s" id="%s">%s</div>',
	$css_class ? esc_attr( $css_class ) : '',
	esc_attr( $accordion_id ),
	do_shortcode( $content )
);
