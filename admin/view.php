<?php

// pull the options
$hours_for_cron    = crtbr()->options()->get( 'hours_for_cron' );
$days_for_deletion = crtbr()->options()->get( 'days_for_deletion' );
$cron_enabled      = crtbr()->options()->get( 'cron_enabled' );
$enable_logging    = crtbr()->options()->get( 'enable_logging' );
$cron_timeout      = crtbr()->options()->get( 'cron_timeout' );
$cron_maxrows      = crtbr()->options()->get( 'cron_maxrows' );
$save_timeout      = crtbr()->options()->get( 'save_timeout' );
$stats             = crtbr()->options()->get( 'stats' );
$stats_total       = 0;

foreach ( $stats as $key => $value ) {
	$stats_total += $value;
}

?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div id="admin-view">

	<div id="logo"><img src="<?php echo esc_url( CRTBR_PLUGIN_DIR . 'admin/img/logo.png' ); ?>"></div>
	
	<form id="admin-view-form" autocomplete="off">
		
		<section id="crtbrWrapper">

			<ul class="tabs">
				<li data-tab="stats"><?php esc_html_e( 'Stats', 'time-based-revisions' ); ?></li>
				<li data-tab="settings"><?php esc_html_e( 'Settings', 'time-based-revisions' ); ?></li>
				<?php if ( $cron_enabled == true ) : ?><li data-tab="cron"><?php esc_html_e( 'CRON', 'time-based-revisions' ); ?></li><?php endif; ?>
				<?php if ( $enable_logging == true ) : ?><li data-tab="log"><?php esc_html_e( 'Log', 'time-based-revisions' ); ?></li><?php endif; ?>
			</ul>

			<ul class="tab__content">

				<li id="tab-stats">
					<div class="content__wrapper">
					
						<h1><?php esc_html_e( 'Total Revisions Cleaned', 'time-based-revisions' ); ?>: <?php echo esc_html( $stats_total ); ?></h1>
						<select id="chart-timespan">
							<option value="last-24h" selected><?php esc_html_e( 'Last 24 Hours', 'time-based-revisions' ); ?></option>
							<option value="last-30d"><?php esc_html_e( 'Last 30 Days', 'time-based-revisions' ); ?></option>
							<option value="last-60d"><?php esc_html_e( 'Last 60 Days', 'time-based-revisions' ); ?></option>
							<option value="last-1y"><?php esc_html_e( 'Year to date', 'time-based-revisions' ); ?></option>
						</select>
						<div id="chart" class="ajax-group">
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							<div id="chart_div"></div>
						</div>

					</div>
				</li>

				<li id="tab-settings">

					<div class="content__wrapper">

						<div class="notify">
							<h2><?php esc_html_e( 'Notice', 'time-based-revisions' ); ?></h2>
							<?php if ( defined( 'WP_POST_REVISIONS' ) && is_numeric( WP_POST_REVISIONS ) ) : ?>
							<div class="desc">
								<?php 
									printf(
										wp_kses(
											/* translators: 1: Code tag for the WP_POST_REVISIONS setting, 2: The number of revisions currently set in wp-config.php */
											__( 'To ensure optimal functionality of time-based revisions, the %1$s setting of %2$s in your wp-config.php is being overridden to allow unlimited revisions for all posts that support them. This change ensures that our age-based cleanup operates as expected.', 'time-based-revisions' ),
											[ 'code' => [] ]
										),
										'<code>WP_POST_REVISIONS</code>',
										'<code>5</code>'
									); 
								?>
							</div>
							<?php endif; ?>
							<div class="desc">
								<?php
									printf(
										wp_kses(
											/* translators: 1: Hook name for wp_post_revisions_to_keep */
											__( 'If you\'re customizing revision limits per post type with the %1$s hook, these limits will be respected, but as maximums. Revisions older than your defined retention period will still be deleted, potentially reducing the actual number of revisions below your set limit.', 'time-based-revisions' ),
											[ 'code' => [] ]
										),
										'<code>wp_post_revisions_to_keep</code>'
									);
								?>
							</div>
						</div>

						<div class="field">
							<label for="days_for_deletion"><?php esc_html_e( 'Revision Retention Period', 'time-based-revisions' ); ?></label><br>
							<input id="days_for_deletion" name="days_for_deletion" type="number" placeholder="<?php esc_attr_e( 'In Days', 'time-based-revisions' ); ?>" value="<?php echo esc_attr( $days_for_deletion ); ?>">
							<div class="desc">
								<?php esc_html_e( 'Set the maximum number of days you want to keep revisions. After this period, older revisions will be automatically deleted. For instance, entering 365 will preserve revisions for one year.', 'time-based-revisions' ); ?>
							</div>
						</div>

						<div class="field">
							<label for="save_timeout"><?php esc_html_e( 'Save Deletion Timeout', 'time-based-revisions' ); ?></label><br>
							<input id="save_timeout" name="save_timeout" type="number" placeholder="<?php esc_attr_e( 'In Seconds', 'time-based-revisions' ); ?>" value="<?php echo esc_attr( $save_timeout ); ?>">
							<div class="desc">
								<?php esc_html_e( 'Specify the duration in seconds for the plugin to wait before timing out during the deletion of old revisions upon saving a post.', 'time-based-revisions' ); ?>
							</div>
						</div>
								
						<div class="checkbox">
							<div class="check">
								<input type="checkbox" 
									name="cron_enabled" id="cron_enabled" 
									value="<?php echo esc_attr( $cron_enabled ? 'true' : 'false' ); ?>" 
									<?php if ( $cron_enabled == true ) : ?>checked="checked"<?php endif; ?>
								/>
							</div>
							<div class="label">
								<label for="cron_enabled"><?php esc_html_e( 'Enable Scheduled Cleanup', 'time-based-revisions' ); ?></label>
							</div>
							<div class="desc">
								<?php esc_html_e( 'Check this to activate the automated cleanup CRON schedule. When enabled, the plugin will periodically check and delete outdated revisions based on the settings below.', 'time-based-revisions' ); ?>
							</div>
						</div>

						<div class="checkbox">
							<div class="check">
								<input type="checkbox" 
									name="enable_logging" id="enable_logging" 
									value="<?php echo esc_attr( $enable_logging ? 'true' : 'false' ); ?>" 
									<?php if ( $enable_logging == true ) : ?>checked="checked"<?php endif; ?>
								/>
							</div>
							<div class="label">
								<label for="enable_logging"><?php esc_html_e( 'Enable Logging', 'time-based-revisions' ); ?></label>
							</div>
							<div class="desc">
								<?php esc_html_e( 'Log all actions taken by the revision cleanup.', 'time-based-revisions' ); ?>
							</div>
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'time-based-revisions' ); ?>" />
						
					</div>

				</li>

				<li id="tab-cron">

					<div class="content__wrapper">
						
						<div class="field">
							<label for="hours_for_cron"><?php esc_html_e( 'Cleanup Interval', 'time-based-revisions' ); ?></label><br>
							<input id="hours_for_cron" name="hours_for_cron" type="number" placeholder="<?php esc_attr_e( 'In Hours', 'time-based-revisions' ); ?>" value="<?php echo esc_attr( $hours_for_cron ); ?>">
							<div class="desc">
								<?php esc_html_e( 'Enter the frequency in hours at which the scheduled cleanup should run. For example, setting this to 1 will initiate the cleanup process every hour.', 'time-based-revisions' ); ?>
							</div>
						</div>

						<div class="field">
							<label for="cron_timeout"><?php esc_html_e( 'Cleanup Timeout', 'time-based-revisions' ); ?></label><br>
							<input id="cron_timeout" name="cron_timeout" type="number" placeholder="<?php esc_attr_e( 'In Seconds', 'time-based-revisions' ); ?>" value="<?php echo esc_attr( $cron_timeout ); ?>">
							<div class="desc">
								<?php esc_html_e( 'Define the maximum duration in seconds that the plugin should spend on a cleanup task during each scheduled CRON job before timing out.', 'time-based-revisions' ); ?>
							</div>
						</div>

						<div class="field">
							<label for="cron_maxrows"><?php esc_html_e( 'Maximum Revisions per Cleanup', 'time-based-revisions' ); ?></label><br>
							<input id="cron_maxrows" name="cron_maxrows" type="number" placeholder="" value="<?php echo esc_attr( $cron_maxrows ); ?>">
							<div class="desc">
								<?php esc_html_e( 'Input the maximum number of revisions to be processed in each cleanup job. This helps prevent server overload by limiting the number of revisions handled at once.', 'time-based-revisions' ); ?>
							</div>
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'time-based-revisions' ); ?>" />
						
					</div>

				</li>

				<li id="tab-log">
					<div class="content__wrapper">

						<div id="logs" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>

							<table>
								<thead>
									<th class="time" colspan="1"><span class='handle'><?php esc_html_e( 'Time', 'time-based-revisions' ); ?></span></th>
									<th colspan="1"><span class='handle'><?php esc_html_e( 'Log', 'time-based-revisions' ); ?></span></th>
								</thead>
								<tbody>
									<tr class="seed">
										<td class="time"><span class='timestamp'></span></td>
										<td><span class='logdata'></span></td>
									</tr>
								</tbody>
							</table>

							<button class="button logs-clear" data-confirm="<?php esc_attr_e( 'Are you sure?  This will delete all log data permanently.', 'time-based-revisions' ); ?>"><?php esc_html_e( 'Clear', 'time-based-revisions' ); ?></button>
							<button class="button logs-refresh" title="<?php esc_attr_e( 'Refresh', 'time-based-revisions' ); ?>">&#10226;</button>
							<button disabled class="button button-primary logs-start" title="<?php esc_attr_e( 'Rewind', 'time-based-revisions' ); ?>">&#171;</button>
							<button disabled class="button button-primary logs-prev" title="<?php esc_attr_e( 'Previous', 'time-based-revisions' ); ?>">&#8249;</button>
							<button disabled class="button button-primary logs-next" title="<?php esc_attr_e( 'Next', 'time-based-revisions' ); ?>">&#8250;</button>
							<button disabled class="button button-primary logs-end" title="<?php esc_attr_e( 'Forward', 'time-based-revisions' ); ?>">&#187;</button>
							<span class="meta"><?php esc_html_e( 'Page', 'time-based-revisions' ); ?> <span class="page-index"></span> <?php esc_html_e( 'of', 'time-based-revisions' ); ?> <span class="page-count"></span></span>
						
						</div>
						
					</div>
				</li>

			</ul>
		</section>

	</form>
</div>
