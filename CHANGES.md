#### 0.8.0
* update for text from ticket [#43986](https://core.trac.wordpress.org/ticket/43986)
* remove hook interrupting install process
* refactor `is_required_php()`

#### 0.7.0
* added filter in only place on Install Plugin page I could find to add something to the plugin card
* disable hook that interrupts install process

#### 0.6.0
* added caching to store dot org API queries for 12 hours
* added `uninstall.php` to delete cache on uninstall

#### 0.5.0
* fixed PHP notices
* added PHP upgrade nag to plugin row meta

Proof of concept for WordPress.org `readme.txt` `Requires PHP` tag.

- Checks against the Plugin API v1.2 to return dot org `readme.txt` data.
- Unsets the `update_plugins` transient if the server version of PHP is less than the version defined in the `Requires PHP` tag.
- Adds a PHP upgrade nag to plugin row meta.
- Exits the **Add Plugin** process if the server version of PHP is less than the version defined in the `Requires PHP` tag.
- If the `Requires PHP` tag is missing from `readme.txt` everything proceeds as usual without the benefit of a check.

All strings are subject to change.
