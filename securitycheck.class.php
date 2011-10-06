<?php
/*
 Security check class for checking blog for available security holes

 License: GPL2

 Copyright 2010  Eugene Pyvovarov  (email : bsn.dev@gmail.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as 
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class SecurityCheck {
    private $_wp_version = '';
    public $results_from = '';
    public $config_file = '';
    public $test_results = False;
    public $earned_points = 0;
    public $total_possible_points = 0;
    public $all_issues = array(
        array(
            'id' => 1,
            'title' => 'Some installed plugins have updates.',
            'points' => 5,
            'category' => 'updates',
            'callback' => 'run_test_1'
        ),
        array(
            'id' => 2,
            'title' => 'Some installed themes have updates.',
            'points' => 5,
            'category' => 'updates',
            'callback' => 'run_test_2'
        ),
        array(
            'id' => 3,
            'title' => 'Your WordPress version is outdated.',
            'points' => 10,
            'category' => 'updates',
            'callback' => 'run_test_3'
        ),
        array(
            'id' => 4,
            'title' => 'Config file is located in an unsecured place.',
            'points' => 3,
            'category' => 'config',
            'callback' => 'run_test_4'
        ),
        array(
            'id' => 5,
            'title' => 'Some of keys AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY are not set.',
            'points' => 5,
            'category' => 'config',
            'callback' => 'run_test_5'
        ),
        array(
            'id' => 6,
            'title' => 'It\'s better to turn off the file editor for plugins and themes in WordPress admin.',
            'points' => 2,
            'category' => 'config',
            'callback' => 'run_test_6'
        ),
        array(
            'id' => 7,
            'title' => 'WP_DEBUG option should be turned off on LIVE website.',
            'points' => 3,
            'category' => 'config',
            'callback' => 'run_test_7'
        ),
        array(
            'id' => 8,
            'title' => 'Users can see the version of WordPress you are running.',
            'points' => 3,
            'category' => 'code',
            'callback' => 'run_test_8'
        ),
        array(
            'id' => 9,
            'title' => 'Users can see the version of WordPress you are running from the readme.html file.',
            'points' => 3,
            'category' => 'code',
            'callback' => 'run_test_9'
        ),
        array(
            'id' => 10,
            'title' => 'Installation script is still available in your WordPress files.',
            'points' => 3,
            'category' => 'code',
            'callback' => 'run_test_10'
        ),
        array(
            'id' => 11,
            'title' => 'WordPress displays unnecessary error messages on failed log-ins.',
            'points' => 3,
            'category' => 'code',
            'callback' => 'run_test_11'
        ),
        array(
            'id' => 12,
            'title' => 'Your blog can be hacked with malicious URL requests.',
            'points' => 6,
            'category' => 'code',
            'callback' => 'run_test_12'
        ),
        array(
            'id' => 13,
            'title' => 'Your wp-config.php is readable\writeable by others!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_13'
        ),
        array(
            'id' => 14,
            'title' => 'Your .htaccess is unsecured!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_14'
        ),
        array(
            'id' => 15,
            'title' => 'Insufficient rights on wp-content folder!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_15'
        ),
        array(
            'id' => 16,
            'title' => 'Insufficient rights on wp-content/themes folder!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_16'
        ),
        array(
            'id' => 17,
            'title' => 'Insufficient rights on wp-content/plugins folder!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_17'
        ),
        array(
            'id' => 18,
            'title' => 'Insufficient rights on core wordpress folders!',
            'points' => 5,
            'category' => 'files',
            'callback' => 'run_test_18'
        ),
        array(
            'id' => 19,
            'title' => 'Default admin login is not safe.',
            'points' => 5,
            'category' => 'db',
            'callback' => 'run_test_19'
        ),
        array(
            'id' => 20,
            'title' => 'Default database prefix is not safe.',
            'points' => 3,
            'category' => 'db',
            'callback' => 'run_test_20'
        ),
        array(
            'id' => 21,
            'title' => 'Your uploads directory is browsable from the web.',
            'points' => 5,
            'category' => 'server',
            'callback' => 'run_test_21'
        ),
        array(
            'id' => 22,
            'title' => 'Your server shows the PHP version in response.',
            'points' => 5,
            'category' => 'server',
            'callback' => 'run_test_22'
        ),
        array(
            'id' => 23,
            'title' => 'Your server shows too much information about installed software.',
            'points' => 5,
            'category' => 'server',
            'callback' => 'run_test_23'
        )
    );
    
    public $categories = array(
        'updates' => 'Check for updates',
        'config' => 'Check configuration file',
        'code' => 'Code check',
        'files' => 'Files & folders permission check',
        'db' => 'Database check',
        'server' => 'Server configuration check'
    );
    
    public function __construct(){
        global $wp_version;
        $version = explode('-', $wp_version);
        $version = explode('.', $version[0]);
        $ver = $version[0].'.';
        array_shift($version);
        $ver = $ver . implode($version);
        $this->_wp_version = floatval($ver);
    }
    
    private function gen_random_string($len) {
        $length = $len;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }
    
	public function get_permissions($file){
		clearstatcache();
		if(@fileperms($file) != false){
			if(is_dir($file)){
				return substr(sprintf('%o', fileperms($file)),2,3);
			} else {
				return substr(sprintf('%o', fileperms($file)),3,3);
			}
        } else {
			return False;
        }
	}
	public function get_chmod($string_chmod){
        $string_chmod = str_replace('r','4',$string_chmod);
        $string_chmod = str_replace('w','2',$string_chmod);
        $string_chmod = str_replace('x','1',$string_chmod);
        $string_chmod = str_replace('-','0',$string_chmod);
        return ((int)$string_chmod[0]+(int)$string_chmod[1]+(int)$string_chmod[2])*100+((int)$string_chmod[3]+(int)$string_chmod[4]+(int)$string_chmod[5])*10+((int)$string_chmod[6]+(int)$string_chmod[7]+(int)$string_chmod[8]);
	}
    
    public function get_stats(){
    }
    public function display_stats_by_categories($categories){
        if($this->test_results === False){
            echo '<p>No test results yet</p>';
            return False;
        }
        foreach($categories as $cat_title=>$cat_description){
            $total_points = 0;
            $earned_points = 0;
            $comments = '';
            foreach($this->all_issues as $one){
                if($one['category'] == $cat_title){
                    $total_points = $total_points + $one['points'];
                    if(!in_array($one['id'], $this->test_results)){
                        $earned_points = $earned_points + $one['points'];
                    } else {
                        $comments .= $one['title'] . '<br />';
                    }
                }
            }
            $this->display_stats($cat_description, $total_points, $earned_points, $comments);
        }
    }
    public function display_stats($testname, $total_points, $earned_points, $comments){
        $coef = $earned_points / $total_points;
        $letter = '';
        $res = $this->get_grade_color($coef);
        $letter = $res['letter'];
        $color = $res['color'];
        ?>
        <div style="border-left:3px solid <?php echo $color?>; padding: 3px 0 3px 10px;margin:5px;">
            <strong style="padding-right:20px;"><?php echo $letter?></strong>
            <strong><?php echo $testname?></strong><br />
            <span style="margin-left:34px;color:#aaa;display:block;"><?php echo $comments?></span>
        </div>
        <?php
        return $letter;
        flush();
    }
    
    public function display_global_stats() {
        
        $coef = $this->earned_points / $this->total_possible_points;
        $res = $this->get_grade_color($coef);
        $letter = $res['letter'];
        $color = $res['color'];
        ?>
        <style>
        .full-circle {
         background-color: <?php echo $color?>;
         height: 19px;
         -moz-border-radius:30px;
         -webkit-border-radius: 30px;
         width: 25px;
         float:left;
         text-align:center;
         padding:15px 10px 12px 10px;
         color:#fff;
         font-size:23px;
         font-family:Georgia,Helvetica;
         margin-top:12px;
        }
        </style>
        <!-- <h2>Security Check Report</h2> -->
        <div style="padding:20px 10px 10px 20px;margin:15px 0 15px 3px; border:0px solid #ccc; width:700px;background:#ededed;">
        <div class='full-circle'>
         <?php echo $letter?>
        </div>
        <?php
            $result_messages = array(
                'A' => 'You\'re doing very well. Your blog is currently secure.',
                'B' => 'Some security issues. These issues are not critical, but leave you vulnerable. ',
                'C' => 'A few security issues. Fix them immediately to prevent attacks. ',
                'D' => 'Some medium sized security holes have been found in your blog. ',
                'F' => 'Fix your security issues immediately! '
            );
        ?>
        <p style="margin:0 10px 10px 70px;">
        <a style="float:right;margin-top:20px;font-weight:bold;" href="?page=ultimate-security-checker&tab=run-the-tests&dotest" class="button-primary">Run the tests again!</a>
        The most recent test was taken on <b><?php echo date('d M, Y', get_option( 'wp_ultimate_security_checker_lastcheck')); ?></b>. <br>Your blog earns <b><?php echo $this->earned_points?> of <?php echo $this->total_possible_points?></b> security points. <br /><?php echo $result_messages[$letter]; ?> <br />
        If you need a help in fixing these issues <a href="http://www.ultimateblogsecurity.com/?campaignid=plugin">contact us</a>.</p>
        </div>
        <?php
    }
    public function get_grade_color($coef){
        if($coef > 1 or $coef < 0){
            return False;
        }
        if($coef <=1 && $coef > 0.83){
            $letter = 'A';
            $color = '#34a234';
        }
        if($coef <=0.83 && $coef > 0.67){
            $letter = 'B';
            $color = '#a4cb58';
        }
        if($coef <=0.67 && $coef > 0.5){
            $letter = 'C';
            $color = '#fadd3d';
        }
        if($coef <=0.5 && $coef > 0.30){
            $letter = 'D';
            $color = '#f5a249';
        }
        if($coef <=0.30 && $coef >= 0){
            $letter = 'F';
            $color = '#df4444';
        }
        return array('color'=>$color, 'letter'=>$letter);
    }
    
    public function get_cached_test_results(){
        $this->results_from = 'cache';
        $this->test_results = explode(',', get_option( 'wp_ultimate_security_checker_issues'));
        $this->total_possible_points = 0;
        $this->earned_points = 0;
        foreach($this->all_issues as $one){
            $this->total_possible_points += $one['points'];
            if(!in_array($one['id'], $this->test_results)){
                $this->earned_points += $one['points'];
            }
        }
    }
    
    public function run_tests(){
        $this->results_from = 'test';
        $test_results = array();
        $this->total_possible_points = 0;
        $this->earned_points = 0;
        foreach($this->all_issues as $one){
            $this->total_possible_points += $one['points'];
            if($this->$one['callback']() === False){
                $test_results[] = $one['id'];
            } else {
                $this->earned_points += $one['points'];
            }
        }
        $this->test_results = $test_results;
        #update options
        $res = $this->get_grade_color($this->earned_points / $this->total_possible_points);
        update_option( 'wp_ultimate_security_checker_score', $this->earned_points . '/' .$this->total_possible_points);
        update_option( 'wp_ultimate_security_checker_color', $res['color']);
        update_option( 'wp_ultimate_security_checker_issues', implode(',', $test_results));
        update_option( 'wp_ultimate_security_checker_lastcheck', time());
    }
    
    public function run_test_1(){
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_plugins' );   //Get the current update info
        } else {
            $current = get_transient( 'update_plugins' );    //Get the current update info
        }
        if ( ! is_object($current) ) {
            $current = new stdClass;
        }
            
        $current->last_checked = 0;                      //wp_update_plugins() checks this value when determining  
        if($this->_wp_version>2.92){
            set_site_transient('update_plugins', $current);  //whether to actually check for updates, so we reset it to zero.
        } else {
            set_transient('update_plugins', $current);   //whether to actually check for updates, so we reset it to zero.
        }
        wp_update_plugins();                         //Run the internal plugin update check
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_plugins' );
        } else {
            $current = get_transient( 'update_plugins' );
        }
        $plugin_update_cnt = ( isset( $current->response ) && is_array( $current->response ) ) ? count($current->response) : 0;
        $total_points += 5;
        if($plugin_update_cnt > 0){
            return False;
        } 
        return True;
    }
    
    public function run_test_2(){
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_themes' );
        } else {
             $current = get_transient( 'update_themes' );
        }
        if ( ! is_object($current) ){
            $current = new stdClass;
        }
        $current->last_checked = 0;
        if($this->_wp_version>2.92){
            set_site_transient( 'update_themes', $current );
        } else {
            set_transient( 'update_themes', $current );
        }
        wp_update_themes();
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_themes' );
        } else {
            $current = get_transient( 'update_themes' );
        }
        
        $theme_update_cnt = ( isset( $current->response ) && is_array( $current->response ) ) ? count($current->response) : 0;
        if($theme_update_cnt > 0){
            return False;
        }
        return True;
    }
    
    public function run_test_3(){
        if($this->_wp_version>2.92){
            $current = get_site_transient( 'update_core' );
        } else {
            $current = get_transient( 'update_core' );
        }
        $current->last_checked = 0;
        if($this->_wp_version>2.92){
            set_site_transient( 'update_core', $current );
        } else {
            set_transient( 'update_core', $current );
        }
        wp_version_check();
        
        $latest_core_update = get_preferred_from_update_core();
        $total_points += 10;
        if ( isset( $latest_core_update->response ) && ( $latest_core_update->response == 'upgrade' ) ){
         return False;
        } 
        return True;
    }
    
    public function run_test_4(){
        //check config file path
        if ( file_exists( ABSPATH . 'wp-config.php') ) {
            /** The config file resides in ABSPATH */
            return False;

        } elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {
            /** The config file resides one level above ABSPATH but is not part of another install*/
            return True;
        }
    }
    
    public function run_test_5(){
        //checking secret keys values
        $keys_absent = array();

        if($this->_wp_version>2.6){
            //if version > 2.6

            if(AUTH_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'AUTH_KEY';
            }
            if(SECURE_AUTH_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'SECURE_AUTH_KEY';
            }
            if(LOGGED_IN_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'LOGGED_IN_KEY';
            }
        }
        if($this->_wp_version>2.7){
            //if version > 2.7
            if(NONCE_KEY == 'put your unique phrase here'){
                $keys_absent[] = 'NONCE_KEY';
            }

        }
        if($keys_absent == array()){
            return True;
        } 
        return False;
    }
    
    public function run_test_6(){
        if(defined('DISALLOW_FILE_EDIT') && (DISALLOW_FILE_EDIT == True)){
            return True;
        } 
        return False;
    }
    
    public function run_test_7(){
        if(defined('WP_DEBUG') && WP_DEBUG == True){
            return False;
        } 
        return True;
    }
    
    public function run_test_8(){
        // check if wordpress has info about it's version in header
        $current_theme_root = get_template_directory();
        $file = @file_get_contents($current_theme_root.'/header.php');
        if($file !== FALSE){
            if(strpos($file,  "bloginfo(’version’)") === false){
                return True;
            } else {
                return False;
            }
        }
    }
    public function run_test_9(){
        if(file_exists( ABSPATH . '/readme.html' )){
            return False;
        } 
        return True;
    }
    public function run_test_10(){
        if(file_exists( ABSPATH . 'wp-admin/install.php' )){
            return False;
        } 
        return True;
    }
    
    public function run_test_11(){
        //check for unnecessary messages on failed logins
        $params = array(
            'log' => '123123123123123',
            'pwd' => '123123123123123'
        );
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = (array)$http->request(get_bloginfo( 'wpurl' ).'/wp-login.php',array( 'method' => 'POST', 'body' => $params));
        if( strpos($response['body'],'Invalid username.') !== false){
            return False;
        } 
        return True;
    }
    public function run_test_12(){
        //check for long urls with eval,base64,etc
        $test_urls = array(
            'eval' => $this->gen_random_string(50).'eval('.$this->gen_random_string(50),
            'base64' => $this->gen_random_string(50).'base64('.$this->gen_random_string(50)
        );
        $malicious_comment = '';
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        foreach($test_urls as $key=>$val){
            $response = (array)$http->request(get_bloginfo( 'wpurl' ).'?'.$val);
            if($response['response']['code'] == 200){
                return False;
            }
        }
        return True;
    }
    public function run_test_13(){
        //check config file path
        if ( file_exists( ABSPATH . '/wp-config.php') ) {
            /** The config file resides in ABSPATH */
            $config_file = ABSPATH . '/wp-config.php';

        } elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {
            /** The config file resides one level above ABSPATH but is not part of another install*/
            $config_file = dirname(ABSPATH) . '/wp-config.php';
        }
		$perms = $this->get_permissions($config_file);
		if($perms !== False){
			if($perms < 645){
				return True;
			}
			return False;
		} 
		return False;
    }
    public function run_test_14(){
        //check .htaccess 
		$file = ABSPATH . '/.htaccess';
		if ( file_exists( $file ) ) {
			$perms = $this->get_permissions($file);
			if($perms < 645){
				return True;
			} 
			return False;
		} 
		return True;
		
    }
    public function run_test_15(){
        //check wp-content
		$file = ABSPATH . '/wp-content/';
		if ( file_exists( $file ) ) {
			$perms = $this->get_permissions($file);
			if(in_array($perms, array(755, 775, 777))){
				return True;
			} 
			return False;
		} 
		return False;
    }
    public function run_test_16(){
        //check themes
		$file = ABSPATH . '/wp-content/themes/';
		if ( file_exists( $file ) ) {
			$perms = $this->get_permissions($file);
			if(in_array($perms, array(755, 775))){
				return True;
			} 
			return False;
		} 
		return False;
    }
    public function run_test_17(){
        //check plugins
		$file = ABSPATH . '/wp-content/plugins/';
		if ( file_exists( $file ) ) {
			$perms = $this->get_permissions($file);
			if(in_array($perms, array(755, 775))){
				return True;
			} 
			return False;
		} 
		return False;
    }
    public function run_test_18(){
        //check core folders
		$file1 = ABSPATH . '/wp-admin/';
		$file2 = ABSPATH . '/wp-includes/';
		if ( file_exists( $file1 ) && file_exists( $file2 ) ) {
			$perms1 = $this->get_permissions($file1);
			$perms2 = $this->get_permissions($file2);
			if(in_array($perms1, array(755, 775)) && in_array($perms2, array(755, 775))){
				return True;
			} 
			return False;
		} 
		return False;
    }
    public function run_test_19(){
        $wpdb =& $GLOBALS['wpdb'];

        #find admin users with 'admin' login
        $admin_username = false;
        $users = $wpdb->get_results("
            SELECT 
                users.user_login,
                users.ID,
                (SELECT umeta_id FROM $wpdb->usermeta as meta WHERE meta.`meta_key` = '{$wpdb->prefix}capabilities' AND meta.`meta_value` like 'a:1:{s:13:\"administrator\";%' AND meta.`user_id` = users.ID) as capabilities,
                (SELECT umeta_id FROM $wpdb->usermeta as meta WHERE meta.`meta_key` = '{$wpdb->prefix}user_level' AND meta.`meta_value` = '10' AND meta.`user_id` = users.ID) as userlevel
            FROM 
                $wpdb->users as users");
        foreach($users as $one){
            if($one->userlevel != NULL && $one->capabilities != NULL){
                if($one->user_login == 'admin'){
                    $admin_username = true;
                    break;
                }
            }
        }
        if($admin_username == true){
            return False;
        } 
        return True;
    }
    public function run_test_20(){
        $wpdb =& $GLOBALS['wpdb'];
        #check prefix
        if($wpdb->prefix != 'wp_'){
            return True;
        } 
        return False;
    }
    public function run_test_21(){
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = (array)$http->request(get_bloginfo( 'wpurl' ).'/wp-content/uploads/');
        if(!$response['body'] || strpos('Index of',$response['body']) == false){
            return True;
        } 
        return False;
    }
    public function run_test_22(){
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = (array)$http->request(get_bloginfo( 'wpurl' ));
        if(isset($response['headers']['x-powered-by']) && count(split('/',$response['headers']['x-powered-by'])) > 1){
            return False;
        } 
        return True;
    }
    public function run_test_23(){
        if ( ! class_exists('WP_Http') )
            require( ABSPATH . WPINC . '/class-http.php' );
        $http = new WP_Http();
        $response = (array)$http->request(get_bloginfo( 'wpurl' ));
        if(isset($response['headers']['server']) && preg_match("/apache|nginx/i",$response['headers']['server']) !== 0 && preg_match("/^(apache|nginx)$/i",$response['headers']['server']) === 0){
            return False;
        } 
        return True;
    }
}
?>