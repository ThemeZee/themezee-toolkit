/*! jQuery Tabbed Content
  Javascript for Tab Content Widget
  Author: Thomas W (themezee.com)
*/

(function($) {

	$.fn.tabbedWidget = function( widget ) {
		
		var instance = '#' + widget.attr('id');

		$(instance + ' .tzwb-tabnavi li a:first').addClass('current-tab'); //add active class to the first li
		$(instance + ' .tzwb-tabcontent').hide(); //hide all content classes.
		$(instance + ' .tzwb-tabcontent:first').show(); //show only first div content

		$(instance + ' .tzwb-tabnavi li a').click(function(){ //add the click function
			$(instance + ' .tzwb-tabnavi li a').removeClass('current-tab'); //remove current-tab class from previous li
			$(this).addClass('current-tab'); //add current-tab class to the active li.
			$(instance + ' .tzwb-tabcontent').hide(); //hide all content classes
			var activeTab = $(this).attr('href'); //find the href attribute of the active tab
			$(activeTab).fadeIn('fast'); //fade in the content of active tab
			return false;
		});
	};
	
	function initTabbedWidget( widget ) {
		widget.find( '.tzwb-tabbed-content' ).tabbedWidget(widget);
	}
	
	$( document ).ready( function() {
		$( '.tzwb-tabbed-content').each( function () {
			initTabbedWidget( $( this ) );
		} );
	} );

}(jQuery));