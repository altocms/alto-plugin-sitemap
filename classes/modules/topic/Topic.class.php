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
 * Модуль Topic плагина Sitemap
 */
class PluginSitemap_ModuleTopic extends Module {

    public function Init() {
        // npthing
    }

    /**
     * Фильтр для выборки опубликованых топиков в открытых блогах
     *
     * @return array
     */
    protected function _getSitemapFilterForTopics() {

        return array(
            'blog_type' => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1,
            'topic_index_ignore' => 0,
        );
    }

    /**
     * Количество опубликованых топиков в открытых блогах
     *
     * @return integer
     */
    public function GetTopicsCountForSitemap() {

        return (int) $this->Topic_GetCountTopicsByFilter($this->_getSitemapFilterForTopics());
    }

    /**
     * Список опубликованых топиков в открытых блогах (с кешированием)
     *
     * @param integer $iCurrPage
     * @return array
     */
    public function getTopicsForSitemap($iCurrPage = 0) {

        $sCacheKey = $this->PluginSitemap_Sitemap_GetCacheIdPrefix()
                     . "sitemap_topics_{$iCurrPage}_" . Config::Get('plugin.sitemap.objects_per_page');

        if (false === ($aData = $this->Cache_Get($sCacheKey))) {
            $aTopics = $this->Topic_GetTopicsByFilter(
                    $this->_getSitemapFilterForTopics(),
                    $iCurrPage,
                    Config::Get('plugin.sitemap.objects_per_page'),
                    array('blog' => array('owner'=>array()))
            );

            $aData = array();
            foreach ($aTopics['collection'] as $oTopic) {
                $aData[] = $this->PluginSitemap_Sitemap_GetDataForSitemapRow(
                        $oTopic->getUrl(),
                        $oTopic->getDateLastMod(),
                        Config::Get('plugin.sitemap.topics.sitemap_changefreq'),
                        Config::Get('plugin.sitemap.topics.sitemap_priority')
                );
            }

            // тег 'blog_update' т.к. при редактировании блога его тип может измениться
            // с открытого на закрытый или наоборот
            $this->Cache_Set($aData, $sCacheKey, array('topic_new', 'blog_update'), Config::Get('plugin.sitemap.topics.cache_lifetime'));
        }

        return $aData;
    }

}

// EOF