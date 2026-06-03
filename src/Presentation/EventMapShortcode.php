<?php
declare(strict_types=1);

namespace Campsflow\Presentation;

/**
 * [campsflow_event_map provider="openstreetmap" height="400" zoom="14" map_type="roadmap" post_id="0"]
 */
final class EventMapShortcode {
	use EventMapRenderMethods;

	public function register(): void {
		add_shortcode( 'campsflow_event_map', array( $this, 'render' ) );
	}

	/**
	 * @param array<string, string>|string $atts
	 */
	public function render( array|string $atts ): string {
		$atts = shortcode_atts(
			array(
				'provider' => 'openstreetmap',
				'height'   => '400',
				'zoom'     => '14',
				'map_type' => 'roadmap',
				'post_id'  => '0',
				'el_class' => '',
				'css'      => '',
			),
			is_array( $atts ) ? $atts : array(),
			'campsflow_event_map'
		);

		$provider = sanitize_key( (string) $atts['provider'] );
		if ( ! in_array( $provider, array( 'google', 'openstreetmap' ), true ) ) {
			$provider = 'openstreetmap';
		}
		$height  = max( 50, absint( $atts['height'] ) );
		$zoom    = min( 20, max( 1, absint( $atts['zoom'] ) ) );
		$mapType = sanitize_key( (string) $atts['map_type'] );
		$postId  = absint( $atts['post_id'] );
		if ( $postId === 0 ) {
			$postId = (int) get_the_ID();
		}

		$cssClass = '';
		if ( $atts['css'] !== '' && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$cssClass = vc_shortcode_custom_css_class( $atts['css'], ' ' );
		}
		$wrapClass = trim( sanitize_html_class( $atts['el_class'] ) . $cssClass );

		ob_start();
		if ( $wrapClass !== '' ) {
			echo '<div class="' . esc_attr( $wrapClass ) . '">';
		}
		$this->echoMapDiv( $postId, $provider, $zoom, $mapType, $height );
		if ( $wrapClass !== '' ) {
			echo '</div>';
		}
		return (string) ob_get_clean();
	}
}
