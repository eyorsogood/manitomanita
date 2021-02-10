<?php
/**
 * * Users Class. Classes and functions for Manito Manita.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   Eyorsogood
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Class Users
 */
class Users extends Theme {
	/**
	 * Constructor runs when this class instantiates.
	 *
	 * @param array $config Data via config file.
	 */
	public function __construct( array $config = array() ) {
        $this->initActions();
        $this->initFilters();
    }

    protected function initActions() {
        /**
         * 
         * function should be public when adding to an action hook.
         */        

        add_action('acf/save_post', array($this, 'my_save_post'));
        add_action( 'wp_ajax_verify_password', array($this, 'verify_password') );
        add_action( 'wp_ajax_nopriv_verify_password', array($this, 'verify_password') ); 
        add_action( 'wp_ajax_load_popup_content', array($this, 'load_popup_content') );
        add_action( 'wp_ajax_nopriv_load_popup_content', array($this, 'load_popup_content') ); 
        add_action( 'wp_ajax_user_leave_group', array($this, 'user_leave_group') );
        add_action( 'wp_ajax_nopriv_user_leave_group', array($this, 'user_leave_group') ); 
        add_action( 'wp_ajax_pass_generate', array($this, 'pass_generate') );
        add_action( 'wp_ajax_nopriv_pass_generate', array($this, 'pass_generate') ); 

    }

    protected function initFilters() {
        /**
         * Place filters here
         */
    }

    public function verify_password(){
        $password = $_POST['password'];
        $uid = $_POST['uid'];

        $hashed = get_field('password', $uid);

		if(wp_check_password($password, $hashed, $uid)) {
		    $result = true;
		} else {
		    $result = false;
        }
        
        $posttype = get_post_type($uid);

        $name = ($posttype == "groups")?get_field('group_name', $uid):get_field('screen_name', $uid);
        
        wp_send_json_success(array($result, $name));
    }

    public function load_popup_content(){
        $uid = $_POST['uid'];
        $form = $_POST['form'];

        if($form){
            if(trim($form) == "9999"): //see match
                $this->generateSeeMatchElements($uid);
            elseif(trim($form) == "99999"): //leave group
                echo "<h2>Are you sure you want to leave group?</h2><div class='leave-btns'><a href='#' data-action='false'>No</a><a href='#' data-action='true'>Yes</a></div>";
            else:
                parent::updateAcfForm($uid, $form, 'Update', 'group-dashboard/?gid='.$_SESSION['groupid'].'&pw='.$_SESSION['grouppw']);
            endif;
        }
    }

    public function user_leave_group(){
        $uid = $_POST['uid'];

        $result = wp_delete_post($uid);

        wp_send_json_success($result);
    }

