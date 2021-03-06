<?php
/**
 * Header template part.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

get_template_part( 'templates/header/header', 'clean' );

$is_sticky_header = qed_get_option( 'sticky_header', 'option' );

if ( $is_sticky_header ) {
	//SD_Js_Client_Script::add_script( 'sticky-header', 'Theme.initStickyHeader();' );
	echo '<div class="header-wrap">';
}

$group = new Groups();
$users = new Users();
$u = $users->getAllUsersPerGroup($group->getGroupId());
$allow = ($u)?true:false;

if($allow){
	$print = (count($u) > 2)?"class='user-action shuffle-btn' group-data='".$group->getGroupId()."' data-action='shuffle-group'":'disabled';
}else{
	$print = 'disabled';
}

?>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<script type="text/javascript">
	var onloadCallback = function() {
		grecaptcha.render('comment-grecaptcha', {
			'sitekey' : '6LfV5iMUAAAAAKt1NU6cqlBLjjSVE3gsEYvyM2Ny'
		});
	};
</script>
<header class="header" role="banner">
	<div class="top_layer">
		<div class="header__content-wrap">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<?php get_template_part( 'templates/header/logo' ); ?>
					</div><!-- .header__content -->
					<div class="clearfix"></div>
				</div>
			</div><!-- .container -->
		</div><!-- .header__content-wrap -->
	</div>
	<div class="clearfix"></div>
	<div class="bottom_layer dasboard-nav">
		<div class="container">
			<div class="header__content-wrap">
				<div class="row">
					<div class="col-md-12 header__content">
						<nav class="main-nav-header" role="navigation">
							<ul id="navigation-dashboard" class="main-nav">
								<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="javascript:;" <?php echo (get_field('matched', $group->getGroupId()))?'disabled':'data-fancybox="" data-src="#join-group"'; ?>>Join Group</a></li>
								<?php if(!get_field('matched', $group->getGroupId())): ?>
									<li class="menu-item menu-item-type-custom menu-item-object-custom shuff-btn"><a href="javascript:;" <?php echo $print; ?>><?php echo (wp_is_mobile())?'Shuffle':'Shuffle Match'; ?></a></li>
								<?php else: ?>
									<li class="menu-item menu-item-type-custom menu-item-object-custom join-btn"><a href="javascript:;" id="who-joined" data-fancybox data-src="#who-joined-box"><?php echo (wp_is_mobile())?'Joined':'Who Joined'; ?></a></li>
								<?php endif; ?>
								<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="<?php echo get_permalink(77); ?>">Gift Ideas</a></li>
							</ul>							
						</nav>
						<div class="clearfix"></div>
					</div><!-- .header__content -->
				</div>
			</div><!-- .header__content-wrap -->
		</div><!-- .container -->
	</div>
	<div class="clearfix"></div>
</header>
<?php if ( $is_sticky_header ) { echo '</div>'; }
SD_Js_Client_Script::add_script( 'initResizeHandler', 'Theme.initResizeHandler();' );
//SD_Js_Client_Script::add_script( 'initResizeHandler', 'Theme.initResizeHandler(' . wp_json_encode( $js_config ) . ');' );
get_template_part( 'templates/header/header', 'section' );
do_action('eyor_before_main_content');
