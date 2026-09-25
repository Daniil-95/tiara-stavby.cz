USE tiara_stavby;

-- Categories remain editable labels in projects.category so each language can use its own wording.
-- This migration reserves stable category keys for filters and CMS forms.
ALTER TABLE projects ADD COLUMN category_key VARCHAR(40) NULL AFTER category;
UPDATE projects SET category_key = CASE
  WHEN LOWER(category) LIKE '%rekonstruk%' OR LOWER(category) LIKE '%renovat%' THEN 'renovation'
  WHEN LOWER(category) LIKE '%modern%' THEN 'modernisation'
  WHEN LOWER(category) LIKE '%komer%' OR LOWER(category) LIKE '%commercial%' THEN 'commercial'
  ELSE 'construction' END
WHERE category_key IS NULL;
