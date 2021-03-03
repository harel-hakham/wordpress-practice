<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
            <div id="btnContainer">
                <button class="btn" onclick="listView()"><i class="fa fa-bars"></i> List</button>
                <button class="btn active" onclick="gridView()"><i class="fa fa-th-large"></i> Grid</button>
            </div>
            <br>

            <div class="row">
		<?php


        $args = array('post_type'=> 'wp_product','post_status' => 'publish','posts_per_page' =>-1,'order'    => 'ASC');
        $the_query = new WP_Query( $args );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $attachment_image = wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'post-thumbnail' );
		    $sale_status ="";
            $key_1_values = get_post_meta( $post->ID, '_mcf_is_on_sale' );
           if($key_1_values[0]=='yes') {
               $sale_status ="<span class=\"sale\">in sale </span>";
           }
           echo "<div class=\"column\" style=\"background-color:#aaa;\">". $sale_status."
                     <div>
                        <img src=\"".$attachment_image." \" style=\"width: 240px;height: 180px;\">
                    </div>
                    <div>
                        <h2>".get_the_title()."</h2>
                        <p>".get_the_excerpt()."</p>
                    </div>

                </div>";
        endwhile;
        wp_reset_postdata();
        ?>



            </div>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
    <script>
        // Get the elements with class='column"
        var elements = document.getElementsByClassName("column");

        // Declare a loop variable
        var i;

        // List View
        function listView() {
            for (i = 0; i < elements.length; i++) {
                elements[i].style.width = "100%";
            }
        }

        // Grid View
        function gridView() {
            for (i = 0; i < elements.length; i++) {
                elements[i].style.width = "50%";
            }
        }

        /* Optional: Add active class to the current button (highlight it) */
        var container = document.getElementById("btnContainer");
        var btns = container.getElementsByClassName("btn");
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener("click", function() {
                var current = document.getElementsByClassName("active");
                current[0].className = current[0].className.replace(" active", "");
                this.className += " active";
            });
        }
    </script>

<?php get_footer(); ?>
