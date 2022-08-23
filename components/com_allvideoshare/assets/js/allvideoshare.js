/*
 * @version		$Id: allvideoshare.js 3.5.0 2020-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2020 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

(function( $ ) {
	'use strict';

	/**
	 * Initialize the slick slider.
	 *
	 * @since 3.6.1
	 */
	function avs_init_slick( $this ) {
		$this.addClass( 'avs-slick-initialized' );

		var params = $this.data( 'params' );			
		var arrow_styles = 'top: ' + params.arrow_top_offset + '; width: ' + params.arrow_size  + '; height: ' + params.arrow_size + '; background: ' + params.arrow_bg_color + '; border-radius: ' + params.arrow_radius + '; font-size: ' + params.arrow_icon_size + '; color: ' + params.arrow_icon_color + '; line-height: ' + params.arrow_size + ';';
		
		// Slick			
		var $carousel = $this.slick({
			rtl: ( parseInt( params.is_rtl ) ? true : false ),
			prevArrow: '<div class="avs-slick-prev" style="left: ' + params.arrow_left_offset + '; ' + arrow_styles + '" role="button">&#10094;</div>',
			nextArrow: '<div class="avs-slick-next" style="right: ' + params.arrow_right_offset + '; ' + arrow_styles + '" role="button">&#10095;</div>',
			dotsClass: 'avs-slick-dots',
			customPaging: function( slider, i ) {					
				return '<div class="avs-slick-dot" style="color: ' + params.dot_color + '; font-size: ' + params.dot_size + '" role="button">&#9679;</div>';
			}
		});

		// Popup
		if ( $this.hasClass( 'avs-popup-video' ) ) {
			var player_ratio = parseFloat( $( this ).data( 'player_ratio' ) );

			$carousel.magnificPopup({ 
				delegate: '.slick-slide:not(.slick-cloned) .aiovg-slick-item', // the selector for gallery item
				type: 'iframe',
				overflowY: 'auto',			
				removalDelay: 300,
				iframe: { // to create title, close, iframe, counter div's
				markup: '<div class="mfp-title-bar">' +
							'<div class="mfp-close" title="Close (Esc)"></div>' +
						'</div>' +							
						'<div class="mfp-iframe-scaler" style="padding-top:' + player_ratio + '%;" >' +            												
							'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +																								
						'</div>'																							        			
				},	
				gallery: { // to build gallery				
					enabled: true													
				}									
			});	
		}
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.0.0
	 */
	$(function() {
			
		// Magnific Popup 
		if ( $.fn.magnificPopup !== undefined ) {
			$( '.avs-popup' ).each(function() {	
				var player_ratio = parseFloat( $( this ).data( 'player_ratio' ) );

				$( this ).magnificPopup({ 
					delegate: 'li', // the selector for gallery item
					type: 'iframe',
					overflowY: 'auto',			
					removalDelay: 300,
					iframe: { // to create title, close, iframe, counter div's
					markup: '<div class="mfp-title-bar">' +
								'<div class="mfp-close" title="Close (Esc)"></div>' +
							'</div>' +							
							'<div class="mfp-iframe-scaler" style="padding-top:' + player_ratio + '%;" >' +            												
								'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +																								
							'</div>'																							        			
					},	
					gallery: { // to build gallery				
						enabled: true													
					}									
				});	
			});
		};

		// Slider
		if ( $.fn.slick !== undefined ) {
			// Initialize the Slider
			$( '.avs-slick' ).each(function() {						
				avs_init_slick( $( this ) );
				
				// On before slide change
				var type = $( this ).data( 'type' );
				
				if ( 'player' == type ) {				
					$( this ).on( 'beforeChange', function( event, slick, current_slide, next_slide ) {								   
						var $current_slide = $( slick.$slides[ current_slide ] );
						$current_slide.find( '.avs-player' ).html( '' );
						
						var $next_slide = $( slick.$slides[ next_slide ] );
						var src = $next_slide.find( '.avs-player' ).data( 'src' );
						$next_slide.find( '.avs-player' ).html( '<iframe src="' + src + '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>' );					
					});				
				}										   
			});	

			// Disable click event on thumbnail element
			$( '.avs-slick-video' ).on( 'click', 'a', function( event ) {
				event.preventDefault();  
				event.stopPropagation();  			
			});
		};		

		// Video Submission Form: On file adding method changed
		$( 'input[type="radio"]', '.avs-file-uploader-types' ).on( 'change', function() {																						
			var type;
			
			if ( this.checked ) {
				type = this.value;
			} else {
				type = ( 'url' == this.value ) ? 'upload' : 'url';
			}
			
			$( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type' ).hide();
			$( this ).closest( '.avs-file-uploader' ).find( '.avs-file-uploader-type-' + type ).show();
			$( '#type', '#avs-videos' ).trigger( 'change' );			
		});
		
		// Video Submission Form: On file browse button clicked
		$( '.avs-btn-upload' ).on( 'click', function() {														
			var $element = $( this ).closest( '.avs-file-uploader-type-upload' );
			
			$element.find( 'input[type="file"]' ).off( 'change' ).on( 'change', function() {																						
				var value = $( this ).val().split( '\\' ).pop();
				$element.find( 'input[type="text"]' ).val( value );				
			}).trigger( 'click' );			
		});
		
		// Video Submission Form: On type change
		$( '#type', '#avs-videos' ).on( 'change', function() {						
			var type = $( this ).val();
			
			$( '.avs-toggle-fields', '#avs-videos' ).hide();
			$( '.avs-'+type+'-fields', '#avs-videos' ).show();
			
			// Set required
			$( '#video, #upload-video, #streamer, #external, #hls', '#avs-videos' ).removeClass( 'required' ).removeAttr( 'required' );
			
			switch ( type ) {
				case 'general':
					var option = $( '#avs-file-uploader-video', '#avs-videos' ).find( 'input[type="radio"]:checked' ).val();
					
					if ( 'url' == option ) {
						$( '#video', '#avs-videos' ).addClass( 'required' );
					} else {
						$( '#upload-video', '#avs-videos' ).addClass( 'required' );
					}
					break;
				case 'youtube':
					$( '#external', '#avs-videos' ).addClass( 'required' );
					break;
				case 'vimeo':
					$( '#external', '#avs-videos' ).addClass( 'required' );
					break;
				case 'rtmp':
					$( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).hide();
					$( '#streamer, #external', '#avs-videos' ).addClass( 'required' );
					break;
				case 'hls':
					$( '#hls', '#avs-videos' ).addClass( 'required' );
					$( '.avs-hls-fields', '#avs-videos' ).find( '.star' ).show();
					break;
			}			
		}).trigger( 'change' );
		
		// GDPR
		$( '.avs-gdpr-consent-button' ).on( 'click', function() {	
			var $this = $( this );
			$this.html( '...' );

			// Set Cookie
			var xmlhttp;

			if ( window.XMLHttpRequest ) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
			};
			
			xmlhttp.onreadystatechange = function() {                
				if ( 4 == xmlhttp.readyState && 200 == xmlhttp.status ) {                    
					if ( 'success' == xmlhttp.responseText ) {
						var container = $this.closest( '.avs-player' );
			
						var iframe = container.find( 'iframe' ).clone();
						var src = iframe.data( 'src' );
						iframe.attr( 'src', src );
						
						container.html( iframe );	
					}                        
				}                    
			};	

			xmlhttp.open( 'GET', $( this ).data( 'baseurl' ) + 'index.php?option=com_allvideoshare&view=player&task=gdpr&format=raw', true );
			xmlhttp.send();	
		});	
		
	});

})( jQuery );