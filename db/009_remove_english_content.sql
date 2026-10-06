-- Web je pouze v češtině: odstranění anglických záznamů.
DELETE FROM project_images WHERE project_id IN (SELECT id FROM projects WHERE lang <> 'cs');
DELETE FROM projects WHERE lang <> 'cs';
DELETE FROM services WHERE lang <> 'cs';
DELETE FROM navigation WHERE lang <> 'cs';
DELETE FROM page_sections WHERE lang <> 'cs';
DELETE FROM seo_metadata WHERE lang <> 'cs';
DELETE FROM inquiries WHERE lang <> 'cs';DELETE FROM settings WHERE setting_key = 'seo_keywords_home';
