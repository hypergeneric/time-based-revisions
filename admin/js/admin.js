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

			var hours_for_cron     = $( this ).find( '#hours_for_cron' ).val();
			var days_for_deletion  = $( this ).find( '#days_for_deletion' ).val();
			var save_timeout       = $( this ).find( '#save_timeout' ).val();
			var cron_timeout       = $( this ).find( '#cron_timeout' ).val();
			var cron_maxrows       = $( this ).find( '#cron_maxrows' ).val();
			var cron_enabled       = $( this ).find( '#cron_enabled' ).is( ':checked' );
			var disable_save_clean = $( this ).find( '#disable_save_clean' ).is( ':checked' );

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
					disable_save_clean: disable_save_clean
				},
				success: function( response ) {
					location.reload();
				}
			} );
			
		} );

		// charting

		var chart_data = 0;
		
		function drawChart() {
			$( '#chart' ).removeClass( 'loading' );
			var rows = [];
			for ( var key in chart_data ) {
				if ( Object.hasOwnProperty.call( chart_data, key ) ) {
					var value    = chart_data[key];
					var key_bits = key.split( '-' );
					if ( key_bits.length == 4 ) {
						var hour = key_bits.pop();
						key = key_bits.join( '-' ) + " " + hour + ":00:00";
					}
					rows.push( [new Date( key ), value ] );
				}
			}
			var data = new google.visualization.DataTable();
			data.addColumn( 'date', 'Date' );
			data.addColumn( 'number', 'Total' );
			data.addRows( rows );
			var options = {
				chart: {
					title: $( "#chart-timespan option:selected" ).text()
				},
				height: 250,
				legend: { position: 'none' },
				colors: ["#000000"],
			};
			var chart = new google.charts.Line( document.getElementById( 'chart_div' ) );
			chart.draw( data, google.charts.Line.convertOptions( options ) );
		}

		function loadChart () {
			google.charts.load( 'current', { 'packages':[ 'line' ] } );
			google.charts.setOnLoadCallback( drawChart );
		}

		function loadChartData () {
			$( '#chart' ).addClass( 'loading' );
			$( '#chart_div' ).empty();
			$.ajax( {
				method: 'POST',
				url: ajaxURL,
				cache: false,
				data:{
					action: 'crtbr_get_stats_data',
					timespan: $( '#chart-timespan' ).val()
				},
				success: function( response ) {
					chart_data = response.data;
					loadChart();
				}
			} );
		}

		crtbradmin.find( '#chart-timespan' ).change( loadChartData );

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
			if ( hash == 'stats' ) {
				loadChartData();
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