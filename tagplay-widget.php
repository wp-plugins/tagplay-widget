<?php
/*
Plugin Name: Tagplay Widget
Plugin URI: http://tagplay.github.io/tagplay-wordpress-plugin
Description: Provides Tagplay widget functionality to show social media posts managed by Tagplay (https://tagplay.co).
Version: 1.0
Author: Tagplay
Author URI: http://tagplay.co
License: GPL2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'TAGPLAY_WIDGET_DEFAULT_VERSION', '1.8.2' );

class Tagplay_Widget extends WP_Widget {
    function __construct() {
        parent::__construct('tagplay_widget', __('Tagplay Widget', 'text_domain'), array('description' => 'Displays your latest social media posts.'));
    }

    /**
     * Return an array that contains the keys from $defaults, with values from
     * $instance if $instance has a value for that key, but otherwise
     * defaulting to the values from $defaults.
     *
     * Used as a helper to get the appropriate settings for the widget.
     *
     * @param array $defaults Array containing all the keys that should be in
     *                        the result, with their default values.
     * @param array $instance Array containing the overriding values.
     * @return array An array containing the keys from $defaults, their values
     *               potentially overridden by corresponding values from
     *               $instance.
    */
    private static $default_settings = array(
        'title' => '',
        'project_id' => '',
        'feed_id' => '',
        'token' => '',
        'html_id' => '',
        'widget_version' => TAGPLAY_WIDGET_DEFAULT_VERSION,
    );

    private static $default_attributes = array(
        'type' => 'grid',
        'style' => 'style-1',
        'text' => 'tagless',
        'include_usernames' => true,
        'include_captions' => true,
        'videos' => true,
        'images' => true,
        'include_link_metadata' => true,
        'link_image' => true,
        'link_description' => true,
        'include_dates' => false,
        'include_times' => false,
        'include_like' => false,
        'include_flag' => false,
        'text_color' => '',
        'background_color' => '',
        'rows' => 4,
        'cols' => 1,
        'spacing' => 10,
        'responsive' => false,
    );

    public static $inverted_attributes = array('videos', 'images', 'link_image', 'link_description');

    private static function override_defaults($defaults, $instance) {
        $result = array();

        foreach ( $defaults as $key => $default_value ) {
            if ( array_key_exists( $key, $instance ) ) {
                $result[$key] = $instance[$key];
            }
            else {
                $result[$key] = $default_value;
            }
        }
        return $result;
    }

    public static function get_base_settings($instance=array()) {
        $settings = Tagplay_Widget::override_defaults(self::$default_settings, $instance);
        if ( !$settings['html_id'] && $settings['feed_id'] ) {
            $settings['html_id'] = 'tagplay-widget-' . substr($settings['feed_id'], 0, 6);
        }
        return $settings;
    }

    public static function get_attributes($instance=array()) {
        return Tagplay_Widget::override_defaults(self::$default_attributes, $instance);
    }

    public static function stringify_attributes($attributes) {
        $attribute_strings = array();

        foreach ($attributes as $key => $value) {
            $attribute = str_replace('_', '-', $key);
            if (in_array($key, Tagplay_Widget::$inverted_attributes)) {
                $attribute = 'no-' . $attribute;
                $value = !$value;
            }
            if ($value === true || $value === 'on') {
                $attribute_strings[] = 'data-' . $attribute;
            }
            else if ($value !== false && $value !== '') {
                $attribute_strings[] = 'data-' . $attribute . '="' . esc_attr($value) . '"';
            }
        }
        return implode(' ', $attribute_strings);
    }

    function show_form_label($attribute, $colon) {
        $label = ucfirst( str_replace( '_', ' ', $attribute ) );
        if ($colon) {
            $label .= ':';
        }
        ?>
        <label for="<?php echo $this->get_field_id( $attribute ); ?>"><?php _e( $label ); ?></label>
        <?php
    }

    function show_form_field($attribute, $value) {

        $default_value = array_key_exists( $attribute, self::$default_settings ) ? self::$default_settings[$attribute] : ( array_key_exists( $attribute, self::$default_attributes ) ? self::$default_attributes[$attribute] : '' );

        $select_fields = array(
            'type' => array(
                array('grid', 'Grid'),
                array('waterfall', 'Waterfall'),
            ),
            'style' => array(
                array('minimal', 'No style'),
                array('style-1', 'Default'),
            ),
            'text' => array(
                array('original', 'Show hashtags'),
                array('normalized', 'Strip #'),
                array('stripped', 'Remove trigger hashtags'),
                array('tagless', 'Remove all trailing tags'),
            ),
        );

        if (array_key_exists( $attribute, $select_fields ) ) {
            // Show a select box
            ?>
            <p>
                <?php $this->show_form_label( $attribute, true ); ?>
                <select id="<?php echo $this->get_field_id( $attribute ) ?>" name="<?php echo $this->get_field_name( $attribute ); ?>">
                <?php foreach ( $select_fields[$attribute] as $option ) { ?>
                    <option value="<?php echo $option[0]; ?>" <?php selected($value, $option[0]) ?>><?php echo $option[1]; ?></option>
                <?php } ?>
                </select>
            <?php
        }
        else if ($default_value === true || $default_value === false) {
            // Boolean - show a checkbox
            ?>
            <p>
                <input class="checkbox" id="<?php echo $this->get_field_id( $attribute ); ?>" name="<?php echo $this->get_field_name( $attribute ); ?>" type="checkbox"<?php checked( boolval($value), true ); ?>>
                <?php $this->show_form_label( $attribute, false ); ?>
            </p>
            <?php
        }
        else {
            $is_number = is_int( $default_value );
            ?>
            <p>
                <?php $this->show_form_label( $attribute, true ); ?>
                <input id="<?php echo $this->get_field_id( $attribute ); ?>" name="<?php echo $this->get_field_name( $attribute ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>"<?php if ( $is_number ) { ?> size="3"<?php } else { ?> class="widefat"<?php } ?>>
            </p>
            <?php
        }
    }

    function form($instance) {
        $settings = self::get_base_settings($instance);
        $attributes = self::get_attributes($instance);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (displayed above the widget if set; leave blank for no title):' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_version' ); ?>"><?php _e( "Widget version (leave this alone unless you know what you're doing):" ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_version' ); ?>" name="<?php echo $this->get_field_name( 'widget_version' ); ?>" type="text" value="<?php echo esc_attr( $settings['widget_version'] ? $settings['widget_version'] : TAGPLAY_WIDGET_DEFAULT_VERSION ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'html_id' ); ?>"><?php _e( 'HTML ID (leave blank for default derived from feed ID; if you have multiple widgets of the same feed, you must customize this to make sure your widgets have unique IDs):' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'html_id' ); ?>" name="<?php echo $this->get_field_name( 'html_id' ); ?>" type="text" value="<?php echo esc_attr( $settings['html_id'] ); ?>">
        </p>
        <p><?php _e( 'To find these values, click the name of your feed on <a href="https://tagplay.co">your Tagplay dashboard</a>, click the Get Code button in the upper right corner, scroll down and select WordPress in the instructions.' ); ?></p>
        <p>
            <label for="<?php echo $this->get_field_id( 'project_id' ); ?>"><?php _e( 'Project ID:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'project_id' ); ?>" name="<?php echo $this->get_field_name( 'project_id' ); ?>" type="text" value="<?php echo esc_attr( $settings['project_id'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'feed_id' ); ?>"><?php _e( 'Feed ID:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'feed_id' ); ?>" name="<?php echo $this->get_field_name( 'feed_id' ); ?>" type="text" value="<?php echo esc_attr( $settings['feed_id'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'token' ); ?>"><?php _e( 'Token:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'token' ); ?>" name="<?php echo $this->get_field_name( 'token' ); ?>" type="text" value="<?php echo esc_attr( $settings['token'] ); ?>">
        </p>
        <?php
        foreach ($attributes as $attribute => $value) {
            $this->show_form_field( $attribute, $value );
        }
    }

    function update($new_instance, $old_instance) {
        if ($old_instance) {
            // If the old instance is not empty, we must assume boolean fields
            // that are not included in $new_instance are actually false
            foreach ( self::$default_attributes as $attribute => $default ) {
                if ( ( $default === true || $default === false ) && !array_key_exists( $attribute, $new_instance ) ) {
                    $new_instance[$attribute] = false;
                }
            }
        }
        $instance = self::get_base_settings($new_instance) + self::get_attributes($new_instance);

        // Run validation and sanitization on all the attributes
        foreach ( $instance as $key => $value ) {
            if ( $key === 'title' ) {
                $instance[$key] = strip_tags( $instance[$key] );
            }
            else if ( $key === 'widget_version' ) {
                if ( !preg_match( '/^\\d+\\.\\d+\\.\\d+$/', $instance[$key] ) ) {
                    $instance[$key] = TAGPLAY_WIDGET_DEFAULT_VERSION;
                }
            }
            else {
                $instance[$key] = esc_attr( $instance[$key] );
            }
        }

        return $instance;
    }

    function widget($args, $instance) {
        echo $args['before_widget'];
        $settings = self::get_base_settings($instance);
        $attributes = self::get_attributes($instance);

        if ( $settings['title'] ) {
            echo $args['before_title'] . apply_filters( 'widget_title', esc_html( $settings['title'] ) ). $args['after_title'];
        }
        if ( !$settings['project_id'] || !$settings['feed_id'] || !$settings['token'] ) {
            echo "Misconfigured widget: project, feed or token missing";
        }
        else {
            ?>
            <div id="<?php echo esc_attr( $settings['html_id'] ); ?>" class="tagplay-widget"
             data-project="<?php echo esc_attr( $settings['project_id'] ); ?>"
             data-feed="<?php echo esc_attr( $settings['feed_id'] ); ?>"
             data-token="<?php echo esc_attr( $settings['token'] ); ?>"
             <?php echo $this->stringify_attributes($attributes); ?>></div>
            <script>
             var widgetElement = document.getElementById("<?php echo esc_attr( $settings['html_id'] ); ?>");
             if (typeof TagplayWidget === "function") {
               TagplayWidget(widgetElement);
             } else {
               if (!window.tagplayWidgetQueue) window.tagplayWidgetQueue = [];
               window.tagplayWidgetQueue.push(widgetElement);
             }
            </script>
            <?php

            wp_enqueue_script( 'tagplay_widget', 'https://api.tagplay.co/' . esc_attr( $settings['widget_version'] ) . '/tagplay-widget.min.js' );
        }
        echo $args['after_widget'];
    }
}

function register_tagplay_widget() {
    register_widget('Tagplay_Widget');
}

add_action('widgets_init', 'register_tagplay_widget');

// Add Shortcode
function tagplay_widget_shortcode( $atts ) {

    // Attributes

    // Make sure inverted attributes get handled correctly, so that e.g.
    // no_images="on" specified in the shortcode becomes images => false
    // internally in the widget and gets output as no-images.
    foreach ( Tagplay_Widget::$inverted_attributes as $attribute ) {
        $inverted_key = 'no_' . $attribute;
        if ( array_key_exists( $inverted_key, $atts ) ) {
            $atts[$attribute] = !$atts[$inverted_key];
            unset( $atts[$inverted_key] );
        }
    }

    $atts = Tagplay_Widget::get_base_settings($atts) + Tagplay_Widget::get_attributes($atts);

    // Code
    global $wp_widget_factory;
    $widget_obj = $wp_widget_factory->widgets['Tagplay_Widget'];

    $args = array(
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => ''
    );

    $widget_obj->_set(-1);
    $widget_obj->widget($args, $atts);
}
add_shortcode( 'tagplay-widget', 'tagplay_widget_shortcode' );

?>