<?php
/**
 * Plugin Name:     Ultimate Member - Folder Permissions
 * Description:     Extension to Ultimate Member with a shortcode [um_folder_permissions] to list folder permissions in Active Theme's UM folders and the UM Upload folders.
 * Version:         1.4.0
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

add_shortcode( "um_folder_permissions", "um_folder_permissions_shortcode" );

function um_folder_permissions_shortcode_display( $case, $folder ) {

    switch( $case ) {

        case 'theme':
                $theme = str_replace( WP_CONTENT_DIR, '', get_stylesheet_directory());
                echo '<p><strong>...' . $theme . $folder . '</strong>';
                um_folder_permissions_shortcode_display_details( get_stylesheet_directory() . $folder );
                break;

        case 'uploads':
                $wp_upload_dir = wp_upload_dir();
                $upload_dir = str_replace( WP_CONTENT_DIR, '', $wp_upload_dir['basedir']);
                echo '<p><strong>...' . $upload_dir . $folder . '</strong>';
                um_folder_permissions_shortcode_display_details( $wp_upload_dir['basedir'] . $folder );
                break;
    }
}

function um_folder_permissions_shortcode_display_details( $folder ) {

    if( file_exists( $folder )) {
        
        echo '</p>';

        if( file_exists( $folder . '/.htaccess' )) {
            echo sprintf( '<p><strong>WARNING</strong>: .htacces file exists with filesize %d bytes</p>', filesize( $folder . '/.htaccess' ));
        }

        echo '<p>';
        echo 'folder permission=' . substr( sprintf( '%o', fileperms( $folder )), -4 ) . ' octal<br>';

        $stat = @stat( $folder );
        echo 'UID=' . $stat['uid'];
        echo ' GID=' . $stat['gid'] . '<br>';

        $files = new DirectoryIterator( $folder . '/' );
        $permissions = array();

        foreach ( $files as $file ) {
            if ( $file->isDot() || !$file->isFile() ) continue;
            $key = substr( sprintf( '%o', fileperms( $file->getPathname() )), -4 );
            if( !isset( $permissions[$key])) $permissions[$key] = 1;
            else $permissions[$key]++;            
        }

        foreach( $permissions as $key => $count ) {
            echo 'file permission ' . $key . ' count=' . $count . ' files<br>'; 
        }
        echo '</p>';

    } else {
        echo ' folder not found</p>';
    }
}

function um_folder_permissions_shortcode() {

    global $current_user;
    ob_start();

    echo "<h4>UM folder permissions 1.4.0</h4>";

    if ( !current_user_can( 'administrator' )) { 

        echo 'Administrators access only';
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
    
    echo '<p><strong>WP Standard permissions:</strong><br>Folder permission not less than 0755 octal<br>File permission not less than 0644 octal</p>';

    if( !empty( array_intersect( array_map( 'strtolower', get_loaded_extensions()), array( 'mod_security', 'mod security' )))) {
        echo( '<p><strong>WARNING</strong>: MOD SECURITY is active</p>' );
    }
    if( extension_loaded( 'suhosin' )) echo '<p><strong>WARNING</strong>: SUHOSIN is active</p>';

    $basedir = ini_get( 'open_basedir' );
    if( !empty( $basedir )) echo '<p><strong>WARNING</strong>: open_basedir is active: ' . $basedir . '</p>';

    echo '<p>';
    echo 'ABSPATH length in characters: ' . strlen( ABSPATH ) . '<br>';
    echo 'get_template: ' . get_template() . '<br>';    
    echo 'get_stylesheet: ' . get_stylesheet() . '<br>';
    echo 'get_theme_root: ABSPATH/' . str_replace( ABSPATH, '', get_theme_root( get_stylesheet() )) . '<br>';
    $get_stylesheet_directory = get_stylesheet_directory();
    //echo 'get_stylesheet_directory: ' . $get_stylesheet_directory . '<br>';
    echo 'get_stylesheet_directory: ABSPATH/' . str_replace( ABSPATH, '', $get_stylesheet_directory ) . '<br>';
    if( empty( $get_stylesheet_directory ) || $get_stylesheet_directory == '/' ) {
        echo '</p>';
        echo '<p><strong>ERROR</strong>: The themes directory is either empty or does not exist.<br>
              Please check your installation.<br>
              WP is returning an empty get_stylesheet_directory.</p>';

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
    
    echo 'all themes: ' . implode( ', ', array_keys( search_theme_directories())) . '<br>';
    echo 'child-theme active: ' . ( is_child_theme()? 'yes':'no' ) . '<br>';
    echo 'UM active: ' . ( class_exists( 'UM' )? 'yes':'no' ) . '<br>';

    if( file_exists( $get_stylesheet_directory . '/functions.php' )) {
        $functions_content = strtolower( file_get_contents( $get_stylesheet_directory . '/functions.php' ));
        echo 'active functions.php add_action count: ' . substr_count( $functions_content, 'add_action' ) . '<br>';
        echo 'active functions.php add_filter count: ' . substr_count( $functions_content, 'add_filter' ) . '<br>';
        echo 'active functions.php remove_action count: ' . substr_count( $functions_content, 'remove_action' ) . '<br>';
        echo 'active functions.php remove_filter count: ' . substr_count( $functions_content, 'remove_filter' ) . '<br>';
    } else echo 'active functions.php not found<br>';

    echo 'Code Snippets plugin active: ' . ( defined( 'CODE_SNIPPETS_FILE' )? 'yes':'no' ) . '<br>';
    echo '</p>';

    um_folder_permissions_shortcode_display( 'theme', '' );
    um_folder_permissions_shortcode_display( 'theme', '/ultimate-member' );
    um_folder_permissions_shortcode_display( 'theme', '/ultimate-member/email' );       
    um_folder_permissions_shortcode_display( 'theme', '/ultimate-member/templates' );       
    um_folder_permissions_shortcode_display( 'theme', '/ultimate-member/profile' );       

    if( is_multisite()) {
        um_folder_permissions_shortcode_display( 'theme', '/ultimate-member/email/' . get_current_blog_id()); 
    }

    um_folder_permissions_shortcode_display( 'uploads', '' );
    um_folder_permissions_shortcode_display( 'uploads', '/ultimatemember' );
    um_folder_permissions_shortcode_display( 'uploads', '/ultimatemember/temp' );

    if( isset( $current_user ) && !empty( $current_user->ID )) {
        um_folder_permissions_shortcode_display( 'uploads', '/ultimatemember/' . $current_user->ID );
    }

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}
