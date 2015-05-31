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
 * Модуль User плагина Sitemap
 */
class PluginSitemap_ModuleUser extends PluginSitemap_Inherits_ModuleUser {

    /**
     * Маппер
     * @var PluginSitemap_ModuleUser_MapperUser
     */
    protected $oMapper;

    /**
     * Количество пользователей
     *
     * @return integer
     */
    public function getUsersCountForSitemap() {

        $aStatUsers = E::ModuleUser()->GetStatUsers();
        return $aStatUsers['count_all'];
    }

    /**
     * Список пользователей (с кешированием)
     *
     * @param integer $iPage
     *
     * @return array
     */
    public function getUsersForSitemap($iPage) {

        $iPerPage = C::Get('plugin.sitemap.users_per_page');

        $sCacheKey = "sitemap_users_{$iPage}_{$iPerPage}";

        if (false === ($aData = E::ModuleCache()->Get($sCacheKey))) {
            $aFilter = array(
                'activate' => 1
            );
            $aUsers = E::ModuleUser()->GetUsersByFilter($aFilter, array(), $iPage, $iPerPage);

            $aData = array();
            /** @var ModuleUser_EntityUser $oUser */
            foreach ($aUsers['collection'] as $oUser) {
                // профиль пользователя
                $aData[] = E::ModuleSitemap()->GetDataForSitemapRow(
                    $oUser->getProfileUrl(),
                    $oUser->getDateLastMod(),
                    C::Get('plugin.sitemap.type.users.profile.changefreq'),
                    C::Get('plugin.sitemap.type.users.profile.priority')
                );

                // публикации пользователя
                $aData[] = E::ModuleSitemap()->GetDataForSitemapRow(
                    $oUser->getUserTopicsLink(),
                    // TODO временем изменения страницы публикаций должно быть время последней публикации пользователя
                    null,
                    C::Get('plugin.sitemap.type.users.my.changefreq'),
                    C::Get('plugin.sitemap.type.users.my.priority')
                );

                // комментарии пользователя
                $aData[] = E::ModuleSitemap()->GetDataForSitemapRow(
                    $oUser->getUserCommentsLink(),
                    $oUser->getDateCommentLast(),
                    C::Get('plugin.sitemap.type.users.comments.changefreq'),
                    C::Get('plugin.sitemap.type.users.comments.priority')
                );

                E::ModuleCache()->Set($aData, $sCacheKey, array('user_new', 'user_update'), C::Get('plugin.sitemap.type.users.cache_lifetime'));
            }
        }

        return $aData;
    }

}

// EOF