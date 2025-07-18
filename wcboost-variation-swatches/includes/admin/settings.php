<?php
namespace WCBoost\VariationSwatches\Admin;

defined( 'ABSPATH' ) || exit;

use WCBoost\VariationSwatches\Plugin;

class Settings {
	const OPTION_NAME = 'wcboost_variation_swatches';

	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @var WCBoost\VariationSwatches\Admin\Settings
	 */
	protected static $_instance = null;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return WCBoost\VariationSwatches\Admin\Settings An instance of the class.
	 */
	public static function instance() {
		if ( null == self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Get default settings
	 *
	 * @since 1.0.15
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters(
			'wcboost_variation_swatches_settings_default',
			[
				'shape'               => 'round',
				'size'                => ['width' => 30, 'height' => 30],
				'tooltip'             => 'yes',
				'auto_button'         => 'yes',
				'show_selected_label' => 'no',
				'invalid_display'     => 'blur',
			],
			Plugin::instance()->get_mapping()->get_option_value( 'settings' )
		);
	}

	/**
	 * Get default setting
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get_default( $name = null ) {
		$settings = $this->get_settings();

		if (  ! $name ) {
			return (array) $settings;
		}

		if ( ! isset( $settings[ $name ] ) ) {
			return false;
		}

		// Check the theme support.
		if ( current_theme_supports( 'woocommerce' ) ) {
			$support = wc_get_theme_support( 'variation_swatches::' . $name );
		}

		if ( ! empty( $support ) ) {
			return $support;
		}

		return $settings[ $name ];
	}

	/**
	 * Get setting value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get_option( $name ) {
		$value = get_option( $this->get_option_name( $name ) );

		// Get value from other plugins if there is no option of this plugin in the database.
		if ( false === $value ) {
			$value = Plugin::instance()->get_mapping()->get_option_value( $name );
			$value = $value ? $value : $this->get_default( $name );

			// Save the value to databse for faster loading next time.
			if ( false !== $value ) {
				$this->update_option( $name, $value );
			}
		}

		return $value;
	}

	/**
	 * Update option
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function update_option( $name, $value ) {
		update_option( $this->get_option_name( $name ), $value );
	}

	/**
	 * Get the list of shape options
	 *
	 * @return array
	 */
	public function get_shape_options() {
		$options = apply_filters( 'wcboost_variation_swatches_shape_options', [
			'round'   => esc_html__( 'Circle', 'wcboost-variation-swatches' ),
			'rounded' => esc_html__( 'Rounded corners', 'wcboost-variation-swatches' ),
			'square'  => esc_html__( 'Square', 'wcboost-variation-swatches' ),
		] );

		if ( current_theme_supports( 'woocommerce' ) ) {
			$default = wc_get_theme_support( 'variation_swatches::theme_style' );
			$default = $default ? $default : wc_get_theme_support( 'variation_swatches::shape' );

			if ( $default && ! array_key_exists( $default, $options ) ) {
				// Only allow 'default' and 'theme' values.
				$default = in_array( $default, ['default', 'theme'], true ) ? $default : 'default';

				$options = array_merge( [ $default => esc_html__( 'Theme Default', 'wcboost-variation-swatches' ) ], $options );
			}
		}

		return $options;
	}

	/**
	 * Get the list of invalid display options
	 *
	 * @since 1.1.2
	 *
	 * @return array
	 */
	public function get_invalid_display_options() {
		$options = apply_filters( 'wcboost_variation_swatches_invalid_display_options', [
			'hide'       => esc_html__( 'Hide', 'wcboost-variation-swatches' ),
			'blur'       => esc_html__( 'Blur', 'wcboost-variation-swatches' ),
			'cross'      => esc_html__( 'Cross out', 'wcboost-variation-swatches' ),
			'line'       => esc_html__( 'Line through', 'wcboost-variation-swatches' ),
			'slash'      => esc_html__( 'Slash', 'wcboost-variation-swatches' ),
			'blur_cross' => esc_html__( 'Blur & Cross out', 'wcboost-variation-swatches' ),
			'blur_line'  => esc_html__( 'Blur & Line through', 'wcboost-variation-swatches' ),
			'blur_slash' => esc_html__( 'Blur & Slash', 'wcboost-variation-swatches' ),
		] );

		if ( current_theme_supports( 'woocommerce' ) ) {
			$default = wc_get_theme_support( 'variation_swatches::invalid_display' );

			if ( $default && ! array_key_exists( $default, $options ) ) {
				// Only allow 'default' and 'theme' values.
				$default = in_array( $default, ['default', 'theme'], true ) ? $default : 'default';

				$options = array_merge( [ $default => esc_html__( 'Theme Default', 'wcboost-variation-swatches' ) ], $options );
			}
		}

		return $options;
	}

	/**
	 * Get option name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function get_option_name( $name ) {
		return self::OPTION_NAME . '_' . $name;
	}

	/**
	 * Sanitize the shape option
	 *
	 * @param string $shape
	 * @return string
	 */
	public function sanitize_type( $value ) {
		$types = array_keys( wc_get_attribute_types() );

		return in_array( $value, $types ) ? $value : '';
	}

	/**
	 * Sanitize the shape option
	 *
	 * @param string $shape
	 * @return string
	 */
	public function sanitize_shape( $value ) {
		$shapes = array_keys( $this->get_shape_options() );

		return in_array( $value, $shapes ) ? $value : '';
	}

	/**
	 * Sanitize the shape option
	 *
	 * @param array $value
	 * @return string
	 */
	public function sanitize_size( $value ) {
		$width  = isset( $value['width'] ) ? $value['width'] : array_shift( $value );
		$height = isset( $value['height'] ) ? $value['height'] : array_shift( $value );

		return [
			'width'  => absint( $width ),
			'height' => absint( $height ),
		];
	}

	/**
	 * Sanitize the invalid display option
	 *
	 * @since 1.1.2
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize_invalid_display( $value ) {
		$options = array_keys( $this->get_invalid_display_options() );

		return in_array( $value, $options ) ? $value : 'blur';
	}
}

Settings::instance();
