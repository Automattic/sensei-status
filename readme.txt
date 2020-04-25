=== Sensei LMS Status and Tools ===
Contributors: automattic, jakeom
Tags: sensei lms, status, tools
Requires at least: 4.9
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Sensei LMS Status and Tools is a feature plugin to test out new tools to be incorporated into Sensei LMS. 

This plugin requires Sensei LMS v3.0 or greater. 

Several tools are included in the plugin, including:
 - Recalculate Course Enrollment: Manually recalculate course enrollment for a specific course.
 - Debug Course Enrollment: Debug issues around a learner's enrollment in a course.
 
In addition, this plugin will add helpful information in WordPress' Site Health tool.

To add a helpful `Debug Enrollment` button in Learner Management, add this snippet:
```
add_filter( 'sensei_show_enrolment_debug_button', '__return_true' );
```

== Installation ==

= Automatic installation =

1. Log into your WordPress admin panel and go to *Plugins* > *Add New*.
2. Enter "Sensei LMS Status and Tools" into the search field.
3. Once you've located the plugin, click *Install Now*.
4. Click *Activate*.

= Manual installation =

1. Download the plugin file to your computer and unzip it.
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory on the server.
3. Log into your WordPress admin panel and activate the plugin from the *Plugins* menu.

== Screenshots ==
1. Tools page
2. Debug enrollment tool

== Changelog ==
[See changelog for all versions](https://raw.githubusercontent.com/automattic/sensei-status/master/changelog.txt).
