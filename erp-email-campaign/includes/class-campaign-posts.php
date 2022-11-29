<?php
namespace WeDevs\ERP\CRM\EmailCampaign;

class Campaign_Posts {

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Returns all the post types
     *
     * @return array
     */
    public function get_all_post_types() {
        $post_types = [];
        $registered_types = get_post_types( [ 'public' => true ], 'objects' );

        foreach ( $registered_types as $name => $object ) {
            $post_types[ $name ] = $object->labels->name;
        }

        $ignore_types = [ 'attachment' ];

        foreach ( $ignore_types as $type ) {
            if ( isset( $post_types[ $type ] ) ) {
                unset( $post_types[ $type ] );
            }
        }

        return $post_types;
    }

    /**
     * Return a registered post type's title
     *
     * @param string $post_type
     *
     * @return string
     */
    public function get_post_type_title( $post_type ) {
        $post_type_object = get_post_type_object( $post_type );

        return $post_type_object->labels->name;
    }

    /**
     * Taxonomies and Terms list for a Post Type
     *
     * @param string $post_type
     *
     * @return array
     */
    public function get_post_type_tax_terms( $post_type ) {
        $tax_terms = [];

        $taxonomies = get_object_taxonomies( $post_type, 'object' );

        if ( !empty( $taxonomies ) ) {
            foreach ( $taxonomies as $tax => $taxObj ) {
                $terms = get_terms( [
                    'taxonomy' => $tax,
                    'hide_empty' => true,
                ] );

                if ( $terms ) {
                    $tax_terms[ $tax ]['title'] = $taxObj->labels->singular_name;

                    foreach ( $terms as $term ) {
                        $tax_terms[ $tax ]['terms'][ $term->term_id ] = $term->name;
                    }
                }
            }
        }

        return $tax_terms;
    }

    /**
     * Get WP Posts
     *
     * @param array $args WP_Query arguments
     * @param bool $details when false, returns a few params
     *
     * @return array
     */
    public function get_posts( $args, $details = true ) {
        $results = [
            'posts' => [],
            'totalPages' => 1
        ];

        $query = new \WP_Query( $args );

        if ( $query->have_posts() ) {
            if ( !array_key_exists( 'fields' , $args ) ) {
                foreach ( $query->posts as $i => $post ) {
                    $results['posts'][ $i ] = [
                        'postId' => $post->ID,
                        'title' => $post->post_title,
                        'postType' => $this->get_post_type_title( $post->post_type ),
                        'postStatus' => $post->post_status
                    ];

                    if ( $details ) {
                        $results['posts'][ $i ]['url'] = get_permalink( $post->ID );
                        $results['posts'][ $i ]['image'] = $this->get_post_image( $post->ID );
                        $results['posts'][ $i ]['content'] = $post->post_content;
                        $results['posts'][ $i ]['excerpt'] = !empty( $post->post_excerpt ) ? $post->post_excerpt : $this->make_excerpt_from_content( $post->post_content );
                    }
                }

                $results['totalPages'] = $query->max_num_pages;
            } else {
                $results['posts'] = $query->posts;
            }
        }

        wp_reset_postdata();

        return $results;
    }

    /**
     * Get featured image url
     *
     * @param int $post_id
     * @param string $size image size
     *
     * @return string image link
     */
    public function get_post_image( $post_id, $size = 'full' ) {
        $url = '';

        $image_id = get_post_meta( $post_id, '_thumbnail_id', true );

        if ( !empty( $image_id ) ) {
            $url_array = wp_get_attachment_image_src( $image_id, $size, true );
            $url = $url_array[0];
        }

        return $url;
    }


    public function make_excerpt_from_content( $content ) {
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
        $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );

        return wp_trim_words( $content, $excerpt_length, $excerpt_more );
    }
}

/**
 * Class instance
 *
 * @return object
 */
function campaign_posts() {
    return Campaign_Posts::instance();
}
