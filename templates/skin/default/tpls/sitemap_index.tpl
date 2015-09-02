<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{foreach $aData as $aItem}
    <sitemap>
        <loc>{$aItem.loc}</loc>
        {if $aItem.lastmod}<lastmod>{$aItem.lastmod}</lastmod>{/if}
        {if $aItem.changefreq}<changefreq>{$aItem.changefreq}</changefreq>{/if}
    </sitemap>
{/foreach}
</sitemapindex>