<?php
/**
 * Plugin Name:     Ultimate Member - Folder Permissions
 * Description:     Extension to Ultimate Member with a shortcode [um_folder_permissions] to list folder permissions in Active Theme's UM folders and the UM Upload folders.
 * Version:         2.2.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

class UM_Folder_Permissions {

    public $templates             = array();
    public $templates_update      = array();
    public $child                 = false;
    public $uploads_folder_exists = true;

	public function __construct() {

        add_shortcode( "um_folder_permissions", array( $this, "um_folder_permissions_shortcode" ));

        $this->templates[DIRECTORY_SEPARATOR . 'ultimate-member'] = array( 'login-to-view.php', 'members-grid.php', 'members-header.php',
                                                                           'members-list.php', 'members-pagination.php', 'searchform.php' );

        $this->templates[DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'email'] = array(  'deletion_email.php', 'notification_review.php', 'changedaccount_email.php',
                                                                                                            'rejected_email.php', 'notification_deletion.php', 'checkmail_email.php',
                                                                                                            'resetpw_email.php', 'welcome_email.php', 'changedpw_email.php', 'notification_new_user.php',
                                                                                                            'inactive_email.php', 'approved_email.php', 'pending_email.php' );

        $this->templates[DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'email'] = $this->templates[DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'email'];

        $this->templates[DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'templates'] = array(  'account.php', 'gdpr-register.php', 'login.php',
                                                                                                                'logout.php', 'members.php', 'message.php', 'password-change.php',
                                                                                                                'password-reset.php', 'profile.php', 'register.php' );

        $this->templates[DIRECTORY_SEPARATOR. 'ultimate-member' . DIRECTORY_SEPARATOR . 'profile'] =   array( 'comments.php', 'comments-single.php', 'posts.php', 'posts-single.php' );

        $this->templates_update = array('members.php'         => ' Updated UM 2.2.0',  
                                        'password-change.php' => ' Updated UM 2.5.0', 
                                        'password-reset.php'  => ' Updated UM 2.5.0',  
                                        'members-grid.php'    => ' Updated UM 2.3.0',
                                        'members-list.php'    => ' Updated UM 2.3.0',
                                      );
    }

    public function um_folder_permissions_shortcode_display( $case, $folder ) {

        echo '<div style="padding-top: 10px;"><strong>...';

        switch( $case ) {

            case 'theme':
                    $theme = str_replace( WP_CONTENT_DIR, '', get_stylesheet_directory());
                    echo esc_html( $theme . $folder ) . '</strong></div>';
                    $this->um_folder_permissions_shortcode_display_details( get_stylesheet_directory(), $folder );

                    if( $folder == DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'email' ) {
                        
                        if( $this->child ) {
                            echo '<div style="padding-top: 10px;"><strong>...';
                            $theme = str_replace( WP_CONTENT_DIR, '', get_template());
                            echo esc_html( $theme . $folder ) . '</strong></div>';
                            $this->um_folder_permissions_shortcode_display_details( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme, $folder );
                        }

                        echo '<div style="padding-top: 10px;"><strong>...';
                        $theme = DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'ultimate-member';
                        $folder = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'email';
                        echo esc_html( $theme . $folder ) . '</strong></div>';
                        $this->um_folder_permissions_shortcode_display_details( WP_CONTENT_DIR . $theme, $folder );
                    }
                    break;

            case 'uploads':
                    $wp_upload_dir = wp_upload_dir();
                    $upload_dir = esc_html( str_replace( WP_CONTENT_DIR, '', $wp_upload_dir['basedir']));
                    echo esc_html( $upload_dir . $folder ) . '</strong></div>';
                    $this->um_folder_permissions_shortcode_display_details( $wp_upload_dir['basedir'], $folder );
                    break;
        }        
    }

    public function display_table_row( $text1, $text2 = '', $text3 = false, $text4 = false ) {

        switch( $text1 ) {
            case 'start_table': echo '<div style="padding-top: 10px;">' . $text2 . '</div>
                                      <div style="display:table">';
                                break;

            case 'end_table':   echo '</div>'; break;

            default:            echo '<div style="display: table-row;">
                                        <div style="display: table-cell; padding-right: 10px;">' . $text1 . '</div>
                                        <div style="display: table-cell;">' . $text2 . '</div>';
                                        if( $text3 ) {
                                            echo '<div style="display: table-cell; padding-left: 10px;">' . $text3 . '</div>';
                                        }
                                        if( $text4 ) {
                                            echo '<div style="display: table-cell; padding-left: 10px;">' . $text4 . '</div>';
                                        }
                                echo '</div>';
                                break;
        }
    }

    public function display_text_line( $text, $status = false ) {

        echo '<div style="padding-top: 10px;">';
        if( $status ) echo '<strong>' . $status . '</strong>: ';
        echo $text . '</div>';
    }

    public function um_folder_permissions_shortcode_display_details( $path, $folder ) {

        $folder_path = $path . $folder;
        if( file_exists( $folder_path )) {

            if( file_exists( $folder_path . DIRECTORY_SEPARATOR . '.htaccess' )) {
                $this->display_text_line( sprintf( '.htacces filesize %d bytes updated %s', 
                                          esc_html( filesize( $folder_path . DIRECTORY_SEPARATOR . '.htaccess' )), 
                                          esc_html( date_i18n( 'Y-m-d H:i:s', filemtime( $folder_path . DIRECTORY_SEPARATOR . '.htaccess' ))) ), 
                                          'WARNING' );
            }

            $stat = @stat( $folder_path );
            if( $stat['uid'] == 0 && $stat['gid'] == 0 ) $windows = ' valid for windows';
            else $windows = '';

            $this->display_table_row( 'start_table', 'Folder' );
            $this->display_table_row( 'Permission',   esc_html( substr( sprintf( '%o', fileperms( $folder_path )), -4 )));
            $this->display_table_row( 'Updated',      esc_html( date_i18n( 'Y-m-d H:i:s', filemtime( $folder_path ))));
            $this->display_table_row( 'UID',          $stat['uid'] . $windows );
            $this->display_table_row( 'GID',          $stat['gid'] . $windows );
            $this->display_table_row( 'end_table' );

            $files = new DirectoryIterator( $folder_path . DIRECTORY_SEPARATOR );

            $count_templates = 0;
            foreach( $files as $file ) {
                if ( $file->isDot() || !$file->isFile() ) continue;
                $count_templates++;
            }

            if( $count_templates > 0 ) {

                if( !empty( $folder ) && isset( $this->templates[$folder] )) {
                    
                    $this->display_table_row( 'start_table', $count_templates . ' Templates' );

                    foreach( $files as $file ) {
                        if ( $file->isDot() || !$file->isFile() ) continue;
                        if( array_key_exists( $file->getFilename(), $this->templates_update )) $upd = $this->templates_update[$file->getFilename()];
                        else $upd = '';            
                        $this->display_table_row( in_array( $file->getFilename(), $this->templates[$folder] )? 'UM': 'Custom', 
                                                esc_html( $file->getFilename() ), 
                                                esc_html( substr( sprintf( '%o', $file->getPerms()), -4 )),
                                                esc_html( date_i18n( 'Y-m-d H:i:s', $file->getMTime()) . $upd ));
                    }

                    $this->display_table_row( 'end_table' );
            
                } else {

                    $permissions = array();
                    $counter = 0;
        
                    foreach( $files as $file ) {
                        if ( $file->isDot() || !$file->isFile() ) continue;
                        $key = substr( sprintf( '%o', $file->getPerms()), -4 );
                        if( !isset( $permissions[$key])) $permissions[$key] = 1;
                        else $permissions[$key]++;
                        $counter++;
                    }
        
                    if( $counter > 0 ) {
        
                        $this->display_table_row( 'start_table', 'Total of ' . $counter . ' files in this folder with these permissions' );
        
                        foreach( $permissions as $key => $count ) {
                            $this->display_table_row( esc_html( $key ), esc_html( $count ) );
                        }
        
                        $this->display_table_row( 'end_table' );
        
                    } else $this->display_text_line( 'No files in this folder' );
                }

            } else {

                $this->display_text_line( 'No templates found' );
            }

        } else {

            $this->display_text_line( 'Folder not found' );
            if( $folder == DIRECTORY_SEPARATOR . 'ultimatemember' ) $this->uploads_folder_exists = false;
        }
    }

    public function um_folder_permissions_shortcode() {

        global $current_user;
        ob_start();

        echo "<h4>UM folder permissions 2.2.0</h4>";

        if ( !current_user_can( 'administrator' )) { 

            $this->display_text_line( 'Administrators access only', 'ERROR' );
            return ob_get_clean();
        }
        
        $this->display_text_line( '<br>Folder permission not less than 0755 octal<br>
                                       File permission not less than 0644 octal', 'WP Standard permissions' );

        if( !empty( array_intersect( array_map( 'strtolower', get_loaded_extensions()), array( 'mod_security', 'mod security' )))) {
            $this->display_text_line( 'MOD SECURITY is active', 'WARNING' );
        }

        if( extension_loaded( 'suhosin' )) {
            $this->display_text_line( 'SUHOSIN is active', 'WARNING' );
        }

        $basedir = ini_get( 'open_basedir' );
        if( !empty( $basedir )) {
            $this->display_text_line( 'open_basedir is active: ' . esc_html( $basedir ), 'WARNING' );
        }        

        $get_stylesheet_directory = get_stylesheet_directory();

        $this->display_table_row( 'start_table' );
        $this->display_table_row( 'get_template',   esc_html( get_template()) );
        $this->display_table_row( 'get_stylesheet', esc_html( get_stylesheet()) );
        $this->display_table_row( 'get_theme_root',           'ABSPATH' . DIRECTORY_SEPARATOR . esc_html( str_replace( ABSPATH, '', get_theme_root( get_stylesheet() ))) );
        $this->display_table_row( 'get_stylesheet_directory', 'ABSPATH' . DIRECTORY_SEPARATOR . esc_html( str_replace( ABSPATH, '', $get_stylesheet_directory )) );
        $this->display_table_row( 'end_table' );

        if( get_template() == 'oxygen-is-not-a-theme' && get_stylesheet() == 'fake' ) {

            $this->display_text_line( 'You are using Oxygen Builder.<br>
                                       Install the UM Oxygen Builder plugin.', 'NOTICE' );
            return ob_get_clean();
        }

        if( empty( $get_stylesheet_directory ) || $get_stylesheet_directory == DIRECTORY_SEPARATOR ) {

            $this->display_text_line( 'The themes directory is either empty or does not exist.<br>
                                       Please check your installation.<br>
                                       WP is returning an empty get_stylesheet_directory.', 'ERROR' );
            return ob_get_clean();
        }
        
        $this->display_table_row( 'start_table', 'Themes' );
        $this->child = false;

        foreach( array_keys( search_theme_directories()) as $theme ) {

            $display_theme = wp_get_theme( $theme );
            if( $display_theme->exists() ) {        

                if( strpos( $theme, '-child') > 0 ) $this->child = true;
                $this->display_table_row( esc_html( $theme ), 
                                          esc_html( $display_theme->get( 'Name' )), 
                                          esc_html( substr( $display_theme->get( 'Version' ), 0, 5 )), 
                                          !empty( $display_theme->parent() )? esc_html( 'Parent: ' . $display_theme->parent()): '' );
            } else {

                $this->display_table_row( esc_html( $theme ), 'not found' );
            }
        }

        $this->display_table_row( 'end_table' );

        if( !$this->child ) {
            $this->display_text_line( 'Try to create a child theme by using the<br>
                                       <a href="https://wordpress.org/plugins/child-theme-configurator/" 
                                       target="_blank">Child Theme Configurator</a> plugin.', 'NOTICE' );
        }

        $php_memory_limit = ini_get( 'memory_limit' );
        if( $php_memory_limit == -1 ) {
            $php_memory_limit = 'No PHP memory limit';
        } else {
            if( is_int( $php_memory_limit )) $php_memory_limit = $php_memory_limit/1024/1024 . 'M';
        }

        $this->display_table_row( 'start_table' );
        $this->display_table_row( 'Child-theme active',          is_child_theme()?                'yes':'no' );
        $this->display_table_row( 'UM active',                   class_exists( 'UM' )?            'yes version ' . ultimatemember_version:'no' );
        $this->display_table_row( 'UM update installed',         esc_html( date_i18n( 'Y-m-d H:i:s', filemtime( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'ultimate-member' ))) );
        $this->display_table_row( 'Code Snippets plugin active', defined( 'CODE_SNIPPETS_FILE' )? 'yes':'no' );
        $this->display_table_row( 'Multi site',                  is_multisite()?                  'yes':'no' );
        $this->display_table_row( 'WP Version',					 get_bloginfo( 'version' ));
        $this->display_table_row( 'PHP Version',              	 PHP_VERSION );
        $this->display_table_row( 'PHP built on OS',             PHP_OS );
        $this->display_table_row( 'PHP server API',              PHP_SAPI );
        $this->display_table_row( 'Web Server',          		 $_SERVER['SERVER_SOFTWARE'] );
        $this->display_table_row( 'WP Frontend Memory Limit',    WP_MEMORY_LIMIT );
        $this->display_table_row( 'WP Backend Memory Limit',     WP_MAX_MEMORY_LIMIT );
        $this->display_table_row( 'PHP Memory Limit',         	 $php_memory_limit );
        $this->display_table_row( 'Memory Limit allows raising', wp_is_ini_value_changeable( 'memory_limit' )? 'yes':'no' );
        $this->display_table_row( 'ABSPATH character length',    strlen( ABSPATH ));
        //$this->display_table_row( 'GD Library imagerotate',      function_exists( 'imagerotate' )?  'yes':'no' );
        //$this->display_table_row( 'GD Library imagecrop',        function_exists( 'imagecrop' )?    'yes':'no' );
        
        $this->display_table_row( 'end_table' );

        if( file_exists( $get_stylesheet_directory . DIRECTORY_SEPARATOR . 'functions.php' )) {

            $functions_content = strtolower( file_get_contents( $get_stylesheet_directory . DIRECTORY_SEPARATOR . 'functions.php' ));

            $this->display_table_row( 'start_table', 'Active theme\'s functions.php' );
            $this->display_table_row( 'Last update',   esc_html( date_i18n( 'Y-m-d H:i:s', filemtime( $get_stylesheet_directory . DIRECTORY_SEPARATOR . 'functions.php' ))) );
            $this->display_table_row( 'File size',     esc_html( intval( filesize( $get_stylesheet_directory . DIRECTORY_SEPARATOR . 'functions.php' )/1024 ) . 'Kb' ));
            $this->display_table_row( 'add_action',    substr_count( $functions_content, 'add_action' ));
            $this->display_table_row( 'add_filter',    substr_count( $functions_content, 'add_filter' ));
            $this->display_table_row( 'remove_action', substr_count( $functions_content, 'remove_action' ));
            $this->display_table_row( 'remove_filter', substr_count( $functions_content, 'remove_filter' ));
            $this->display_table_row( 'do_action',     substr_count( $functions_content, 'do_action' ));
            $this->display_table_row( 'apply_filters', substr_count( $functions_content, 'apply_filters' ));
            $this->display_table_row( 'add_shortcode', substr_count( $functions_content, 'add_shortcode' ));
            $this->display_table_row( 'function',      substr_count( $functions_content, 'function ' ));
            $this->display_table_row( 'end_table' );

            $this->display_table_row( 'start_table', 'Deprecated: UM 2.5.0 and removed since UM 2.7.0' );
            $this->display_table_row( 'UM()->query()->get_users_by_status',    substr_count( $functions_content, 'UM()->query()->get_users_by_status' ));
            $this->display_table_row( 'UM()->user()->get_pending_users_count', substr_count( $functions_content, 'UM()->user()->get_pending_users_count' ));
            $this->display_table_row( 'UM()->user()->remove_cached_queue',     substr_count( $functions_content, 'UM()->user()->remove_cached_queue' ));            
            $this->display_table_row( 'end_table' );

        } else $this->display_text_line( 'Active theme\'s functions.php not found' );

        $this->um_folder_permissions_shortcode_display( 'theme', '' );
        $this->um_folder_permissions_shortcode_display( 'theme', DIRECTORY_SEPARATOR . 'ultimate-member' );
        $this->um_folder_permissions_shortcode_display( 'theme', DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'email' );       
        $this->um_folder_permissions_shortcode_display( 'theme', DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'templates' );       
        $this->um_folder_permissions_shortcode_display( 'theme', DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'profile' );       

        if( is_multisite()) {
            $this->um_folder_permissions_shortcode_display( 'theme', DIRECTORY_SEPARATOR . 'ultimate-member' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . get_current_blog_id()); 
        }

        $this->um_folder_permissions_shortcode_display( 'uploads', '' );
        $this->um_folder_permissions_shortcode_display( 'uploads', DIRECTORY_SEPARATOR . 'ultimatemember' );

        if( $this->uploads_folder_exists ) {
            $this->um_folder_permissions_shortcode_display( 'uploads', DIRECTORY_SEPARATOR . 'ultimatemember' . DIRECTORY_SEPARATOR . 'temp' );

            if( isset( $current_user ) && !empty( $current_user->ID )) {
                $this->um_folder_permissions_shortcode_display( 'uploads', DIRECTORY_SEPARATOR . 'ultimatemember' . DIRECTORY_SEPARATOR . $current_user->ID );
            }
        }

        return ob_get_clean();
    }
}

new UM_Folder_Permissions();
