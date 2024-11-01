=== Plugin Name ===
Contributors: brunoneves
Donate link: 
Tags: gallery, list, list products
Requires at least: 2.0.2
Tested up to: 2.1
Stable tag: 4.3

This pluin generates a XML file to you use with a list, swf files or any another functionality.

== Description ==

XML-Galley is a simple and efficient plugin to use a xml gallery. You can use this with flash movies that needs xml files to load anything or to show the cadastred items.

To show the items in your blog without flash movies the XML-Gallery have a native function to do this. To see how this work you can visit the "FAQ" tab.

== Installation ==

The intallation is sample like any WP Plugin.

1. Upload `XML-Gallery` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'XML-Gallery' menu in WordPress
3. To use this in your WP blog without falsh movies to load the XML files visit the "FAQ" tab.

== Frequently Asked Questions ==

= How to use the XML-Gallery plugin without a flash movie to load this =

The XML-Gallery plugin have a native function to load and show the gallery. To use this is so easy look:

<pre>
&lt;?php
if(function_exists('xml_gallery_theme')) {
xml_gallery_theme($before,$after);
}
?&gt;
</pre>

e.g:
<pre>
&lt;?php
if(function_exists('xml_gallery_theme')) {
xml_gallery_theme("&lt;li class=\"itenGallery\"&gt;","&lt;/li&gt;");
}
?&gt;
</pre>

== Changelog ==

= 0.1 =
Starting the life of this plugin, all comments, critiques or suggestions are welcome.

= 0.2 =
Bug in time to edit image and link fixed.

`<?php code(); // goes in backticks ?>`
