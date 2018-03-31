#### [unreleased]

Proof of concept for WordPress.org `readme.txt` `Requires PHP` tag.

- Checks against the Plugin API v1.2 to return dot org `readme.txt` data.
- Unsets the `update_plugins` transient if the server version of PHP is less than the version defined in the `Requires PHP` tag.
- Exits the **Add Plugin** process if the server version of PHP is less than the version defined in the `Requires PHP` tag.
- If the `Requires PHP` tag is missing from `readme.txt` everything proceeds as usual without the benefit of a check.

