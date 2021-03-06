<?php
/**
 * * Groups Class. Classes and functions for Manito Manita.
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
 * Class Groups
 */
class Groups extends Theme {
    public $groupid;

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
        add_action( 'wp_ajax_execute_matching', array($this, 'execute_matching') );
        add_action( 'wp_ajax_nopriv_execute_matching', array($this, 'execute_matching') ); 
    }

    protected function initFilters() {
        /**
         * Place filters here
         */

        add_filter('pre_get_document_title', array($this, 'replace_group_title'), 50);
    }

    
    public function replace_group_title($title){
        if(!isset($_GET['gid'])){
            return false;
        }

        if($this->getGroupId()){
            $title = 'Group Dashboard » Manito Manita » ' . get_field('group_name', $this->getGroupId());
        }else{
            $title = 'Group Dashboard » Manito Manita';
        }

        return $title;
    }

    public function randString($length) {
	    $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $char = str_shuffle($char);

	    for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
	        $rand .= $char{mt_rand(0, $l)};
	    }

	    return $rand;
    }
    
    public function setUserGroupSession($groupid, $grouppw){
        $_SESSION['groupid'] = $groupid;
        $_SESSION['grouppw'] = $grouppw;
    }

    public function getGroupDetails($groupid){
        $this->groupid = $_SESSION['groupid'];

        return get_fields($groupid);
    }

    public function getGroupId(){
        if(isset($_SESSION['groupid']) || isset($_GET['gid'])){
            if(isset($_SESSION['groupid'])){
                return $_SESSION['groupid'];
            }else{
                return $_GET['gid'];
            }
        }else{
            return false;
        }
    }

    public function getGroupPassword($uid) {
        $gpw = "";

        if(get_post_type($uid) == 'users'){
            $field = get_field('groups', $uid);
            $gpw = get_field('group_password', $field);
        }else{
            $gpw = get_field('group_password', $uid);
        }
        
        return $gpw;
    }

    public function checkGroupCredentials(){
        $allow = false;

        if(isset($_GET['gid']) and isset($_GET['pw'])){
            $groupid = $_GET['gid']; 
            $grouppw = $_GET['pw']; 

            if((strlen($groupid) == 0) || (strlen($grouppw) == 0)) return false;

            if(trim(get_field('group_password', $groupid)) == trim($grouppw)){
                $_SESSION['groupid'] = $groupid;
                $_SESSION['grouppw'] = $grouppw;
                $allow = true;
            }

        }else{
            if(isset($_SESSION['groupid']) and isset($_SESSION['grouppw'])){
                if(trim(get_field('group_password', $_SESSION['groupid'])) == trim($_SESSION['grouppw'])){
                    $allow = true;
                }
            }
        }

        return $allow;
    }

    public function getGroupCredentials(){
        $creds = array();

        if($this->checkGroupCredentials()){
            $creds = array($_SESSION['groupid'], $_SESSION['grouppw']);
        }

        return $creds;
    }

    public function update_password(){
        $uid = $_POST['uid'];
        $newPass = $_POST['new-pass'];

        $posttype = get_post_type($uid);
		$email = ($posttype == "groups")?get_field('your_email', $uid):get_field('email', $uid);
        $update = update_field('password', wp_hash_password($newPass), $uid);
           
        if($update){
            $tag = "new-pass";

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

            $gpw = "";
            $gid = "";

            if(get_post_type($uid) == 'users'){
                $field = get_field('groups', $uid);
                $gid = $field;
                $gpw = get_field('group_password', $field);
            }else{
                $gid = $uid;
                $gpw = get_field('group_password', $uid);
            }

            $name = ($posttype == "groups")?get_field('your_name', $uid):get_field('name', $uid);

            $message = $e['email_body'];
            $message = str_replace('[email_name]', ucwords($name), $message);
            $message = str_replace('[email_grouplink]', get_permalink(23).'?gid='.$gid.'&pw='.$gpw, $message);
            
            $to = $email;

            $subject = $e['email_subject'];

            if(parent::sendEmail($to, $subject, $message)){
                return 'Password Changed Successfully!';
            }else{
                return 'Password Change Failed!';
            }
        }else {
            return 'Password Change Failed!';
        }
        
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
            $types = array('groups');

            if(!(in_array($post_values->post_type, $types))){
                return;
            }

            if($_POST['_acf_post_id'] == "new_post"){
                /**
                 * groups set values
                 */
                if($post_values->post_type == 'groups'){
                    /**
                     * update post
                     */

                    $my_post = array(
                        'ID'           => $post_id,
                        'post_title'   => $_POST['acf']['field_5f55be68de4c7'].' - '.$_POST['acf']['field_5f55bf19de4cb']
                    );

                    $gen = $this->randString(6);
                    //group password
                    update_field('group_password', $gen, $post_id);
                    //admin password hashed
                    update_field('password', wp_hash_password($_POST['acf']['field_5f55bf45de4cd']), $post_id);

                    wp_update_post( $my_post );

                    $this->setUserGroupSession($post_id, get_field('group_password', $post_id));
                    $this->setEmailForCreateGroup($post_id);
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
                //update_field('password', wp_hash_password($_POST['acf']['field_5f55bf45de4cd']), $post_id);
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

    public function setEmailForCreateGroup($gid) {
        $tag = "new-group";

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

        $creds = $this->getGroupCredentials();

        $message = $e['email_body'];
        $message = str_replace('[email_name]', get_field('your_name', $gid), $message);
        $message = str_replace('[email_grouplink]', get_permalink(23).'?gid='.$creds[0].'&pw='.$creds[1], $message);
        

        $to = get_field('your_email', $gid);

        $subject = $e['email_subject'];

        parent::sendEmail($to, $subject, $message, true);
    }

    public function setEmailForGroupMatched($gid){
        $tag = "group-matched";

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
        
        $creds = $this->getGroupCredentials();

		$message = $e['email_body'];
		$message = str_replace('[email_group]', get_field('group_name', $gid), $message);
        $message = str_replace('[email_grouplink]', get_permalink(23).'?gid='.$creds[0].'&pw='.$creds[1], $message);

		$to = $this->getAllMembersEmailsPerGroupId($gid);

		$subject = $e['email_subject'];
        $subject = str_replace('[email_group]', get_field('group_name', $gid), $subject);
        
        parent::sendEmail($to, $subject, $message);
    }

    public function getAllMembersEmailsPerGroupId($gid){
        $meta_query = array(
            'key' => 'groups',
            'value' => $gid
        );

        $q = parent::createQuery('users', $meta_query);

        $emails = array();

        foreach($q->posts as $u):
            $emails[] = get_field('email', $u->ID);
        endforeach;

        return $emails;
    }

    public function execute_matching(){

        $gid = $_POST['gid'];

        $matched = get_field('matched', $gid);
        
        $result = true;

		if(!$matched)
		{
            $args = array(
                'key' => 'groups',
                'value' => $gid
            );

            $q = parent::createQuery('users', $args);

            $users = $q->posts;
            
			$dontStop = true;
	
			$match1 = array();
			$match2 = array();
			$emails = array();
	
			foreach($users as $user)
			{
				$match1[] = $user->ID;
				$match2[] = $user->ID;
				$emails[] = get_field('email', $user->ID);
			}		
	
			while($dontStop)
			{
				shuffle($match2);
                $i=0;
                
				foreach($match1 as $k => $u)
				{
					if($u == $match2[$k])
					{
						$i++;
					}
				}
	
				if($i == 0) $dontStop=false;
            }
            
			foreach($match1 as $k => $userId)
			{
                $update = update_field('pair', $match2[$k], $userId);
				
				if(!$update) $result = false;
            }
            
            if($result){
                $this->setEmailForMembers($gid);
            
                update_field('matched', true, $gid);
            }
			
		}else{
            $result = false;
        }

        wp_send_json_success($result);
    }
    
    public function setEmailForMembers($gid){
        $tag = "group-matched";

		$args = array(
		'post_type'   => 'emails'
		);
		
		$em = get_posts( $args );
		
		foreach($em as $epost){
			$f = get_fields($epost->ID);

			if($f['email_tag'] == $tag){
				$e = $f;
				break;
			}
        }
        
        $creds = $this->getGroupCredentials();

		$message = $e['email_body'];
		$message = str_replace('[email_group]', get_field('group_name', $gid), $message);
		$message = str_replace('[email_grouplink]', get_permalink(23).'?gid='.$creds[0].'&pw='.$creds[1], $message);

		$to = $this->getAllMembersEmailsPerGroupId($gid);

		$subject = $e['email_subject'];
		$subject = str_replace('[email_group]', get_field('group_name', $gid), $subject);
        
        parent::sendEmail($to, $subject, $message);
    }
}