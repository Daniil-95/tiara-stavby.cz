USE tiara_stavby;

DELETE FROM navigation
WHERE lang = 'cs' AND (url IN ('/reference', '/cs/reference') OR title = 'Reference');

DELETE FROM seo_metadata
WHERE lang = 'cs' AND page_path IN ('/reference', '/cs/reference');