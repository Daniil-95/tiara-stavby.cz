# TIARA s.r.o. — webové stránky stavební společnosti

## O projektu

Web představuje společnost TIARA s.r.o., která se zaměřuje na výstavbu, rekonstrukce a modernizace rodinných domů i komerčních prostor. Zákazníci zde najdou nabídku služeb, ukázky dokončených realizací a kontaktní formulář pro nezávaznou poptávku.

Součástí projektu je neveřejná správa obsahu. Oprávněný správce v ní může upravovat stránky, služby, realizace, fotografie, kontaktní údaje a navigaci. Může také prohlížet poptávky a měnit jejich stav.

## Technologie

Projekt používá PHP 8.2, Nette Framework 3.2, šablony Latte 3 a databázi MySQL 8. Pro tvorbu stylů slouží Sass a Node.js.

## Místní spuštění

1. Nainstalujte PHP 8.2 nebo novější, MySQL 8, Composer a Node.js. PHP musí mít zapnutá rozšíření `fileinfo`, `gd`, `mbstring`, `pdo` a `pdo_mysql`.
2. Nastavte připojení k databázi v `app/config/local.neon`. Tento soubor obsahuje místní údaje, které nepatří do verzovacího systému.
3. Spusťte SQL soubory z adresáře `db/` postupně od `001_initial_schema.sql` do `008_contact_anchor.sql`. První soubor vytvoří databázi `tiara_stavby`.
4. Nainstalujte závislosti, sestavte styly a vytvořte účet správce:

   ```sh
   composer install
   npm install
   npm run build:css
   php bin/create-admin.php
   ```

   Skript pro vytvoření účtu si vyžádá jméno, e-mail a heslo.

   Přihlašovací stránka administrace záměrně neuvádí tento technický příkaz běžným
   uživatelům. Pro vytvoření prvního administrátora použijte `php bin/create-admin.php`.

5. Spusťte místní web:

   ```sh
   php -d upload_max_filesize=8M -d post_max_size=10M -S 127.0.0.1:8000 -t www www/router.php
   ```

   Veřejný web bude dostupný na `http://127.0.0.1:8000/`, správa obsahu na `http://127.0.0.1:8000/admin/login`.

Fotografie realizací se ukládají do `www/uploads/projects`. Při nasazení na server musí mít PHP právo do tohoto adresáře zapisovat. Před zveřejněním doplňte skutečné firemní kontakty a nastavte odesílání e-mailů; bez něj se poptávky uloží do databáze, ale nepřijde e-mailové upozornění.
