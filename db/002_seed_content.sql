USE tiara_stavby;
SET NAMES utf8mb4 COLLATE utf8mb4_czech_ci;

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('company_name','TIARA s.r.o.','general'),
('phone','+420 000 000 000','contact'),
('email','info@tiara-stavby.cz','contact'),
('address','Česká republika','contact'),
('hours','Po–Pá 8:00–17:00','contact'),
('ico','','legal'),('dic','','legal'),
('facebook','','social'),('instagram','','social'),('linkedin','','social'),
('google_maps_url','','contact'),('admin_email','info@tiara-stavby.cz','general'),
('stat_projects','100+','general'),('stat_years','10+','general'),('stat_satisfaction','100%','general'),
('tagline','Stavíme s jistotou.','general')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_group=VALUES(setting_group);

INSERT INTO services (lang,title,slug,short_description,content,image_path,icon,active,sort_order) VALUES
('cs','Výstavba','vystavba','Rodinné domy a komerční objekty postavené s důrazem na detail.','Od prvního návrhu po předání klíčů zajistíme koordinaci řemesel, dodávky materiálů i kontrolu kvality. Každou stavbu plánujeme podle potřeb klienta a držíme se dohodnutého rozpočtu i harmonogramu.',NULL,'fa-solid fa-house',1,1),
('cs','Rekonstrukce','rekonstrukce','Proměňujeme byty, domy a nebytové prostory v místa pro nový začátek.','Rekonstrukci vedeme s ohledem na charakter budovy i váš každodenní provoz. Zajistíme bourací práce, nové rozvody, povrchy i finální detaily.',NULL,'fa-solid fa-hammer',1,2),
('cs','Modernizace','modernizace','Více komfortu, nižší náklady a vyšší hodnota vaší nemovitosti.','Navrhneme a provedeme úpravy, které prodlouží životnost objektu a zlepší jeho energetickou i užitnou hodnotu. Od zateplení po modernizaci interiéru.',NULL,'fa-solid fa-arrows-rotate',1,3)
ON DUPLICATE KEY UPDATE title=VALUES(title), short_description=VALUES(short_description), content=VALUES(content), image_path=VALUES(image_path), icon=VALUES(icon), active=VALUES(active), sort_order=VALUES(sort_order);

INSERT INTO projects (lang,title,slug,category,location,year,short_description,description,main_image,active,featured,sort_order) VALUES
('cs','Rodinný dům Na Vyhlídce','dum-na-vyhlidce','Výstavba','Praha-západ',2025,'Novostavba rodinného domu s čistou architekturou a velkorysým výhledem.','Kompletní realizace domu od přípravy pozemku až po finální povrchy. Hlavní důraz jsme kladli na přesnost detailů, přirozené světlo a dlouhodobě úsporný provoz.','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=85',1,1,1),
('cs','Nový život městského bytu','byt-vinohrady','Rekonstrukce','Praha 2',2024,'Citlivá rekonstrukce bytu, která zachovala charakter původního prostoru.','Kompletní rekonstrukce zahrnovala nové rozvody, úpravu dispozice, obnovu parket a zakázkové truhlářské prvky. Výsledek propojuje původní architekturu se současným komfortem.','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=85',1,1,2),
('cs','Administrativní prostory Karlín','kancelare-karlin','Komerční objekty','Praha 8',2024,'Funkční modernizace kanceláří pro rostoucí tým.','Modernizace pracovního prostředí s důrazem na akustiku, kvalitní světlo a flexibilní členění prostoru. Práce probíhaly za provozu po etapách.','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=85',1,1,3)
ON DUPLICATE KEY UPDATE title=VALUES(title), slug=VALUES(slug), category=VALUES(category), location=VALUES(location), year=VALUES(year), short_description=VALUES(short_description), description=VALUES(description), main_image=VALUES(main_image), active=VALUES(active), featured=VALUES(featured), sort_order=VALUES(sort_order);

