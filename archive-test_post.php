<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @link https://wordpress.stackexchange.com/a/208075
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		
		<?php
			if ( get_query_var('paged') ) {
			    $paged = get_query_var('paged');
			} elseif ( get_query_var('page') ) { // 'page' is used instead of 'paged' on Static Front Page
			    $paged = get_query_var('page');
			} else {
			    $paged = 1;
			}

			$sortby = tp_5583_get_orderby(get_option('_tp_5583_sort'));
			$args = array(
			    'post_type' => 'test_post', 
			    'posts_per_page' => 3,
			    'paged' => $paged,
			    'post_status' => 'publish',
			    'ignore_sticky_posts' => true,
			    'orderby' =>  $sortby // modified | title | name | ID | rand
			);

			$query = new WP_Query($args);
	 		if ($query->have_posts()) : 
			    while( $query->have_posts() ) : $query->the_post(); ?>
			        <article <?php post_class(); ?>>
			            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php 
						$subtitle = get_post_meta( get_the_ID(), 'tp_5583_subtitle', true );
						if ( $subtitle !== '') echo '<h4>' .$subtitle. '</h4>'; 
						?>
			            <small><?php the_time('F jS, Y') ?> by <?php the_author_posts_link() ?>
			            <?php echo wpdocs_custom_taxonomies_terms_links(); ?>
			        	</small>
			            <div><?php the_excerpt(); ?></div>
			        </article>
			    <?php
			    endwhile;
		    wp_reset_postdata(); // reset the query 

			// Previous/next page navigation.
			the_posts_pagination(
				array(
					'prev_text'          => __( 'Previous page', 'twentysixteen' ),
					'next_text'          => __( 'Next page', 'twentysixteen' ),
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentysixteen' ) . ' </span>',
				)
			);

		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
