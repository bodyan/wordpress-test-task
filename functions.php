<?php
//test_post => tp_5583

//1. Create a child theme for the Twenty Sixteen standard theme.
// https://developer.wordpress.org/themes/advanced-topics/child-themes/
add_action( 'wp_enqueue_scripts', 'tp_5583_theme_enqueue_styles' );
function tp_5583_theme_enqueue_styles() {
 
    $parent_style = 'parent-style'; // This is 'twentysixteen-style' for the twentysixteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

//2. Register a new type of posts - "test_post".
function tp_5583_post_type() {
    $supports = array('title', 'editor','revisions');
    $labels = array(
        'name' => _x('Test posts', 'plural'),
        'singular_name' => _x('Test post', 'singular'),
        'menu_name' => _x('Test Post', 'admin menu'),
        'name_admin_bar' => _x('Test Post', 'admin bar'),
        'add_new' => _x('Add New', 'add new'),
        'add_new_item' => __('Add New Test Post'),
        'new_item' => __('New Test Post'),
        'edit_item' => __('Edit Test Post'),
        'view_item' => __('View Test Post'),
        'all_items' => __('All Test Post'),
        'search_items' => __('Search Test Post'),
        'not_found' => __('No Test Post found.'),
    );
    $args = array(
        'supports' => $supports,
        'labels' => $labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'test_post'),
        'has_archive' => true,
        'hierarchical' => false,
    );
    register_post_type('test_post', $args);
}

//3. Register a new taxonomy "Blog".
function create_my_taxonomies() {
    register_taxonomy(
        'test_post_test_blog',
        'test_post',
        array(
            'labels' => array(
                'name' => 'Blog',
                'add_new_item' => 'Add New Blog',
                'new_item_name' => "New Blog"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
            )
        );
}

//show taxonomy in test post list
function test_post_columns( $taxonomies ) {
    $taxonomies[] = 'test_post_test_blog';
    return $taxonomies;
}


add_action('init', 'tp_5583_post_type');
add_action( 'init', 'create_my_taxonomies', 0 );
add_filter( 'manage_taxonomies_for_test_post_columns', 'test_post_columns' );

// 4. For a new type of posts, add metabox to the sub-header.
// i think simple input for subtitle looking more prettier, than meta boxes
// based on https://wordpress.stackexchange.com/a/98553
function tp_5583_subtitle_metabox() {
    global $post;
    $key = 'tp_5583_subtitle';

    if (empty($post) || 'test_post' !== get_post_type( $GLOBALS['post'] ) ) return;
    if (!$content = get_post_meta( $post->ID, $key, TRUE )) $content = '';
    printf(
        '<input type="text" name="%1$s" id="%1$s_id" value="%2$s" size="20" spellcheck="true" autocomplete="off" placeholder="Enter subtitle here">',
        $key,
        esc_attr( $content )
    );
}

function tp_5583_save_subtitle_metabox( $post_id ) {
    $key = 'tp_5583_subtitle';

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST[ $key ])) return update_post_meta( $post_id, $key, $_POST[ $key ] );
    delete_post_meta( $post_id, $key );
}

function tp_5583_subtitle_style() {
    ?>
    <style>
      #tp_5583_subtitle_id  {
            padding: 3px 8px;
            font-size: 1.4em;
            line-height: 100%;
            height: 1.5em;
            width: 100%;
            outline: 0;
            margin: 10px 0 3px;
            background-color: #fff;
        }
    </style>
    <?php
}

add_action( 'edit_form_after_title', 'tp_5583_subtitle_metabox' );
add_action( 'admin_head', 'tp_5583_subtitle_style');
add_action( 'save_post', 'tp_5583_save_subtitle_metabox' );

function tp_5583_sort($post_type) {
    $post_types = ['test_post'];

    if (in_array($post_type, $post_types)) {
        add_meta_box(
            'subtitle-meta-box', // HTML 'id' attribute of the edit screen section.
            'Sort By',              // Title of the edit screen section, visible to user.
            'tp_5583_sort_meta_box', // Function that prints out the HTML for the edit screen section.
            $post_type,          // The type of Write screen on which to show the edit screen section.
            'normal',          // The part of the page where the edit screen section should be shown.
            'high'               // The priority within the context where the boxes should show.
        );
    }
}

