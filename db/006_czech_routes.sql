USE tiara_stavby;

-- Preserve the existing Czech content while moving the public URLs to the root.
UPDATE navigation SET url = CASE url
  WHEN '/cs/' THEN '/'
  WHEN '/cs/o-nas' THEN '/o-nas'
  WHEN '/cs/sluzby' THEN '/sluzby'
  WHEN '/cs/realizace' THEN '/realizace'
  WHEN '/cs/reference' THEN '/reference'
  WHEN '/cs/kontakt' THEN '/kontakt'
  ELSE url
END
WHERE lang = 'cs';
