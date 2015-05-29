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
 * Маппер User модуля User плагина Sitemap
 */
class PluginSitemap_ModuleUser_MapperUser extends PluginSitemap_Inherits_ModuleUser_MapperUser {

    /**
     * Список ID активированных пользователей
     *
     * @param integer $iCount
     * @param integer $iCurrPage
     * @param integer $iPerPage
     * @return array
     */
    public function getUsersId(&$iCount, $iCurrPage, $iPerPage) {

        $sql = 'SELECT
                    user_id
                FROM
                    ?_user
                WHERE
                    user_activate=1
                ORDER BY
                    user_id ASC
                LIMIT
                    ?d, ?d
                ';
        $aReturn = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage-1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['user_id'];
            }
        }

        return $aReturn;
    }

}
// EOF