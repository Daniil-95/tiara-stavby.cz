USE tiara_stavby;

INSERT INTO project_images (project_id,lang,image_path,title,alt_text,sort_order,active)
SELECT p.id, p.lang, p.main_image, p.title, CONCAT(p.title, ' — hlavní fotografie'), 1, 1
FROM projects p
WHERE p.main_image IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM project_images pi WHERE pi.project_id = p.id AND pi.image_path = p.main_image);
