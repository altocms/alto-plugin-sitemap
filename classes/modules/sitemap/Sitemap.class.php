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
 * Модуль для плагина генерации Sitemap
 */
class PluginSitemap_ModuleSitemap extends Module {

    /**
     * Инициализация модуля
     *
     * @return void
     */
    public function Init() {
    }

    /**
     * Конвертирует дату в формат W3C Datetime
     *
     * @param mixed $mDate - UNIX timestamp или дата в формате понимаемом функцией strtotime()
     * @return string - дата в формате W3C Datetime (http://www.w3.org/TR/NOTE-datetime)
     */
    private function _convDateToLastMod($mDate = null) {

        if (is_null($mDate)) {
            return null;
        }
        
        $mDate = is_int($mDate) ? $mDate : strtotime($mDate);
        return date('Y-m-d\TH:i:s+00:00', $mDate);
    }

    /**
     * Возвращает массив с данными для генерации sitemap'а
     *
     * @param string $sUrl
     * @param mixed $sLastMod
     * @param mixed $sChangeFreq
     * @param mixed $sPriority
     * @return array 
     */
    public function getDataForSitemapRow($sUrl, $sLastMod = null, $sChangeFreq = null, $sPriority = null) {

        return array(
            'loc' => $sUrl,
            'lastmod' => $this->_convDateToLastMod($sLastMod),
            'priority' => $sChangeFreq,
            'changefreq' => $sPriority,
        );
    }

    /**
     * Этот метод переопределяется в других плагинах и добавляет их наборы данных
     * к основному набору
     *
     * @return array
     */
    public function getExternalCounters() {

        return array();
    }


    /**
     * Этот метод переопределяется в других плагинах и добавляет нужные ссылки на
     * сайтмапы к основному набору ссылок
     *
     * @return array
     */
    public function getExternalLinks() {

        return array();
    }

    /**
     * Генерирует преффикс для кеша
     *
     * @return string
     */
    public function getCacheIdPrefix() {

        return '';
    }

    /**
     * Данные для Sitemap общих страниц сайта
     *
     * @param integer $iCurrPage
     * @return array
     */
    public function getDataForGeneral($iCurrPage) {

        $sRootUrl = F::File_RootUrl(true);
        $aData = array();
        $aData[] = $this->GetDataForSitemapRow(
            $sRootUrl,
            time(),
            Config::Get('plugin.sitemap.general.mainpage.sitemap_priority'),
            Config::Get('plugin.sitemap.general.mainpage.sitemap_changefreq')
        );
        $aData[] = $this->GetDataForSitemapRow(
            $sRootUrl . 'comments/',
            null, //time(),
            Config::Get('plugin.sitemap.general.comments.sitemap_priority'),
            Config::Get('plugin.sitemap.general.comments.sitemap_changefreq')
        );
        return $aData;
    }

    /**
     * Данные для Sitemap открытых коллективных блогов
     *
     * @param integer $iCurrPage
     * @return array
     */
    public function getDataForBlogs($iCurrPage) {

        return $this->PluginSitemap_Blog_GetBlogsForSitemap($iCurrPage);
    }

    /**
     * Данные для Sitemap опубликованных топиков
     *
     * @param integer $iCurrPage
     * @return void
     */
    public function getDataForTopics($iCurrPage) {

        return $this->PluginSitemap_Topic_GetTopicsForSitemap($iCurrPage);
    }

    /**
     * Данные для Sitemap пользовательских профилей, топиков и комментариев
     *
     * @param integer $iCurrPage
     * @return void
     */
    public function getDataForUsers($iCurrPage) {

        return $this->PluginSitemap_User_GetUsersForSitemap($iCurrPage);
    }

    public function GetLastMod($sType, $iPage) {

        $sDate = null;
        if ($sType == 'general') {
            $aTopics = $this->Topic_GetTopicsLast(1);
            if ($aTopics) {
                $oTopic = reset($aTopics);
                if ($oTopic->getTopicDateEdit()) {
                    $sDate = $oTopic->getTopicDateEdit();
                } elseif ($oTopic->getTopicDateShow()) {
                    $sDate = $oTopic->getTopicDateShow();
                } else {
                    $sDate = $oTopic->getTopicDateAdd();
                }
            }
            $aComments = $this->Comment_GetCommentsOnline('topic', 1);
            if ($aComments) {
                $oComment = reset($aComments);
                if ($oComment->getDateEdit()) {
                    $sDate = $oComment->getCommentDateEdit();
                } else {
                    $sDate = $oComment->getCommentDate();
                }
            }
        }
        if ($sDate && strpos($sDate, ' ')) {
            $sDate = str_replace(' ', 'T', $sDate);
        }

        return $sDate;
    }
}

// EOF