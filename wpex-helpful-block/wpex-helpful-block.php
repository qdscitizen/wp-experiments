<?php
/**
 * Plugin Name: WPEx Helpful Block
 * Description: Adds a "Was this helpful?" voting block to the end of every post via Block Hooks.
 * Version: 0.1.0
 * Author: Chongzhou Sun
 * Text Domain: wpex-helpful
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Register the block (block.json carries the blockHooks rule)
add_action( 'init', function () {
    register_block_type_from_metadata( __DIR__ . '/block.json', array(
        'render_callback' => 'wpex_helpful_render_block',
    ) );
} );

// Render: buttons + live counters
function wpex_helpful_render_block() {
    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return '';
    }

    wp_enqueue_script( 'wpex-helpful-js' );
    wp_enqueue_style( 'wpex-helpful-css' );

    $up   = (int) get_post_meta( $post_id, 'wpex_helpful_up', true );
    $down = (int) get_post_meta( $post_id, 'wpex_helpful_down', true );

    ob_start();
    ?>
    <div class="wpex-helpful" data-post-id="<?php echo esc_attr( $post_id ); ?>">
        <span class="wpex-helpful__label"><?php esc_html_e( 'Was this article helpful?' ); ?></span>
        <button type="button" class="wpex-helpful__btn" data-vote="up">
            👍 <?php esc_html_e( 'Yes' ); ?> (<span class="wpex-helpful__count-up"><?php echo esc_html( $up ); ?></span>)
        </button>
        <button type="button" class="wpex-helpful__btn" data-vote="down">
            👎 <?php esc_html_e( 'No' ); ?> (<span class="wpex-helpful__count-down"><?php echo esc_html( $down ); ?></span>)
        </button>
    </div>
    <?php
    return ob_get_clean();
}

// REST endpoint: POST /wp-json/wpex-helpful/v1/vote
add_action( 'rest_api_init', function () {
    register_rest_route( 'wpex-helpful/v1', '/vote', array(
        'methods'             => 'POST',
        'permission_callback' => '__return_true', // visitors may vote
        'args'                => array(
            'post_id' => array( 'required' => true, 'type' => 'integer' ),
            'vote'    => array( 'required' => true, 'enum' => array( 'up', 'down' ) ),
        ),
        'callback'            => 'wpex_helpful_handle_vote',
    ) );
} );

function wpex_helpful_handle_vote( WP_REST_Request $request ) {
    $post_id = absint( $request['post_id'] );
    $vote    = ( 'up' === $request['vote'] ) ? 'up' : 'down';

    if ( ! get_post( $post_id ) ) {
        return new WP_Error( 'invalid_post', 'Post not found.', array( 'status' => 404 ) );
    }

    $key   = ( 'up' === $vote ) ? 'wpex_helpful_up' : 'wpex_helpful_down';
    $count = (int) get_post_meta( $post_id, $key, true );
    update_post_meta( $post_id, $key, $count + 1 );

    return array( 'success' => true, 'count' => $count + 1 );
}

// Assets + pass REST URL and nonce to JS
add_action( 'wp_enqueue_scripts', function () {
    wp_register_script( 'wpex-helpful-js', plugins_url( 'assets/helpful.js', __FILE__ ), array(), '0.1.0', true );
    wp_register_style( 'wpex-helpful-css', plugins_url( 'assets/helpful.css', __FILE__ ), array(), '0.1.0' );

    wp_localize_script( 'wpex-helpful-js', 'wpexHelpful', array(
        'restUrl' => esc_url_raw( rest_url( 'wpex-helpful/v1/vote' ) ),
        'nonce'   => wp_create_nonce( 'wp_rest' ),
    ) );
} );
