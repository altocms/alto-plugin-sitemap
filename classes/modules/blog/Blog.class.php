<?php

/**
 * Модуль Blog плагина Sitemap
 */
class PluginSitemap_ModuleBlog extends Module {

    /**
     * Mapper
     * @var PluginSitemap_ModuleBlog_MapperBlog
     */
    protected $oMapper;

    /**
     * Инициализация
     *
     * @return void
     */
    public function Init() {

        $this->oMapper = Engine::GetMapper(__CLASS__);
    }

    /**
     * Количество коллективных блогов
     *
     * @return integer
     */
    public function GetBlogsCountForSitemap() {

        // @todo это количество должно ТОЧНО совпадать с тем которое будет тянутся родным методом.
        // поэтому возможно лучше продублировать этот код в маппере плагина
        return (int) $this->oMapper->getCountOfOpenBlogs();
    }

    /**
     * Список коллективных блогов (с кешированием)
     *
     * @param integer $iCurrPage
     *
     * @return array
     */
    public function GetBlogsForSitemap($iCurrPage = 0) {

        $sCacheKey = $this->PluginSitemap_Sitemap_GetCacheIdPrefix()
                     . "sitemap_blogs_{$iCurrPage}_" . Config::Get('plugin.sitemap.objects_per_page');

        if (false === ($aData = $this->Cache_Get($sCacheKey))) {
            $iCount = 0;
            $aBlogsId = $this->oMapper->getIdsOfOpenBlogs($iCount, $iCurrPage, Config::Get('plugin.sitemap.objects_per_page'));
            $aBlogs = $this->Blog_GetBlogsAdditionalData($aBlogsId, array('owner' => array()));

            $aData = array();
            foreach ($aBlogs as $oBlog) {
                // @todo временем последнего изменения блога должно
                // быть время его обновления (публикация последнего топика),
                $aData[] = $this->PluginSitemap_Sitemap_GetDataForSitemapRow(
                        $oBlog->getUrlFull(),
                        null,
                        Config::Get('plugin.sitemap.blogs.sitemap_priority'),
                        Config::Get('plugin.sitemap.blogs.sitemap_changefreq')
                );

                // @todo страницы блога разбиты на подстраницы. значит нужно генерировать
                // ссылки на каждую из подстраниц
                // т.е. тянуть количество топиков блога
            }

            $this->Cache_Set($aData, $sCacheKey, array('blog_new'), Config::Get('plugin.sitemap.blogs.cache_lifetime'));
        }
        
        return $aData;
    }

}

// EOF