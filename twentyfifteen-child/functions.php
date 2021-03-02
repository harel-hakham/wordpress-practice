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

if ( !class_exists('myCustomFields') ) {
    class myCustomFields {
        var $prefix = '_mcf_';
        var $postTypes = array(  "wp_product" );
        var $customFields = array(
            array(
                "name"          => "product-description",
                "title"         => "product description",
                "description"   => "product description",
                "type"          => "textarea",
                "scope"         =>   array( "wp_product" ),
                "capability"    => "edit_posts"),
            array(
                "name"          => "product-price",
                "title"         => "product price",
                "description"   => "product price",
                "type"          =>   "text",
                "scope"         =>   array( "wp_product" ),
                "capability"    => "edit_posts" ),
            array(
                "name"          => "is_on_sale",
                "title"         => "Is on sale",
                "description"   => "Is on sale",
                "type"          => "checkbox",
                "scope"         =>   array( "wp_product" ),
                "capability"    => "manage_options"),
            array(
                "name"          => "product-link",
                "title"         => "product video",
                "description"   => "product video",
                "type"          =>   "text",
                "scope"         =>   array( "wp_product" ),
                "capability"    => "edit_posts")
        );

        function myCustomFields() {
            $this->__construct();
        }

        function __construct() {
            add_action( 'admin_menu', array( $this, 'createCustomFields' ) );
            add_action( 'save_post', array( $this, 'saveCustomFields' ), 1, 2 );
            // Comment this line out if you want to keep default custom fields meta box
            add_action( 'do_meta_boxes', array( $this, 'removeDefaultCustomFields' ), 10, 3 );
        }

        function removeDefaultCustomFields( $type, $context, $post ) {
            foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
                foreach ( $this->postTypes as $postType ) {
                    remove_meta_box( 'postcustom', $postType, $context );
                }
            }
        }

        function createCustomFields() {
            if ( function_exists( 'add_meta_box' ) ) {
                foreach ( $this->postTypes as $postType ) {
                    add_meta_box( 'my-custom-fields', 'Custom Fields', array( $this, 'displayCustomFields' ), $postType, 'normal', 'high' );
                }
            }
        }

        function displayCustomFields() {
            global $post;
            ?>
            <div class="form-wrap">
                <?php
                wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );
                foreach ( $this->customFields as $customField ) {
                    // Check scope
                    $scope = $customField[ 'scope' ];
                    $output = false;
                    foreach ( $scope as $scopeItem ) {
                        switch ( $scopeItem ) {
                            default: {
                                if ( $post->post_type == $scopeItem )
                                    $output = true;
                                break;
                            }
                        }
                        if ( $output ) break;
                    }
                    // Check capability
                    if ( !current_user_can( $customField['capability'], $post->ID ) )
                        $output = false;
                    // Output if allowed
                    if ( $output ) { ?>
                        <div class="form-field form-required">
                            <?php
                            switch ( $customField[ 'type' ] ) {
                                case "checkbox": {
                                    // Checkbox
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label>';
                                    echo '<input type="checkbox" name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="yes"';
                                    if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )
                                        echo ' checked="checked"';
                                    echo '" style="width: auto;" />';
                                    break;
                                }
                                case "textarea":
                                case "wysiwyg": {
                                    // Text area
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
                                    echo '<textarea name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '</textarea>';
                                    // WYSIWYG
                                    if ( $customField[ 'type' ] == "wysiwyg" ) { ?>
                                        <script type="text/javascript">
                                            jQuery( document ).ready( function() {
                                                jQuery( "<?php echo $this->prefix . $customField[ 'name' ]; ?>" ).addClass( "mceEditor" );
                                                if ( typeof( tinyMCE ) == "object" and typeof( tinyMCE.execCommand ) == "function" ) {
                                                    tinyMCE.execCommand( "mceAddControl", false, "<?php echo $this->prefix . $customField[ 'name' ]; ?>" );
                                                }
                                            });
                                        </script>
                                    <?php }
                                    break;
                                }
                                default: {
                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
                                    echo '<input type="text" name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';
                                    break;
                                }
                            }
                            ?>
                            <?php if ( $customField[ 'description' ] ) echo '<p>' . $customField[ 'description' ] . '</p>'; ?>
                        </div>
                        <?php
                    }
                } ?>
            </div>
            <?php
        }

        function saveCustomFields( $post_id, $post ) {
            if ( !isset( $_POST[ 'my-custom-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) )
                return;
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
            if ( ! in_array( $post->post_type, $this->postTypes ) )
                return;
            foreach ( $this->customFields as $customField ) {
                if ( current_user_can( $customField['capability'], $post_id ) ) {
                    if ( isset( $_POST[ $this->prefix . $customField['name'] ] ) and trim( $_POST[ $this->prefix . $customField['name'] ] ) ) {
                        $value = $_POST[ $this->prefix . $customField['name'] ];

                        if ( $customField['type'] == "wysiwyg" ) $value = wpautop( $value );
                        update_post_meta( $post_id, $this->prefix . $customField[ 'name' ], $value );
                    } else {
                        delete_post_meta( $post_id, $this->prefix . $customField[ 'name' ] );
                    }
                }
            }
        }

    }

}


if ( class_exists('myCustomFields') ) {
    $myCustomFields_var = new myCustomFields();
}

add_theme_support( 'post-thumbnails' );
