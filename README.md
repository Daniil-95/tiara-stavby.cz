# TIARA s.r.o. — stavební práce

Dvojjazyčný web (čeština / angličtina) s veřejnou prezentací, MySQL obsahem a administrační částí na Nette Framework 3.2 + Latte 3. Vyžaduje PHP 8.2+, MySQL 8+, Composer a Node.js.

## Instalace

1. Vytvořte databázi a naimportujte migrace v číselném pořadí:

   ```sh
   mysql --default-character-set=utf8mb4 -u root -p < db/001_initial_schema.sql
   mysql --default-character-set=utf8mb4 -u root -p tiara_stavby < db/002_seed_content.sql
   mysql --default-character-set=utf8mb4 -u root -p tiara_stavby < db/003_project_categories.sql
   mysql --default-character-set=utf8mb4 -u root -p tiara_stavby < db/004_seed_project_galleries.sql
   mysql --default-character-set=utf8mb4 -u root -p tiara_stavby < db/005_content_language_constraints.sql
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
   php -d upload_max_filesize=8M -d post_max_size=10M -S 127.0.0.1:8000 -t www www/router.php
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

Для Apache и PHP-FPM задайте `upload_max_filesize=8M` и `post_max_size=10M` в конфигурации PHP, чтобы загрузка изображений до 8 MB проходила через веб-сервер.

Перед публикацией замените примерные телефон, адрес, IČO/DIČ и адреса электронной почты в админке на фактические данные компании. Настройте PHP `mail()` или SMTP relay: без почтового транспорта заявки будут сохраняться в базе, но email-уведомление не отправится.
