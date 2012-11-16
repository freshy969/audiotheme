<?php
/**
 * AudioTheme API for working with media and defines filters for modifying
 * WordPress behavior related to media.
 *
 * @package AudioTheme_Framework
 */

/**
 * Register custom oEmbed providers.
 *
 * Post content is filtered on display, so limited services should be
 * supported by default.
 *
 * @since 1.0.0
 * @link http://core.trac.wordpress.org/ticket/15734
 * @link http://core.trac.wordpress.org/ticket/21635#comment:8
 */
function audiotheme_add_default_oembed_providers() {
	if ( audiotheme_version_compare( 'wp', '3.5-beta-1', '<' ) ) {
		wp_oembed_add_provider( '#https?://(www\.)?soundcloud\.com/.*#i', 'http://soundcloud.com/oembed', true );
	}
	
	#wp_oembed_add_provider( 'http://snd.sc/*', 'http://soundcloud.com/oembed' );
	#wp_oembed_add_provider( 'http://www.rdio.com/#artist/*album/*', 'http://www.rdio.com/api/oembed/' );
	#wp_oembed_add_provider( 'http://rd.io/*', 'http://www.rdio.com/api/oembed/' );
}

/**
 * Add an HTML wrapper to certain videos retrieved via oEmbed.
 *
 * The wrapper is useful as a styling hook and for responsive designs. Also
 * attempts to add the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 * @todo Remove the preg_replace_callback() when WP3.4 support is dropped and
 *       use the filter introduced in ticket #16996.
 * @link http://core.trac.wordpress.org/ticket/16996
 * 
 * @return string Embed HTML with wrapper.
 */
function audiotheme_oembed_html( $html, $url, $attr, $post_id ) {
	$players = array( 'youtube', 'vimeo', 'dailymotion', 'hulu', 'blip.tv', 'wordpress.tv', 'viddler', 'revision3' );
	
	foreach( $players as $player ) {
		if( false !== strpos( $url, $player ) ) {
			if ( false !== strpos( $url, 'youtube' ) && false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'wmode' ) ) {
				$html = preg_replace_callback( '|https?://[^"]+|im', '_audiotheme_oembed_youtube_wmode_parameter', $html );
			}
		
			$html = '<div class="audiotheme-video">' . $html . '</div>';
			break;
		}
	}
	
	if ( false !== strpos( $html, '<embed' ) && false === strpos( $html, 'wmode' ) ) {
		$html = str_replace( '</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque"', $html );
	}
	
	return $html;
}

/**
 * Private callback.
 *
 * Adds wmode=transparent query argument to oEmbedded YouTube videos.
 *
 * @since 1.0.0
 * @access private
 */
function _audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}
?>