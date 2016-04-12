<?php
/**
 * GoogleSiteMap
 *
 * Builds the Google SiteMap XML. 
 * Version 2+ of GoogleSiteMap uses code by Garry Nutting of the MODX Core Team to deliver sitemaps blazingly fast.
 *
 * @author YJ Tso <yj@modx.com>, Garry Nutting <garry@modx.com>
 *
 *
 * GoogleSiteMap is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * GoogleSiteMap is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * GoogleSiteMap; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package googlesitemap
 */

// "300 lives of men I've walked this earth and now I have no time"
ini_set('max_execution_time', 0);

// Set cache options
$cacheKey = $modx->getoption('cacheKey', $scriptProperties, 'googlesitemap');
$cachePartition = $modx->getoption('cachePartition', $scriptProperties, 'googlesitemap');
$expires = $modx->getOption('expires', $scriptProperties, 86400);
$options = array(
  xPDO::OPT_CACHE_KEY => $cachePartition,
);

// Set context(s)
$context = array_filter(array_map('trim', explode(',', $modx->getOption('context', $scriptProperties, $modx->context->get('key'), true))));
$cacheKey .= '-' . implode('-', $context);

// Fetch from cache
$output = null;
$output = $modx->cacheManager->get($cacheKey, $options);
if ($output !== null) return $output;

/* Legacy Snippet handling */
$legacyProps = $modx->getOption('legacyProps', $scriptProperties, 'allowedtemplates,excludeResources,excludeChildrenOf,sortByAlias,templateFilter,itemTpl,startId,where');
$legacyProps = array_flip(array_filter(array_map('trim', explode(',', $legacyProps))));
$legacyProps = array_intersect_key($scriptProperties, $legacyProps);
$legacySnippet = $modx->getOption('legacySnippet', $scriptProperties, 'GoogleSiteMapVersion1');

if (!empty($legacyProps) && $modx->getCount('modSnippet', array('name' => $legacySnippet))) {
    
    $output = $modx->runSnippet($legacySnippet, $scriptProperties);
    if ($output !== null) {
        $modx->cacheManager->set($cacheKey, $output, $expires, $options);
        return $output;
    }
    
}

/* Begin new Snippet scope */
$googleSchema = $modx->getOption('googleSchema',$scriptProperties,'http://www.sitemaps.org/schemas/sitemap/0.9');

/* Map specified filter properties to new variables for convenience */
$filters = array();
$filters['deleted'] = ($modx->getOption('hideDeleted', $scriptProperties, true)) ? 's.deleted = 0' : false;
$filters['hidemenu'] = ($modx->getOption('showHidden', $scriptProperties, false)) ? false : 's.hidemenu = 0';
$filters['published'] = ($modx->getOption('published', $scriptProperties, true)) ? 's.published = 1' : false;
$filters['searchable'] = ($modx->getOption('searchable', $scriptProperties, true)) ? 's.searchable = 1' : false;
$criteria = implode(' AND ', array_filter($filters));

$sortBy = $modx->getOption('sortBy', $scriptProperties, 'menuindex');
$sortDir = $modx->getOption('sortDir', $scriptProperties, 'ASC');
$orderby = 's.' . strtolower($sortBy) . ' ' . strtoupper($sortDir);

$containerTpl = $modx->getOption('containerTpl',$scriptProperties,'gContainer');
$priorityTV = (int) $modx->getOption('priorityTV', $scriptProperties, '');

/* Query by Context and set site_url / site_start */
$items = '';
// Set today's date for homepage lastmod
$today = date('Y-m-d');
foreach ($context as $ctx) {
    
    $siteUrl = '';
    // Fetch current context object for site_url
    $currentCtx = $modx->getContext($ctx);
    if ($currentCtx) {
        $siteUrl = $currentCtx->getOption('site_url');
        // Add site_url to output
        $items .= "<url><loc>{$siteUrl}</loc><lastmod>{$today}</lastmod></url>" . PHP_EOL;
    } 
    if (empty($siteUrl)) {
        // We need something to build the links with, even if no context setting
        $siteUrl = $modx->getOption('site_url', null, MODX_SITE_URL);
    }
    
    // Add all resources that meet criteria
    $stmt = $modx->query("
        SELECT
    	    GROUP_CONCAT(
                '<url>',        
                CONCAT('<loc>" . $siteUrl . "',uri,'</loc>'),
                CONCAT('<lastmod>',FROM_UNIXTIME(editedon, '%Y-%m-%d'),'</lastmod>'),
                IFNULL(
                    CONCAT('<priority>',(
                        SELECT value
                        FROM modx_site_tmplvar_contentvalues
                        USE INDEX (tv_cnt)
                        WHERE contentid=id AND tmplvarid={$priorityTV}
                    ),'</priority>'),''),
                '</url>'
                SEPARATOR ''
            ) AS node
        FROM modx_site_content AS s
        WHERE " . $criteria . " AND context_key='" . $ctx . "'
        GROUP BY s.id
        ORDER BY " . $orderby . "
    ");
    
    // Add to output
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $items .= implode(PHP_EOL, $rows);
    }
    
}

/* get container tpl and content */
$output = $modx->getChunk($containerTpl, array(
    'schema' => $googleSchema,
    'items' => $items,
));

if ($output !== null) {
    $modx->cacheManager->set($cacheKey, $output, $expires, $options);
    return $output;
}