<?php
//add style from the parent
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles(){
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

//remove toll bar for  wp-test
if (!function_exists('disable_admin_bar')) {
    function disable_admin_bar() {
        if (get_current_user_id() == 2) {
            remove_action('admin_footer', 'wp_admin_bar_render', 1000);
            remove_action('wp_footer', 'wp_admin_bar_render', 1000);

            function remove_admin_bar_style_backend() {
                echo '<style>body.admin-bar #wpcontent, body.admin-bar #adminmenu { padding-top: 0px !important; }</style>';
            }
            add_filter('admin_head','remove_admin_bar_style_backend');

            function remove_admin_bar_style_frontend() {
                echo '<style type="text/css" media="screen"> html { margin-top: 0px !important; }  * html body { margin-top: 0px !important; }</style>';
            }
            add_filter('wp_head','remove_admin_bar_style_frontend', 99);
        }
    }
}
add_action('init','disable_admin_bar');


//add castom post type named Products
function create_posttype() {
    register_post_type( 'wp_product',
        array(
            'labels' => array(
                'name' => __( 'Products' ),
                'singular_name' => __( 'Product' )
            ),
            'public' => true,
            'taxonomies' => array('category', 'post_tag'),
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
        )
    );
}
add_action( 'init', 'create_posttype' );

