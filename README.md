[![Build Status](https://secure.travis-ci.org/stfalcon-studio/ls-plugin_sitemap.png?branch=master)](https://travis-ci.org/stfalcon-studio/ls-plugin_sitemap)

ОПИСАНИЕ
--------

Плагин предназначен для генерации Sitemaps в Alto CMS.

С помощью файла Sitemap веб-мастеры могут сообщать поисковым системам о
веб-страницах, которые доступны для сканирования. Файл Sitemap представляет
собой XML-файл, в котором перечислены URL-адреса веб-сайта в сочетании с
метаданными, связанными с каждым URL-адресом (дата его последнего изменения;
частота изменений; его приоритетность на уровне сайта), чтобы поисковые системы
могли более грамотно сканировать этот сайт.

Более подробную информацию вы можете найти на странице http://sitemaps.org/ru/.

ЛИЦЕНЗИИ
-------

Плагин распостраняется по лицензии GNU GPL. Вы можете найти копию
этой лицензии в файле LICENSE.txt.


ИСТОРИЯ ВЕРСИЙ
--------------

v1.0.0
- Первая версия плагина для Alto CMS v.1.0, созданная на основе одноименного плагина для LiveStreet CMS
  (автор Stepan Tanasiychuk)

Пример конфигурации server для корректной работы с nginx
-------

    server {
        # ... другие настройки
    
        location / {
            index       index.html index.htm index.php;
            try_files   $uri $uri/ @ls; # для несуществующих файлов
        }
    
        location ~ \.php$ {
            fastcgi_pass        unix:/var/run/php5-fpm.sock;
            fastcgi_index       index.php;
            fastcgi_param       SCRIPT_FILENAME /var/www/example.com/www/$fastcgi_script_name;
            include             fastcgi_params;
        }
    
        location @ls {
            fastcgi_pass        unix:/var/run/php5-fpm.sock;
            fastcgi_param       SCRIPT_FILENAME  /var/www/example.com/www/index.php;
            fastcgi_param       QUERY_STRING     $uri;
            include             fastcgi_params;
        }
    
    }
