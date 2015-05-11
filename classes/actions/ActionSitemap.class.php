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

    /**
     * Инициализация
     *
     * @return void
     */
    public function Init() {

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
     * @return void
     */
    public function EventSitemap() {

        $iCurrPage = intval($this->GetParam(1));
        $aType = explode('_', $this->GetParam(0));
        $sName = '';
        foreach ($aType as $val) {
            $sName .= ucfirst($val);
        }

        try {
            $aData = call_user_func_array(
                array($this, 'PluginSitemap_Sitemap_GetDataFor' . $sName),
                array($iCurrPage)
            );
        } catch (Exception $e) {
            return $this->EventNotFound();
        }

        $this->_displaySitemap($aData);
    }

    /**
     * Генерирует карту Sitemap-ов, разбивая каждый тип сущностей на наборы
     *
     * @return void
     */
    protected function EventSitemapIndex() {

        $iPerPage = Config::Get('plugin.sitemap.objects_per_page');
        $aCounters = array(
            'general' => 1,
            'blogs'   => (int)ceil($this->PluginSitemap_Blog_GetBlogsCountForSitemap() / $iPerPage),
            'topics'  => (int)ceil($this->PluginSitemap_Topic_GetTopicsCountForSitemap() / $iPerPage),
            // в sitemap пользователей в 3ри раза больше ссылок
            'users'   => (int)ceil(
                    $this->PluginSitemap_User_GetUsersCountForSitemap() / Config::Get('plugin.sitemap.users_per_page')
                ),
        );

        // Возможность сторонними плагинами добавлять свои данные в Sitemap Index
        $aExternalCounters = $this->PluginSitemap_Sitemap_GetExternalCounters();
        if (is_array($aExternalCounters)) {
            foreach ($aExternalCounters as $k => $v) {
                if (is_string($k) && is_numeric($v)) {
                    $aCounters[$k] = (int)$v;
                }
            }
        }

        // Генерируем ссылки на конечные Sitemap'ы для Sitemap Index
        $aData = array();

        $sRootUrl = F::File_RootUrl(true);
        foreach ($aCounters as $sType => $iCount) {
            if ($iCount > 0) {
                for ($iPage = 1; $iPage <= $iCount; ++$iPage) {
                    $aItem = array(
                        'loc' => $sRootUrl . 'sitemap_' . $sType . '_' . $iPage . '.xml'
                    );
                    $sChangeFreq = Config::Get('plugin.sitemap.' . $sType . '.sitemap_changefreq');
                    if ($sChangeFreq) {
                        $aItem['changefreq'] = $sChangeFreq;
                    }
                    $sDate = $this->PluginSitemap_Sitemap_GetLastMod($sType, $iPage);
                    if ($sDate) {
                        $aItem['lastmod'] = $sDate;
                    }
                    $aData[] = $aItem;
                }
            }
        }

        $aLinks = $this->PluginSitemap_Sitemap_GetExternalLinks();
        foreach ($aLinks as $sLink) {
            $aData[] = array(
                'loc' => $sLink
            );
        }

        $this->_displaySitemap($aData, 'sitemap_index.tpl');
    }

    /**
     * Устанавливат соответсвующий сonten-type и шаблон для sitemap'a
     *
     * @param array $aData
     * @param string $sTemplate
     *
     * @return void
     */
    protected function _displaySitemap(array $aData, $sTemplate = 'sitemap.tpl') {

        header('Content-type: application/xml');
        $this->Viewer_Assign('aData', $aData);
        $this->SetTemplate(Plugin::GetTemplateDir('sitemap') . 'tpls/' . $sTemplate);
    }

}

// EOF