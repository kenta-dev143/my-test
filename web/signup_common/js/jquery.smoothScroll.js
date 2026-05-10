/**
 * Plugin Name: jquery.SmoothScroll
 * Plugin URI: http://2inc.org
 * Description: スムーススクロールでページ内移動するためのプラグイン。指定要素のhashをもとに移動する。
 * Version: 0.3.5
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 5, 2012
 * Modified : March 7, 2013
 * License: GPL2
 *
 * easing : http://jqueryui.com/demos/effect/easing.html
 * @param	{ duration, easing )
 *
 * Copyright 2013 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
;( function( $ ) {
	$.fn.SmoothScroll = function( params ) {
	
var scZoom = $("html").css("zoom");
var header_height = $("#header").height();
		var defaults = {
			duration : 800,
			easing   : 'easeInOutCubic'
		};
		params = $.extend( defaults, params );

		var targetBody;

		var methods = {
			scrollStop: function() {
				targetBody.stop( true );
			},
			getTargetBody: function() {
				if ( $( 'html' ).scrollTop() > 0 ) {
					targetBody = $( 'html' );
				} else if ( $( 'body' ).scrollTop() > 0 ) {
					targetBody = $( 'body' );
				}
				return targetBody;
			}
		}

		return this.each( function( i, e ) {
			$( e ).on( 'click', function() {
				var targetHash = this.hash;
				var offset = $( targetHash ).eq( 0 ).offset();
				if ( ! ( targetHash && offset !== null ) )
					return;

				var wst = $( window ).scrollTop();
				if ( wst === 0 )
					$( window ).scrollTop( wst + 1 );

				targetBody = methods.getTargetBody();
				if ( typeof targetBody === 'undefined' )
					return;
					var Roffset = offset.top;
if (!( navigator.userAgent.indexOf('iPhone') > 0 || navigator.userAgent.indexOf('iPad') > 0
|| navigator.userAgent.indexOf('iPod') > 0 || navigator.userAgent.indexOf('Android') > 0)) {
	Roffset = offset.top - header_height;
}else{	Roffset = offset.top*scZoom - header_height*scZoom;}
				
				targetBody.animate(
					{
						scrollTop: Roffset
					},
					params.duration,
					params.easing,
					function() {
						//location.hash = targetHash ;
					}
				);

				if ( window.addEventListener )
					window.addEventListener( 'DOMMouseScroll', methods.scrollStop, false );
				window.onmousewheel = document.onmousewheel = methods.scrollStop;
				return false;
			} );
		} );
	};
} )( jQuery );

jQuery( function( $ ) {
	$( window ).on( 'load', function() {
		$('a:not(.not-scroll,#pagetop a)[href^="#"],area[href^="#"]').SmoothScroll( {
			duration : 800,
			easing : 'easeInOutCubic'
		} );
	} );
	} );