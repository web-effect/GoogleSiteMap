# GoogleSiteMap

Version: 2.0.2-rc1
Created: Apr 13, 2016
Author: YJ Tso @sepiariver, Garry Nutting @garryn

- Complete rewrite based on Garry's blazing fast sitemap code
- Added cachemanager
- Efforts were made to make it backwards compatible using runSnippet to call the legacy snippet if legacy features are required.

Examples: 

```
[[!GoogleSiteMap]] // Will output a sitemap many times faster than the legacy Snippet
```

```
[[!GoogleSiteMap? &itemTpl=`gItem`]] // &itemTpl is a legacy feature, so the legacy Snippet will be called. No performance benefit, except new caching mechanism.
```

This project is managed at: [https://github.com/modxcms/GoogleSiteMap](http://github.com/modxcms/GoogleSiteMap).

Read or contribute to the documentation here: [https://rtfm.modx.com/extras/revo/googlesitemap](https://rtfm.modx.com/extras/revo/googlesitemap).

