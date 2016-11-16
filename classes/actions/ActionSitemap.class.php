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

    protected $bCacheMode = true;

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
        if (C::Get('plugin.sitemap.cache') !== null) {
            $this->bCacheMode = (bool)C::Get('plugin.sitemap.cache');
        }
    }

    /**
     * Load sitemap content from cache
     *
     * @param $sType
     *
     * @return bool|mixed
     */
    protected function _getCache($sType) {

        if ($this->bCacheMode) {
            return E::ModuleCache()->Get('plugin.sitemap.' . $sType, ',file');
        }
        return null;
    }

    /**
     * Save sitemap content in cache
     *
     * @param $sType
     * @param $sContent
     * @param $sPeriod
     */
    protected function _setCache($sType, $sContent, $sPeriod) {

        if ($this->bCacheMode) {
            E::ModuleCache()->Set($sContent, 'plugin.sitemap.' . $sType, array(), $sPeriod, ',file');
        }
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
                $iCount = $this->_getSitemapIndexCount($sType);
                for($iPage = 1; $iPage <= $iCount; $iPage++) {
                    $aItem = $this->_getSitemapIndexData($sType, $iPage);
                    $aData[] = $aItem;
                }
            }
        }
        $this->_displaySitemap('index', $aData, 'sitemap_index.tpl');
    }

    /**
     * Генерирует Sitemap
     *
     * @return string|null
     */
    public function EventSitemap() {

        $sType = $this->GetParam(0);
        $iPage = (int)$this->GetParam(1);
        $sCacheKey = $sType . '_' . $iPage;

        $sSiteMapContent = $this->_getCache($sCacheKey);
        if ($sSiteMapContent) {
            $this->_displaySitemap('index', $sSiteMapContent);
            return null;
        }

        try {
            $iPerPage = C::Val('plugin.sitemap.items_per_page', 100);
            $aData = E::ModuleSitemap()->GetDataFor($sType, $iPage, $iPerPage);
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
    protected function _getSitemapIndexCount($sType) {

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
                $iCount = (int)ceil(E::ModuleUser()->GetUsersCountForSitemap() / $iPerPage);
                break;
            default:
                $iCount = (int)ceil(E::Module('Sitemap')->getItemsCountFor($sType) / $iPerPage);
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
    protected function _getSitemapIndexData($sType, $iPage) {

        if ($iPage < 1) {
            $iPage = 1;
        }
        $sCacheKey = 'sitemap_data_' . $sType . '_' . $iPage;
        $aItem = E::ModuleCache()->Get($sCacheKey, ',file');
        if ($aItem !== false) {
            return $aItem;
        }

        $nPriority = C::Get('plugin.sitemap.' . $sType . '.priority');
        if (!$nPriority) {
            $nPriority = C::Get('plugin.sitemap.default.sitemap.priority');
        }
        $sLastMod = $this->_getSitemapLastmod($sType, $iPage);

        $aItem = array(
            'loc' => F::File_RootUrl(true) . 'sitemap_' . $sType . '_' . $iPage . '.xml'
        );
        if ($nPriority) {
            $aItem['priority'] = $nPriority;
        }
        if ($sLastMod) {
            $aItem['lastmod'] = $sLastMod;
        }

        E::ModuleCache()->Set($aItem, $sCacheKey, array('sitemap'), 'PT1H', ',file');

        return $aItem;
    }

    /**
     * Display content of sitemap and save it in cache
     *
     * @param string       $sCacheKey
     * @param array|string $xData
     * @param null|string  $sTemplate
     */
    protected function _displaySitemap($sCacheKey, $xData, $sTemplate = 'sitemap.tpl') {

        E::ModuleViewer()->SetResponseHeader('Content-type', 'application/xml; charset=utf-8');
        if (is_array($xData)) {
            $sTemplate = Plugin::GetTemplateDir('sitemap') . 'tpls/' . $sTemplate;
            $sSiteMapContent = E::ModuleViewer()->Fetch($sTemplate, array('aData' => $xData));
            $sPeriod = C::Get();
            foreach((array)$xData as $aItem) {
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