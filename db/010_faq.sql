USE tiara_stavby;

CREATE TABLE IF NOT EXISTS faqs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lang CHAR(2) NOT NULL DEFAULT 'cs',
  question VARCHAR(500) NOT NULL,
  answer MEDIUMTEXT NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_faqs_public (lang, active, sort_order),
  CONSTRAINT chk_faqs_lang CHECK (lang = 'cs')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO faqs (lang, question, answer, active, sort_order)
SELECT 'cs', 'Zajistíte celý projekt?', 'Ano, koordinujeme celý proces od konzultace a plánování až po dokončovací práce.', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM faqs);

INSERT INTO faqs (lang, question, answer, active, sort_order)
SELECT 'cs', 'Jak připravíte cenovou nabídku?', 'Nejdříve projdeme zadání a stav nemovitosti. Poté připravíme přehledný návrh rozsahu a termínů.', 1, 2
WHERE (SELECT COUNT(*) FROM faqs) = 1;

INSERT INTO faqs (lang, question, answer, active, sort_order)
SELECT 'cs', 'Lze práce rozdělit do etap?', 'Etapy plánujeme společně s vámi a zohledňujeme praktické fungování domácnosti nebo firmy.', 1, 3
WHERE (SELECT COUNT(*) FROM faqs) = 2;
