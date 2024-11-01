=== WP Plugin Cache ===
Contributors: dartiss
Donate link: http://artiss.co.uk/donate
Tags: Cache, WordPress, Plugin, Developer
Requires at least: 2.0.0
Tested up to: 3.1
Stable tag: 1.2

WP Plugin Cache provides a file caching facility for any plugin that wishes to interface with it.

== Description ==

WP Plugin Cache was designed to provide developers with an easy to use cache system that they could integrate into their plugins. Now, even the smallest and simplest plugin can use a caching system!

If you're not a developer, you may be asked to install this plugin to add caching features to a plugin. Having a seperate caching facility ensures efficient use of code and, ultimately, improved performance.

However, performance improvements are always down to how any developer implements this plugin - hints and tips are provided throughout the instructions.

Unless you're a developer, wishing to use this plugin to compliment one of your own, there's no need to read any further. If you are, read on!

**For help with this plugin, or simply to comment or get in touch, please read the appropriate section in "Other Notes" for details. This plugin, and all support, is supplied for free, but [donations](http://artiss.co.uk/donate "Donate") are always welcome.**

**Introduction**

When activated, the plugin creates a folder in your `wp-contents/uploads` directory named `plugin_cache`. This will store any cached files.

The main functions to be aware of are `plugin_cache_read` and `plugin_cache_update`. The read and updating functions are seperate to allow for the files to be "stripped down" before being saved, allowing for improved performance. However, if changes are not necessary then the `plugin_cache_read` function can perform the entire caching procedure by itself.

**plugin_cache_read**

The function `plugin_cache_read` has 4 parameters...

*file key* : This is a unique key to identify the cached file. Often, just the filename would be sufficient, but if you need to differentiate between different versions of the same filename (e.g. a news feed which may contain different numbers of entries) then extra data can be added.

*filename* : This is the filename of the file that you wish to read if a cache doesn't exist. This is optional - if no cache is found and a filename hasn't been specified, then nothing will be returned by the function. This allows you to fetch the file yourself.

*timeout* : This is the number of hours after which you wish the cache to be updated. Specifying this causes the function to also perform the same processing as `plugin_cache_update`.

*prefix* : This is an optional parameter and allows you to specify a prefix that will be added to the cache filename. This is useful is plugins wish to identify their cached files with a unique prefix - the plugin can then, additionally, provide the option to delete its own cache files.

So, specifying a filename but NOT a timeout will cause the file to be fetched - either from cache or not. Specifying a timeout and a filename will mean that the file will be fetched and then updated in the cache.

The function will return an array containing the following data...

**cache_update** : Does the cache need updating? This is relevant if you are using the `plugin_cache_update` function, as you can use it to determine whether you need to call it or not. It contains either `Y` or blank.

**data** : This is the returned file contents. If it was not fetched from cache and you did not specify a filename, then this will be blank.

An example of usage would be...

`$return=plugin_cache_read("test","http://www.artiss.co.uk/feed",3,"test");`

This would fetch the feed from my site, updating the cache and giving it a timeout of 3 hours. The cache file would begin with the prefix "test_".

**plugin_cache_update**

The function `plugin_cache_update` has 4 parameters... *file key*, *file contents*, *timeout* and *prefix*.

Only the last parameter is optional and all except the second are the same as with `plugin_cache_read`. The second parameter is the file contents rather than the filename itself.

An example would be...

`$return=plugin_cache_update("test",$data,3);`

This would update the cache with the contents of `$data` for 3 hours.

== Debugging ==

If the cache does not appear to be working, then there is a way of debugging it. After calling the `plugin_cache_read` or `plugin_cache_update` function, you can call a function named `plugin_cache_debug`. The only parameter is the output array from the `plugin_cache_read` or `plugin_cache_update` function.

For example, let's say you call `plugin_cache_read` in the following way...

`$return=plugin_cache_read("test","http://www.artiss.co.uk/feed",3);`

In this case, you can display the debug information via the following function call...

`plugin_cache_debug($return);`

== Housekeeping ==

A number of housekeeping functions exist to help clear up cache files.

The function `remove_cache_file` can be used to remove a file, or collection of files, from the cache folder. A wildcard can be used in the filename, which you must pass as a parameter. For example...

`remove_cache_file("test_*");`

This will remove all cache files with a prefix of "test_".

When deactivating the plugin, no attempt will be made to clear down the cache folder, as you may only be performing a temporary deactivation, for example during a WordPress upgrade. However, a function exists to request this manually.

plugin_cache_uninstall will remove all files from the cache folder and then remove the cache folder. An example of usage would be...

`plugin_cache_uninstall();`

If you wish to recreate the cache folder you must now either deactivated and then reactivate the plugin OR call `plugin_cache_install`.

== Licence ==

This WordPRess plugin is licensed under the [GPLv2 (or later)](http://wordpress.org/about/gpl/ "GNU General Public License").

== Support ==

All of my plugins are supported via [my website](http://www.artiss.co.uk "Artiss.co.uk").

Please feel free to visit the site for plugin updates and development news - either visit the site regularly, follow [my news feed](http://www.artiss.co.uk/feed "RSS News Feed") or [follow me on Twitter](http://www.twitter.com/artiss_tech "Artiss.co.uk on Twitter") (@artiss_tech).

For problems, suggestions or enhancements for this plugin, there is [a dedicated page](http://www.artiss.co.uk/wp-plugin-cache "WP Plugin Cache") and [a forum](http://www.artiss.co.uk/forum "WordPress Plugins Forum"). The dedicated page will also list any known issues and planned enhancements.

Alternatively, please [contact me directly](http://www.artiss.co.uk/contact "Contact Me"). 

**This plugin, and all support, is supplied for free, but [donations](http://artiss.co.uk/donate "Donate") are always welcome.**

== Installation ==

1. Upload the entire `wp-plugin-cache` folder to your `wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it - there's no admin screen!

== Frequently Asked Questions ==

= Which version of PHP does this plugin work with? =

It has been tested and been found valid from PHP 4 upwards.

== Changelog ==  
  
= 1.0 =
* Initial release

= 1.1 =
* Bug fix to cache update (thanks to Bruce Kroeze)
* Added prefix option, allowing plugins to identify their cache files
* Added uninstall function to remove the cache folder and to clear cache files

= 1.2 =
* For pre Wordpress 2.6 versions, a new function has been added to define the locations of the content directory
* Fixed bug in file fetching routine
* Fixed bug where files retrieved from cache may have an extra character at the beginning

== Upgrade Notice ==

= 1.0 =
* Initial release

= 1.1 =
* Upgrade to fix a bug which could affect caches being saved

= 1.2 =
* Upgrade if you are using a version of WordPress pre 2.6 or with to fix 2 critical bugs