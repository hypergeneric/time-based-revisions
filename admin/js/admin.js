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
			var enable_logging    = $( this ).find( '#enable_logging' ).is( ':checked' );

			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				cache: false,
				data:{
					action: 'crtbr_save_admin_page',
					hours_for_cron: hours_for_cron,
					days_for_deletion: days_for_deletion,
					save_timeout: save_timeout,
					cron_timeout: cron_timeout,
					cron_maxrows: cron_maxrows,
					cron_enabled: cron_enabled,
					enable_logging: enable_logging
				},
				success: function( response ) {
					location.reload();
				}
			} );
			
		} );

		// logs

		var logs_page = 0;

		function createLogTable ( data ) {
			crtbradmin.find( '.logs-prev' ).prop( "disabled", logs_page == 0 );
			crtbradmin.find( '.logs-next' ).prop( "disabled", data.index + data.count >= data.max );
			crtbradmin.find( '#logs .meta' ).text( "Page " + ( logs_page + 1 ) + " of " + ( Math.floor( data.max / data.count ) + 1 ) );
			crtbradmin.find( '#logs tbody tr:not( .seed )' ).remove();
			var seed = crtbradmin.find( '#logs tbody tr.seed' );
			for ( var i = 0; i < data.rows.length; i++ ) {
				var url = data.rows[ i ];
				var clone = seed.clone( true );
				clone.removeClass( 'seed' );
				clone.find( '.timestamp' ).text( new Date( url[0] * 1000 ).toLocaleString()  );
				clone.find( '.logdata' ).text( url[1] );
				clone.find( '.button-delete' ).attr( 'data-url', url[0] );
				clone.find( '.button-delete' ).data( 'url', url[0] );
				crtbradmin.find( '#logs tbody' ).append( clone );
			}
		}

		function getLogData () {
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				cache: false,
				data:{
					page: logs_page,
					action: 'crtbr_logs_get_page'
				},
				success: function( response ) {
					$( '#logs' ).removeClass( 'loading' );
					createLogTable( response.data );
				}
			} );
		}

		crtbradmin.find( '.logs-clear' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				cache: false,
				data:{
					action: 'crtbr_logs_clear'
				},
				success: function( response ) {
					$( '#logs' ).removeClass( 'loading' );
					createLogTable( response.data );
				}
			} );
			e.preventDefault();
			return false;
		} );

		crtbradmin.find( '.logs-refresh' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			getLogData();
			e.preventDefault();
			return false;
		} );

		crtbradmin.find( '.logs-prev' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			logs_page -= 1;
			getLogData();
			e.preventDefault();
			return false;
		} );

		crtbradmin.find( '.logs-next' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			logs_page += 1;
			getLogData();
			e.preventDefault();
			return false;
		} );

		// tabs
		var tabs         = crtbradmin.find( '.tabs > li' );
		var tabs_content = crtbradmin.find( '.tab__content > li' );
		var page_hash    = window.location.hash == '' ? tabs.first().data( 'tab' ) : window.location.hash.substr( 1 );
		
		function setCurrentTab ( hash ) {
			tabs.each( function () {
				if ( $( this ).data( 'tab' ) == hash ) {
					tabs.removeClass( 'active' );
					$( this ).addClass( 'active' );
					tabs_content.removeClass( 'active' );
					$( '#tab-' + hash ).addClass( 'active' );
				}
			} );
			window.location.hash = hash;
			if ( hash == 'log' ) {
				getLogData();
			}
		}
		
		tabs.click( function( e ) {
			if ( $( this ).hasClass( 'disabled' ) ) {
				return;
			}
			setCurrentTab( $( this ).data( 'tab' ) );
		} );
		
		setCurrentTab( page_hash );
	
	});
} ( jQuery, window, document ) );