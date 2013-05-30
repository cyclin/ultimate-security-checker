<?php
/*
Plugin Name: Ultimate Security Checker
Plugin URI: http://www.ultimateblogsecurity.com/
Description: Security plugin which performs all set of security checks on your WordPress installation.<br>Please go to <a href="tools.php?page=wp-ultimate-security.php">Tools->Ultimate Security Checker</a> to check your website.
Version: 2.7.10
Author: Eugene Pyvovarov
Author URI: http://www.ultimateblogsecurity.com/
License: GPL2

Copyright 2013  Eugene Pyvovarov  (email : bsn.dev@gmail.com)

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
    global $wp_version;
    require_once("securitycheck.class.php");
    
    function wp_ultimate_security_checker_deactivate() {
        delete_option( 'wp_ultimate_security_checker_color');
        delete_option( 'wp_ultimate_security_checker_score');
        delete_option( 'wp_ultimate_security_checker_issues');
        delete_option( 'wp_ultimate_security_checker_lastcheck');
    }
    
    register_deactivation_hook( __FILE__, 'wp_ultimate_security_checker_deactivate' );
    function wp_ultimate_security_checker_activate() {
        add_option( 'wp_ultimate_security_checker_color', 0 , null , 'yes' );
        add_option( 'wp_ultimate_security_checker_score', 0 , null , 'yes' );
        add_option( 'wp_ultimate_security_checker_issues', '' , null, 'yes' );
        add_option( 'wp_ultimate_security_checker_lastcheck', '' , null , 'yes' );
		add_option( 'wp_ultimate_security_checker_hide_header', 0 , null , 'yes' );
    }

    register_activation_hook( __FILE__, 'wp_ultimate_security_checker_activate' );
    function wp_ultimate_security_checker_admin_init()
    {
         // wp_enqueue_script('jquery');
          $lang_dir = basename(dirname(__FILE__))."/languages";
          load_plugin_textdomain( 'ultimate-security', false, $lang_dir );
         
    }
    add_action( 'network_admin_menu', 'wp_ultimate_security_checker_setup_admin' );
    function wp_ultimate_security_checker_setup_admin() {
      add_submenu_page(
        $parent_slug = 'settings.php',
        $page_title =  __('Ultimate Security Checker', 'wp_ultimate_security_checker'),
        $menu_title =  __('Ultimate Security Checker', 'wp_ultimate_security_checker'),
        $capability = 'manage_network_options',
        $menu_slug = 'ultimate-security-checker',
        $function = 'wp_ultimate_security_checker_main'
      );
    }

    function wp_ultimate_security_checker_admin_menu()
    {
        if (function_exists('is_multisite') && !is_multisite()) {
        $page = add_submenu_page( 'tools.php', 
                                  __('Ultimate Security Checker', 'wp_ultimate_security_checker'), 
                                  __('Ultimate Security Checker', 'wp_ultimate_security_checker'), 'manage_options',  'ultimate-security-checker', 
                                  'wp_ultimate_security_checker_main');
        add_action('admin_print_scripts-' . $page, 'wp_ultimate_security_checker_admin_styles');
        }
    }

    function wp_ultimate_security_checker_admin_styles()
    {
        /*
         * It will be called only on your plugin admin page, enqueue our script here
         */
        // wp_enqueue_script('myPluginScript');
    }
    function wp_ultimate_security_checker_main(){
        $tabs  = array('run-the-tests', 'how-to-fix', 'core-files', 'wp-files',
					   'wp-posts', 'settings', 'pro', 'current-status', 'register');
        $tab = '';
        if(!isset($_GET['tab']) || !in_array($_GET['tab'],$tabs)){
            $tab = 'run-the-tests';
        } else {
            $tab = $_GET['tab'];
        }
        $function_name = 'wp_ultimate_security_checker_' . str_replace('-','_',$tab);
        $function_name();
    }    
    
    function wp_ultimate_security_checker_how_to_fix(){
        ?>
        <div class="wrap">
            <style>
            #icon-security-check {
                background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
            }
            </style>

                <?php screen_icon( 'security-check' );?>
            <h2 style="padding-left:5px;">Ultimate Security Checker
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <?php endif; ?>
            <style>
                h3.nav-tab-wrapper .nav-tab {
                    padding-top:7px;
                }
            </style>
            <h3 class="nav-tab-wrapper">
                    <a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab"><?php _e('Run the Tests');?></a>
                    <a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab"><?php _e('Files Analysis');?></a>
                    <a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab nav-tab-active"><?php _e('How to Fix');?></a>
                    <a href="?page=ultimate-security-checker&tab=settings" class="nav-tab"><?php _e('Settings');?></a>
                    <!--<a href="?page=ultimate-security-checker&tab=pro" class="nav-tab"><?php _e('PRO Checks');?></a>-->
            </h3>
