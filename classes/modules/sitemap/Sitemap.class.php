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
     * @param mixed $xDate - UNIX timestamp или дата в формате понимаемом функцией strtotime()
     *
     * @return string - дата в формате W3C Datetime (http://www.w3.org/TR/NOTE-datetime)
     */
    protected function _convDateToLastMod($xDate = null) {

        if (is_null($xDate)) {
            return null;
        }
        $xDate = is_int($xDate) ? $xDate : strtotime($xDate);

        return date('Y-m-d\TH:i:s+00:00', $xDate);
    }

    protected function _getLastDateOfTopics() {

        $sDate = null;
        $aTopics = E::ModuleTopic()->GetTopicsLast(1);
        if ($aTopics['collection']) {
            $oTopic = reset($aTopics['collection']);
            $sDate = $oTopic->getDateLastMod();
        }
        $aComments = E::ModuleComment()->GetCommentsOnline('topic', 1);
        if ($aComments) {
            $oComment = reset($aComments);
            if ($oComment->getDateEdit() && $oComment->getDateEdit() > $sDate) {
                $sDate = $oComment->getCommentDateEdit();
            } elseif ($oComment->getCommentDate() > $sDate) {
                $sDate = $oComment->getCommentDate();
            }
        }
        return $sDate;
    }

    /**
     * Возвращает массив с данными для генерации sitemap'а
     *
     * @param string $sUrl
     * @param string $sLastMod
     * @param string $sChangeFreq
     * @param float  $nPriority
     *
     * @return array
     */
    public function getDataForSitemapRow($sUrl, $sLastMod = null, $sChangeFreq = null, $nPriority = null) {

        $aItem = array(
            'loc' => $sUrl,
        );
        if (!empty($sLastMod)) {
            $aItem['lastmod'] = $this->_convDateToLastMod($sLastMod);
        }
        if (!empty($sChangeFreq)) {
            $aItem['changefreq'] = $sChangeFreq;
        }
        if (!empty($nPriority)) {
            $aItem['priority'] = $nPriority;
        }
        return $aItem;
    }

    /**
     * Данные для Sitemap общих страниц сайта
     *
     * @return array
     */
    public function getDataForGeneral() {

        $aData = array();
        $aUrls = C::Get('plugin.sitemap.type.general.url');
        foreach($aUrls as $aItem) {
            if (!empty($aItem['loc'])) {
                $aData[] = $this->GetDataForSitemapRow(
                    $aItem['loc'],
                    time(),
                    !empty($aItem['changefreq']) ? $aItem['changefreq'] : C::Get('plugin.sitemap.default.url.changefreq'),
                    !empty($aItem['priority']) ? $aItem['priority'] : C::Get('plugin.sitemap.default.url.priority')
                );
            }
        }

        return $aData;
    }

    /**
     * Get sitemap data for the sitemap data and page
     *
     * @param string $sType
     * @param int    $iPage
     *
     * @return array
     */
    public function GetDataFor($sType, $iPage) {

        if ($iPage < 1) {
            $iPage = 1;
        }
        switch ($sType) {
            case 'general':
                $aData = $this->getDataForGeneral();
                break;
            case 'topics':
                $aData = E::ModuleTopic()->GetTopicsForSitemap($iPage);
                break;
            case 'blogs':
                $aData = E::ModuleBlog()->GetBlogsForSitemap($iPage);
                break;
            case 'users':
                $aData = $this->PluginSitemap_User_GetUsersForSitemap($iPage);
                break;
            default:
                $aData = array();
        }
        return $aData;
    }

    public function GetLastMod($sType, $iPage) {

        $sDate = null;
        if ($sType == 'general' || ($sType == 'topics' && $iPage == 1)) {
            $sDate = $this->_getLastDateOfTopics();
        }
        if ($sDate) {
            $sDate = $this->_convDateToLastMod($sDate);
        }

        return $sDate;
    }
}

// EOF