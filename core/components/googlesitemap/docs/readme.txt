--------------------
Snippet: GoogleSiteMap
--------------------
Version: 2.0.2-rc1
Created: Apr 13, 2016
Author: YJ Tso <yj@modx.com>, Garry Nutting <garry@modx.com>

- Complete rewrite based on Garry's blazing fast sitemap code
- Added cachemanager
- Efforts were made to make it backwards compatible using runSnippet to call the legacy snippet if legacy features are required.

Examples: 
[[!GoogleSiteMap]] // Will output a sitemap many times faster than the legacy Snippet
[[!GoogleSiteMap? &itemTpl=`gItem`]] // &itemTpl is a legacy feature, so the legacy Snippet will be called. No performance benefit, except new caching mechanism.

This project is managed at: https://github.com/modxcms/GoogleSiteMap
--------------------
Version: 1.2
Created: June 23, 2009
Author: Shaun McCormick <shaun@modx.com>

- Based on Michal Till's MODx Evolution GoogleSiteMap_XML snippet

This component builds the GoogleSiteMap XML for you.
    
Example:
[[!GoogleSiteMap]]