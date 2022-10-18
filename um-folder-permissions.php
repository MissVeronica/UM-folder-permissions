<?php
/**
 * Plugin Name:     Ultimate Member - Folder Permissions
 * Description:     Extension to Ultimate Member with a shortcode [um_folder_permissions] to list folder permissions in Active Theme's UM folders and the UM Upload folders.
 * Version:         1.0.0
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
if ( ! class_exists( 'UM' ) ) return;

add_shortcode( "um_folder_permissions", "um_folder_permissions_shortcode" );

function um_folder_permissions_shortcode_display( $case, $folder ) {

    switch( $case ) {

        case 'theme':
                $theme = explode( '/wp-content', get_stylesheet_directory());
                echo '<p><strong>...' . $theme[1] . $folder . '</strong>';
                um_folder_permissions_shortcode_display_details( get_stylesheet_directory() . $folder );
                break;

        case 'uploads':
                $folder = '/uploads' . $folder;
                echo '<p><strong>...' . $folder . '</strong>';
                um_folder_permissions_shortcode_display_details( WP_CONTENT_DIR . $folder );
                break;
    }
}

function um_folder_permissions_shortcode_display_details( $folder ) {

    if( file_exists( $folder )) {
        echo '</p>';
        echo '<p>folder permission=' . substr( sprintf( '%o', fileperms( $folder )), -4 ) . ' octal</p>';

        if( file_exists( $folder . '/.htaccess' )) {
            echo sprintf( '<p>WARNING: .htacces file exists with filesize %d bytes</p>', filesize( $folder . '/.htaccess' ));
        }

        $stat = @stat( $folder );
        echo '<p>UID=' . $stat['uid'];
        echo '   GID=' . $stat['gid'] . '</p>';

        $files = new DirectoryIterator( $folder . '/' );
        $permissions = array();

        foreach ( $files as $file ) {
            if ( $file->isDot() || !$file->isFile() ) continue;
            $key = substr( sprintf( '%o', fileperms( $file->getPathname() )), -4 );
            if( !isset( $permissions[$key])) $permissions[$key] = 1;
            else $permissions[$key]++;            
        }

        foreach( $permissions as $key => $count ) {
            echo '<p>file permission ' . $key . ' count=' . $count . ' files</p>'; 
        }

    } else {
        echo ' folder not found</p>';
    }
}

function um_folder_permissions_shortcode() {

    global $current_user;
    ob_start();

    echo "<h4>UM folder permissions</h4>";
    echo '<p<strong>WP Standard permissions:</strong><br>Folder permission not less than 0755 octal<br>File permission not less than 0644 octal</p>';

    if( !empty( array_intersect( array_map( 'strtolower', get_loaded_extensions()), array( 'mod_security', 'mod security' )))) {
        echo( '<p>WARNING: MOD SECURITY is active</p>' );
    }
    if( extension_loaded( 'suhosin' )) echo '<p>WARNING: SUHOSIN is active</p>';

    $basedir = ini_get( 'open_basedir' );
    if( !empty( $basedir )) echo '<p>WARNING: open_basedir is active: ' . $basedir . '</p>';

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
    um_folder_permissions_shortcode_display( 'uploads', '/ultimatemember/' . $current_user->ID );
    um_folder_permissions_shortcode_display( 'uploads', '/ultimatemember/temp' );

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}