    public function generateSeeMatchElements($uid) {
        $pair = get_field('pair', $uid);

        echo '<div class="header-image"><img src="'.get_template_directory_uri().'/assets/images/gift.png"></div>';
        echo '<div class="inner-contents">';
        echo '    <div class="container">';
        echo '        <div class="row">';
        echo '            <div class="col-md-12">';
        echo '                <h2><i class="far fa-user"></i> '.get_field('screen_name', $pair).'</h2>';
        echo '                <h2><i class="fas fa-gifts"></i> WISH<span>LIST</span></h2>';
        echo '                  <ul class="wishlist-list">';
                                $wishlist = get_field('my_wishlists', $pair);
        echo '                                                ';
                                if(!$wishlist){
                                    echo '<div class="placeholder">No wishlist provided yet.</div>';
                                }
                                if($wishlist):
                                foreach($wishlist as $w):
        echo '                <li>';
        echo '                    <div class="wish-content">'.$w['wishlist_description'].'</div>';
        echo '                    <div class="wish-links">';
                                    if($w['reference_links']):
                                        foreach($w['reference_links'] as $k => $links):
                                            $stringlink = (strpos($links['link_url'], 'lazada.com.ph') !== false)?'https://invol.co/aff_m?offer_id=101165&aff_id=189674&source=deeplink_generator_v2&url='.urlencode($links['link_url']):$links['link_url'];

                                            $stringlink = (strpos($links['link_url'], 'shopee.ph') !== false)?'https://invol.co/aff_m?offer_id=101653&aff_id=189674&source=deeplink_generator_v2&property_id=133170&url='.urlencode($links['link_url']):$stringlink;

                                            echo '<a href="'.$stringlink.'" target="_blank">Link '.($k+1).'</a>';
                                        endforeach;
                                    endif;
        echo '                    </div>';
        echo '                </li>';
                                endforeach; 
                                endif;
        echo '                  </ul>';

        /** VALUE DEALS */
        echo '<h2>GREAT VALUE <span>DEALS</span></h2><div class="row product-list"><div class="col-md-12 col-sm-12 col-12 product-item text-center"><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Manito Manita Match Page -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:300px;height:250px"
             data-ad-client="ca-pub-8648985139343614"
             data-ad-slot="3964563495"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script></div></div>';
        //get_template_part('lazada/assets/offers', 'content');// offers template
        /** VALUE DEALS END */

        echo '            </div>';
        echo '            <div class="col-md-12">';
        echo '                <h2><i class="far fa-comments"></i> COMMENT<span>S</span></h2>';
        echo '                        <ul class="comments-list">';
                                        $comments = get_comments(array('post_id' => $pair));
                                        if(count($comments) == 0){
                                            echo '<div class="placeholder"><i class="fas fa-comment"></i> Somebody has to say something.</div>';
                                        }
                                        foreach($comments as $c):
        echo '                            <li>';
        echo '                                <div class="com-content">'.$c->comment_content.'</div>';
        echo '                                <div class="com-date">'.date('F d, Y h:i:sa', strtotime($c->comment_date)).'</div>';
        echo '                            </li>';
                                        endforeach;
        echo '                    </ul>';
        echo '            </div>';
        echo '        </div>';
        echo '      <h2 class="fb">Thank you for using Manito<span>Manita</span>. Kindly show support by clicking Like and <span>Share</span> below for our facebook page.</h2>';
        echo '      <iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fmanitomanitaph&width=450&layout=standard&action=like&size=large&show_faces=false&share=true&height=35&appId=62030021851" width="264" height="60" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>';
        echo '    </div>';
        echo '</div>';
    }

    public function my_save_post( $post_id ) {	

        if(isset($_POST['_acf_post_id'])) {
            /**
             * get post details
             */
            $post_values = get_post($post_id);


            /**
             * bail out if not a custom type and admin
             */
            $types = array('users');

            if(!(in_array($post_values->post_type, $types))){
                return;
            }

            if($_POST['_acf_post_id'] == "new_post"){
                /**
                 * groups set values
                 */
                if($post_values->post_type == 'users'){
                    /**
                     * update post
                     */

                    $my_post = array(
                        'ID'           => $post_id,
                        'post_title'   => $_POST['acf']['field_5f55f0e476675'].' - '.$_POST['acf']['field_5f55f1bc76676']
                    );

                    //group assign
                    update_field('groups', $_SESSION['groupid'], $post_id);
                    //user password hashed
                    update_field('password', wp_hash_password($_POST['acf']['field_5f55f1e576678']), $post_id);

                    wp_update_post( $my_post );

                    $this->setEmailNewMember($post_id);
                }

                /**
                 *  Clear POST data
                 */
                unset($_POST);

                /**
                 * notifications
                 */
         
            }
            else if($_POST['_acf_post_id'] == $post_id) {

                /**
                 *  Clear POST data
                 */
                unset($_POST);

                /**
                 * notifications
                 */

            }
        }
    }

