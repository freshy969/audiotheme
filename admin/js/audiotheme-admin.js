/**
 * Utility to show WP pointers added via audiotheme_enqueue_pointer().
 *
 * @todo Add a method to hide pointers without dismissing them.
 * @todo Consider how to create tours.
 */
(function($) {
	$.fn.audiothemePointer = function( id ) {
		return this.each(function() {
			var $this = $(this),
				pointer;
			
			if( 'undefined' !== typeof jQuery().pointer && 'undefined' !== typeof audiothemePointers ) {
				pointer = audiothemePointers[ id ] || null;
				
				if ( pointer ) {
					$this.pointer({
						content: pointer.content,
						position: pointer.position,
						close: function() {
							jQuery.post( ajaxurl, {
								pointer: id,
								action: 'dismiss-wp-pointer'
							});
							// @todo Remove the pointer from the global so it won't be shown again before a page refresh.
						}
					}).pointer('open');
				}
			}
		});
	}
})(jQuery);


jQuery(function($) {
	$('.audiotheme-input-append input').on('focus', function() {
		$(this).parent().addClass('focused');
	}).on('blur', function() {
		$(this).parent().removeClass('focused');
	});
});


/**
 * Media Popup Helper
 *
 * Monitors clicks on any element with a class of "thickbox" and checks
 * for data attributes to modify the behavior the media popup.
 *
 * To change the "Insert Into Post" button text, add the following attribute
 * to the calling element: data-insert-button-text="New Text"
 *
 * To change the field that the media popup inserts the url to the selected
 * media item, add the following attribute with the id of the target element
 * as its value: data-insert-field="Target ID"
 *
 * @todo Test. The interval may not always be cleared when a popup is closed,
 *       so values may need to be reset when a popup is opened.
 */
jQuery(function($) {
	var audiothemeInsertField = null,
		audiothemeInsertButtonText = null
		audiothemeInsertButtonInterval = null;
	
	$('body').on('click', '.thickbox', function() {
		var $this = $(this),
			insertField = $this.data('insert-field'),
			insertButtonText = $this.data('insert-button-text');
		
		clearInterval(audiothemeInsertButtonInterval);
		
		if ( 'undefined' != typeof insertField ) {
			audiothemeInsertField = insertField;
		}
		
		if ( 'undefined' != typeof insertButtonText ) {
			audiothemeInsertButtonText = insertButtonText;
			
			audiothemeInsertButtonInterval = setInterval( function() {
				var buttons = $('#TB_iframeContent').contents().find('.button[name^="send"], #insertonlybutton');
				
				buttons.val( audiothemeInsertButtonText );
				
				if (audiothemeInsertField.length) {
					buttons.off('click').on('click.audiotheme', function(e) {
						var $this = $(this),
							mediaItem = $this.closest('table');
						
						e.preventDefault();
						
						if ( mediaItem.find('#src').length ) {
							url = mediaItem.find('#src').val();
						} else if ( mediaItem.find('.urlfile').length ) {
							url = $(this).closest('table').find('.urlfile').data('link-url');
						}
						
						jQuery('#' + audiothemeInsertField).val(url);
						tb_remove();
				
						audiothemeInsertField = null;
						audiothemeInsertButtonText = null;
						clearInterval(audiothemeInsertButtonInterval);
					});
				}
			}, 500 );
		}
	});
});


/**
 * Meta Repeater
 */
(function($) {
	// .clear-on-add will clear the value of a form element in a newly added row
	// .remove-on-add will remove an element from a newly added row
	
	var methods = {
		init : function( options ) {
			var settings = { };
			if (options) $.extend(settings, options);

			return this.each(function() {
				var repeater = $(this)
					firstItem = repeater.find('.meta-repeater-item:eq(0)');
				
				firstItem.parent().sortable({
					axis: 'y',
					forceHelperSize: true,
					forcePlaceholderSize: true,
					placeholder: 'meta-repeater-placeholder',
					helper: function(e, ui) {
						var $helper = ui.clone();
						$helper.children().each(function(index) {
						  $(this).width(ui.children().eq(index).width())
						});
						
						return $helper;
					},
					start: function(e, ui) {
						var colCount = ui.helper.children().length;
						//ui.placeholder.css('visibility','visible').html('<td colspan="' + colCount + '">&nbsp;</td>');
					},
					update: function(e, ui) {
						repeater.metaRepeater('updateIndex');
					},
					change: function() {
						repeater_id = $( '#' + repeater.attr('id') );
						$('.meta-repeater-sort-warning', repeater_id).fadeIn('slow');
					}
				});
				
				repeater.data('itemIndex', repeater.find('.meta-repeater-item').length).data('itemTemplate', firstItem.clone());
				
				repeater.find('.meta-repeater-add-item').on('click', function(e) {
					e.preventDefault();
					$(this).closest('.meta-repeater').metaRepeater('addItem');
				});
				
				repeater.on('click', '.meta-repeater-remove-item', function(e) {
					var repeater = $(this).closest('.meta-repeater');
					e.preventDefault();
					$(this).closest('.meta-repeater-item').remove();
					repeater.metaRepeater('updateIndex');
				});
				
				repeater.on('blur', 'input', function() {
					$(this).closest('.meta-repeater').find('.meta-repeater-item').removeClass('meta-repeater-active-item');
				}).on('focus', 'input', function() {
					$(this).closest('.meta-repeater-item').addClass('meta-repeater-active-item').siblings().removeClass('meta-repeater-active-item');
				});
			});
		},
		
		addItem : function() {
			var repeater = $(this),
				itemIndex = repeater.data('itemIndex'),
				itemTemplate = repeater.data('itemTemplate');
			
			repeater.find('.meta-repeater-items').append(itemTemplate.clone()).children(':last-child').find('input,select,textarea').each(function(e) {
				var $this = $(this);
				$this.attr('name', $this.attr('name').replace('[0]', '[' + itemIndex + ']') );
				if ('undefined' != typeof $this.attr('id')) {
					$this.attr('id', $this.attr('id').replace('0', itemIndex) );
				}
			}).end().find('.thickbox').each(function(e) {
				var $this = $(this);
				$this.attr('data-insert-field', $this.attr('data-insert-field').replace('0', itemIndex) );
			}).end().find('.clear-on-add').val('').end().find('.remove-on-add').remove().end().find('.show-on-add').show();
			
			repeater.data('itemIndex', itemIndex+1 ).metaRepeater('updateIndex');
		},
			
		updateIndex : function() {
			$('.meta-repeater-index', this).each(function(i) {
				$(this).text(i + 1 + '.');
			});
		}
	};	
	
	$.fn.metaRepeater = function(method) {
		if ( methods[method] ) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.metaRepeater');
		}    
	};
})(jQuery);