<?php

// pull the options
$hours_for_cron    = crtbr()->options()->get( 'hours_for_cron', 1 );
$days_for_deletion = crtbr()->options()->get( 'days_for_deletion', 365 );
$cron_enabled      = crtbr()->options()->get( 'cron_enabled' );
$cron_timeout      = crtbr()->options()->get( 'cron_timeout', 30 );
$cron_maxrows      = crtbr()->options()->get( 'cron_maxrows', 50 );
$save_timeout      = crtbr()->options()->get( 'save_timeout', 15 );

?>
<div id="admin-view">

	<h1><?php esc_html_e( 'Time-based Revisions', 'crtbr' ); ?></h1>
	
	<form id="admin-view-form" autocomplete="off">
		
		<section id="crtbrWrapper">

			<div class="content__wrapper">

				<div class="field">
					<label for="days_for_deletion"><?php esc_html_e( 'Revision Retention Period', 'crtbr' ); ?></label><br>
					<input id="days_for_deletion" name="days_for_deletion" type="number" placeholder="<?php esc_attr_e( 'In Days', 'crtbr' ); ?>" value="<?php echo esc_attr( $days_for_deletion ); ?>">
					<div class="desc">
						<?php esc_html_e( 'Set the maximum number of days you want to keep revisions. After this period, older revisions will be automatically deleted. For instance, entering 365 will preserve revisions for one year.', 'crtbr' ); ?>
					</div>
				</div>

				<div class="field">
					<label for="save_timeout"><?php esc_html_e( 'Save Deletion Timeout', 'crtbr' ); ?></label><br>
					<input id="save_timeout" name="save_timeout" type="number" placeholder="<?php esc_attr_e( 'In Seconds', 'crtbr' ); ?>" value="<?php echo esc_attr( $save_timeout ); ?>">
					<div class="desc">
						<?php esc_html_e( 'Specify the duration in seconds for the plugin to wait before timing out during the deletion of old revisions upon saving a post.', 'crtbr' ); ?>
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
						<label for="cron_enabled"><?php esc_html_e( 'Enable Scheduled Cleanup', 'crtbr' ); ?></label>
					</div>
					<div class="desc">
						<?php esc_html_e( 'Check this to activate the automated cleanup CRON schedule. When enabled, the plugin will periodically check and delete outdated revisions based on the settings below.', 'crtbr' ); ?>
					</div>
				</div>
				
				<div class="field">
					<label for="hours_for_cron"><?php esc_html_e( 'Cleanup Interval', 'crtbr' ); ?></label><br>
					<input id="hours_for_cron" name="hours_for_cron" type="number" placeholder="<?php esc_attr_e( 'In Hours', 'crtbr' ); ?>" value="<?php echo esc_attr( $hours_for_cron ); ?>">
					<div class="desc">
						<?php esc_html_e( 'Enter the frequency in hours at which the scheduled cleanup should run. For example, setting this to 1 will initiate the cleanup process every hour.', 'crtbr' ); ?>
					</div>
				</div>

				<div class="field">
					<label for="cron_timeout"><?php esc_html_e( 'Cleanup Timeout', 'crtbr' ); ?></label><br>
					<input id="cron_timeout" name="cron_timeout" type="number" placeholder="<?php esc_attr_e( 'In Seconds', 'crtbr' ); ?>" value="<?php echo esc_attr( $cron_timeout ); ?>">
					<div class="desc">
						<?php esc_html_e( 'Define the maximum duration in seconds that the plugin should spend on a cleanup task during each scheduled CRON job before timing out.', 'crtbr' ); ?>
					</div>
				</div>

				<div class="field">
					<label for="cron_maxrows"><?php esc_html_e( 'Maximum Revisions per Cleanup', 'crtbr' ); ?></label><br>
					<input id="cron_maxrows" name="cron_maxrows" type="number" placeholder="" value="<?php echo esc_attr( $cron_maxrows ); ?>">
					<div class="desc">
						<?php esc_html_e( 'Input the maximum number of revisions to be processed in each cleanup job. This helps prevent server overload by limiting the number of revisions handled at once.', 'crtbr' ); ?>
					</div>
				</div>
				
				<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'crtbr' ); ?>" />
				
			</div>

		</section>

	</form>
</div>