function tp_5583_sort_meta_box($post) {
    $key = '_tp_5583_sort';
    $sort = get_option($key);
    ?>
        <label class="screen-reader-text" for="tp_5583_sort">Sort</label>
        <input id="tp_5583_sort" type="text" autocomplete="off" value="<?=esc_attr($sort)?>" name="tp_5583_sort" placeholder="Sort">
        <p><i>orderby - Sort retrieved posts by parameter.<i> More information 
            <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">here</a>
        </p>
    <?php
}

function tp_5583_sort_save_orderby($post_id) {
    $key = 'tp_5583_sort';
    $sort = intval($_POST[$key]);
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST[$key]) && $sort !== '') 
        return add_option('_tp_5583_sort', $sort, '', 'yes') OR update_option('_tp_5583_sort', $sort);
    delete_option('_tp_5583_sort');
}

add_action('add_meta_boxes', 'tp_5583_sort');
add_action('save_post', 'tp_5583_sort_save_orderby');

function tp_5583_move_under_title() {
    global $post, $wp_meta_boxes;
    do_meta_boxes(get_current_screen(), 'advanced', $post);
    unset($wp_meta_boxes['test_post']['advanced']);
}

add_action('edit_form_after_title', 'tp_5583_move_under_title');

//4. For a new type of posts, add metabox to the sub-header. And another one metabox Sort
//(where you can enter 1 or 2 or 3 or 4 ...). Use the WordPress functionality, no plugins!
function tp_5583_get_orderby($arg) {
    $arg = intval($arg);
    $order_by_types = [
        'none', 'ID', 'author', 'title', 'name', 'type', 'date', 
        'modified', 'parent', 'rand', 'comment_count', 'relevance', 
        'menu_order', 'meta_value', 'meta_value_num', 'post__in', 
        'post_name__in', 'post_parent__in'
    ];
    return ($arg > 0 && $arg <= count($order_by_types)) ? $order_by_types[$arg] : 'none';
}

/**
 * Get taxonomies terms links.
 *
 * @see get_object_taxonomies()
 * @link https://developer.wordpress.org/reference/functions/get_the_terms/#comment-405
 */
function wpdocs_custom_taxonomies_terms_links() {
    // Get post by post ID.
    if ( !$post = get_post()) return '';
 
    // Get post type by post.
    $post_type = $post->post_type;
 
    // Get post type taxonomies.
    $taxonomies = get_object_taxonomies( $post_type, 'objects' );
 
    $out = array();
 
    foreach ( $taxonomies as $taxonomy_slug => $taxonomy ){
        // Get the terms related to post.
        $terms = get_the_terms( $post->ID, $taxonomy_slug );
        if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $out[] = sprintf( ' in <a href="%1$s">%2$s</a>',
                    esc_url( get_term_link( $term->slug, $taxonomy_slug ) ),
                    esc_html( $term->name )
                );
            }
        }
    }
    return implode( '', $out );
}

// 6. Display 3 posts and create pagination under the posts.
// https://css-tricks.com/forums/topic/wp-posts-per-page-on-custom-post-type/
function tp_5583_set_posts_per_page($query) {
  if ( !is_admin() && $query->is_main_query() && is_post_type_archive('test_post')) {
    $query->set('posts_per_page', '3');
  }
}

add_action( 'pre_get_posts', 'tp_5583_set_posts_per_page' );

// 7. Create a simple widget to display the last 4 new posts (test_post). Show only headline with a link.
// https://www.hostinger.com/tutorials/how-to-create-custom-widget-in-wordpress
require_once ('tp_5583_widget.php');

?>

<!-- 
WordPress task
1. Create a child theme for the Twenty Sixteen standard theme.
2. Register a new type of posts - "test_post".
3. Register a new taxonomy "Blog".
4. For a new type of posts, add metabox to the sub-header. And another one metabox Sort
(where you can enter 1 or 2 or 3 or 4 ...). Use the WordPress functionality, no plugins!
5. Output a new type of posts on the blog page using the wp_query object. Sort posts by a
custom metabox Sort. Each post must have a title, subtitle, short description, date, author, and
category to which it belongs. Category should be a link.
6. Display 3 posts and create pagination under the posts.
7. Create a simple widget to display the last 4 new posts (test_post). Show only headline with a
link.
 -->