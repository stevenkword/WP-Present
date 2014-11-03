var WPPresentAdmin;
(function($) {

	WPPresentAdmin = function() {
		this.init();
		return this;
	};

	WPPresentAdmin.prototype = {
		init: function() {
			var self = this;
			var currentContainerWidth= $('#container').width();
			var columnWidth = 210;

			$( "#outer-container" ).resizable();

			$('#container').width( currentContainerWidth + columnWidth );
			$( "#container" ).sortable({
				stop: function() {
					self.updateTaxonomyDescription();
				}
			});

			$( ".column-inner" ).sortable({
				connectWith: ".column-inner",
				stop: function() {
					self.updateTaxonomyDescription();
				}
			});

			self.widgetButtonExpand();
			self.widgetButtonDelete();
			self.widgetButtonTidy();
			//self.widgetButtonDetails();
			self.bindButtonAddColumn();
			self.bindButtonRemoveColumn();
			self.bindButtonViewPresentation();
			//self.backfillColumns();
			self.refreshUI();

			// Select the first column on load
			var $columnTitleBar = $('#col-1').children('.widget-top').children('.widget-title');
			self.activateColumn( $columnTitleBar );

			// Save it after the autopop if this is our first time
			if( '' === $('#description').val() ) {
				self.updateTaxonomyDescription();
			}

			$('.spinner').hide();
			return self;
		},

		/**
		 * Sortables and such
		 */
		refreshUI: function () {
			var self = this;

			// Refresh the columns
			$( "#container" ).sortable("refresh");

			// TODO: Fixed outer containers resizable
			//$( "#outer-container" ).resizable("destroy");
			//$( "#outer-container" ).resizable();

			// Append an inner column to each column that doesn't contain any slides.
			$('.column' ).not(":has(div.column-inner)").append('<div class="column-inner ui-sortable"></div>');

			// This is a little bit of hackery.  It would be nice if the sortable("refresh") method worked here.
			// TODO: Investigate hackery
			if ($( '.column-inner' ).data( 'sortable' )) {
				$( ".column-inner" ).sortable("refresh");
			} else {
				$( ".column-inner" ).sortable({
					connectWith: ".column-inner",
					stop: function() {
						self.updateTaxonomyDescription();
					}
				});
			}

			// This really should be called only once inside init
			self.enableColumns();
			self.renumberColumns();
		},

		/**
		 * Backfill Columns
		 */
		backfillColumns: function () {
			var self = this;
			var numSlides = WPPNumSlides[0];
			var numExisting = $('#container > .column').size();

			for (var col=numExisting+1;col<=numSlides;col++){
				self.addColumn();
			}
			//$('#container').append( '<div style="clear: both;"></div>' );
		},

		/**
		 * Make the column the target for adding new columns
		 */
		activateColumn: function ( $col ) {
			// TODO: cache widget-title
			// Remove the active class from all columns
			var $widgetTitle = $('.widget-title');
			//$widgetTitle.css({ 'background' : '', 'color' : '' });
			$widgetTitle.removeClass('active wp-ui-highlight');

			// Select the given column
			//$col.css({ 'background-color' : '#0074a2', 'color' : '#ffffff' });
			$col.addClass('active wp-ui-highlight');
		},

		/**
		 * Encode,update, and save the columns data into the hidden taxonomy description field
		 */
		updateTaxonomyDescription: function () {
			var self = this;
			var columns = { }; // Creates a new object
			var i = 1;
			$( '.column-inner' ).each( function() {
				var $order = $(this).sortable( "toArray" );
				columns['col-'+i] = $order;
				i++;
			});
			var encoded = JSON.stringify( columns );
			$('#description').val(encoded);

			// This is a convient time to do some UI clean-up
			self.renumberColumns();

			// Send this change off to ajax land
			self.updatePresentation();
		},

		/**
		 * AJAX request to update the current presentation taxonomy
		 */
		updatePresentation: function () {
			var nonce = $('#wp-present-nonce').val();
			var params = { 'content':$('#description').val(), 'id':presentation, 'nonce':nonce };
			$.ajax({
				url: ajaxurl + '?action=update_presentation',
				type: 'POST',
				data: jQuery.param(params),
				beforeSend: function() {
					$('.spinner').show();
				},
				complete: function() {
					//$('.spinner').hide();
				},
				success: function() {
					$('.spinner').hide();
				}
			});
		},

		/**
		 * Bind Details button
		 */
		widgetButtonDetails: function () {
			$('.action-buttons').on('click', '#details-button', function(e) {
				//e.preventDefault();
				//alert('Details');
			});
		},

		/**
		 * Bind Tidy button (unused)
		 */
		widgetButtonTidy: function () {
			$('.action-buttons').on('click', '#tidy-button', function(e) {
				e.preventDefault();
				this.consolidateColumns();
				// Why does this break?
				//updatePresentation();
			});
		},

		/**
		 * Expand slide details
		 */
		widgetButtonExpand: function () {
			// Change Icons, that's it!
		},

		/**
		 * The action taken by the Tidy button (unused)
		 */
		consolidateColumns: function () {
			var self = this;
			var numCols = $('.column').length;

			$('.column').each(function(outerIndex){

				// Don't have a col 0
				outerIndex = outerIndex + 1;

				// Fixes the condition where we are looking at the last item
				if( outerIndex >= numCols )
					return;

				var $outerCol = $(this);
				var $innerCol = $outerCol.children('.column-inner');

				var innerHTML = $innerCol.html().trim();
				var outerHTML = $outerCol.html().trim();

				if( typeof(innerHTML) !== 'string' || '' === innerHTML ) {
					var $nextOuterCol = $('#col-'+(outerIndex+1));
					var nextOuterHTML = $nextOuterCol.html().trim();

					$outerCol.html(nextOuterHTML);
					$nextOuterCol.html(outerHTML);
				}
			} );

			//Finally refresh description array
			$( ".column-inner" ).sortable({
				connectWith: ".column-inner",
				stop: ( function() {
					self.updateTaxonomyDescription();
				}),
				create: ( function() {
					self.updateTaxonomyDescription();
				})
			});
			self.updateTaxonomyDescription();
		},

		/**
		 * Setup the column to allow for activation
		 */
		enableColumns: function () {
			var self = this;
			$('.column').children('.widget-top').on('click', '.widget-title', function() {
				var $col = $(this);
				self.activateColumn( $col );
			});
		},

		// Bind Delete button
		widgetButtonDelete: function () {
			var self = this;
			$('#container').on('click', '.widget-control-remove', function(e) {
				e.preventDefault();

				var confirmDelete = confirm("You are about to permanently delete the selected slide. 'Cancel' to stop, 'OK' to delete.");
				if( false === confirmDelete )
					return;

				var $button       = $(this);
				var $parentWidget = $button.parents('.widget');
				var widgetID      = $parentWidget.find('.slide-id').val();
				var nonce         = $('#wp-present-nonce').val();
				var params        = { 'id':widgetID, 'nonce':nonce };

				// AJAX request to delete slides
				$.ajax({
					url: ajaxurl + '?action=delete_slide',
					type: 'POST',
					data: jQuery.param(params),
					beforeSend: function() {
						$('.spinner').show();
					},
					complete: function() {
						//$('.spinner').hide();
					},
					success: function() {
						$('.spinner').hide();
						$parentWidget.remove();
						self.updateTaxonomyDescription();
					}
				});
			});
		},

		// Adds a column to the presentation
		addColumn: function () {
			// TODO: I would be better if the column has the active class instead of the child elements

			var self                  = this;
			var col                   = $('#container > .column').size() + 1;
			var currentContainerWidth = $('#container').width();
			var columnWidth           = 210;

			// TODO: Insert column after the active column as opposed to the end
			//var $activeColumn = $('.widget-title.active').parent('.widget-top').parent('.column').children('.column-inner');

			//$('#container').append( '<div class="column ui-sortable" id="col-'+col+'"><div class="widget-top"><div class="widget-title"><h4 class="hndle">'+col+'<span class="in-widget-title"></span></h4></div></div></div>' );
			$('.widget-title.active').parent('.widget-top').parent('.column').after( '<div class="column ui-sortable" id="col-'+col+'"><div class="widget-top"><div class="widget-title"><h4 class="hndle">'+col+'<span class="in-widget-title"></span></h4></div></div></div>' );

			$('#container').width( currentContainerWidth + columnWidth );
			self.renumberColumns();
		},

		// Adds a column to the presentation
		removeColumn: function () {
			// TODO: I would be better if the column has the active class instead of the child elements

			var confirmRemove = confirm("You are about to permanently delete the selected column. 'Cancel' to stop, 'OK' to delete.");
			if( false === confirmRemove )
				return;

			var self = this;
			var currentContainerWidth = $('#container').width();
			var columnWidth           = 210;

			$('.widget-title.active').parent('.widget-top').parent('.column').remove();

			$('#container').width( currentContainerWidth - columnWidth );
			self.renumberColumns();

			// Activate the first column
			var $columnTitleBar = $('#col-1').children('.widget-top').children('.widget-title');
			self.activateColumn( $columnTitleBar );
		},

		//Bind Add Column button to addColumn()
		bindButtonAddColumn: function () {
			var self = this;
			$('.action-buttons').on('click', '#add-column', function(e) {
				e.preventDefault();
				self.addColumn();
				self.refreshUI();
				self.updateTaxonomyDescription();
				//self.updatePresentation();
			});
		},

		//Bind Remove Column button to removeColumn()
		bindButtonRemoveColumn: function () {
			var self = this;
			$('.action-buttons').on('click', '#remove-column', function(e) {
				e.preventDefault();
				self.removeColumn();
				self.refreshUI();
				self.updateTaxonomyDescription();
				//self.updatePresentation();
			});
		},

		//Bind View Presentation button
		bindButtonViewPresentation: function () {
			$('.action-buttons').on('click', '#view-button', function(e) {
				e.preventDefault();
				window.open(WPPTaxonomyURL,'_blank');
			});
		},

		renumberColumns: function() {
			var i = 1;
			$( '.column' ).each( function() {
				var self = this;
				var $self = $(self);
				$self.find('> .widget-top > .widget-title > h4').html( i );
				i++;
			});
		},

		loadModalBackground: function() {
			var self = this;
		},


		/**
		 * Reset Modal
		 */
		resetModal: function() {

			// Clear the editor
			/*
			if( typeof(tinymce) !== undefined ) {
				tinyMCE.activeEditor.setContent(''); // tinymce.get('editor_slide');
			}
			*/

			// Make existing content go away
			$( '#slide-title' ).val( '' );
			$( '#slide-slug' ).val( '' );
			$('.theme-name').html( '' );

			// Reset customizer background image
			//$('#customize-control-wp_present_background_image .dropdown-content img').hide();
			//$('#customize-control-wp_present_background_image .dropdown-status').show();

			// Colors
			// This is a temporary fix that will not play nicely with default themes.  Note it!
			$('#customize-control-wp_present_background_color .color-picker-hex').val( '#fff' ).change();
			$('#customize-control-wp_present_text_color .color-picker-hex').val( '#000' ).change();
			$('#customize-control-wp_present_link_color .color-picker-hex').val( '#0000EE' ).change();

			$('.dropdown-content img').attr('src','').change();
		},

	};

})(jQuery);
var slideManager = new WPPresentAdmin();
WPPresentAdmin.maxModalEditorHeight = 0;