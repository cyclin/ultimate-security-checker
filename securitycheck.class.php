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
@ini_set( 'max_execution_time', 240 );
class SecurityCheck {
    private $_wp_version = '';
    public $results_from = '';
    public $config_file = '';
    public $test_results = False;
    public $earned_points = 0;
    public $total_possible_points = 0;

    public $changed_core_files = array();
    public $wp_files = array();
    public $wp_files_checks_result = array();
    public $wp_db_check_results = array();
    
    public $wp_content_dir = '';
    public $wp_plugins_dir = '';

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
        ),
        array(
            'id' => 24,
            'title' => 'Some of blog core files have been changed. <a href="?page=ultimate-security-checker&tab=core-files">View Report</a>',
            'points' => 5,
            'category' => 'code',
            'callback' => 'run_test_24'
        ),
        array(
            'id' => 25,
            'title' => 'You have some suspicious code in your posts and/or comments. <a href="?page=ultimate-security-checker&tab=wp-posts">View Report</a>',
            'points' => 5,
            'category' => 'db',
            'callback' => 'run_test_25'
        ),
        array(
            'id' => 26,
            'title' => 'Core files check cancelled. Please wait till update of this plugin.',
            'points' => 1,
            'category' => 'code',
            'callback' => 'run_test_26'
        ),
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
        $this->get_defined_filesystem_constants();
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
    
    function get_file_diff( $file ) {
    	global $wp_version;
    	// core file names have a limited character set
    	$file = preg_replace( '#[^a-zA-Z0-9/_.-]#', '', $file );
    	if ( empty( $file ) || ! is_file( ABSPATH . $file ) )
    		return '<p>Sorry, an error occured. This file might not exist!</p>';
    
    	$key = $wp_version . '-' . $file;
    	$cache = get_option( 'source_files_cache' );
    	if ( ! $cache || ! is_array($cache) || ! isset($cache[$key]) ) {
    		$url = "http://core.svn.wordpress.org/tags/$wp_version/$file";
    		$response = wp_remote_get( $url );
    		if ( is_wp_error( $response ) || 200 != $response['response']['code'] )
    			return '<p>Sorry, an error occured. Please try again later.</p>';
    
    		$clean = $response['body'];
    
    		if ( is_array($cache) ) {
    			if ( count($cache) > 4 ) array_shift( $cache );
    			$cache[$key] = $clean;
    		} else {
    			$cache = array( $key => $clean );
    		}
    		update_option( 'source_files_cache', $cache );
    	} else {
    		$clean = $cache[$key];
    	}
    
    	$modified = file_get_contents( ABSPATH . $file );
    
    	$text_diff = new Text_Diff( explode( "\n", $clean ), explode( "\n", $modified ) );
    	$renderer = new USC_Text_Diff_Renderer();
    	$diff = $renderer->render( $text_diff );
        
    	$r  = "<div class=\"danger-found\">\n";
    	$r .= "\n$diff\n\n";
    	$r .= "</div>";
    	return $r;
    }
   	public function recurse_directory( $dir ) {
		if ( $handle = @opendir( $dir ) ) {
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					$file = $dir . '/' . $file;
					if ( is_dir( $file ) ) {
						$this->recurse_directory( $file );
					} elseif ( is_file( $file ) ) {
						$this->wp_files[] = str_replace( ABSPATH.'/', '', $file );
					}
				}
			}
			closedir( $handle );
		}
	}
    function replace( $matches ) {
		return '$#$#' . $matches[0] . '#$#$';
	}
    function highlight_matches( $text ) {
    	$start = strpos( $text, '$#$#' ) - 50;
    	if ( $start < 0 ) $start = 0;
    	$end = strrpos( $text, '#$#$' ) + 50;
    
    	$text = substr( $text, $start, $end - $start + 1 );
    
    	return str_replace( array('$#$#','#$#$'), array('<span style="background:#ff0">','</span>'), $text );
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
        The most recent test was taken on <b><?php echo date('d M, Y', get_option( 'wp_ultimate_security_checker_lastcheck')); ?></b>. <br>Your blog earned <b><?php echo $this->earned_points?> of <?php echo $this->total_possible_points?></b> security points. <br /><?php echo $result_messages[$letter]; ?> <br />
        We have a service which can automate the fix of some of these. <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin_results_link">Click Here to try it.</a></p>
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
    
    public function get_defined_filesystem_constants(){
        $wp_content_dir = '';
        $wp_plugins_dir = '';
        if(defined(WP_CONTENT_DIR)){
            if(is_dir(WP_CONTENT_DIR))
                $this->wp_content_dir = WP_CONTENT_DIR;
            else
                $this->wp_content_dir = ABSPATH . 'wp-content';
        }else
            $this->wp_content_dir = ABSPATH . 'wp-content';
        if (is_multisite()) {
            if (defined(WPMU_PLUGIN_DIR)){
                if(is_dir(WPMU_PLUGIN_DIR))
                    $this->wp_plugins_dir = WPMU_PLUGIN_DIR;
                else
                    $this->wp_plugins_dir = $this->wp_content_dir . '/mu-plugins';
            }else
                $this->wp_plugins_dir = $this->wp_content_dir . '/mu-plugins';
        }else{
            if(defined(WP_PLUGIN_DIR)){
                if(is_dir(WP_PLUGIN_DIR))
                    $this->wp_plugins_dir = WP_PLUGIN_DIR;
                else
                    $this->wp_plugins_dir = $this->wp_content_dir . '/plugins';
            }else
                $this->wp_plugins_dir = $this->wp_content_dir . '/plugins';   
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
        update_option( 'wp_ultimate_security_checker_wp_files', $this->wp_files);
        update_option( 'wp_ultimate_security_checker_hashes_issues', $this->changed_core_files);
        update_option( 'wp_ultimate_security_checker_posts_issues', $this->wp_db_check_results);
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
		$file = $this->wp_content_dir . '/';
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
		$file = $this->wp_content_dir . '/themes/';
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
		$file = $this->wp_plugins_dir . '/';
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
    public function run_test_24(){
            
            global $wp_version;
            global $wp_local_package;
            unset( $filehashes );
			$hashes = dirname(__FILE__) . '/hashes/hashes-'. $wp_version .'.php';
            $localisations = dirname(__FILE__) . '/hashes/hashes-'. $wp_version .'_international.php';
			if ( file_exists( $hashes ) ){
				include( $hashes );
                include( $localisations );
			}else{
                return True;
			}
            
            if (isset($$wp_local_package)) {
                $filehashes = array_merge($filehashes, $$wp_local_package);
            }

            if ((!isset($$wp_local_package)) && (isset($wp_local_package))) {
                unset(
                $filehashes['license.txt'],
                $filehashes['wp-config-sample.php'],
                $filehashes['readme.html'],
                $filehashes['wp-includes/version.php'],
                $filehashes['wp-includes/ms-settings.php'],
                $filehashes['wp-includes/functions.php'],
                $filehashes['wp-includes/ms-load.php'],
                $filehashes['wp-includes/wp-db.php'],
                $filehashes['wp-includes/default-constants.php'],
                $filehashes['wp-includes/load.php'],
                $filehashes['wp-load.php'],
                $filehashes['wp-admin/setup-config.php']
                );
            }
        
			$this->recurse_directory( ABSPATH );

			foreach( $this->wp_files as $k => $file ) {

				// don't scan unmodified core files
				if ( isset( $filehashes[$file] ) ) {
				   
					if ( $filehashes[$file] == md5_file( ABSPATH.$file ) ) {
						unset( $this->wp_files[$k] );
						continue;
					} else {
				        $diffs[$file][] = $this->get_file_diff($file);
					}
				}
                //for avoiding false alerts in 25 test
                if ($file == "wp-content/plugins/ultimate-security-checker/securitycheck.class.php" || $file == "wp-content/plugins/ultimate-security-checker/wp-ultimate-security.php") {
                    unset( $this->wp_files[$k] );
                }

				// don't scan files larger than 400 KB
				if ( filesize(ABSPATH . $file) > (400 * 1024) ) {
					unset( $this->wp_files[$k] );
				}
				
				// detect old export files
				if ( substr( $file, -9 ) == '.xml_.txt' ) {
			         $old_export[] = $file;
				}
			}

            if (!isset($diffs) && !isset($old_export)) {
            		return True;
           	} else {
            	    $this->changed_core_files = array(
                    'diffs' => $diffs,
                    'old_export' => $old_export
                    );
            		return False;
            }
    //end function    
    }
    
    public function run_heuristic_check() {
        global $wp_version;
        
        $patterns = array(
		'/(\$wpdb->|mysql_).+DROP/siU' => 'Possible database table deletion',
		'/(echo|print|<\?=).+(\$GLOBALS|\$_SERVER|\$_GET|\$_REQUEST|\$_POST)/siU' => 'Possible output of restricted variables',
		'/ShellBOT/i' => 'This may be a script used by hackers to get control of your server',
		'/uname -a/i' => 'Tells a hacker what operating system your server is running',
		'/YW55cmVzdWx0cy5uZXQ=/i' => 'base64 encoded text found in Search Engine Redirect hack <a href="http://blogbuildingu.com/wordpress/wordpress-search-engine-redirect-hack">[1]</a>' ,
		'/eval\s*\(/i' => 'Often used to execute malicious code',
		'/\$_COOKIE\[\'yahg\'\]/i' => 'YAHG Googlerank.info exploit code <a href="http://creativebriefing.com/wordpress-hacked-googlerankinfo/">[1]</a>',
		'/ekibastos/i' => 'Possible Ekibastos attack <a href="http://ocaoimh.ie/did-your-wordpress-site-get-hacked/">[1]</a>',
		'/base64_decode\s*\(/i' => 'Used by malicious scripts to decode previously obscured data/programs',
		'/<script>\/\*(GNU GPL|LGPL)\*\/ try\{window.onload.+catch\(e\) \{\}<\/script>/siU' => 'Possible "Gumblar" JavaScript attack <a href="http://threatinfo.trendmicro.com/vinfo/articles/securityarticles.asp?xmlfile=042710-GUMBLAR.xml">[1]</a> <a href="http://justcoded.com/article/gumblar-family-virus-removal-tool/">[2]</a>',
		'/php \$[a-zA-Z]*=\'as\';/i' => 'Symptom of the "Pharma Hack" <a href="http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html">[1]</a>',
		'/defined?\(\'wp_class_support/i' => 'Symptom of the "Pharma Hack" <a href="http://blog.sucuri.net/2010/07/understanding-and-cleaning-the-pharma-hack-on-wordpress.html">[1]</a>' ,
		'/str_rot13/i' => 'Decodes/encodes text using ROT13. Could be used to hide malicious code.',
		'/uudecode/i' => 'Decodes text using uuencoding. Could be used to hide malicious code.',
		//'/[^_]unescape/i' => 'JavaScript function to decode encoded text. Could be used to hide malicious code.',
		'/<!--[A-Za-z0-9]+--><\?php/i' => 'Symptom of a link injection attack <a href="http://www.kyle-brady.com/2009/11/07/wordpress-mediatemple-and-an-injection-attack/">[1]</a>',
		'/<iframe/i' => 'iframes are sometimes used to load unwanted adverts and code on your site',
        '/TimThumb script created by Ben Gillbanks/i' => 'Signature of timthumb hack',
        '/Uploadify v/i' => 'Signature of Uploadify hack',
        '/\$allowedSites\s*=\s*array\s*\(/i' => 'Signature of Uploadify hack',
		'/String\.fromCharCode/i' => 'JavaScript sometimes used to hide suspicious code',
		'/preg_replace\s*\(\s*(["\'])(.).*(?<!\\\\)(?>\\\\\\\\)*\\2([a-z]|\\\x[0-9]{2})*(e|\\\x65)([a-z]|\\\x[0-9]{2})*\\1/si' => 'The e modifier in preg_replace can be used to execute malicious code' ,
        //'/(<a)(\\s+)(href(\\s*)=(\\s*)\"(\\s*)((http|https|ftp):\\/\\/)?)([[:alnum:]\-\.])+(\\.)([[:alnum:]]){2,4}([[:blank:][:alnum:]\/\+\=\%\&\_\\\.\~\?\-]*)(\"(\\s*)[[:blank:][:alnum:][:punct:]]*(\\s*)>)[[:blank:][:alnum:][:punct:]]*(<\\/a>)/is' => 'Hardcoded hyperlinks in code is not a real threat, but they may lead to phishing websites.',
        );
        $this->wp_files = get_transient('wp_ultimate_security_checker_wp_files');
        $this->wp_files_checks_result = get_transient('wp_ultimate_security_checker_files_issues');
        if ((sizeof($this->wp_files) <= 0) || (!is_array($this->wp_files))) {
            unset( $filehashes );
            
            $hashes = dirname(__FILE__) . '/hashes/hashes-'. $wp_version .'.php';
            if ( file_exists( $hashes ) )
				include( $hashes );
			else{
                return array('status'=>'error', 'data'=>'Hashes file not found!');
			}
			$this->recurse_directory( ABSPATH );
			foreach( $this->wp_files as $k => $file ) {
				if ( isset( $filehashes[$file] ) ) {
				   unset( $this->wp_files[$k] );
				   continue;
				}
                if ($file == "wp-content/plugins/ultimate-security-checker/securitycheck.class.php" || $file == "wp-content/plugins/ultimate-security-checker/wp-ultimate-security.php") {
                    unset( $this->wp_files[$k] );
                    continue;
                }
				if ( filesize(ABSPATH . $file) > (400 * 1024) ) {
					unset( $this->wp_files[$k] );
				}
			}
            $total = count($this->wp_files);
            $options = array(
            'total'=>$total,
            );
            set_transient('wp_ultimate_security_checker_utility', $options, 3600);

        }
        for ($i=1;$i<=100;$i++) {
            if ($file = array_shift($this->wp_files)) {
				$contents = file( ABSPATH . $file );
				foreach ( $contents as $n => $line ) {
					foreach ( $patterns as $pattern => $description ) {
						$test = preg_replace_callback( $pattern, array( &$this, 'replace' ), $line );
						if ( $line !== $test )
                        $this->wp_files_checks_result[$file][] = "<div class=\"danger-found\"><strong>Line " . ($n+1) . ":</strong><pre>".$this->highlight_matches(esc_html($test))."</pre><span class=\"danger-description\">".$description."</span></div>";


					}
				}
            }else
                break;
		}
        
        $utility = get_transient('wp_ultimate_security_checker_utility');
        $scanned_count = intval($utility['total']) - count($this->wp_files);
        $data = "Scanned $scanned_count from {$utility['total']} files...";
		if (count($this->wp_files) > 0 ) {
            set_transient( 'wp_ultimate_security_checker_wp_files', $this->wp_files, 3600 );
            set_transient( 'wp_ultimate_security_checker_files_issues', $this->wp_files_checks_result, 3600 );
            return array('status'=>'processing', 'data'=>$data);
		} else {
			if (sizeof($this->wp_files_checks_result)>0){
                update_option( 'wp_ultimate_security_checker_files_issues', $this->wp_files_checks_result);
            }
            delete_transient('wp_ultimate_security_checker_utility');
            delete_transient('wp_ultimate_security_checker_wp_files');
            delete_transient('wp_ultimate_security_checker_files_issues');
            return array('status'=>'finished', 'data'=>$this->wp_files);
		}
    //end function    
	}

	function run_test_25() {
		global $wpdb;

	   $suspicious_post_text = array(
            'eval\(' => 'Often used by hackers to execute malicious code',
    		'<iframe' => 'iframes are sometimes used to load unwanted adverts and code on your site',
    		'<noscript' => 'Could be used to hide spam in posts/comments',
    		'display:' => 'Could be used to hide spam in posts/comments',
    		'visibility:' => 'Could be used to hide spam in posts/comments',
    		'<script' => 'Malicious scripts loaded in posts by hackers perform redirects, inject spam, etc.',
    	);

		foreach ( $suspicious_post_text as $text => $description ) {
			$posts = $wpdb->get_results( "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type<>'revision' AND post_content LIKE '%{$text}%'" );
			if ( $posts )
				foreach ( $posts as $post ) {
                    
                    $s = strpos( $post->post_content, $text ) - 25;
            		if ( $s < 0 ) $s = 0;
            
            		$content = preg_replace( '/('.$text.')/', '$#$#\1#$#$', $post->post_content );
            		$content = substr( $content, $s, 150 );
                    $posts_found[$post->ID]['post-title'] = esc_html($post->post_title);
                    $posts_found[$post->ID]['content'][] = "<pre>".$this->highlight_matches(esc_html($content))."</pre>".$description;

				}

			$comments = $wpdb->get_results( "SELECT comment_ID, comment_author, comment_content FROM {$wpdb->comments} WHERE comment_content LIKE '%{$text}%'" );
			if ( $comments )
				foreach ( $comments as $comment ) {
                    
                    $s = strpos( $comment->comment_content, $text ) - 25;
            		if ( $s < 0 ) $s = 0;
            
            		$content = preg_replace( '/('.$text.')/', '$#$#\1#$#$', $comment->comment_content );
            		$content = substr( $content, $s, 150 );
                    $comments_found[$comment->comment_ID]['comment-autor'] = esc_html($comment->comment_author);
                    $comments_found[$comment->comment_ID]['content'][] = "<pre>".$this->highlight_matches(esc_html($content))."</pre>".$description;

				}
		}
        if (!isset($posts_found) && !isset($comments_found)) {
            return True;
        }
        else{
            $this->wp_db_check_results = array(
                'posts_found' => $posts_found,
                'comments_found' => $comments_found,
            );
            return False;
        }
    //end function
	}
    
    public function run_test_26() {
            global $wp_version;
			if ( file_exists( dirname(__FILE__) . '/hashes/hashes-'. $wp_version .'.php' ) ){
				return True;
			}else{
                return False;
			}
    }
    
//end class
}
include_once( ABSPATH . WPINC . '/wp-diff.php' );

if ( class_exists( 'Text_Diff_Renderer' ) ) :
class USC_Text_Diff_Renderer extends Text_Diff_Renderer {
	function USC_Text_Diff_Renderer() {
		parent::Text_Diff_Renderer();
	}

	function _startBlock( $header ) {
		return "<span class=\"textdiff-line\">Lines: $header</span>\n";
	}

	function _lines( $lines, $prefix, $class ) {
		$r = '';
		foreach ( $lines as $line ) {
			$line = esc_html( $line );
			$r .= "<div class='{$class}'>{$prefix} {$line}</div>\n";
		}
		return $r;
	}

	function _added( $lines ) {
		return $this->_lines( $lines, '+', 'diff-addedline' );
	}

	function _deleted( $lines ) {
		return $this->_lines( $lines, '-', 'diff-deletedline' );
	}

	function _context( $lines ) {
		return $this->_lines( $lines, '', 'diff-context' );
	}

	function _changed( $orig, $final ) {
		return $this->_deleted( $orig ) . $this->_added( $final );
	}
}
endif;
?>