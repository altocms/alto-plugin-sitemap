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
     * Get blog add date
     *
     * @return string|null
     */
    public function getDateAdd() {

        return $this->getProp('blog_date_add');
    }

    /**
     * Get blog edit date
     *
     * @return string|null
     */
    public function getDateEdit() {

        return $this->getProp('blog_date_edit');
    }

    /**
     * Set blog add date
     *
     * @param $data
     *
     * @return void
     */
    public function setDateAdd($data) {

        $this->setProp('blog_date_add', $data);
    }

    /**
     * Set blog edit date
     *
     * @param $data
     *
     * @return void
     */
    public function setDateEdit($data) {

        $this->setProp('blog_date_edit', $data);
    }

    /**
     * Get date of last blog modification
     *
     * @return string
     */
    public function getDateLastMod() {

        return is_null($this->getDateEdit()) ? $this->getDateAdd() : $this->getDateEdit();
    }
}

// EOF