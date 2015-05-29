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

class PluginSitemap_ModuleBlog_EntityBlog extends PluginSitemap_Inherits_ModuleBlog_EntityBlog {

    /**
     * Get date of last blog modification
     *
     * @return string
     */
    public function getDateLastMod() {

        return is_null($this->getBlogDateEdit()) ? $this->getBlogDateAdd() : $this->getBlogDateEdit();
    }
}

// EOF