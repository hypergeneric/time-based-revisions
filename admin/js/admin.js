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
		var logs_pages = 0;

		function createLogTable ( data ) {
			logs_pages = data.max == 0 ? 0 : Math.floor( data.max / data.count );
			crtbradmin.find( '.logs-start' ).prop( "disabled", logs_pages == 0 || logs_page == 0 );
			crtbradmin.find( '.logs-prev' ).prop( "disabled", logs_pages == 0 || logs_page == 0 );
			crtbradmin.find( '.logs-next' ).prop( "disabled", logs_pages == 0 || data.index + data.count >= data.max );
			crtbradmin.find( '.logs-end' ).prop( "disabled", logs_pages == 0 || logs_page == logs_pages );
			crtbradmin.find( '#logs .meta .page-index' ).text( logs_page + 1 );
			crtbradmin.find( '#logs .meta .page-count' ).text( logs_pages + 1 );
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
			if ( confirm( $( this ).data( 'confirm' ) ) == true ) {
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
			}
			e.preventDefault();
			return false;
		} );

		crtbradmin.find( '.logs-refresh' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			getLogData();
			e.preventDefault();
			return false;
		} );

		crtbradmin.find( '.logs-start' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			logs_page = 0;
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

		crtbradmin.find( '.logs-end' ).click( function( e ) {
			$( '#logs' ).addClass( 'loading' );
			logs_page = logs_pages;
			getLogData();
			e.preventDefault();
			return false;
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
			console.log(rows);
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
			if ( hash == 'log' ) {
				getLogData();
			} else if ( hash == 'stats' ) {
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