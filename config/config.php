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

// Добавляем экшен плагина в роутер
Config::Set('router.page.sitemap', 'PluginSitemap_ActionSitemap');

$config['$root$']['router']['uri'] = array(
    'sitemap.xml' => 'sitemap',
    '[/^sitemap_(\w+)_(\d+)\.xml$/i]' => 'sitemap/sitemap/$1/$2',
);

$config['items_per_page'] = 100;   // максимальное количество ссылок на одной странице карты
$config['users_per_page'] = 100;   // максимальное количество пользователей на одной странице карты

$config['default'] = array(
    'sitemap' => array(
        'cache_lifetime' => 'P1D', // 1 day
        'changefreq'     => 'daily',
        'priority'       => 0.7,
    ),
    'url' => array(
        'changefreq'     => 'weekly',
        'priority'       => 0.7,
    ),
);
/**
 * Настройки времени жизни кеша данных, приоритета страниц, вероятной частоты изменений страницы
 *
 * cache_lifetime - время жизни кеша для наборов извлекаемых из БД, значение задается в секундах
 * priority - приоритет страницы, значение от 0.0 до 1.0
 * changefreq - вероятная частота изменений страницы, значения: always|hourly|daily|weekly|monthly|yearly|never
 */

$config['type']['index'] = array(
    //'cache_lifetime' => 'PT1H', // 1 hour
    'sitemap' => array(
        'general',
        'topics',
        'blogs',
        'users',
    ),
);

// Главная страница и комментарии
$config['type']['general'] = array (
    'cache_lifetime' => 'PT1H', // 1 hour
    'changefreq' => 'hourly',
    'url' => array(
        // Главная страница
        'mainpage' => array (
            'loc' => '___path.root.url___',
            'priority' => '1',
            'changefreq' => 'hourly'
        ),
        // Страница комментариев
        'comments' => array (
            'loc' => '___path.root.url___/comments/',
            'priority' => '0.7',
            'changefreq' => 'hourly'
        ),
    ),
);
// Записи
$config['type']['topics'] = array (
    'cache_lifetime' => 60 * 60 * 0.5, // 30 минут
    'priority' => array(1, 1, 1, 0.9, 0.9, 0.9, 0.8),
    'changefreq' => array('hourly', 'hourly', 'hourly', 'daily', 'daily', 'daily', 'weekly'),
);
// Блоги
$config['type']['blogs'] = array (
    'cache_lifetime' => 60 * 60 * 8, // 8 часов
    'priority' => '0.8',
    'changefreq' => 'weekly'
);
// Пользователи
$config['type']['users'] = array (
    'cache_lifetime' => 60 * 60 * 1, // 1 час
    // Профиль пользователя
    'profile' => array (
        'priority' => '0.5',
        'changefreq' => 'weekly'
    ),
    // Комментарии пользователя
    'comments' => array (
        'priority' => '0.7',
        'changefreq' => 'weekly'
    ),
    // Топики пользователя
    'my' => array (
        'priority' => '0.8',
        'changefreq' => 'weekly'
    ),
);

return $config;

// EOF