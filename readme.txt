=== Plugin Name ===
Contributors: tagplay
Tags: widgets, shortcodes
Requires at least: 3.3
Tested up to: 4.2.4
Stable tag: 1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides Tagplay widget functionality to show social media posts managed by Tagplay (https://tagplay.co).

== Description ==

This is a plugin for integrating [Tagplay widgets](https://tagplay.co) into WordPress. It adds:

-  a WordPress widget that can be used in sidebars or other widget areas
-  a shortcode for use in posts or pages

Both the widget and shortcode are simple wrappers around Tagplay's widget, which pulls social media posts into a curated feed.
For more information, visit https://tagplay.co.

== Installation ==

1. Upload the `tagplay-widget` directory to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in the WordPress admin.
3. Sign up on https://tagplay.co if you haven't already, create some feeds, and get some posts into them!
4. Click the name of the feed you want to integrate into your WordPress site on your Tagplay dashboard.
5. Click 'Get Code' in the upper right corner of the feed detail page.
6. Below the widget designer, click the WordPress button on the left under the 'Get Code' heading.
7. Follow the instructions there for either the widget or shortcode.

== Frequently Asked Questions ==

= What is Tagplay? =

Tagplay is a service that allows you to collect posts from your social media accounts - or even everyone's - and pull them
into 'feeds', optionally using hashtags to control where the post should go. Feeds can then be integrated into your website,
where they will automatically update whenever you make posts on your social media accounts. For more information, visit
https://tagplay.co.

= Do I need a Tagplay account to use this plugin? =

Yes, you will need a Tagplay account. Both the widget and shortcode are only wrappers around Tagplay's HTML/Javascript widget.
They require a Tagplay project ID, feed ID and authentication token.

= Why should I use Tagplay instead of a simple social media feed widget? =

Tagplay can combine feeds from multiple social media accounts into one, allows moderation, pinning and bumping within the
feed, and allows you to make certain feeds collect only posts with certain hashtags, making for a much greater level of
control over your content. For instance, a restaurant site could have both a Meal of the Day feed and a News feed, controlled
from the same social media account but one collecting only posts hashtagged #mealoftheday and one collecting only #news.

== Screenshots ==

1. The admin interface for the widget.
2. A Tagplay widget in a post, using the shortcode. The appearance of the widget can be modified using shortcode attributes.
3. A Tagplay widget in a sidebar, with a different appearance showing only the images.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release.
