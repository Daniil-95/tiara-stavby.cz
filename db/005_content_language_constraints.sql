USE tiara_stavby;

ALTER TABLE page_sections
  ADD CONSTRAINT chk_page_sections_lang CHECK (lang = 'cs');

ALTER TABLE services
  ADD CONSTRAINT chk_services_lang CHECK (lang = 'cs');

ALTER TABLE inquiries
  ADD CONSTRAINT chk_inquiries_lang CHECK (lang = 'cs');

ALTER TABLE navigation
  ADD CONSTRAINT chk_navigation_lang CHECK (lang = 'cs');

ALTER TABLE seo_metadata
  ADD CONSTRAINT chk_seo_metadata_lang CHECK (lang = 'cs');