INSERT INTO page_sections (section_key,lang,title,subtitle,content,image_path,active,sort_order) VALUES
('about','cs','Spolehlivý stavební partner','TIARA s.r.o.','Pracujeme pro soukromé i firemní klienty. Spojujeme zkušené řemeslo s moderními technologiemi, držíme se dohod a termínů a každému projektu věnujeme individuální pozornost. Od úvodní konzultace až po předání hotového díla máte jednoho partnera, který za výsledek odpovídá.',NULL,1,1),
('home_intro','cs','Stavíme vaši lepší budoucnost','Veškeré stavební práce','Kvalitní výstavba, rekonstrukce a modernizace pro váš domov i podnikání.',NULL,1,1),
('cta','cs','Plánujete stavbu nebo rekonstrukci?','Pojďme ji společně proměnit v realitu.','Ozvěte se nám. Připravíme řešení podle vašich představ, rozpočtu a termínu.',NULL,1,1),
('stats','cs','TIARA v číslech','Zkušenost, na kterou se můžete spolehnout.','100+ realizovaných projektů|10+ let zkušeností|100% spokojených zákazníků|Stavíme s jistotou.',NULL,1,1)
ON DUPLICATE KEY UPDATE title=VALUES(title), subtitle=VALUES(subtitle), content=VALUES(content), image_path=VALUES(image_path), active=VALUES(active), sort_order=VALUES(sort_order);

INSERT INTO navigation (lang,title,url,active,sort_order) VALUES
('cs','Domů','/',1,1),('cs','O nás','/o-nas',1,2),('cs','Služby','/sluzby',1,3),('cs','Realizace','/realizace',1,4),('cs','Reference','/reference',1,5),('cs','Kontakt','/kontakt',1,6)
ON DUPLICATE KEY UPDATE title=VALUES(title), active=VALUES(active), sort_order=VALUES(sort_order);

INSERT INTO seo_metadata (page_path,lang,meta_title,meta_description,og_title,og_description,robots) VALUES
('/','cs','TIARA s.r.o. | Veškeré stavební práce','Výstavba, rekonstrukce a modernizace pro váš domov i podnikání. Spolehlivý partner pro váš stavební projekt.','TIARA s.r.o. – stavíme vaši lepší budoucnost','Kvalitní stavby s důrazem na přesnost, kvalitu a termíny.','index,follow')
ON DUPLICATE KEY UPDATE meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), og_title=VALUES(og_title), og_description=VALUES(og_description), robots=VALUES(robots);

INSERT INTO seo_metadata (page_path,lang,meta_title,meta_description,og_title,og_description,robots) VALUES
('/o-nas','cs','O nás | TIARA s.r.o.','Poznejte stavební společnost TIARA s.r.o. a náš přístup k výstavbě, rekonstrukcím a modernizacím.','Spolehlivý stavební partner','Od první konzultace po předání hotového díla.','index,follow'),
('/sluzby','cs','Stavební služby | TIARA s.r.o.','Výstavba rodinných domů, rekonstrukce bytů a modernizace nemovitostí.','Komplexní stavební práce','Jeden partner pro váš stavební projekt.','index,follow'),
('/realizace','cs','Realizované projekty | TIARA s.r.o.','Prohlédněte si vybrané stavby, rekonstrukce a modernizace realizované společností TIARA.','Realizace TIARA s.r.o.','Stavby s péčí o každý detail.','index,follow'),
('/reference','cs','Reference | TIARA s.r.o.','Galerie stavebních realizací TIARA s.r.o.','Naše reference','Domovy a místa pro práci, proměněná s péčí.','index,follow'),
('/kontakt','cs','Kontakt | TIARA s.r.o.','Plánujete stavbu nebo rekonstrukci? Kontaktujte TIARA s.r.o. a probereme váš projekt.','Kontaktujte TIARA s.r.o.','Úvodní konzultace bez závazků.','index,follow')
ON DUPLICATE KEY UPDATE meta_title=VALUES(meta_title), meta_description=VALUES(meta_description), og_title=VALUES(og_title), og_description=VALUES(og_description), robots=VALUES(robots);