<!--			<p style="border:2px solid #eee;margin-left:3px;background:#f5f5f5;padding:10px;width:706px;font-size:14px;color:green;font-family:helvetica;">
				Please check out our new idea: <strong>WP AppStore</strong>. 1-click install best plugins and themes.
				<a style="color:#e05b3c;text-decoration:underline;" href="http://wordpress.org/extend/plugins/wp-appstore/" target="_blank">Check it out!</a>
			</p>-->
            <style>
            pre {
                padding:10px;
                background:#f3f3f3;
                margin-top:10px;
            }
            .answers p, .answers ul, .answers pre {
                margin-left:10px;
                line-height:19px;
            }
            .answers ul{
                list-style-type:disc !important;
                padding-left:17px !important;
            }
            </style>
                <a name="#top"></a>
                <ul>
                    <li><a href="#upgrades"><?php _e('WordPress/Themes/Plugins Upgrades.');?></a></li>
                    <li><a href="#unneeded-files"><?php _e('Removing unneeded files.');?></a></li>
                    <li><a href="#config-place"><?php _e('Config file is located in an unsecured place.');?></a></li>
                    <li><a href="#config-keys"><?php _e('Editing global variables or keys in config file.');?></a></li>
                    <li><a href="#code-edits-login"><?php _e('Removing unnecessary error messages on failed log-ins.');?></a></li>
                    <li><a href="#code-edits-version"><?php _e('Removing WordPress version from your website.');?></a></li>
                    <li><a href="#code-edits-requests"><?php _e('Securing blog against malicious URL requests.');?></a></li>
                    <li><a href="#config-rights"><?php _e('Changing config file rights.');?></a></li>
                    <li><a href="#rights-htaccess"><?php _e('Changing .htaccess file rights.');?></a></li>
                    <li><a href="#rights-folders"><?php _e('Changing rights on WordPress folders.');?></a></li>
                    <li><a href="#db"><?php _e('Database changes.');?></a></li>
                    <li><a href="#uploads"><?php _e('Your uploads directory is browsable from the web.');?></a></li>
                    <li><a href="#server-config"><?php _e('Your server shows too much information about installed software.');?></a></li>
                    <li><a href="#security-check"><?php _e('How to keep everything secured?');?></a></li>
                </ul>
                <div class="clear"></div>
                <div class="answers">
                <!-- upgrades -->
                <h3>WordPress/Themes/Plugins Upgrades.<a name="upgrades"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('You should upgrade your software often to keep it secure.');?><br />
                    <?php _e("However, you shouldn't upgrade WordPress yourself if you don't know how to fix it if the upgrade process goes wrong.");?>
                </p>
                <p>
                <?php _e("Here's why you should be afraid to upgrade your WordPress:");?>
                <ul>
                <li><?php _e("WordPress might run out of memory or have a network problem during the update");?></li>
                <li><?php _e("There could be a permissions issue which causes problems with folder rights");?></li>
                <li><?php _e("You could cause database problems which could cause you to lose data or take your entire site down");?></li>
                </ul>
                </p>
                <p>
                    <a href="http://codex.wordpress.org/Updating_WordPress"><?php _e("Step-by-step explanations</a> are available at WordPress Codex.");?>
                </p>
                <p>
                    <?php _e('You can let the professionals do the work for you and upgrade your blog with plugins. <a href="http://ultimateblogsecurity.com/blog-update">See details</a>.');?>
                </p>
                <!-- end upgrades -->
                <!-- config-place -->
                <h3><?php _e('Config file is located in an unsecured place.');?><a name="config-place"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e("The most important information in your blog files is located in wp-config.php. It's good practice to keep it in the folder above your WordPress root.");?>
                </p>
                <p>
                    <?php _e("Sometimes this is impossible to do because:");?>
                    <ul>
                        <li><?php _e("you don't have access to folder above your WordPress root");?></li>
                        <li><?php _e("some plugins were developed incorrectly and look for the config file in your WordPress root");?></li>
                        <li><?php _e("there is another WordPress installation in the folder above");?></li>
                    </ul>
                </p>
                <!-- end config-place -->
                <!-- config-keys -->
                <h3><?php _e("Editing global variables or keys in config file.");?><a name="config-keys"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <b><?php _e("Some of keys AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY are not set.");?></b><br />
                    <?php _e('Create secret keys from this link <a href="https://api.wordpress.org/secret-key/1.1/">https://api.wordpress.org/secret-key/1.1/</a> and paste them into wp-config.php');?>
                </p>
                <p>
                    <b><?php _e("It's better to turn off file editor for plugins and themes in wordpress admin.");?></b><br />
                    <?php _e("You're not often editing your theme or plugins source code in WordPress admin? Don't let potential hacker do this for you. Add <em>DISALLOW_FILE_EDIT</em> option to wp-config.php");?>
                    <pre><?php echo htmlentities("define('DISALLOW_FILE_EDIT', true);"); ?></pre>
                </p>
                <p>
                    <b><?php _e("WP_DEBUG option should be turned off on LIVE website."); ?></b><br />
                    <?php _e("Sometimes developers use this option when debugging your blog and keep it after the website is done. It's very unsafe and allow hackers to see debug information and infect your site easily. Should be turned off."); ?>
                    <pre><?php echo htmlentities("define('WP_DEBUG', false);"); ?></pre>
                </p>
                <!-- end config-keys -->
                <!-- code-edits-version -->
                <h3><?php _e("Removing the WordPress version from your website."); ?><a name="code-edits-version"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e("When WordPress version which is used in your blog is known, hacker can find proper exploit for exact version of WordPRess."); ?>
                </p>
                <p>
                    <?php _e("To remove WordPress version you should do two things:"); ?>
                    <ul>
                        <li><?php _e("check if it's not hardcoded in header.php or index.php of your current theme(search for"); ?> <i>'<meta name="generator">'</i>)</li>
                        <li>
                            <?php _e("add few lines of code to functions.php in your current theme:"); ?>
                            <pre><?php echo htmlentities("function no_generator() { return ''; }  
add_filter( 'the_generator', 'no_generator' );"); ?></pre>
                        </li>
                    </ul>
                </p>
                <!-- end code-edits-version -->
                <!-- unneeded-files -->
                <h3><?php _e("Removing unneeded files."); ?><a name="unneeded-files"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <b><?php _e("Users can see version of WordPress you are running from readme.html file."); ?></b><br>
                </p>
                <p>
                    <?php _e("When WordPress version which is used in your blog is known, hacker can find proper exploit for exact version of WordPRess."); ?>
                </p>
                <p>
                    <?php _e("Remove readme.html file which is located in root folder of your blog. <br>
                    <em>NOTE:</em> It will appear with next upgrade of WordPress."); ?>
                </p>
                <p>
                    <b><?php _e("Installation script is still available in your wordpress files."); ?></b><br>
                    <?php _e("Remove /wp-admin/install.php from your WordPress."); ?>
                </p>
                <!-- end unneeded-files -->
                <!-- code-edits-login -->
                
                <h3><?php _e("Removing unnecessary error messages on failed log-ins."); ?><a name="code-edits-login"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e("By default WordPress will show you what was wrong with your login credentials - login or password. This will allow hackers to start a brute force attack to get your password once they know the login."); ?>
                </p>
                <p>
                    <?php _e("Add few lines of code to functions.php in your current theme:"); ?>
                    <pre><?php echo htmlentities("function explain_less_login_issues($data){ return '<strong>ERROR</strong>: Entered credentials are incorrect.';}
add_filter( 'login_errors', 'explain_less_login_issues' );"); ?></pre>
                </p>
                <!-- end code-edits-login -->
                <!-- code-edits-requests -->
                <h3><?php _e("Securing blog against malicious URL requests."); ?><a name="code-edits-requests"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e("Malicious URL requests are requests which may have SQL Injection inside and will allow hacker to broke your blog."); ?> 
                </p>
                <p>
                <?php _e("Paste the following code into a text file, and save it as blockbadqueries.php. Once done, upload it to your wp-content/plugins directory and activate it like any other plugins."); ?> 
                <pre><?php echo htmlentities('<?php
/*
Plugin Name: Block Bad Queries
Plugin URI: http://perishablepress.com/press/2009/12/22/protect-wordpress-against-malicious-url-requests/
Description: Protect WordPress Against Malicious URL Requests
Author URI: http://perishablepress.com/
Author: Perishable Press
Version: 1.0
*/
if (strpos($_SERVER[\'REQUEST_URI\'], "eval(") ||
  strpos($_SERVER[\'REQUEST_URI\'], "CONCAT") ||
  strpos($_SERVER[\'REQUEST_URI\'], "UNION+SELECT") ||
  strpos($_SERVER[\'REQUEST_URI\'], "base64")) 
  {
    @header("HTTP/1.1 400 Bad Request");
    @header("Status: 400 Bad Request");
    @header("Connection: Close");
    @exit;
  }
?>'); ?></pre>
                </p>
                <!-- end code-edits-requests -->                
                <!-- config-rights -->
                <h3><?php _e("Changing config file rights."); ?> <a name="config-rights"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('According to <a href="http://codex.wordpress.org/Hardening_WordPress#Securing_wp-config.php">WordPress Codex</a> you should change rights to wp-config.php to 400 or 440 to lock it from other users.'); ?> 
                </p>
                <p>
                    <?php _e("In real life a lot of hosts won't allow you to set the last digit to 0, because they configured their webservers the wrong way. Be careful hosting on web hostings like this."); ?>
                </p>
                <!-- end config-rights -->
                <!-- rights-htaccess -->
                <h3><?php _e("Changing .htaccess file rights."); ?><a name="rights-htaccess"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e(".htaccess rights should be set to 644 or 664(depending if you want wordpress to be able to edit .htaccess for you)."); ?>
                </p>
                <!-- end rights-htaccess -->
                <!-- rights-folders -->
                <h3> <?php _e("Changing rights on WordPress folders."); ?><a name="rights-folders"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                <?php _e('According to <a href="http://codex.wordpress.org/Hardening_WordPress#File_permissions">WordPress Codex</a> right for next folders should be set like this.');?>
                </p>
                <p><b><?php printf(__('Insufficient rights on %s folder!'),'wp-content');?></b><br>
                <?php _e('<i>/wp-content/</i> should be writeable for all(777) - according to WordPress Codex. But better to set it 755 and change to 777(temporary) if some plugins asks you to do that.');?><br>
                </p>
                <p>
                <b><?php printf(__('Insufficient rights on %s folder!'),'wp-content/themes');?></b><br>
                <i>/wp-content/themes/</i> <?php _e('should have rights 755.');?> <br>
                </p>
                <p>
                <b><?php printf(__('Insufficient rights on %s folder!'),'wp-content/plugins');?></b><br>
                <i>/wp-content/plugins/</i> <?php _e('should have rights 755.');?><br>
                </p>
                <p>
                <b>Insufficient rights on core wordpress folders!</b><br>
                <i>/wp-admin/</i> <?php _e('should have rights 755.');?><br>
                <i>/wp-includes/</i> <?php _e('should have rights 755.');?>
                </p>
                <!-- end rights-folders -->
                <!-- db -->
                <h3><?php _e('Changes in database.');?><a name="db"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                <b><?php _e('Default admin login is not safe.');?></b><br>
                    <?php _e('Using MySQL frontend program(like phpmyadmin) change administrator username with command like this:');?>
                    <pre><?php echo htmlentities("update tableprefix_users set user_login='newuser' where user_login='admin';"); ?></pre>
                </p>
                <p>
                <b><?php _e('Default database prefix is not safe.');?></b><br>
                    <?php _e('Using MySQL frontend program(like phpmyadmin) change all tables prefixes from <i>wp_</i> to something different. And put the same into wp-confg.php');?>
                    <pre><?php echo htmlentities('$table_prefix  = \'tableprefix_\';'); ?></pre>
                </p>
                <!-- end db -->
                <!-- uploads -->
                <h3><?php _e('Your uploads directory is browsable from the web.');?><a name="uploads"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                <?php _e('Put an empty index.php to your uploads folder.');?>
                </p>
                <!-- end uploads -->
                <!-- server-config -->
                <h3><?php _e('Your server shows too much information about installed software.');?><a name="server-config"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                <?php _e('If you\'re using Apache web server and have root access(or can edit httpd.conf) - you can define <i>ServerTokens</i> directive with preffered options(less info - better). <a href="http://httpd.apache.org/docs/2.0/mod/core.html#servertokens">See details</a>.');?>
                </p>
                <!-- end server-config -->
                <!-- security-check -->
                <h3><?php _e('Keep your blog secure with automated checks.');?><a name="security-check"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('A lot of the security vulnerabilities are put back in place when themes and the WordPress core version is updated.  You need to run regular checks using this plugin, or <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">register for our service</a> and we will check your blog for you weekly and email you the results.');?></p>
					<p><?php _e('We also have a paid service which automatically fixes these vulnerabilities. Try it by clicking the button:');?><br><a href="http://www.ultimateblogsecurity.com/?utm_campaign=fix_issues_plugin_button"><img src="<?php echo plugins_url( 'img/fix_problems_now.png', __FILE__ ); ?>" alt="" /></a>
                </p>
                <!-- end security-check -->
                </div>
        </div>
        <?php
    }
    function wp_ultimate_security_checker_settings(){
            if (isset($_GET['flike']) || isset($_GET['rescan'])) {
                switch ($_GET['flike']) {
                   case 'k' :
                                update_option('wp_ultimate_security_checker_flike_deactivated', false);
                                break;
                   case 'n' :
                                update_option('wp_ultimate_security_checker_flike_deactivated', true);
                                break;
                }
                switch ($_GET['rescan']) {
                   case 'w' :
                                update_option('wp_ultimate_security_checker_rescan_period', 14);
                                break;
                   case 'm' :
                                update_option('wp_ultimate_security_checker_rescan_period', 30);
                                break;
                   case 'n' :
                                update_option('wp_ultimate_security_checker_rescan_period', 0);
                                break;
                }
            }
			// hide_header
			if (isset($_GET['hide_header'])) {
				update_option('wp_ultimate_security_checker_hide_header', 1);
			} elseif (isset($_GET['flike']) || isset($_GET['rescan'])) {
				update_option('wp_ultimate_security_checker_hide_header', 0);
			}
            /*if (isset($_GET['apikey'])) {
				update_option('wp_ultimate_security_checker_apikey', $_GET['apikey']);
				?><div id="message" class="updated"><p>API key is updated</p></div><?php
			}*/
			$apikey = get_option('wp_ultimate_security_checker_apikey');
			$params['apikey'] = $apikey;
			$params['blog_url'] = get_option('siteurl');
			$status_url = sprintf("http://beta.ultimateblogsecurity.com/api/%s/?%s", "get_status", http_build_query($params));
			?>
			<script>
				jQuery(document).ready(function($) {
					var linked_data_packed = "<?php echo get_option('wp_ultimate_security_checker_linked_data');?>";
					var linked_data = linked_data_packed ? JSON.parse(linked_data_packed) : undefined;					
					if (linked_data) {
						var option = $('#blog_linked option:first');
						$(option).attr('id', 'srvid_' + linked_data.id).attr('selected', true);				
						$(option).text('server: ' + linked_data.ftphost +', WP location: ' + linked_data.ftppath).val(linked_data_packed);
						$('#blog_linked').append(option);
					}
					$("#blog_unlink").live("click", function(e){
						if ($('#blog_linked option:first').attr('id') != 'link_unavailable') {
							var data = {action: 'unlink_blog', csrfmiddlewaretoken: ajax_token};
							$('#ajax_loading').fadeIn();
							jQuery.post(ajaxurl, data, function(response) {
								$('#ajax_loading').fadeOut();
								window.location.reload();
							});
						}						
					});
					
					$("#blog_change_link").live("click", function(e){
						var that = this;
						$('#ajax_loading').fadeIn();
						$.ajax({
							url: "<?php echo $status_url; ?>&callback=?",
							dataType: "jsonp",
							complete: function (){
								$('#ajax_loading').fadeOut();
							},
							success: function(response) {
								if (response && response.state == 'error') {
									switch (response.errno) {
										case -3: // Multiple blogs found
											$("#blog_link_ops").hide();
											select_website(response.data);
											return;
									}
								}
							}
						});
					});
						
					$("#website_confirm").live("click", submit_selected_site);
				});
			</script>
            
            <div class="wrap">
                <style>
                #icon-security-check {
                    background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
                }
                </style>
    
                    <?php screen_icon( 'security-check' );?>
                <h2 style="padding-left:5px;">Ultimate Security Checker
                <span style="position:absolute;padding-left:25px;">
                <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
                <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
                </span>
                </h2>
                <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
                <?php endif; ?>
                <style>
                    h3.nav-tab-wrapper .nav-tab {
                        padding-top:7px;
                    }
                </style>
    
                <h3 class="nav-tab-wrapper">
                    <a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab"><?php _e('Run the Tests');?></a>
					<a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab"><?php _e('Files Analysis');?></a>
                    <a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab"><?php _e('How to Fix');?></a>
                    <a href="?page=ultimate-security-checker&tab=settings" class="nav-tab nav-tab-active"><?php _e('Settings');?></a>
                    <!--<a href="?page=ultimate-security-checker&tab=pro" class="nav-tab"><?php _e('PRO Checks');?></a>-->
                </h3>
<!--    			<p style="border:2px solid #eee;margin-left:3px;background:#f5f5f5;padding:10px;width:706px;font-size:14px;color:green;font-family:helvetica;">
					Please check out our new idea: <strong>WP AppStore</strong>. 1-click install best plugins and themes.
					<a style="color:#e05b3c;text-decoration:underline;" href="http://wordpress.org/extend/plugins/wp-appstore/" target="_blank">Check it out!</a>
				</p> -->
                <style>
                pre {
                    padding:10px;
                    background:#f3f3f3;
                    margin-top:10px;
                }
                .answers p, .answers ul, .answers pre {
                    margin-left:10px;
                    line-height:19px;
                }
                .answers ul{
                    list-style-type:disc !important;
                    padding-left:17px !important;
                }
                input[type="radio"] {
                    margin-right: 5px;
                }
                </style>
                    <a name="#top"></a>
                    <h2><?php _e('Plugin options');?></h2>
					
                    <form method="get" action="<?php echo admin_url( 'tools.php' ); ?>" enctype="text/plain" id="wp-ultimate-security-settings">
                    <!--<h4>API key from site's settings page:</h4>
					<input type="text" style="width:300px" name="apikey" value="<?php echo htmlspecialchars(get_option('wp_ultimate_security_checker_apikey')); ?>"/>
					<input type="submit" class="button-primary" value="Save"/>-->
                    
                    <h4><?php _e('Disable Facebook Like:');?></h4>
                    <input type="hidden" value="ultimate-security-checker" name="page" />
                    <input type="hidden" value="settings" name="tab" />
                    <ul>
                    <li><input type="radio" <?php if(! get_option('wp_ultimate_security_checker_flike_deactivated', false)) echo 'checked="checked"';?> value="k" name="flike" /><?php _e('Keep Facebook Like');?></li>
                    <li><input type="radio" <?php if(get_option('wp_ultimate_security_checker_flike_deactivated', true)) echo 'checked="checked"';?> value="n" name="flike" /><?php _e('Disable it');?></li>
                    </ul>
                    <h4>Remind me about re-scan in:</h4>
                    <ul>
                    <li><input type="radio" <?php if(get_option('wp_ultimate_security_checker_rescan_period') == 14) echo 'checked="checked"';?> value="w" name="rescan" />2 weeks</li>
                    <li><input type="radio" <?php if(get_option('wp_ultimate_security_checker_rescan_period') == 30) echo 'checked="checked"';?> value="m" name="rescan" />1 month</li>
                    <li><input type="radio" <?php if(get_option('wp_ultimate_security_checker_rescan_period') == 0) echo 'checked="checked"';?> value="n" name="rescan" />Never remind me</li>
                    </ul>
                    
                    <p>
                    	<input id="id_hide_header" type="checkbox" name="hide_header" value="1" <?php if(get_option('wp_ultimate_security_checker_hide_header') == 1) echo 'checked="checked"';?> /><label for="id_hide_header">Hide header security points</label> 
                    </p>
                    
                    <p><input type="submit" class="button-primary" value="<?php _e('Save Settings');?>" /></p>
                    
                    </form>
                    <div class="clear"></div>
                    <h3>System Information.</h3>
                    <p>
                        WordPress location (copy to <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">add site page</a> for <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">automated security checking service</a>):<br/>
                        <pre><?php echo ABSPATH; ?></pre>
                    </p>
                    <!-- security-check -->
	                <h3><?php _e('Keep your blog secure with automated checks.');?><a name="security-check"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
	                <p>
	                    <?php _e('A lot of the security vulnerabilities are put back in place when themes and the WordPress core version is updated.  You need to run regular checks using this plugin, or <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">register for our service</a> and we will check your blog for you weekly and email you the results.');?></p>
						<p><?php _e('We also have a paid service which automatically fixes these vulnerabilities. Try it by clicking the button:');?><br> <a href="http://www.ultimateblogsecurity.com/?utm_campaign=fix_issues_plugin_button"><img src="<?php echo plugins_url( 'img/fix_problems_now.png', __FILE__ ); ?>" alt="" /></a>
	                </p>
                    <!-- end security-check -->
                    <div class="clear"></div>
                    </div>
                    <?php
    }
    
    function wp_ultimate_security_checker_pro(){
            ?>
            <div class="wrap">
                <style>
                #icon-security-check {
                    background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
                }
                #logout-link {
                    float: right;
                    display: none;
                }
                </style>
    
                    <?php screen_icon( 'security-check' );?>
                <h2 style="padding-left:5px;">Ultimate Security Checker
                <span style="position:absolute;padding-left:25px;">
                <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
                <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
                </span>
                </h2>
                <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
                <?php endif; ?>
                <style>
                    h3.nav-tab-wrapper .nav-tab {
                        padding-top:7px;
                    }
                </style>
    
                <h3 class="nav-tab-wrapper">
                    <a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab"><?php _e('Run the Tests');?></a>                    
					<a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab"><?php _e('Files Analysis');?></a>
                    <a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab"><?php _e('How to Fix');?></a>
                    <a href="?page=ultimate-security-checker&tab=settings" class="nav-tab"><?php _e('Settings');?></a>
                    <!--<a href="?page=ultimate-security-checker&tab=pro" class="nav-tab nav-tab-active"><?php _e('PRO Checks');?></a>-->
                </h3>
<!--    			<p style="border:2px solid #eee;margin-left:3px;background:#f5f5f5;padding:10px;width:706px;font-size:14px;color:green;font-family:helvetica;">
					Please check out our new idea: <strong>WP AppStore</strong>. 1-click install best plugins and themes.
					<a style="color:#e05b3c;text-decoration:underline;" href="http://wordpress.org/extend/plugins/wp-appstore/" target="_blank">Check it out!</a>
				</p> -->
                <style>
                pre {
                    padding:10px;
                    background:#f3f3f3;
                    margin-top:10px;
                }
                .answers p, .answers ul, .answers pre {
                    margin-left:10px;
                    line-height:19px;
                }
                .answers ul{
                    list-style-type:disc !important;
                    padding-left:17px !important;
                }
                .button-submit-wrapper{
                    float: right;
                }
                .links-wrapper{
                    float: left;
                    width: 100px;
                }
                .login-controlls{
                    width: 230px;
                }
                label{
                    display: block;
                }
                </style>
                <div class="wrap">
                <div id="ajax-content"></div>
                <div id="ajax-result"></div>
                <div class="clear"></div>
                </div>
                </div>
    <?php
    }
    
    function wp_ultimate_security_checker_ajaxscreen_loader(){
        check_admin_referer('ultimate-security-checker-ajaxrequest', 'csrfmiddlewaretoken');
        $apikey = get_option('wp_ultimate_security_checker_apikey');
        if (isset($_POST['screen'])){
            switch ($_POST['screen']) {
               case 'register' :
                        return wp_ultimate_security_checker_ajaxscreen_register();
            		    break;
               case 'ftp' :
                        if (!$apikey) {
                            return wp_ultimate_security_checker_ajaxscreen_login();
                        }
                        return wp_ultimate_security_checker_ajaxscreen_ftp();
            	  	    break;
               case 'dashboard' :
                        if (!$apikey) {
                            return wp_ultimate_security_checker_ajaxscreen_login();
                        }
                        return wp_ultimate_security_checker_ajaxscreen_dashboard();
            		    break;
               default:
                        return wp_ultimate_security_checker_ajaxscreen_login();
                        break;
            }
        }else{
            if (!$apikey) {
                return wp_ultimate_security_checker_ajaxscreen_login();
            }else{
                return wp_ultimate_security_checker_ajaxscreen_dashboard();
            }
        }
        exit;
    }
    
    function wp_ultimate_security_checker_ajaxscreen_login(){
        global $current_user;
        get_currentuserinfo();
        preg_match_all("/([\._a-zA-Z0-9-]+)@[\._a-zA-Z0-9-]+/i", $current_user->user_email, $matches);
    	$email_name = $matches[1][0];	
        $apikey = get_option('wp_ultimate_security_checker_apikey');        
        ?>
        <script type="text/javascript">
        <!--
        var apikey = "<?php echo $apikey;?>";
        $(document).ready(function(){
                    //login window
                    $("#ajax-content").delegate("#pro-login-submit", "click", function(event){
                            $("#ajax_loading").css("display", "block");
                            $('#ajax-result').text('');
                            $(this).attr('disabled', 'disabled');
                            var el = $(this);
                            var post_data = {
                                             username: $("#pro-login-email").val(),
                                             password: $("#pro-login-password").val(),
                                             };
                        	
                            $.post(get_apikey_url, post_data , function(data) {
                              if(data.state == 'error'){
                                if('data' in data){
                                    if('errors' in data.data){
                                        if('username' in data.data.errors)
                                            $('#ajax-result').text(data.data.errors.username);
                                        if('password' in data.data.errors)
                                            $('#ajax-result').text(data.data.errors.password);
                                    }    
                                }else if('message' in data){
                                    $('#ajax-result').text(data.message);
                                }
                                $("#ajax_loading").css("display", "none");
                                el.removeAttr('disabled');
                              }
                              if(data.state == 'ok'){
                                ajax_update_apikey(
                                    data.data.apikey,
                                    false,
                                    false,
                                    function(local_resp){
                                        ajax_get_screen('dashboard');
                                        console.log(local_resp);  
                                    },
                                    function(local_resp){
                                        $("#ajax_loading").css("display", "none");
                                        $('#ajax-result').text('Can\'t update your site values');
                                        console.log(local_resp);  
                                    }
                                );
                              }
                            }, 'json');
                            //console.log(post_data);
                    });
            });	
            -->
            </script>            
        <p>If you don't want to spend time to deal with manual fixes, want professionals to take care of your website - register your website and get API key, so we can help you get those fixes done. Fill the form below to complete registration</p>
        <h4><?php _e('Login to Ultimate Blog Security service');?></h4>
        <ul>
        <li><label for="login"><?php _e('Username or Email');?></label><input id="pro-login-email" type="text" name="login" size="40" value="<?php echo $email_name; ?>" /></li>
        <li><label for="pwd"><?php _e('Password');?></label><input id="pro-login-password" type="password" name="pwd" size="40" /></li>
        <li>
            <div class="login-controlls">
                <div class="links-wrapper">
                <a id="register-link" href="#"><?php _e("I don't have account");?></a>
                <div class="clear"></div>
                </div>
                <div class="button-submit-wrapper">
                <input type="submit" id="pro-login-submit" class="button-primary" value="<?php _e('Submit');?>" />
                <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
        </li>
        <li>
            <p id="ajax_loading" style="display: none;"><?php _e('Communicating with server...');?>
            <img style="margin-left:15px;" src="<?php echo plugins_url( 'img/ajax-loader.gif', __FILE__ ); ?>" alt="loading" />
            </p>
        </li>
        </ul>
        <?php
        exit;
    }
    
    function wp_ultimate_security_checker_ajaxscreen_register(){
        global $current_user;
        get_currentuserinfo();
        preg_match_all("/([\._a-zA-Z0-9-]+)@[\._a-zA-Z0-9-]+/i", $current_user->user_email, $matches);
    	$email_name = $matches[1][0];					
        $url = home_url();
        if (is_multisite()) {
            $url = network_home_url();
        }
        $apikey = get_option('wp_ultimate_security_checker_apikey');        
        ?>
        <script type="text/javascript">
        <!--
        var apikey = "<?php echo $apikey;?>";
        $(document).ready(function(){
                    //reg window
                    $("#ajax-content").delegate("#ajax-register-submit", "click", function(event){
                            $("#ajax_loading").css("display", "block");
                            $('#ajax-result').text('');
                            $(this).attr('disabled', 'disabled');
                            var el = $(this);
                            var post_data = {
                                             email: $("#ajax-register-email").val(),
                                             username: $("#ajax-register-username").val(),
                                             blogurl: blogurl
                                             };
                        	
                            $.post(register_url, post_data , function(data) {
                              if(data.state == 'error'){
                                if('data' in data){
                                    if('errors' in data.data){
                                        if('username' in data.data.errors)
                                            $('#ajax-result').text(data.data.errors.username);
                                        if('email' in data.data.errors)
                                            $('#ajax-result').text(data.data.errors.email);
                                        if('blogurl' in data.data.errors)
                                            $('#ajax-result').text(data.data.errors.blogurl);
                                    }    
                                }else if('message' in data){
                                    $('#ajax-result').text(data.message);
                                }
                                $("#ajax_loading").css("display", "none");
                                el.removeAttr('disabled');
                              }
                              $('#ubs_regmsg').html('You sucessfully registered account in our service. Please - use this password for authentication in your plugin and our site: </br><strong>'+data.data.password+'</strong></br>We sent account activation details to your email. Please follow these instructions to complete registration.</br><a href="#" id="dashboard-link" class="button-primary">Go to dashboard -></a>');
                              if(data.state == 'ok'){
                                ajax_update_apikey(
                                    data.data.apikey,
                                    data.data.password,
                                    1,
                                    function(local_resp){
                                        if (local_resp.state == 'ok') {
                                            $('#pro-reg-form').css('display', 'none');
                                            $("ubs_regmsg").append('<p>Apikey sucessfully stored in your wordpress blog</p>');    
                                        }else{
                                            $('#ubs_regerr').text('Can\'t update your site values'); 
                                        }  
                                    },
                                    function(local_resp){
                                        $('#ubs_regerr').text('Can\'t update your site values'); 
                                    }
                                );
                                $("#ajax_loading").css("display", "none");
                              }
                            }, 'json');
                            //console.log(post_data);
                    });
            });	
            -->
            </script>
            <h2 style="padding-left:5px;"><?php _e('Register to Ultimate Blog Security service');?></h2>
            <div id="ubs_regmsg">
				<?php if ($apikey) { ?>
				<p>Thanks for registering. A confirmation email was sent to your email address.
				Please check your email and click on the link to confirm your account and complete your registration.</p>
				<?php } ?>
            </div>              
            <div id="ubs_regerr"></div>
            
            <div id="pro-reg-form" style="<?php if ($apikey) { ?>display:none<?php }?>">                    
            <p>If you don't want to spend time to deal with manual fixes, want professionals to take care of your website - register your website and get API key, so we can help you get those fixes done. Fill the form below to complete registration</p>
            <ul>
            <li><label for="login"><?php _e('Email');?></label><input type="text" id="ajax-register-email" value="<?php echo $current_user->user_email; ?>" name="email" size="40" /></li>
            <li><label for="login"><?php _e('Username');?></label><input type="text" id="ajax-register-username" value="<?php echo $email_name; ?>" name="username" size="40" /></li>
            <li>
                <div class="login-controlls">
                    <div class="links-wrapper">
                    <a id="login-link" href="#"><?php _e("login");?></a>
                    <div class="clear"></div>
                    </div>
                    <div class="button-submit-wrapper">
                    <input type="submit" id="ajax-register-submit" class="button-primary" value="<?php _e('Submit');?>" />
                    <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>  
            </li>
            <li>
                <p id="ajax_loading" style="display: none;"> Communicating with server...
                <img style="margin-left:15px;" src="<?php echo plugins_url( 'img/ajax-loader.gif', __FILE__ ); ?>" alt="loading" />
                </p>
            </li>
            </ul>
            </div>                    
        <?php
        exit;
    }
    
    function wp_ultimate_security_checker_ajaxscreen_ftp(){
        $url = home_url();
        if (is_multisite()) {
            $url = network_home_url();
        }
        $apikey = get_option('wp_ultimate_security_checker_apikey');        
        ?>
            <script type="text/javascript">
            <!--
            var apikey = "<?php echo $apikey;?>";
            $(document).ready(function(){
                $("#ajax-content").delegate("#pro-ftp-submit", "click", function(event){
                    $("#ajax_loading").css("display", "block");
                    $('#ajax-result').text('');
                    $(this).attr('disabled', 'disabled');
                    var el = $(this);
                    var post_data = {
                                     apikey:apikey,
                                     uri:$("#pro-ftp-web_link").val(),
                                     ftphost:$("#pro-ftp-ftp_host").val(),
                                     ftppath:$("#pro-ftp-ftp_path").val(),
                                     ftpuser:$("#pro-ftp-ftp_user").val(),
                                     ftppass:$("#pro-ftp-ftp_pwd").val()
                                     };
                    $.post(add_website_url, post_data , function(data) {
                          if(data.state == 'error'){
                            if('data' in data){
                                if('errors' in data.data){
                                    if('uri' in data.data.errors)
                                        $('#ajax-result').text(data.data.errors.uri);
                                    if('ftphost' in data.data.email)
                                        $('#ajax-result').text(data.data.errors.ftphost);
                                    if('ftppath' in data.data.email)
                                        $('#ajax-result').text(data.data.errors.ftppath);
                                    if('ftpuser' in data.data.email)
                                        $('#ajax-result').text(data.data.errors.ftpuser);
                                    if('ftppass' in data.data.email)
                                        $('#ajax-result').text(data.data.errors.ftppass);
                                }    
                            }else if('message' in data){
                                $('#ajax-result').text(data.message);
                            }
                            $("#ajax_loading").css("display", "none");
                            el.removeAttr('disabled');
                          }
                          if(data.state == 'ok'){
                            ajax_get_screen('dashboard');
                            $('#ajax-result').text('Your blog has been sucessfully added to our system');
                          }
                    }, 'json');
                });    
            });	
            -->
            </script>
            <h2><?php _e('FTP Information');?></h2>
            <p>If you don't want to spend time to deal with manual fixes, want professionals to take care of your website - register your website and get API key, so we can help you get those fixes done. Fill the form below to complete registration</p>
            <h4><?php _e('Website details');?></h4>
            <ul>
            <li><label for="web_link"><?php _e('Website link');?></label><input id="pro-ftp-web_link" type="text" value="<?php echo $url; ?>" name="web_link" size="40" /></li>
            <li><label for="ftp_host"><?php _e('FTP Host');?></label><input id="pro-ftp-ftp_host" type="text" name="ftp_host" size="40" /></li>
            <li><label for="ftp_user"><?php _e('FTP User');?></label><input id="pro-ftp-ftp_user" type="text" name="ftp_user" size="40" /></li>
            <li><label for="ftp_pwd"><?php _e('FTP Password');?></label><input id="pro-ftp-ftp_pwd" type="password" name="ftp_pwd" size="40" /></li>
            <li><label for="ftp_path"><?php _e('Path to directory on your server (optional)');?></label><input id="pro-ftp-ftp_path" type="text" name="ftp_path" size="40" /></li>
            <li>
                <input type="submit" id="pro-ftp-submit" class="button-primary" value="<?php _e('Submit');?>" />
            </li>
            <li>
                <p id="ajax_loading" style="display: none;"> Communicating with server...
                <img style="margin-left:15px;" src="<?php echo plugins_url( 'img/ajax-loader.gif', __FILE__ ); ?>" alt="loading" />
                </p>
            </li>
            </ul>
        <?php
        exit;
    }
    
    function wp_ultimate_security_checker_ajaxscreen_dashboard(){
        $apikey = get_option('wp_ultimate_security_checker_apikey');        
       ?>
                    <style type="text/css">
                    <!--
                    dt{
                        font-weight: bold;
                    }
                    dd{
                         margin-left: 50px;
                    }	
                    -->
                    </style>
                    <script type="text/javascript">
                        var apikey = "<?php echo $apikey;?>";
                        
                        var all_issues = {
                            5 : '<div>Fix wp-config.php location - Wp-config.php in the document root makes it easier for hackers to access your configuration data.</div>',
                            6 : '<div>Fix wp-config.php rights issue - Incorrect rights allows others to edit the wp-config file.</div>',
                            8 : '<div>Do not display WordPress version in the code - Showing the version is on by default, and gives hackers clues on the best way to attack your blog.</div>',
                            11 : '<div>Remove readme.html file - readme.html exposes Wordpress vesion to hackers, which they can then use to more easily hack into your blog.</div>',
                            12 : '<div>Remove installation script - The install script can be used to damage your Wordpress blog.</div>',
                            13 : '<div>Fix uploads folder from being accessed from the web - Your uploads should not be able to be accessed from the web.</div>',
                            14 : '<div>WordPress displays unnecessary error messages on failed log-ins - With detailed error messages it\'s easy to brute force admin credentials.</div>',
                            15 : '<div>WordPress core should be updated. - You should update to latest version of WordPress regularly.</div>',
                            16 : '<div>Some of your plugins should be updated. - Outdated version of plugins may have unresolved security issues.</div>',
                            17 : '<div>Some of your themes should be updated. - Outdated version of themes may have unresolved security issues.</div>',
                            18 : '<div>Secure admin login. - Default admin login is not safe.</div>',
                            19 : '<div>Secure database prefix. - Default database prefix is not safe.</div>',
                            101 : '<div>Lock wp-config.php. Recommendation: On - <strong>Turn off only if wordpress needs access to this file.</strong> Keeping wp-config.php writeable makes it easier for hackers to access your configuration data.</div>',
                            102 : '<div>Lock .htaccess file. Recommendation: On - The reason Wordpress needs to access your .htaccess file is to make your urls more user-friendly. Leave it turned off if you use canonical URLs.</div>',
                            103 : '<div>Allow writing to wp-content folder. Recommendation: On - This folder is created to store your media content, like photos, music, etc. Also used by plugins for storing various data. It must be writeable.</div>',
                            104 : '<div>Lock themes folder. Recommendation: On - Unlock if you\'re doing any changes to the themes files through wordpress admin or installing new themes.</div>',
                            105 : '<div>Lock plugins folder. Recommendation: On - Unlock if you\'re installing or updating plugins.</div>',
                            106 : '<div>Lock wordpress core folders. Recommendation: On - Should be always locked. Only turn off when updating your WordPress installation.</div>',
                        };
                        
                		jQuery(document).ready(function($) {			
                           ajax_get_status(
                           function(data){
                           $("#ajax_loading").css("display", "none"); 
                           if(data.state == 'ok')
                           {
                                $("#pro-dashboard-content").css("display", "block");
                                $("#pro-dashboard-content-uri").text(data.data.uri);
                                $("#pro-dashboard-content-ubs_url").html('<a href="'+data.data.ubs_url+'">Manage this blog on UBS site</a>');
                                $("#pro-dashboard-content-latest_check_date").text(data.data.latest_check_date);
                                if(data.data.latest_check_result){
                                  var errors_text = data.data.latest_check_result;
                                  errors_text = errors_text.replace(/[\[\],]/g, '');
                                  console.log(errors_text);
                                  $.each(all_issues, function(index, value) {
                                      errors_text = errors_text.replace(new RegExp("'"+index+"'",'g'), value);
                                  }); 
                                }
                                $("#pro-dashboard-content-latest_check_result").html(errors_text);
                           }else{
                            if('message' in data){
                                if(data.message == 'Invalid API key'){
                                    $("#pro-dashboard-content").html('<div>You haven\'t activated your account, or your apikey blocked.</div>');
                                }
                                if(data.message == 'Blog not found'){
                                    $("#pro-dashboard-content").html('<div>You haven\'t registered this blog in our service. <a href="#" id="ftp-link" class="button-primary">Add this blog to our service!</a></div>');
                                }
                            }
                            if('data' in data){
                                $("#pro-dashboard-content").css("display", "block");
                                console.log(data);
                            }
                            $("#pro-dashboard-content").css("display", "block");
                            console.log(data);
                           }
                           },
                           function(data){
                            $('#ajax-result').text('Ajax error occured. Please try again later.');
                           }
                           ); 
                		});
                        </script>
                    <h2><?php _e('Dashboard');?><a id="logout-link" class="button-primary" <?php if($apikey){echo 'style="display: block;"';} ?> href="#">logout</a></h2>
                    <p id="ajax_loading"> Communicating with server...
                    <img style="margin-left:15px;" src="<?php echo plugins_url( 'img/ajax-loader.gif', __FILE__ ); ?>" alt="loading" />
                    </p>
                    <div id="pro-dashboard-content" style="display: none;">
                    <dl>
                        <dt>Blog Url:</dt>
                        <dd id="pro-dashboard-content-uri"></dd>
                        <dt>Link to our site:</dt>
                        <dd id="pro-dashboard-content-ubs_url">Manage this blog on UBS site</dd>
                        <dt>Latest check date:</dt>
                        <dd id="pro-dashboard-content-latest_check_date"></dd>
                        <dt>Latest check result:</dt>
                        <dd id="pro-dashboard-content-latest_check_result"></dd>
                    </dl>
                    </div>
                    <?php
                    $failed_logins = get_option('wp_ultimate_security_checker_failed_login_attempts_log');
                    if (is_array($failed_logins)):
                    ?>
                    <h4>List of failed login attempts:</h4>
                    <table style="text-align: center;">
                    <tr>
                        <td style="width: 15px;">#</td>
                        <td style="width: 150px;">Time</td>
                        <td style="width: 200px;">login username</td>
                        <td style="width: 120px;">IP address</td>
                    </tr>
                    <?php
                        foreach ($failed_logins as $key => $row) {
                        echo "<tr>";
                        echo ("<td>$key</td><td>{$row['time']}</td><td>{$row['username']}</td><td>{$row['ip']}</td>");
                        echo "</tr>";
                        }
                    endif;
                    ?>
                    </table>			
       <?php
       exit; 
    }
    
    function wp_ultimate_security_checker_core_files(){
        $core_tests_results = get_option('wp_ultimate_security_checker_hashes_issues');
        ?>
        <div class="wrap">
            <style>
            #icon-security-check {
                background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
            }
            div.diff-addedline {
                font-family: monospace;
                display: block;
                font-size: 13px;
                font-weight: normal;
                padding: 10px;
                background-color: #DDFFDD;
            }
            div.diff-deletedline {
                font-family: monospace;
                display: block;
                font-size: 13px;
                font-weight: normal;
                padding: 10px;
                background-color: #FBA9A9;
            }
            div.diff-context {
                font-family: monospace;
                display: block;
                font-size: 13px;
                font-weight: normal;
                padding: 10px;
                background-color: #F3F3F3;
            }
            </style>

                <?php screen_icon( 'security-check' );?>
            <h2 style="padding-left:5px;">Ultimate Security Checker
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <?php endif; ?>
            <style>
                h3.nav-tab-wrapper .nav-tab {
                    padding-top:7px;
                }
            </style>

            <h3 class="nav-tab-wrapper">
                <a href="?page=ultimate-security-checker&tab=run-the-tests" style="text-decoration: none;">&lt;- <?php _e('Back to Test results');?></a>
            </h3>

            <style>
            pre {
                padding:10px;
                background:#f3f3f3;
                margin-top:10px;
            }
            .answers p, .answers ul, .answers pre {
                margin-left:10px;
                line-height:19px;
            }
            .answers ul{
                list-style-type:disc !important;
                padding-left:17px !important;
            }
            </style>
                <a name="#top"></a>
                <h2><?php _e('Your blog core files check results:');?></h2>
                <?php if ($core_tests_results['diffs']): ?>
                <h3><?php _e('Some files from the core of your blog have been changed. Files and lines different from original WordPress core files:');?></h3>
                <?php
                    $i = 1; 
                    foreach($core_tests_results['diffs'] as $filename => $lines){
                        $li[]  .= "<li><a href=\"#$i\">$filename</a></li>\n";
                        $out .= "<h4>$filename<a name=\"$i\"></a><a href=\"#top\" style=\"font-size:13px;margin-left:10px;\">&uarr; ".__('Back')."</a></h4>";
                        $out .= implode("\n", $lines);
                        $i++;
                    }
                ?>
                <?php if(sizeof($li) > 4){
                 echo "<ul>\n".implode("\n", $li)."</ul>\n"; 
                 }
                 ?>
                <div class="clear"></div>
                <div class="errors-found">
                <p>
                <?php echo $out; ?>
                <?php else: _e('<h3>No code changes found in your blog core files!</h3>');?>; ?>
                <?php endif;?>
                </p>
                </div>
                <?php 
                if ($core_tests_results['old_export']) {
                    echo _e("<h5>This is old export files. You should delete them.</h5>");
                    echo "<ul>";
                    foreach($core_tests_results['old_export'] as $export){
                        echo "<li>".$static_url."</li>";
                    }
                    echo "</ul>"; 
                }
                ?>
                <!-- end hashes -->
                
                <!-- security-check -->
                <h3><?php _e('Keep your blog secure with automated checks.');?><a name="security-check"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('A lot of the security vulnerabilities are put back in place when themes and the WordPress core version is updated.  You need to run regular checks using this plugin, or <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">register for our service</a> and we will check your blog for you weekly and email you the results.');?></p>
					<p><?php _e('We also have a paid service which automatically fixes these vulnerabilities. Try it by clicking the button:');?><br><a href="http://www.ultimateblogsecurity.com/?utm_campaign=fix_issues_plugin_button"><img src="<?php echo plugins_url( 'img/fix_problems_now.png', __FILE__ ); ?>" alt="" /></a>
                </p>
                <!-- end security-check -->
                <div class="clear"></div>
                </div>
        <?php
    }
    function wp_ultimate_security_checker_ajax_handler(){
	check_ajax_referer( 'ultimate-security-checker_scan' );
    
    $security_check = new SecurityCheck();
    $responce = $security_check->run_heuristic_check(); 
    echo json_encode($responce);
	exit;
}
add_action( 'wp_ajax_ultimate_security_checker_ajax_handler', 'wp_ultimate_security_checker_ajax_handler' );

    function wp_ultimate_security_checker_wp_files(){
        $files_tests_results = get_option('wp_ultimate_security_checker_files_issues');
        ?>
        <script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#run-scanner').click( function() {

			$.ajaxSetup({
				type: 'POST',
				url: ajaxurl,
				complete: function(xhr,status) {
					if ( status != 'success' ) {
						$('#scan-loader img').hide();
						$('#scan-loader span').html( '<?php _e('An error occurred. Please try again later.');?>' );
					}
				}
			});

			$('#scan-results').hide();
			$('#scan-loader').show();
            $('#run-scanner').hide();
			usc_file_scan();
			return false;
		});
	});

        usc_file_scan = function() {
		jQuery.ajax({
			data: {
				action: 'ultimate_security_checker_ajax_handler',
				_ajax_nonce: '<?php echo wp_create_nonce( 'ultimate-security-checker_scan' ); ?>',
			}, success: function(r) {
				var res = jQuery.parseJSON(r);
				if ( 'processing' == res.status ) {
					jQuery('#scan-loader span').html(res.data);
					usc_file_scan();
				} else if ( 'error' == res.status ) {
					// console.log( r );
					jQuery('#scan-loader img').hide();
					jQuery('#scan-loader span').html(
						'<?php _e('An error occurred:');?> <pre style="overflow:auto">' + res.data + '</pre>'
					);
				} else {
				    jQuery('#scan-loader img').hide();
				    jQuery('#scan-loader span').html('<?php _e('Scan complete. Refresh the page to view the results.');?>');
				    window.location.reload(false);
				}
			}
		});
	};

</script>
        <div class="wrap">
            <style>
            #icon-security-check {
                background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
            }
            div.danger-found {
                margin-bottom: 25px;
            }
            pre {
                padding:10px;
                background:#f3f3f3;
                margin-top:10px;
            }
            .answers p, .answers ul, .answers pre {
                margin-left:10px;
                line-height:19px;
            }
            .answers ul{
                list-style-type:disc !important;
                padding-left:17px !important;
            }
            div#scan-loader{
                display: none;
            }
            h3.nav-tab-wrapper .nav-tab {
                padding-top:7px;
            }
            </style>

                <?php screen_icon( 'security-check' );?>
            <h2 style="padding-left:5px;">Ultimate Security Checker
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <?php endif; ?>
            <h3 class="nav-tab-wrapper">
                    <a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab"><?php _e('Run the Tests');?></a>                    
					<a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab nav-tab-active"><?php _e('Files Analysis');?></a>
                    <a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab"><?php _e('How to Fix');?></a>
                    <a href="?page=ultimate-security-checker&tab=settings" class="nav-tab"><?php _e('Settings');?></a>
                    <!--<a href="?page=ultimate-security-checker&tab=pro" class="nav-tab"><?php _e('PRO Checks');?></a>-->
            </h3>
<!--			<p style="border:2px solid #eee;margin-left:3px;background:#f5f5f5;padding:10px;width:706px;font-size:14px;color:green;font-family:helvetica;">
				Please check out our new idea: <strong>WP AppStore</strong>. 1-click install best plugins and themes.
				<a style="color:#e05b3c;text-decoration:underline;" href="http://wordpress.org/extend/plugins/wp-appstore/" target="_blank">Check it out!</a>
			</p>-->
                <a name="#top"></a>
                <h2><?php _e('Your blog files vulnerability scan results:');?></h2>
                <span style="margin: 15xp; display: inline-block;"><?php _e("This scanner will test your blog on suspicious code patterns. Even if it finds something - it doesn't mean, that code is malicious code actually. Also, this test is in beta, so may stop responding. Results of this test <strong>DO NOT</strong> affect your blog security score. We provide it as additional scanning to find possible danger inclusions in your code.");?></span>
                
                <a style="float:left;margin-top:20px;font-weight:bold;" href="#" class="button-primary" id="run-scanner">Scan my blog files now!</a>
                <div class="clear"></div>
                <div id="scan-loader">
                <img src="<?php echo plugins_url( 'img/loader.gif', __FILE__ ); ?>" alt="" />
                <span style="color: red;"></span>
                </div>
                <?php if ($files_tests_results): ?>
                <div id="scan-results">
                <h3><?php _e("Some files from themes and plugins may have potential vulnerabilities:");?></h3>
                <?php
                    $i = 1; 
                    foreach($files_tests_results as $filename => $lines){
                        $li[]  .= "<li><a href=\"#$i\">$filename</a></li>\n";
                        $out .= "<h3>$filename<a name=\"$i\"></a><a href=\"#top\" style=\"font-size:13px;margin-left:10px;\">&uarr; ".__('Back')."</a></h3>";
                        $out .= implode("\n", $lines);
                        $i++;
                    }
                ?>
                <?php if(sizeof($li) > 4){
                 echo "<ul>\n".implode("\n", $li)."</ul>\n"; 
                 }
                 ?>
                <div class="clear"></div>
                <div class="errors-found">
                <p>
                <?php echo $out; ?>
                <?php elseif($files_tests_results[0]): ?>
                <?php echo $files_tests_results[0];?>
                <?php else: _e('<h3>No code changes found in your blog files!</h3>'); ?>
                <?php endif;?>
                </p>
                </div>
                </div>
                <!-- security-check -->
                <h3><?php _e('Keep your blog secure with automated checks.');?><a name="security-check"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('A lot of the security vulnerabilities are put back in place when themes and the WordPress core version is updated.  You need to run regular checks using this plugin, or <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">register for our service</a> and we will check your blog for you weekly and email you the results.');?></p>
					<p><?php _e('We also have a paid service which automatically fixes these vulnerabilities. Try it by clicking the button:');?><br><a href="http://www.ultimateblogsecurity.com/?utm_campaign=fix_issues_plugin_button"><img src="<?php echo plugins_url( 'img/fix_problems_now.png', __FILE__ ); ?>" alt="" /></a>
                </p>
                <!-- end security-check -->
                <div class="clear"></div>
                </div>
        <?php
    }
    function wp_ultimate_security_checker_wp_posts(){
        $posts_tests_results = get_option('wp_ultimate_security_checker_posts_issues');
        ?>
        <div class="wrap">
            <style>
            #icon-security-check {
                background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
            }
            </style>

                <?php screen_icon( 'security-check' );?>
            <h2 style="padding-left:5px;">Ultimate Security Checker
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <?php endif; ?>
            <style>
                h3.nav-tab-wrapper .nav-tab {
                    padding-top:7px;
                }
            </style>

            <h3 class="nav-tab-wrapper">
                <a href="?page=ultimate-security-checker&tab=run-the-tests" style="text-decoration: none;">&lt;- <?php _e('Back to Tests results');?></a>
            </h3>

            <style>
            pre {
                padding:10px;
                background:#f3f3f3;
                margin-top:10px;
            }
            .answers p, .answers ul, .answers pre {
                margin-left:10px;
                line-height:19px;
            }
            .answers ul{
                list-style-type:disc !important;
                padding-left:17px !important;
            }
            </style>
                <a name="#top"></a>
                <h2><?php _e('Your blog records scan results:');?></h2>
                
                <?php if ($posts_tests_results['posts_found']){
                    $postsHdr = __("<h3>Some posts in your blog contains suspicious code:</h3>\n");
                    $i = 1; 
                    foreach($posts_tests_results['posts_found'] as $postId => $postData){
                        $postsList[] = "<li><a href=\"#p$i\">{$postData['post-title']}($postId)</a></li>\n";
                        $pout .= "<h4>{$postData['post-title']}($postId) - <a href=\"".get_edit_post_link($postId)."\" title=\"".__("Edit")."\">".__("Edit")."</a><a name=\"p$i\"></a><a href=\"#top\" style=\"font-size:13px;margin-left:10px;\">&uarr; ".__('Back').";?></a></h4>";
                        $pout .= implode("\n", $postData['content']);
                        $i++;
                    }
                   
                    $postsOut .= "<div class=\"clear\"></div>\n<div class=\"errors-found\">\n<p>";
                    $postsOut .= $pout;
                    $postsOut .= "</p>\n</div>\n";

                }else{
                    $postsHdr = __("<h3>No potential code vulnerabilities foud in your posts!</h3>\n");
                }
                ?>
                
                <?php if ($posts_tests_results['comments_found']){
                    $commentsHdr = __("<h3>Some comments in your blog contains suspicious code:</h3>\n");
                    $i = 1; 
                    foreach($posts_tests_results['comments_found'] as $commentId => $commentData){
                        $commentsList[] = "<li><a href=\"#c$i\">{$commentData['comment-autor']}($commentId)</a></li>\n";
                        $cout .= "<h4>{$commentData['comment-autor']}($commentId) - <a href=\"".get_edit_comment_link($commentId)."\" title=\"".__("Edit")."\">".__("Edit")."</a><a name=\"c$i\"></a><a href=\"#top\" style=\"font-size:13px;margin-left:10px;\">&uarr; ".__('Back').";?></a></h4>";
                        $cout .= implode("\n", $commentData['content']);
                        $i++;
                    }
                    $commentsOut .= "<div class=\"clear\"></div>\n<div class=\"errors-found\">\n<p>";
                    $commentsOut .= $cout;
                    $commentsOut .= "</p>\n</div>\n";

                }else{
                    $commentsHdr = __("<h3>No potential code vulnerabilities foud in your comments!</h3>\n");
                }
                ?>
                <?php echo $postsHdr; ?>
                <?php if(sizeof($postsList) > 4) echo "<ul>\n".implode("\n", $postsList)."</ul>\n"; ?>
                <?php echo $postsOut; ?>
                
                <?php echo $commentsHdr; ?>
                <?php if(sizeof($commentsList) > 4) echo "<ul>\n".implode("\n", $commentsList)."</ul>\n"; ?>
                <?php echo $commentsOut; ?>
                
                
                <!-- security-check -->
                <h3><?php _e('Keep your blog secure with automated checks.');?><a name="security-check"></a><a href="#top" style="font-size:13px;margin-left:10px;">&uarr; <?php _e('Back');?></a></h3>
                <p>
                    <?php _e('A lot of the security vulnerabilities are put back in place when themes and the WordPress core version is updated.  You need to run regular checks using this plugin, or <a href="http://www.ultimateblogsecurity.com/?utm_campaign=plugin">register for our service</a> and we will check your blog for you weekly and email you the results.');?></p>
					<p><?php _e('We also have a paid service which automatically fixes these vulnerabilities. Try it by clicking the button:');?><br><a href="http://www.ultimateblogsecurity.com/?utm_campaign=fix_issues_plugin_button"><img src="<?php echo plugins_url( 'img/fix_problems_now.png', __FILE__ ); ?>" alt="" /></a>
                </p>
                <!-- end security-check -->
                </div>
        <?php
    }
	
	add_action('admin_head', 'wp_ultimate_security_checker_load_common_js');
	add_action('wp_ajax_link_blog', 'wp_ultimate_security_checker_link_blog');
    add_action('wp_ajax_unlink_blog', 'wp_ultimate_security_checker_unlink_blog');
    add_action('wp_ajax_set_apikey', 'wp_ultimate_security_checker_set_apikey');
    add_action('wp_ajax_pro_logout', 'wp_ultimate_security_checker_pro_logout');
    add_action('wp_ajax_ajaxscreen_loader', 'wp_ultimate_security_checker_ajaxscreen_loader');
    
    function wp_ultimate_security_checker_link_blog()
    {
		check_admin_referer('ultimate-security-checker-ajaxrequest', 'csrfmiddlewaretoken');		 
		update_option('wp_ultimate_security_checker_linkedto', intval($_POST['blogid']));
		update_option('wp_ultimate_security_checker_linked_data', $_POST['blogdata']);
		exit;
	}
	
	function wp_ultimate_security_checker_unlink_blog()
    {
		check_admin_referer('ultimate-security-checker-ajaxrequest', 'csrfmiddlewaretoken');		 
		delete_option('wp_ultimate_security_checker_linkedto');
		delete_option('wp_ultimate_security_checker_linked_data');
		exit;
	}
	
	function wp_ultimate_security_checker_set_apikey()
    {
		check_admin_referer('ultimate-security-checker-ajaxrequest', 'csrfmiddlewaretoken');	
		if (isset($_POST['apikey'])) 	 
			$ret = update_site_option('wp_ultimate_security_checker_apikey', htmlspecialchars($_POST['apikey'])) ? 'ok': 'error';
		else
			$ret = 'error';
        if (isset($_POST['registered'])) 	 
			update_site_option('wp_ultimate_security_checker_registered', (bool)$_POST['registered']) ? 'ok': 'error';
        if (isset($_POST['password']))
            set_site_transient('wp_ultimate_security_checker_password', $_POST['password'], 60*60*24 ); 	 

		echo json_encode(Array('state' => $ret));
		exit;
	}
	
	function wp_ultimate_security_checker_pro_logout()
    {
		check_admin_referer('ultimate-security-checker-ajaxrequest', 'csrfmiddlewaretoken');
		$ret = delete_site_option('wp_ultimate_security_checker_apikey') ? 'ok': 'error';
		echo json_encode(Array('state' => $ret));
		exit;
	}		
	
    function wp_ultimate_security_checker_load_common_js(){

        global $current_user;
        get_currentuserinfo();
        preg_match_all("/([\._a-zA-Z0-9-]+)@[\._a-zA-Z0-9-]+/i", $current_user->user_email, $matches);
    	$email_name = $matches[1][0];					
        $url = home_url();
        if (is_multisite()) {
            $url = network_home_url();
        }

        $apikey = get_option('wp_ultimate_security_checker_apikey');
        $linkedto = get_option('wp_ultimate_security_checker_linkedto', '');
        $params['apikey'] = $apikey;
        $params['blog_url'] = get_option('siteurl');
        if ($linkedto) {
        	$params['blog_id'] = $linkedto;
        }
        $register_url = "http://beta.ultimateblogsecurity.com/api/register/";
        $get_apikey_url = "http://beta.ultimateblogsecurity.com/api/get_apikey/";
        $add_website_url = "http://beta.ultimateblogsecurity.com/api/add_website/";
        $status_url = "http://beta.ultimateblogsecurity.com/api/get_status/";
        
		?>
			<script>
				var ajax_token = "<?php echo wp_create_nonce('ultimate-security-checker-ajaxrequest'); ?>";
				var linked = "<?php echo $linkedto;?>";
                var apikey = "<?php echo $apikey;?>";
                var blogurl = "<?php echo $url;?>";
                
                var register_url = "<?php echo $register_url;?>";
                var get_apikey_url = "<?php echo $get_apikey_url;?>";
                var add_website_url = "<?php echo $add_website_url;?>";
                var status_url = "<?php echo $status_url;?>";
                
				var $ = jQuery;
				
                
                function ajax_get_screen(screen_name)
                {
				    $.ajax({
						url: ajaxurl,
						type: "POST",
						data: {csrfmiddlewaretoken: ajax_token, action:'ajaxscreen_loader', screen:screen_name},
						dataType: "html",
						success: function(data){
						  $("#ajax-content").html(data);
						},
						error: function(data){
						  $("#ajax-content").html("Error occured while ajax processing");
						}
					});
                }
                
                function ajax_get_status(success_cb, error_cb)
                {
					$.ajax({
						url: status_url,
						type: "POST",
						data: {apikey:apikey, blog_url:blogurl},
						dataType: "json",
						success: success_cb,
						error: error_cb
					});
                }
                
				function ajax_pro_logout(success_cb, error_cb) 
				{
					$.ajax({
						url: ajaxurl,
						type: "POST",
						data: {csrfmiddlewaretoken: ajax_token, action:'pro_logout'},
						dataType: "json",
						success: success_cb,
						error: error_cb
					});
				}
                
				function ajax_update_apikey(apikey, password, registered, success_cb, error_cb) 
				{
					$.ajax({
						url: ajaxurl,
						type: "POST",
						data: {csrfmiddlewaretoken: ajax_token, action:'set_apikey', apikey:apikey, password:password, registered:registered },
						dataType: "json",
						success: success_cb,
						error: error_cb
					});
				}
                
                $(document).ready(function(){
                    ajax_get_screen();
                    $("#ajax-content").delegate("#register-link", "click", function(event){
                        event.preventDefault();
                        ajax_get_screen('register');
                    });
                    $("#ajax-content").delegate("#dashboard-link", "click", function(event){
                        event.preventDefault();
                        ajax_get_screen('dashboard');
                    });
                    $("#ajax-content").delegate("#login-link", "click", function(event){
                        event.preventDefault();
                        ajax_get_screen('login');
                    });
                    $("#ajax-content").delegate("#ftp-link", "click", function(event){
                        event.preventDefault();
                        ajax_get_screen('ftp');
                    });
                    $("#ajax-content").delegate("#logout-link", "click", function(event){
                        event.preventDefault();
                        ajax_pro_logout(function(){
                            window.location.reload( true );
                        });
                    });
                });
			</script>
    <?php
	}
	
	function wp_ultimate_security_checker_current_status()
    {
		$apikey = get_option('wp_ultimate_security_checker_apikey');
		$linkedto = get_option('wp_ultimate_security_checker_linkedto', '');
		$params['apikey'] = $apikey;
		$params['blog_url'] = get_option('siteurl');		
		if ($linkedto) {
			$params['blog_id'] = $linkedto;
		}
		$status_url = sprintf("http://beta.ultimateblogsecurity.com/api/%s/?%s", "get_status", http_build_query($params));
		$find_url = sprintf("http://beta.ultimateblogsecurity.com/api/%s/?%s", "find_ftppath", http_build_query($params));
        ?>
        <div id="images"></div>
        <div class="wrap">
        <style>
        #icon-security-check {
            background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
        }
        </style>        
        <script type="text/javascript">
		jQuery(document).ready(function($) {			
			$('#select_website').submit(submit_selected_site);
			
			// auto start of info request
			$('#ajax_loading').fadeIn();
			// TODO: if linked and response is not found - reset state.
			$.ajax({
				url: "<?php echo $status_url;?>&callback=?",
				dataType: "jsonp",
				complete: function (){
					$('#ajax_loading').fadeOut();
				},
				success: function(response) {
					if (response && response.state == 'ok') {
						$('#ajax_status').show();
						var path_status = $('#path_status');
						var login_status = $('#login_status');
						if (response.data.path && response.data.verified) {
							path_status.text(response.data.path +" was successfully verified").css('color', 'green');
						} else if (!response.data.path) {	
							path_status.html('<span> was not providen yet - <a id="verify_path" href="#"> click to find</a></span>').css('color', 'red');
						} else {
							var span = document.createElement('span');
							$(span).text(response.data.path +" is not verified yet").css('color', 'orangered');
							path_status.append(span);
							path_status.append('<span> - <a id="verify_path" href="#"> verify</a></span>');
						}
						if (response.data.last_login) {	
							var status = response.data.last_login_status;
							var msg = status ? 'successful' : 'failed';
							var color = status ? 'green' : 'orangered';
							var d = new Date(response.data.last_login*1000);								
							login_status.text(msg + ' at ' + d.toLocaleString()).css('color', color);
						}						
					} else {
						var message;
						if (response.state == 'error')  {
							switch (response.errno) {
								case -2: // Blog not found
									add_website();
									return;
								case -3: // Multiple blogs found
									select_website(response.data);
									return;
								case -1: // Invalid API key									
								case -4: // Bad request
									message = response.message;
									break;
								default:
									message = 'unknown error occured';
									break;
							}
						} else {
							message = "can't connect to UBS server";
						}
						var err_message = '<p>Error: '+ message + '</p>';															
						var ajax_error = $('#ajax_error');
						if (!ajax_error.length) {
							$('#ajax_status').before('<div id="ajax_error" style="color:orangered">'+ err_message +'</div>');
							var ajax_error = $('#ajax_error');
						} else {
							ajax_error.text(err_message);
						}
						if (response.data) {
							for (item in response.data) {
								ajax_error.append('<p>' + item + ': ' + response.data[item] + '</p>');
							}		
						}
						$('#ajax_status').hide();
					}
				}
			});
			$("#verify_path").live("click", function(e){
				e.preventDefault();
				$('#ajax_loading').fadeIn();
				$.ajax({
					url: "<?php echo $find_url;?>&callback=?&path=<?php echo ABSPATH;?>",
					dataType: "jsonp",
					complete: function (){
						$('#ajax_loading').fadeOut();
					},						
					success: function(response) {
						if (response && response.state == 'ok') {
							$('#ajax_status').show();
							var path_status = $('#path_status');
							var login_status = $('#login_status');
							if (response.data.path && response.data.verified) {
								path_status.text(response.data.path +" was successfully verified").css('color', 'green');
							} else if (!response.data.path) {	
								path_status.text(' was not providen yet').css('color', 'red');
							} else {
								var span = document.createElement('span');
								$(span).text(response.data.path +" is not verified yet").css('color', 'orangered');
								path_status.append(span);
								path_status.append('<span> - <a id="verify_path" href="#"> verify</a></span>');
							}													
						} else {
							var msg = (response.state == 'error') ? response.message : "can't connect to UBS server";
							var err_message = '<p>Error: '+ msg + ' (<a id="verify_path" href="#">retry</a>) </p>';															
							var ajax_error = $('#ajax_error');
							if (!ajax_error.length) {
								$('#ajax_status').before('<div id="ajax_error" style="color:orangered">'+ err_message +'</div>');
								var ajax_error = $('#ajax_error');
							} else {
								ajax_error.text(err_message);
							}
							if (response.data) {
								for (item in response.data) {
									ajax_error.append('<p>' + item + ': ' + response.data[item] + '</p>');
								}		
							}
							$('#ajax_status').hide();
						}
					}	
				});				
			});
		});
        </script>
        <?php screen_icon( 'security-check' );?>
		<h2 style="padding-left:5px;">Ultimate Security Checker
		<span style="position:absolute;padding-left:25px;">
		<a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
		<a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
		</span>
		</h2>
		<?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
			<p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
		<?php endif; ?>
		<style>
			h3.nav-tab-wrapper .nav-tab {
				padding-top:7px;
			}
		</style>
		<h3 class="nav-tab-wrapper">
				<a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab">Run the Tests</a>
				<a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab">Files Analysis</a>
				<a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab">How to Fix</a>
				<a href="?page=ultimate-security-checker&tab=settings" class="nav-tab">Settings</a>
		</h3>
		
        <h4>Current status </h4>
		<form id="add_website" style="display: none;" action="." method="GET">
			<?php
			$apikey = get_option('wp_ultimate_security_checker_apikey');
			if ($apikey) { ?>
				<p>Seems like you didn't added your blog at ultimateblogsecurity.com so far, you can do it right now: </p>
				<input type="hidden" name="apikey" value="<?php echo htmlspecialchars($apikey);?>"/>
				<input type="hidden" name="uri" value="<?php echo get_option('siteurl');?>"/>
				<table>
					<tr>
						<td><label>What's the FTP address of your blog (example: ftp://myblog.com)?</label></td>
						<td><input type="text" name="ftphost"/></td>
					</tr>
					<tr>
						<td><label>WordPress location (see settings tab in plugin)</label></td>
						<td><input type="text" name="ftppath" value="<?php echo ABSPATH;?>"/></td>
					</tr>
					<tr>
						<td><label>What's the FTP username for your blog's FTP account?</label></td>
						<td><input type="text" name="ftpuser"/></td>
					</tr>
					<tr>
						<td><label>What's the password for your blog's FTP account?</label></td>
						<td><input type="password" name="ftppass"/></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" value="Submit" style="float:right"/></td>
					</tr>
				</table>
			<?php } else { ?>
				<p>If you already have account at ultimateblogsecurity.com - update APIKEY field in
				plugin's settings with key displayed at account info page. Otherwise, create new account first.</p>
			<?php } ?>
		</form>
		<form id="select_website" style="display:none">
			<p>
				You have multiple records in UBS dashboard for this blog.
				Please choose one, guided by it's FTP info.
			</p>
		</form>
		<table id="ajax_status">
			<tr>
				<td>Path</td><td id="path_status"></td>
			</tr>
			<tr>
				<td>Last login</td><td id="login_status"></td>
			</tr>
		</table>			
        <?php 
	}
	
    function wp_ultimate_security_checker_run_the_tests()
    {
        $security_check = new SecurityCheck();
        ?>
        
        <div class="wrap">
        <style>
        #icon-security-check {
            background: transparent url(<?php echo plugins_url( 'img/shield_32.png', __FILE__ ); ?>) no-repeat;
        }
        </style>
        
            <?php screen_icon( 'security-check' );?>
            <h2 style="padding-left:5px;">Ultimate Security Checker
            <span style="position:absolute;padding-left:25px;">
            <a href="http://www.facebook.com/pages/Ultimate-Blog-Security/141398339213582" target="_blank"><img src="<?php echo plugins_url( 'img/facebook.png', __FILE__ ); ?>" alt="" /></a>
            <a href="http://twitter.com/BlogSecure" target="_blank"><img src="<?php echo plugins_url( 'img/twitter.png', __FILE__ ); ?>" alt="" /></a>
            </span>
            </h2>
            <?php if (!get_option('wp_ultimate_security_checker_flike_deactivated')):?>
                <p style="padding-left:5px;"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FUltimate-Blog-Security%2F141398339213582&amp;layout=standard&amp;show_faces=false&amp;width=550&amp;action=recommend&amp;font=lucida+grande&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:550px; height:35px;" allowTransparency="true"></iframe></p>
            <?php endif; ?>
            <style>
                h3.nav-tab-wrapper .nav-tab {
                    padding-top:7px;
                }
            </style>
            <h3 class="nav-tab-wrapper">
                    <a href="?page=ultimate-security-checker&tab=run-the-tests" class="nav-tab nav-tab-active"><?php _e('Run the Tests');?></a>
                    <a href="?page=ultimate-security-checker&tab=wp-files" class="nav-tab"><?php _e('Files Analysis');?></a>
                    <a href="?page=ultimate-security-checker&tab=how-to-fix" class="nav-tab"><?php _e('How to Fix');?></a>
                    <a href="?page=ultimate-security-checker&tab=settings" class="nav-tab"><?php _e('Settings');?></a>
                    <!--<a href="?page=ultimate-security-checker&tab=pro" class="nav-tab"><?php _e('PRO Checks');?></a>-->
            </h3>
