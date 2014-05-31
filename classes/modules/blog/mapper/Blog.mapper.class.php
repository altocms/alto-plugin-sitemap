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
 * Маппер Blog модуля Blog плагина Sitemap
 */
class PluginSitemap_ModuleBlog_MapperBlog extends Mapper {
        
    /**
     * Количество открытых коллективных блогов
     *
     * @return integer
     */
    public function getCountOfOpenBlogs() {

        $aBlogTypes = $this->Blog_GetOpenBlogTypes();
        $sql = "SELECT
                        COUNT(*)
                FROM
                        ?_blog
                WHERE
                        blog_type IN (?a)
                ";

        return $this->oDb->selectCell($sql, $aBlogTypes);
    }

    /**
     * Список айдишек открытых коллективных блогов
     *
     * @param integer $iCount
     * @param integer $iCurrPage
     * @param integer $iPerPage
     * @return array
     */
    public function getIdsOfOpenBlogs(&$iCount, $iCurrPage, $iPerPage) {

        $aBlogTypes = $this->Blog_GetOpenBlogTypes();
        $sql = 'SELECT
                        blog_id
                FROM
                        ?_blog
                WHERE
                        blog_type IN (?a)
                ORDER BY
                        blog_id ASC
                LIMIT
                        ?d, ?d
                ';
        $aReturn = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, $aBlogTypes, ($iCurrPage-1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['blog_id'];
            }
        }

        return $aReturn;
    }

}

// EOF