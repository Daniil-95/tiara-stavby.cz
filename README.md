# TIARA s.r.o. — stavební práce

Dvojjazyčný web (čeština / angličtina) s veřejnou prezentací, MySQL obsahem a administrační částí na Nette Framework 3.2 + Latte 3. Vyžaduje PHP 8.2+, MySQL 8+, Composer a Node.js.

## Instalace

1. Vytvořte databázi a naimportujte migrace v číselném pořadí:

   ```sh
   mysql -u root -p < db/001_initial_schema.sql
   mysql -u root -p tiara_stavby < db/002_seed_content.sql
   mysql -u root -p tiara_stavby < db/003_project_categories.sql
   ```

2. Установите PHP-зависимости и задайте доступ к MySQL в `app/config/local.neon` (не размещайте реальные секреты в Git):

   ```sh
   composer install
   ```

3. Соберите стили и создайте администратора:

   ```sh
   npm install
   npm run build:css
   php bin/create-admin.php
   ```

4. Настройте Apache VirtualHost с `DocumentRoot` на `www/`, включите `mod_rewrite` и разрешите `.htaccess`. Для локального просмотра можно запустить:

   ```sh
   php -S 127.0.0.1:8000 -t www www/router.php
   ```

   Затем откройте `http://127.0.0.1:8000/cs/`, английская версия: `/en/`, админка: `/admin`.

## Структура

- `app/FrontModule` — публичные presenters и Latte-шаблоны.
- `app/AdminModule` — вход и CMS для страниц, услуг, проектов, заявок и настроек.
- `app/Model` — репозитории, изображения и доступ к базе.
- `app/Security` — Nette authenticator и журнал входов.
- `db/` — SQL-схема и последовательные миграции.
- `www/` — публичный document root, стили, скрипты и загружаемые изображения.

Загруженные фотографии проектов хранятся в `www/uploads/projects`. На сервере этот каталог должен быть доступен на запись PHP-процессу. Создавайте резервные копии базы и каталога uploads.
