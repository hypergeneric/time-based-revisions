( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {
		
		var ajaxURL = crtbr_obj.ajax_url;
		var crtbradmin = $( '#crtbrWrapper' );
		
		if ( crtbradmin.length == 0 ) {
			return;
		}
		
		$( '#admin-view-form' ).submit( function( e ) {
			
			e.preventDefault();

			var hours_for_cron    = $( this ).find( '#hours_for_cron' ).val();
			var days_for_deletion = $( this ).find( '#days_for_deletion' ).val();
			var save_timeout      = $( this ).find( '#save_timeout' ).val();
			var cron_timeout      = $( this ).find( '#cron_timeout' ).val();
			var cron_maxrows      = $( this ).find( '#cron_maxrows' ).val();
			var cron_enabled      = $( this ).find( '#cron_enabled' ).is( ':checked' );

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				data:{
					action: 'crtbr_save_admin_page',
					hours_for_cron: hours_for_cron,
					days_for_deletion: days_for_deletion,
					save_timeout: save_timeout,
					cron_timeout: cron_timeout,
					cron_maxrows: cron_maxrows,
					cron_enabled: cron_enabled
				},
				success: function( response ) {
					location.reload();
				}
			} );
			
		} );
	
	});
} ( jQuery, window, document ) );