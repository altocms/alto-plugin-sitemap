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

class PluginSitemap_ModuleTopic_EntityTopic extends PluginSitemap_Inherit_ModuleTopic_EntityTopic {

    /**
     * Get date of last topic modification
     *
     * @return string
     */
    public function getDateLastMod() {

        return is_null($this->getDateEdit()) ? $this->getDate() : $this->getDateEdit();
    }
    
}

// EOF