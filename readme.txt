=== Ultimate Security Checker ===
Contributors: bsndev
Tags: security, administration, admin, database
Requires at least: 2.8
Tested up to: 3.4.1
Stable tag: 2.7.9

Plugin helps you identify security problems with your wordpress installation. It scans your blog and give a security grade based on passed tests.

== Description ==

\#1 SECURITY PLUGIN for WordPress! We're the only plugin that gets updated regularly to protect against the latest threats! Why trust your work to a plugin which hasn't been updated in months or years?

Every day, hackers take over WordPress installations and delete the user's data and blog posts and use their servers for illegal activities. Don't be a victim - we'll help you prevent getting your thousands of hours of hard work on your blog taken over by a hacker and deleted.

Our plugin identifies security problems with your WordPress Installation. It scans your blog for hundreds of known threats, then gives you a security "grade" based on how well you have protected yourself. You can fix the problems yourself, or you can use our [help](http://www.ultimateblogsecurity.com/ "help") to do it for you automatically.

Our plugin and service is designed to be used by anyone from a complete newbie to an advanced PHP engineer.

**Customer, Blogger and Media Reviews**

* *"The best part about Ultimate Security Checker is that it's so easy to use."* - digwp.com 
* *"I was not aware that the install.php files remained and posed a security risk. Thanks for that!"* - CLN 
* *"I found an issue I was unaware of... Now I can make sure to fix it on all my blogs."* - Derek 
* *"5/5 Stars!"* - Our customers 

**FEATURES**

* One click installation and activation
* Automatic security scan of your blog
* Calculation of a letter grade based on how protected your blog is

== Installation ==

1. Upload `wp-ultimate-security.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0 =
* Initial version of plugin

= 1.1 = 
* added test for readme.html file
* added test for opened directories
* added test for left installation file

= 1.2 =
* Wordpress 3.0 support
* open beta of Ultimate Security PRO(find the link after taking tests)
* some improvements in existing tests

= 1.3 = 
* added 2 more security tests

= 2.0 = 
* added 2 more security tests
* made the layout more user friendly
* few fix to current check


= 2.0.1 =
* fixed position of subscription block on high resolutions
* sorry everybody, i was too tired and pasted wrong link to our website (green button on security check page)

= 2.1 =
* added files and folders permissions check

= 2.1.3 = 
* cleanup of the code

= 2.1.6 = 
* cleanup of the code
* compatibility with wordpress 3.1
* added widget to Admin Bar

= 2.1.7 = 
* bug fixing

= 2.1.8 = 
* bug fixing related to using short php tags

= 2.2.1 = 
* added two more tests

= 2.5 = 
* added tab with description on how to fix issues
* now tests don't run on every page open, the results are cached in db

= 2.5.5 = 
* fixes in FAQ section according to suggests in forum
* updated blocbadqueries plugin contents in FAQ
* check for /wp-content/ folder now passes in 755 mode too(was 777 before)

= 2.6.0 =
* bug fixes in bbq plugin test
* other fixes

= 2.6.5 =
* bug fixes according to suggests in forum
* added core files test based on md5 hash check.
* added serach of suspicious code patterns in wp core, themes and plugin files.
* added search of suspicious code patterns in posts and comments.
* added report pages for new tests.

= 2.7.0 =
* we separated file check in different tab
* FIX main test don't go out of memory or time limit
* added settings page(notifications settings can be changed and facebook like block can be disabled)

= 2.7.1 =
* added check for core files in other laguages(german, french, italian, russian, ukrainian, espanol)
* minor fixes

= 2.7.2 =
* minor fixes
* added small link to our new project/idea

= 2.7.3 =
* minor fixes
* added hashes for wordpress 3.3

= 2.7.4 =
* minor fixes
* added hashes for wordpress 3.3.1, updated for wordpress 3.3
* show status in admin bar only for users with priveleges

= 2.7.5 =
* minor fixes
* check for custom plugin / wp-admin paths

= 2.7.6 =
* added system information to display wordpress location for UBS users

= 2.7.7 =
* changed some plugin text

= 2.7.8 =
* updated hashes

= 2.7.9 =
* updated hashes
* added Ultimate Blog Security API support