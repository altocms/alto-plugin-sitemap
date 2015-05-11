<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{asset file="assets/sitemap_index.xsl" plugin="sitemap" skin="default"}"?>
<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{foreach from=$aData item=aItem}
    <sitemap>
        <loc>{$aItem.loc}</loc>
        {if $aItem.lastmod}<lastmod>{$aItem.lastmod}</lastmod>{/if}
        {if $aItem.changefreq}<changefreq>{$aItem.changefreq}</changefreq>{/if}
    </sitemap>
{/foreach}
</sitemapindex>