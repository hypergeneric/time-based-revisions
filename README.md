# Time-based Revision Cleanup

Keep the history. Lose the clutter. Retain WordPress revisions by age, not count, with optional scheduled cleanup.

## Description

**Keep the history. Lose the clutter.**

Keep your WordPress revision history by age, not count.

A busy day of editing shouldn't erase months of revision history.

WordPress revisions present a familiar tradeoff: limit them by number, and frequent edits can push important older versions out of reach. Keep them indefinitely, and your database can accumulate years of revisions.

**Time-based Revision Cleanup takes a smarter approach: keep revisions by age, not count.**

Choose how long your revision history should last: 30 days, 90 days, a year, or whatever works for your site. Every revision inside that retention window can be preserved, regardless of how often you edit. Older revisions are automatically cleaned up instead of piling up forever.

**Keep the history. Lose the clutter.**

### Why time-based retention?

Imagine you limit WordPress to 10 revisions. After 10 more edits, the oldest saved version is gone, even if it was an important checkpoint from earlier that morning.

With Time-based Revision Cleanup, you can keep **365 days of revision history** (the default) whether you make five edits or five hundred. The retention period, not an arbitrary revision count, determines what gets cleaned up.

It's a practical balance between useful edit history and long-term database maintenance, particularly for actively maintained websites.

### Features

* **Keep revisions by age, not count.** Set a retention period in days and preserve revisions within that window.
* **Automatic cleanup on save.** Remove expired revisions when content is updated, with a configurable execution-time limit.
* **Optional scheduled cleanup.** Use WP-Cron to work through older revisions across the site, including content that hasn't been edited recently.
* **Keep editing responsive.** When scheduled cleanup is enabled, you can turn off save-time cleanup and let background maintenance handle the work.
* **Control the workload.** Set the scheduled cleanup interval, maximum revisions per run, and execution-time limits.
* **See the results.** View total revisions deleted and cleanup activity over the last 24 hours, 30 days, 60 days, or year to date. Charts use a locally bundled copy of ApexCharts.
* **Optional activity logging.** Inspect cleanup activity when troubleshooting, with logging disabled by default.
* **Developer-friendly exclusions.** Use the `crtbr_delete_revision` filter to protect specific revisions from deletion.
* **Works with supported content types.** Manages revisions for posts, pages, and custom post types that support WordPress revisions.

No external service, API key, or account is required.

## Installation

1. Install **Time-based Revision Cleanup** from **Plugins > Add New**, or upload the plugin ZIP.
2. Activate the plugin.
3. Go to **Settings > Time-based Revision Cleanup**.
4. Choose a **Revision Retention Period**. The default is 365 days.
5. Optionally enable **Scheduled Cleanup** for site-wide maintenance, and configure its interval and processing limits.
6. Save your settings. The **Stats** tab shows cleanup activity as revisions are deleted.

Cleanup runs when supported content is saved by default. Scheduled cleanup is optional and disabled by default.

## Frequently Asked Questions

### How is this different from limiting revisions in wp-config.php?

`WP_POST_REVISIONS` limits the number of revisions WordPress keeps. A busy editing session can quickly displace older revisions, even when those revisions are still useful.

Time-based Revision Cleanup overrides the numeric limit for post types that support revisions, then uses the configured retention period to determine which revisions are eligible for deletion.

### Will it clean up revisions that already exist?

Yes. Save-time cleanup processes eligible revisions associated with the content being saved. Enable scheduled cleanup to process older revisions across the site, including posts that are no longer regularly edited.

### Do I need to enable scheduled cleanup?

No. Save-time cleanup is enabled by default. Scheduled cleanup is useful when you want site-wide maintenance without having to edit each post. It is also the option to use if you want to move cleanup work away from the save process.

### Can I disable cleanup when saving a post?

Yes. Enable **Scheduled Cleanup**, then select **Disable Save-based Cleanup**. This lets the scheduled task handle retention without doing deletion work during normal post saves.

### Does scheduled cleanup run at an exact time?

Not necessarily. Scheduled tasks use WordPress WP-Cron, which depends on WordPress being triggered by site activity or an external cron setup. The configured interval controls when cleanup becomes due, not a guaranteed execution time.

### Will this speed up my website or shrink my database?

Removing unnecessary revision records can reduce database clutter and the amount of revision data stored, especially on heavily edited sites. The actual impact depends on the site, database, and hosting environment. The plugin does not optimize database tables or guarantee a particular performance improvement or reduction in disk usage.

### Does it delete published posts or pages?

No. It deletes eligible WordPress revision records, not the published content itself. However, deleted revisions cannot be restored through WordPress revision history. Back up your database before making significant changes to your retention period.

### Does it work with custom post types?

Yes, provided the post type supports WordPress revisions.

### Can a developer protect a revision from cleanup?

Yes. The `crtbr_delete_revision` filter receives the deletion decision and revision object. Returning `false` prevents that revision from being deleted. Protected revision IDs are also excluded from later scheduled cleanup queries.

### Where are the logs stored?

When logging is enabled, the plugin writes to `wp-content/uploads/time-based-revisions/log.txt`. The file may be publicly reachable depending on your server configuration, and it can grow over time. Leave logging disabled unless you need it for troubleshooting, and ensure the file is protected appropriately.

### What happens when I deactivate the plugin?

Your existing WordPress revisions remain in the database. Deactivation clears the scheduled cleanup task and preserves your settings, cleanup statistics, and activity log. Deleting the plugin through WordPress removes its settings, statistics, and activity log.

## Screenshots

1. Cleanup statistics showing the total revisions removed and a chart for the last 24 hours, 30 days, 60 days, or year to date.
2. Retention and cleanup settings, including the age threshold, save-time processing limit, and optional logging.
3. Scheduled cleanup controls for the interval, execution-time limit, and maximum revisions processed per run.

## Changelog

### 1.1.6
* Simplified the admin screen header to use the plugin name without a logo image.

### 1.1.5
* Secured admin AJAX requests with capability and nonce checks.
* Added validation for cleanup settings and chart periods.
* Preserved settings, statistics, and logs during deactivation and reactivation.
* Fixed the settings page link and refreshed the cleanup schedule when settings change.
* Updated the plugin description, documentation, banner, and icon.
* Tested with WordPress 7.1.4. Requires WordPress 6.2 for the revision cleanup hook.

### 1.1.4
* Version metadata update.

### 1.1.3
* Version metadata update.

### 1.1.1
* Fixed incorrect CRTBR variable prefixes.

### 1.1.0
* Replaced Google Charts with locally bundled ApexCharts.
* Updated WordPress compatibility information for version 6.8.

### 1.0.11
* Added file-based logging.

### 1.0.10
* Updated charting documentation.

### 1.0.8
* Code cleanup.

### 1.0.0
* Initial release.

## Plugin information

| Field | Value |
| --- | --- |
| Requires WordPress | 6.2 or later |
| Tested through | 7.1 |
| Requires PHP | 7.4 or later |
| Stable release | 1.1.6 |
| License | [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) |
| Contributors | michaelsablone |
| Directory tags | post revisions, revision history, revision cleanup, database cleanup, revision management |

[Donate](https://www.paypal.com/donate/?hosted_button_id=EUHE8NXYEXJJ6)