    public function getAllUsersPerGroup($groupid){
        $meta_query = array(
            'key' => 'groups',
            'value' => $groupid
        );

        $p = parent::createQuery('users', $meta_query);
        $users = $p->posts;

        if(count($users) > 0){
            $users = (array)$users;
            shuffle($users); //randomize users
            return $users;
        }else{
            return false;
        }
    }

    public function randString($length) {
	    $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $char = str_shuffle($char);

	    for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
	        $rand .= $char{mt_rand(0, $l)};
	    }

	    return $rand;
    }

    public function pass_generate(){
        $uid = $_POST['uid'];
        $groupId = get_field('groups', $uid);

        //$newPass = $this->randString(5);

        $posttype = get_post_type($uid);
		$email = ($posttype == "groups")?get_field('your_email', $uid):get_field('email', $uid);

		if($email){

            //$update = update_field('password', wp_hash_password($newPass), $uid);
            
            //if($update){
            $tag = "pass-gen";

            $args = array(
            'post_type'   => 'emails',
            'posts_per_page' => -1
            );
            
            $em = get_posts( $args );
            
            foreach($em as $epost){
                $f = get_fields($epost->ID);

                if($f['email_tag'] == $tag){
                    $e = $f;
                    break;
                }
            }

            $group = new Groups();

            $creds = $group->getGroupCredentials();

            $name = ($posttype == "groups")?get_field('your_name', $uid):get_field('name', $uid);

            $message = $e['email_body'];
            $message = str_replace('[email_name]', ucwords($name), $message);
            $message = str_replace('[email_resetpass]', get_permalink(1389).'?uid='.$uid.'&gpw='.$creds[1], $message);
            
            $to = $email;

            $subject = $e['email_subject'];

            if(parent::sendEmail($to, $subject, $message)){
                wp_send_json_success(true);
            }else{
                wp_send_json_success(false);
            }
            //}
		}
		else
		{
			wp_send_json_success(false);
		}
    }

    public function getWishlistCount($groupid){
        $users = $this->getAllUsersPerGroup($groupid);
        $c = 0;

        if($users):

            foreach($users as $u):
                $w = get_field('my_wishlists', $u->ID);

                if($w) $c++;
            endforeach;

        endif;

        return $c;
    }

    public function addCommentToUser() {
        if(!(isset($_POST['user-data']) and isset($_POST['comment']) and isset($_POST['g-recaptcha-response']))){
            return;
        }

        if(!$this->gCaptchaChecker()){
            return 'You are not human! Must check captcha before sending new comment.';
        }
        
        $uid = $_POST['user-data'];
        $mes = $_POST['comment'];

        $commentdata = array(
            'comment_content' => $mes,
            'comment_post_ID' => $uid
        );

        $insert = wp_insert_comment($commentdata);

        return ($insert)? 'Comment Successfully Added.': 'Adding comment failed.';
    }

    public function gCaptchaChecker(){
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        #
        # Verify captcha
        $post_data = http_build_query(
            array(
                'secret' => '6LfV5iMUAAAAAKugV-K9Ss0VRsnodlpPzzkvGDek',
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        

        if (!$result->success) {
            return false;
        }

        return true;
    }

    public function setEmailNewMember($uid){
        $tag = "new-member";

        $args = array(
        'post_type'   => 'emails',
        'posts_per_page' => -1
        );
        
        $em = get_posts( $args );
        
        foreach($em as $epost){
            $f = get_fields($epost->ID);

            if($f['email_tag'] == $tag){
                $e = $f;
                break;
            }
        }

        $group = new Groups();

        $creds = $group->getGroupCredentials();

        $message = $e['email_body'];
        $message = str_replace('[email_name]', get_field('name', $uid), $message);
        $message = str_replace('[email_group]', get_field('group_name', get_field('groups', $uid)), $message);
        $message = str_replace('[email_grouplink]', get_permalink(23).'?gid='.$creds[0].'&pw='.$creds[1], $message);
        
        $to = get_field('email', $uid);

        $subject = $e['email_subject'];

        parent::sendEmail($to, $subject, $message);
    }
}