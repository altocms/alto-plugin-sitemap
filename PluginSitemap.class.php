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
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * Плагин для генерации sitemap
 *
 * @author Stepan Tanasiychuk <http://stfalcon.com>
 */
class PluginSitemap extends Plugin {

    /**
     * Указанные в массивы делегаты будут переданы движку автоматически
     * перед инициализацией плагина
     */
    protected $aInherits = array(
        'entity' => array(
            'ModuleBlog_EntityBlog',
            'ModuleTopic_EntityTopic',
            'ModuleUser_EntityUser',
        ),
        'module' => array(
            'ModuleSitemap',
            'ModuleTopic',
            'ModuleBlog',
            'ModuleUser',
        ),
        'mapper' => array(
            'ModuleUser_MapperUser',
        ),
    );

    /**
     * Активация плагина
     *
     * @return boolean
     */
    public function Activate() {

        return true;
    }

    /**
     * Инициализация плагина
     *
     * @return boolean
     */
    public function Init() {

        return true;
    }

    /**
     * Деактивация плагина
     *
     * @return boolean
     */
    public function Deactivate() {

        return true;
    }

}

// EOF