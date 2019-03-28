<?php 

class tp_5583_widget extends WP_Widget 
{

    function __construct() {
        parent::__construct(
        // widget ID
        'tp_5583_widget',
        // widget name
        __('UPQODE Test Post', ' tp_5583_widget_uqpode'),
        // widget description
        array( 'description' => __( 'UPQODE Wordpress Task', 'tp_5583_widget_uqpode' ), )
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        //if title is present
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
        //output
        $recent_posts = wp_get_recent_posts(['post_type'=>'test_post', 'numberposts' => 4, 'orderby' => 'post_date']);
        foreach( $recent_posts as $recent ){
            echo '<li><a href="' . get_permalink($recent["ID"]) . '" title="Look '.esc_attr($recent["post_title"]).'" >' .   $recent["post_title"].'</a> </li> ';
            }
        echo $args['after_widget'];
    }

    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) )
            $title = $instance[ 'title' ];
        else
            $title = __( 'Default Title', 'tp_5583_widget_uqpode' );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

function tp_5583_register_widget() {
    register_widget('tp_5583_widget');
}

add_action('widgets_init', 'tp_5583_register_widget');