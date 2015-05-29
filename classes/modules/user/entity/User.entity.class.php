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

class PluginSitemap_ModuleUser_EntityUser extends PluginSitemap_Inherits_ModuleUser_EntityUser {

    /**
     * Get date of last user modification
     *
     * @return string
     */
    public function getDateLastMod() {

        return is_null($this->getProfileDate()) ? $this->getDateRegister() : $this->getProfileDate();
    }

    /**
     * Get web path to page with user comments
     *
     * @return string
     */
    public function getUserCommentsLink() {

        return Router::GetPath('my') . $this->getLogin() . '/comment/';
    }

    /**
     * Get web path to page with user topics
     *
     * @return string
     */
    public function getUserTopicsLink() {

        return Router::GetPath('my') . $this->getLogin() . '/';
    }

}

// EOF