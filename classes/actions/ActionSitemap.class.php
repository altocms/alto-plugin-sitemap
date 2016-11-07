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
 * Набор действий для плагина генерации sitemap
 */
class PluginSitemap_ActionSitemap extends ActionPlugin {

    protected $aPeriods = array();

    /**
     * Инициализация
     *
     * @return void
     */
    public function Init() {

        $this->aPeriods = array(
            'always' => 'PT1M',
            'hourly' => 'PT1H',
            'daily' => 'P1D',
            'weekly' => 'P7D',
            'monthly' => 'P1M',
            'yearly' => 'P1Y',
            'never' => 'P1Y',
        );

        $this->SetDefaultEvent('index');
        Router::SetIsShowStats(false);
    }

    /**
     * Регистрация событий
     *
     * @return void
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventSitemapIndex');
        $this->AddEvent('sitemap', 'EventSitemap');
    }

    /**
     * Генерирует Sitemap
     *
     * @return string|null
     */
    public function EventSitemap() {

        $sType = $this->GetParam(0);
        $iPage = intval($this->GetParam(1));
        $sCacheKey = $sType . '_' . $iPage;

        $sSiteMapContent = $this->_getCache($sCacheKey);
        if ($sSiteMapContent) {
            $this->_displaySitemap('index', $sSiteMapContent);
            return null;
        }

        try {
            $aData = E::ModuleSitemap()->GetDataFor($sType, $iPage);
        } catch (Exception $e) {
            return $this->EventNotFound();
        }

        $this->_displaySitemap($sCacheKey, $aData);

        return null;
    }

    /**
     * Return list of sitemap type
     *
     * @return string[]
     */
    protected function _getSitemapTypes() {

        $aTypesInfo = C::Get('plugin.sitemap.type');
        $aTypeList = array_diff(array_keys($aTypesInfo), array('index'));

        return $aTypeList;
    }

    /**
     * Return total number of pages of the sitemap type
     *
     * @param string $sType
     *
     * @return int
     */
    protected function _getSitemapCount($sType) {

        $iPerPage = C::Get('plugin.sitemap.items_per_page');
        switch ($sType) {
            case 'general':
                $iCount = 1;
                break;
            case 'topics':
                $iCount = (int)ceil(E::ModuleTopic()->GetTopicsCountForSitemap() / $iPerPage);
                break;
            case 'blogs':
                $iCount = (int)ceil(E::ModuleBlog()->GetBlogsCountForSitemap() / $iPerPage);
                break;
            case 'users':
                $iCount = (int)ceil(E::ModuleUser()->GetUsersCountForSitemap() / C::Get('plugin.sitemap.users_per_page'));
                break;
            default:
                $iCount = 1;
        }

        return $iCount;
    }

    /**
     * @param string $sType
     * @param int    $iPage
     *
     * @return string
     */
    protected function _getSitemapLastmod($sType, $iPage) {

        if ($iPage < 1) {
            $iPage = 1;
        }
        return E::ModuleSitemap()->GetLastMod($sType, $iPage);
    }

    /**
     * @param string $sType
     * @param int    $iPage
     *
     * @return array
     */
    protected function _getSitemapData($sType, $iPage) {

        if ($iPage < 1) {
            $iPage = 1;
        }
        $sCacheKey = 'sitemap_data_' . $sType . '_' . $iPage;
        $aItem = E::ModuleCache()->Get($sCacheKey, ',file');
        if ($aItem !== false) {
            return $aItem;
        }

        $sChangeFreq = C::Get('plugin.sitemap.' . $sType . '.changefreq');
        if (!$sChangeFreq) {
            $sChangeFreq = C::Get('plugin.sitemap.default.sitemap.changefreq');
        }
        $nPriority = C::Get('plugin.sitemap.' . $sType . '.priority');
        if (!$nPriority) {
            $nPriority = C::Get('plugin.sitemap.default.sitemap.priority');
        }
        $sLastMod = $this->_getSitemapLastmod($sType, $iPage);

        $aItem = array(
            'loc' => F::File_RootUrl(true) . 'sitemap_' . $sType . '_' . $iPage . '.xml'
        );
        if ($sChangeFreq) {
            $aItem['changefreq'] = $sChangeFreq;
        }
        if ($nPriority) {
            $aItem['priority'] = $nPriority;
        }
        if ($sLastMod) {
            $aItem['lastmod'] = $sLastMod;
        }

        if (isset($this->aPeriods[$sChangeFreq])) {
            $sCacheTime = $this->aPeriods[$sChangeFreq];
        } else {
            $sCacheTime = 'PT1H';
        }
        E::ModuleCache()->Set($aItem, $sCacheKey, array('sitemap'), $sCacheTime, ',file');

        return $aItem;
    }

    /**
     * Генерирует карту Sitemap-ов, разбивая каждый тип сущностей на наборы
     *
     * @return void
     */
    public function EventSitemapIndex() {

        $sSiteMapContent = $this->_getCache('index');
        if ($sSiteMapContent) {
            $this->_displaySitemap('index', $sSiteMapContent);
            return;
        }

        $aTypeList = $this->_getSitemapTypes();

        // Генерируем ссылки на конечные Sitemap'ы для Sitemap Index
        $aAvailableTypes = F::Array_Str2Array(C::Get('plugin.sitemap.type.index.sitemap'));
        $aData = array();
        foreach ($aAvailableTypes as $sType) {
            if (in_array($sType, $aTypeList)) {
                $iCount = $this->_getSitemapCount($sType);
                for($iPage = 1; $iPage <= $iCount; $iPage++) {
                    $aItem = $this->_getSitemapData($sType, $iPage);
                    $aData[] = $aItem;
                }
            }
        }
        $this->_displaySitemap('index', $aData, 'sitemap_index.tpl');
    }

    /**
     * Load sitemap content from cache
     *
     * @param $sType
     *
     * @return bool|mixed
     */
    protected function _getCache($sType) {

        return E::ModuleCache()->Get('plugin.sitemap.' . $sType, ',file');
    }

    /**
     * Save sitemap content in cache
     *
     * @param $sType
     * @param $sContent
     * @param $sPeriod
     */
    protected function _setCache($sType, $sContent, $sPeriod) {

        E::ModuleCache()->Set($sContent, 'plugin.sitemap.' . $sType, array(), $sPeriod, ',file');
    }

    /**
     * Display content of sitemap and save it in cache
     *
     * @param string       $sCacheKey
     * @param string|array $xData
     * @param null|string  $sTemplate
     */
    protected function _displaySitemap($sCacheKey, $xData, $sTemplate = 'sitemap.tpl') {

        E::ModuleViewer()->SetResponseHeader('Content-type', 'application/xml; charset=utf-8');
        if (is_array($xData)) {
            $sTemplate = Plugin::GetTemplateDir('sitemap') . 'tpls/' . $sTemplate;
            $sSiteMapContent = E::ModuleViewer()->Fetch($sTemplate, array('aData' => $xData));
            $sPeriod = C::Get();
            foreach($xData as $aItem) {
                if (!empty($aItem['changefreq'])) {
                    if (in_array($aItem['changefreq'], $this->aPeriods)) {
                        $sPeriod = $this->aPeriods[$aItem['changefreq']];
                    }
                }
            }
            if (!$sPeriod) {
                $sPeriod = 'P1D';
            }
            $this->_setCache($sCacheKey, $sSiteMapContent, $sPeriod);
        } else {
            $sSiteMapContent = $xData;
        }

        E::ModuleViewer()->Flush($sSiteMapContent);

        exit;
    }

}

// EOF