<!--			<p style="border:2px solid #eee;margin-left:3px;background:#f5f5f5;padding:10px;width:706px;font-size:14px;color:green;font-family:helvetica;">
				Please check out our new idea: <strong>WP AppStore</strong>. 1-click install best plugins and themes.
				<a style="color:#e05b3c;text-decoration:underline;" href="http://wordpress.org/extend/plugins/wp-appstore/" target="_blank">Check it out!</a>
			</p>-->
            <!-- <p>We are checking your blog for security right now. We won't do anything bad to your blog, relax :)</p> -->
            
            <div id="test_results">
             <?php 
                if(isset($_GET['dotest']) || get_option( 'wp_ultimate_security_checker_issues',0) == 0){
                    $security_check->run_tests(); 
                } else {
                    $security_check->get_cached_test_results(); 
                }
                
                $security_check->display_global_stats(); 
                $security_check->display_stats_by_categories($security_check->categories); 
             ?>
            </div>
            <div style="clear:both;"></div>
        </div> 
        <?php        
    }
    function wp_ultimate_security_checker_add_menu_admin_bar() {
        global $wp_admin_bar;
        if (function_exists('is_multisite') && is_multisite() && current_user_can('manage_network_options')) {
        	// Many sites, check settings to hide bar
        	if (get_option('wp_ultimate_security_checker_hide_header') == 1) {
        		// Have multisite and setting hide_header checked
				$wp_admin_bar->add_menu( array( 'id' => 'ubs_header', 'title' =>__( 'Secured by Ultimate Blog Security'), 'href' => FALSE ));
        	} else {
        		// Have multisite and setting hide_header not checked
	            if(get_option('wp_ultimate_security_checker_score') != 0){
	                $wp_admin_bar->add_menu( array( 'id' => 'theme_options', 'title' =>__( 'Security points <b style="color:'.get_option('wp_ultimate_security_checker_color').';">'.get_option('wp_ultimate_security_checker_score').'</b>', 'wp-ultimate-security-checker' ), 'href' => network_admin_url('settings.php')."?page=ultimate-security-checker" ) );
	            } else {
	                $wp_admin_bar->add_menu( array( 'id' => 'theme_options', 'title' =>__( '<span style="color:#fadd3d;">Check your blog\'s security</span>', 'wp-ultimate-security-checker' ), 'href' => network_admin_url('settings.php')."?page=ultimate-security-checker" ) );
	            }
        	}
        }elseif(function_exists('is_multisite') && !is_multisite() && current_user_can('administrator')){
        	if (get_option('wp_ultimate_security_checker_hide_header') == 1) {
        		// Have multisite and setting hide_header checked
				$wp_admin_bar->add_menu( array( 'id' => 'ubs_header', 'title' =>__( 'Secured by Ultimate Blog Security'), 'href' => FALSE ));
        	} else {
	        	// Not multisite and user is admin
	            if(get_option('wp_ultimate_security_checker_score') != 0){
	                $wp_admin_bar->add_menu( array( 'id' => 'theme_options', 'title' =>__( 'Security points <b style="color:'.get_option('wp_ultimate_security_checker_color').';">'.get_option('wp_ultimate_security_checker_score').'</b>', 'wp-ultimate-security-checker' ), 'href' => admin_url('tools.php')."?page=ultimate-security-checker" ) );
	            } else {
	                $wp_admin_bar->add_menu( array( 'id' => 'theme_options', 'title' =>__( '<span style="color:#fadd3d;">Check your blog\'s security</span>', 'wp-ultimate-security-checker' ), 'href' => admin_url('tools.php')."?page=ultimate-security-checker" ) );
	            }
	        }
        } else {
        	// We display the 'Secured by Ultimate Blog Security' header
        	$wp_admin_bar->add_menu( array( 'id' => 'ubs_header', 'title' =>__( 'Secured by Ultimate Blog Security'), 'href' => FALSE ));
        }
    }
    function wp_ultimate_security_checker_old_check(){
        /*if(isset($_GET['page'])){
            $res = explode('/',$_GET['page']);
            if($res[0] == 'ultimate-security-checker'):
            ?>
                <div class='update-nag'>Scared to upgrade to the most recent version of WordPress? Use our <b>Blog Update Service</b> for just $25. <a href="#">See details</a></div>
            <?php
            endif;
        }*/
        $period = get_option('wp_ultimate_security_checker_rescan_period');
        if ($period) {
            if((time() - get_option( 'wp_ultimate_security_checker_lastcheck',time())) > $period * 24 * 3600 ){
                switch ($period) {
                   case '14' :
                                $out = '2 weeks';
                                break;
                   case '30' :
                                $out = 'a month';
                                break;
                }
                ?>
                    <div class='update-nag'><?php printf(__("It's been more than %s since you last scanned your blog for security issues."),$out);?> <a href="<?php echo admin_url('tools.php') ?>?page=ultimate-security-checker"><?php _e('Do it now.');?></a></div>
                <?php
            }
        }           
    }
    function wp_ultimate_security_checker_failed_login_logger($username){
        $ip = wp_ultimate_security_checker_get_address();
        if (!$failed_attepts_log = get_option('wp_ultimate_security_checker_failed_login_attempts_log'))
            $failed_attepts_log = array();
        $failed_attepts_log[] = array(
        'ip' => $ip,
        'username' => $username,
        'time' => date('Y-m-d H:i:s'),
        );
        update_option('wp_ultimate_security_checker_failed_login_attempts_log', $failed_attepts_log);  
    }
    function  wp_ultimate_security_checker_get_address($type = '') {
	if (empty($type)) {
		$type = 'REMOTE_ADDR';
	}

	if (isset($_SERVER[$type])) {
		return $_SERVER[$type];
	}
    $type = 'HTTP_X_FORWARDED_FOR';
    if (isset($_SERVER[$type])) {
		return $_SERVER[$type];
	}
	return '';
}
// JSON functions    
if ( !function_exists('json_decode') ){
function json_decode($json)
{
    $comment = false;
    $out = '$x=';
  
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if (($json[$i] == '{') || ($json[$i] == '['))       $out .= ' array(';
            else if (($json[$i] == '}') || ($json[$i] == ']'))   $out .= ')';
            else if ($json[$i] == ':')    $out .= '=>';
            else                         $out .= $json[$i];          
        }
        else $out .= $json[$i];
        if ($json[$i] == '"' && $json[($i-1)]!="\\")    $comment = !$comment;
    }
    eval($out . ';');
    return $x;
}
}
if ( !function_exists('json_encode') ){
function json_encode( $data ) {            
    if( is_array($data) || is_object($data) ) { 
        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) ); 
        
        if( $islist ) { 
            $json = '[' . implode(',', array_map('__json_encode', $data) ) . ']'; 
        } else { 
            $items = Array(); 
            foreach( $data as $key => $value ) { 
                $items[] = __json_encode("$key") . ':' . __json_encode($value); 
            } 
            $json = '{' . implode(',', $items) . '}'; 
        } 
    } elseif( is_string($data) ) { 
        # Escape non-printable or Non-ASCII characters. 
        # I also put the \\ character first, as suggested in comments on the 'addclashes' page. 
        $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"'; 
        $json    = ''; 
        $len    = strlen($string); 
        # Convert UTF-8 to Hexadecimal Codepoints. 
        for( $i = 0; $i < $len; $i++ ) { 
            
            $char = $string[$i]; 
            $c1 = ord($char); 
            
            # Single byte; 
            if( $c1 <128 ) { 
                $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1); 
                continue; 
            } 
            
            # Double byte 
            $c2 = ord($string[++$i]); 
            if ( ($c1 & 32) === 0 ) { 
                $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128); 
                continue; 
            } 
            
            # Triple 
            $c3 = ord($string[++$i]); 
            if( ($c1 & 16) === 0 ) { 
                $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128)); 
                continue; 
            } 
                
            # Quadruple 
            $c4 = ord($string[++$i]); 
            if( ($c1 & 8 ) === 0 ) { 
                $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1; 
            
                $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3); 
                $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128); 
                $json .= sprintf("\\u%04x\\u%04x", $w1, $w2); 
            } 
        } 
    } else { 
        # int, floats, bools, null 
        $json = strtolower(var_export( $data, true )); 
    } 
    return $json; 
}
}
	add_action( 'admin_notices', 'wp_ultimate_security_checker_old_check' );
    // add_action('all_admin_notices','wp_ultimate_security_checker_upgrade_notice');
    add_action( 'admin_bar_menu', 'wp_ultimate_security_checker_add_menu_admin_bar' ,  70);
    add_action('admin_init', 'wp_ultimate_security_checker_admin_init');
    add_action('admin_menu', 'wp_ultimate_security_checker_admin_menu');
    add_action('wp_login_failed', 'wp_ultimate_security_checker_failed_login_logger');
?>
