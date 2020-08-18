<?php
/*
Plugin Name: Horror Movie API Example Plugin v1
Plugin URI: https://example.com/
Description: Example using the HTTP API to parse JSON from a remote horror movie API
Author: WROX
Author URI: http://wrox.com
*/

// Register custom post type
add_action( 'init', 'ps_register_custom_post_type' );

// Register the movies custom post type
function ps_register_custom_post_type() {
 
    register_post_type( 'movie',
        array(
            'labels' => array(
                'name' => 'Movies',
                'singular_name' => 'Movie'
            ),
            'supports'  => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array( 'slug' => 'movies' ),
            'show_in_rest' => true,
        )
    );
}

// Register a custom page for your plugin
add_action( 'admin_menu', 'ps_create_menu' );
             
function ps_create_menu() {
             
    // Create custom top-level menu
    add_menu_page( 'Movies Page', 'Movies', 'manage_options', 'ps-movies', 'ps_movie_api_results', 'dashicons-smiley', 99 );
             
}

// Request and display Movie API data
function ps_movie_api_results() {

    // Set your API URL
    $request = wp_remote_get( 'https://sampleapis.com/movies/api/horror' );

    // If an error is returned, return false to end the request
    if( is_wp_error( $request ) ) {
        return false;
    }

    // Retrieve only the body from the raw response
    $body = wp_remote_retrieve_body( $request );

    // Decode the JSON string
    $data = json_decode( $body );

    // Verify the $data variable is not empty
    if( ! empty( $data ) ) {
        
        echo '<ul>';

        // Loop through the returned dataset 
        foreach( $data as $movies ) {

            echo '<li>';
                echo 'Movie imported: ';
                echo '<a href="https://www.imdb.com/title/' . esc_attr( $movies->imdbId ) . '" target="_blank">';
                echo esc_html( $movies->title );
                echo '</a>';
            echo '</li>';

            // Set values for our new movies post
            $new_post = array(
                'post_title'    => esc_html( $movies->title ),
                'post_content'  => '<a href="https://www.imdb.com/title/' . esc_attr( $movies->imdbId ) . '">',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'movie',
            );
            
            // Insert the post into the database
            $new_post_id = wp_insert_post( $new_post );
 
            // Sideload the image to the media library
            $image_id = media_sideload_image( esc_url( $movies->posterURL ), $new_post_id, esc_html( $movies->title), 'id' );

            // Set image as featured on the post
            set_post_thumbnail( $new_post_id, $image_id );

            //exit();

        }

        echo '</ul>';
    }

}