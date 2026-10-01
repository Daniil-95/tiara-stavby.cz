USE tiara_stavby;

UPDATE navigation
SET url = '/#contact'
WHERE lang = 'cs' AND (url IN ('/kontakt', '/cs/kontakt') OR title = 'Kontakt');

UPDATE settings
SET setting_value = '/#contact'
WHERE setting_key IN ('home_cta_url', 'header_cta_url')
  AND setting_value IN ('/kontakt', '/cs/kontakt');

DELETE FROM settings WHERE setting_key = 'seo_keywords_kontakt';

DELETE FROM seo_metadata
WHERE lang = 'cs' AND page_path IN ('/kontakt', '/cs/kontakt');