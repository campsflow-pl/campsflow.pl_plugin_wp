<?php
declare(strict_types=1);

namespace Campsflow\Presentation;

/**
 * [campsflow_search_filter fields="category,age,destination,transport,child_age,dates"]
 * Fields order follows the `fields` attribute; all shown by default.
 */
final class SearchFilterShortcode {
	use FilterRenderMethods;

	private const ALL_FIELDS = array( 'category', 'age', 'destination', 'transport', 'child_age', 'season', 'dates' );

	public function register(): void {
		add_shortcode( 'campsflow_search_filter', array( $this, 'render' ) );
	}

	/**
	 * @param array<string, string>|string $atts
	 */
	public function render( array|string $atts ): string {
		$atts = shortcode_atts(
			array(
				'fields'        => implode( ',', self::ALL_FIELDS ),
				'show_reset'    => 'yes',
				'reset_label'   => __( 'Wyczyść filtry', 'campsflow' ),
				'text_color'    => '',
				'bg_color'      => '',
				'border_radius' => '',
				'box_shadow'    => '',
				'el_class'      => '',
				'css'           => '',
			),
			is_array( $atts ) ? $atts : array(),
			'campsflow_search_filter'
		);

		$fields   = array_map( 'trim', explode( ',', $atts['fields'] ) );
		$endpoint = rest_url( 'campsflow/v1/events' );

		$cssClass = '';
		if ( $atts['css'] !== '' && function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$cssClass = vc_shortcode_custom_css_class( $atts['css'], ' ' );
		}
		$classes = trim( 'cf-search-form cf-filters ' . sanitize_html_class( $atts['el_class'] ) . $cssClass );

		$inlineStyle = '';
		$textColor   = sanitize_hex_color( $atts['text_color'] );
		if ( $textColor ) {
			$inlineStyle .= 'color:' . $textColor . ';';
		}
		$bgColor = sanitize_hex_color( $atts['bg_color'] );
		if ( $bgColor ) {
			$inlineStyle .= 'background-color:' . $bgColor . ';';
		}
		if ( $atts['border_radius'] !== '' ) {
			$inlineStyle .= 'border-radius:' . absint( $atts['border_radius'] ) . 'px;';
		}
		$boxShadow = sanitize_text_field( $atts['box_shadow'] );
		if ( $boxShadow !== '' ) {
			$inlineStyle .= 'box-shadow:' . $boxShadow . ';';
		}
		$styleAttr = $inlineStyle !== '' ? ' style="' . esc_attr( $inlineStyle ) . '"' : '';

		ob_start();
		echo '<form class="' . esc_attr( $classes ) . '"' . $styleAttr . ' method="get" action="" data-endpoint="' . esc_url( $endpoint ) . '">';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		foreach ( $fields as $field ) {
			match ( $field ) {
				'category'  => $this->renderTaxFilterSelect( 'cf_event_category', 'category', __( 'Wszystkie profile', 'campsflow' ) ),
				'age'       => $this->renderTaxFilterSelect( 'cf_age_group', 'age', __( 'Wszystkie grupy wiekowe', 'campsflow' ) ),
				'destination' => $this->renderDestinationFilterSelect( __( 'Wszystkie kierunki', 'campsflow' ) ),
				'transport' => $this->renderTaxFilterSelect( 'cf_transport_type', 'transport', __( 'Transport', 'campsflow' ) ),
				'child_age' => $this->renderChildAgeFilterSelect( __( 'Wiek', 'campsflow' ) ),
				'season'    => $this->renderSeasonFilterSelect( __( 'Sezon', 'campsflow' ) ),
				'dates'     => $this->renderDateRangePicker( __( 'Termin', 'campsflow' ) ),
				default     => null,
			};
		}

		if ( $atts['show_reset'] === 'yes' ) {
			echo '<button type="button" class="cf-reset">' . esc_html( $atts['reset_label'] ) . '</button>';
		}

		echo '</form>';
		return (string) ob_get_clean();
	}
}
