<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   Plugin Sitemap for LiveStreet CMS
 *   Author: Stepan Tanasiychuk
 *   Site: http://stfalcon.com
 *----------------------------------------------------------------------------
 */

/**
 * Модуль Blog плагина Sitemap
 */
class PluginSitemap_ModuleBlog extends PluginSitemap_Inherits_ModuleBlog {

    protected $oMapper;

    /**
     * Количество коллективных блогов
     *
     * @return integer
     */
    public function GetBlogsCountForSitemap() {

        $aBlogTypes = $this->GetOpenBlogTypes();
        $aInfo = $this->oMapper->GetBlogCountsByTypes($aBlogTypes);
        return empty($aInfo) ? 0 : array_sum($aInfo);
    }

    /**
     * Список коллективных блогов (с кешированием)
     *
     * @param integer $iPage
     *
     * @return array
     */
    public function GetBlogsForSitemap($iPage = 1) {

        $sCacheKey = "sitemap_blogs_{$iPage}_" . C::Get('plugin.sitemap.items_per_page');

        if (false === ($aData = E::ModuleCache()->Get($sCacheKey))) {
            $aFilter = array(
                'include_type' => $this->GetOpenBlogTypes(),
            );
            $aBlogs = E::ModuleBlog()->GetBlogsByFilter($aFilter, $iPage, C::Get('plugin.sitemap.items_per_page'), array('owner' => array()));

            $aData = array();
            /** @var ModuleBlog_EntityBlog $oBlog */
            foreach ($aBlogs['collection'] as $oBlog) {
                // TODO временем последнего изменения блога должно быть время его обновления (публикация последнего топика),
                $aData[] = E::ModuleSitemap()->GetDataForSitemapRow(
                        $oBlog->getLink(),
                        null,
                        C::Get('plugin.sitemap.type.blogs.changefreq'),
                        C::Get('plugin.sitemap.type.blogs.priority')
                );

                // @todo страницы блога разбиты на подстраницы. значит нужно генерировать
                // ссылки на каждую из подстраниц
                // т.е. тянуть количество топиков блога
            }

            E::ModuleCache()->Set($aData, $sCacheKey, array('blog_new'), C::Get('plugin.sitemap.type.blogs.cache_lifetime'));
        }
        
        return $aData;
    }

}

// EOF