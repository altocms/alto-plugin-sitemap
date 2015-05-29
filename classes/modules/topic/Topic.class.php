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
class PluginSitemap_ModuleTopic extends PluginSitemap_Inherits_ModuleTopic {

    /**
     * Фильтр для выборки опубликованых топиков в открытых блогах
     *
     * @param string $sFilterName
     * @param array  $aParams
     *
     * @return array
     */
    public function GetNamedFilter($sFilterName, $aParams = array()) {

        if ($sFilterName == 'sitemap') {
            $aFilter = array(
                'blog_type' => E::ModuleBlog()->GetOpenBlogTypes(),
                'topic_publish' => 1,
                'topic_index_ignore' => 0,
            );
            $aFilter['order'][] = 't.topic_date_show DESC';
            $aFilter['order'][] = 't.topic_id DESC';
        } else {
            $aFilter = parent::GetNamedFilter($sFilterName, $aParams);
        }

        return $aFilter;
    }

    /**
     * Количество опубликованых топиков в открытых блогах
     *
     * @return integer
     */
    public function GetTopicsCountForSitemap() {

        $aFilter = $this->GetNamedFilter('sitemap');

        return (int) E::ModuleTopic()->GetCountTopicsByFilter($aFilter);
    }

    /**
     * Список опубликованых топиков в открытых блогах (с кешированием)
     *
     * @param int $iPage
     *
     * @return array
     */
    public function getTopicsForSitemap($iPage = 0) {

        $sCacheKey = "sitemap_topics_{$iPage}_" . C::Get('plugin.sitemap.items_per_page');

        if (false === ($aData = E::ModuleCache()->Get($sCacheKey))) {
            $aFilter = $this->GetNamedFilter('sitemap');
            $aTopics = E::ModuleTopic()->GetTopicsByFilter($aFilter, $iPage, C::Get('plugin.sitemap.items_per_page'), array('blog' => array('owner'=>array())));

            $aData = array();
            $iIndex = 0;

            $aPriority = F::Array_Str2Array(C::Get('plugin.sitemap.type.topics.priority'));
            $nPriority = (sizeof($aPriority) ? reset($aPriority) : null);

            $aChangeFreq = F::Array_Str2Array(C::Get('plugin.sitemap.type.topics.changefreq'));
            $sChangeFreq = (sizeof($aChangeFreq) ? reset($aChangeFreq) : null);

            /** @var ModuleTopic_EntityTopic $oTopic */
            foreach ($aTopics['collection'] as $oTopic) {
                if ($aPriority) {
                    if (isset($aPriority[$iIndex])) {
                        $nPriority = $aPriority[$iIndex];
                    }
                }
                if ($aChangeFreq) {
                    if (isset($aChangeFreq[$iIndex])) {
                        $sChangeFreq = $aChangeFreq[$iIndex];
                    }
                }
                $aData[] = E::ModuleSitemap()->GetDataForSitemapRow($oTopic->getLink(), $oTopic->getDateLastMod(), $sChangeFreq, $nPriority);
                $iIndex += 1;
            }

            // тег 'blog_update' т.к. при редактировании блога его тип может измениться
            // с открытого на закрытый или наоборот
            E::ModuleCache()->Set($aData, $sCacheKey, array('topic_new', 'topic_update', 'blog_update'), C::Get('plugin.sitemap.type.topics.cache_lifetime'));
        }

        return $aData;
    }

}

// EOF