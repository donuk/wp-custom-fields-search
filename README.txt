=== Plugin Name ===
Contributors: don@don-benjamin.co.uk
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=don@webhammer.co.uk&item_name=Custom Search Donation&currency_code=GBP
Tags: search,custom fields,widget,sidebar
Requires at least: 3.0.1
Tested up to: 4.7.3
Stable tag: 0.3.28
License: Apache 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0
 
Build search forms to provide custom search functionality allowing search of built in post fields, custom fields via a variety of different inputs and comparison types.
 
== Description ==
 
This plugin provides an admin interface allowing you to build powerful search forms for your wordpress site.

You can configure a number of inputs of different types, to search different fields of your posts in different ways.  These will then be presented to your users as a simple form interface allowing them to find the content they need.

 
== Installation ==
 
1. Unzip `wp-custom-fields-search.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Either add a sidebar widget or include one of the presets via short_code or php code

= Add a sidebar widget =

1. Navigate to the widgets page in your wordpress admin area ( Appearance > Widgets )
1. In the available widgets list you should see "WPCFS Custom Search Form", drag this into the appropriate sidebar.
1. click save on the new widget.
1. Navigate to the front-end of your site

You should now see a very basic search form in that sidebar.  You can expand on this using the instructions below under configuring your form


= Include a preset =

1. Navigate the WP Custom Fields Search section in the menu
1. Either copy the shortcode text into a post / page
1. Or copy the php code into your template
1. Navigate to the front-end of your site

You should now see a very basic search form in that sidebar.  You can expand on this using the instructions below under configuring your form

= Configuring your form =

Each form consists of a list of fields.  Clicking the "New Field" button will add new fields to the list.  Clicking the delete button (labelled X) next to a field will remove it again.

Fields can be re-ordered by drag and drop.

Clicking the edit button (spanner icon) on a field will open up the field edit form, allowing you to configure how that field appears and how it searches the data.

There are three main sections to the field edit form.  Data-type selection which configures which post fields this input will search (e.g. post title or category).  Data-comparison selection which configures how the users search data is compared to the post data to look for matches (e.g. match exactly or match if the word appears in the text).  And the input, which controls how the field appears in the search form and how the user can enter their search query (e.g. a text input or a drop-down element)

There are a core set of datatypes, comparisons and inputs which can be expanded on with extension plugins.

== Changelog ==
 
= 1.0 =
* This is a major rebuild from 0.3.28, the rebuild should allow for easier extension and configuration
* If you are using bespoke or customised versions based on the 0.3 plugin those customisations will almost certainly not be compatible with this upgrade.
 
= 0.3.28 =
* Stable legacy version
 
== Upgrade Notice ==
 
= 1.0 =
* This is a major rebuild from 0.3.28, this should make form configuration significantly easier.
* This will enable new extended functionalities.
* This has been tested against the latest versions of wordpress
* If you are using bespoke or customised versions based on the 0.3 plugin those customisations will almost certainly not be compatible with this upgrade.
 
= 0.3.28 =
This version simply adds a notice warning that the upgrade to 1.0.0 may break compatibility with historic extensions and inviting users to the beta release.
 
= 0.3.27 = 
Stable-ish for up to 2 years.

