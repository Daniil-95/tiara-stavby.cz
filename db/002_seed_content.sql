USE tiara_stavby;

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('company_name','TIARA s.r.o.','general'),
('phone','+420 777 123 456','contact'),
('email','info@tiara-stavby.cz','contact'),
('address','Praha, Česká republika','contact'),
('hours','Po–Pá 8:00–17:00','contact'),
('ico','','legal'),('dic','','legal'),
('facebook','','social'),('instagram','','social'),('linkedin','','social'),
('google_maps_url','','contact'),('admin_email','info@tiara-stavby.cz','general'),
('stat_projects','100+','general'),('stat_years','10+','general'),('stat_satisfaction','100%','general'),
('tagline','Stavíme s jistotou.','general')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO services (lang,title,slug,short_description,content,image_path,icon,active,sort_order) VALUES
('cs','Výstavba','vystavba','Rodinné domy a komerční objekty postavené s důrazem na detail.','Od prvního návrhu po předání klíčů zajistíme koordinaci řemesel, dodávky materiálů i kontrolu kvality. Každou stavbu plánujeme podle potřeb klienta a držíme se dohodnutého rozpočtu i harmonogramu.',NULL,'fa-solid fa-house',1,1),
('cs','Rekonstrukce','rekonstrukce','Proměňujeme byty, domy a nebytové prostory v místa pro nový začátek.','Rekonstrukci vedeme s ohledem na charakter budovy i váš každodenní provoz. Zajistíme bourací práce, nové rozvody, povrchy i finální detaily.',NULL,'fa-solid fa-hammer',1,2),
('cs','Modernizace','modernizace','Více komfortu, nižší náklady a vyšší hodnota vaší nemovitosti.','Navrhneme a provedeme úpravy, které prodlouží životnost objektu a zlepší jeho energetickou i užitnou hodnotu. Od zateplení po modernizaci interiéru.',NULL,'fa-solid fa-arrows-rotate',1,3),
('en','Construction','construction','Private homes and commercial buildings made with care for every detail.','From the first consultation to the handover, we coordinate trades, materials and quality checks. Every build is planned around your needs, budget and agreed schedule.',NULL,'fa-solid fa-house',1,1),
('en','Renovation','renovation','A new chapter for apartments, houses and commercial spaces.','We manage renovations with respect for the building and your day-to-day life. Our team coordinates demolition, utilities, surfaces and final details.',NULL,'fa-solid fa-hammer',1,2),
('en','Modernisation','modernisation','Better comfort, lower running costs and greater property value.','We deliver upgrades that extend a building’s life and improve its energy and practical performance, from insulation to interior renewal.',NULL,'fa-solid fa-arrows-rotate',1,3)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO projects (lang,title,slug,category,location,year,short_description,description,main_image,active,featured,sort_order) VALUES
('cs','Rodinný dům Na Vyhlídce','dum-na-vyhlidce','Výstavba','Praha-západ',2025,'Novostavba rodinného domu s čistou architekturou a velkorysým výhledem.','Kompletní realizace domu od přípravy pozemku až po finální povrchy. Hlavní důraz jsme kladli na přesnost detailů, přirozené světlo a dlouhodobě úsporný provoz.','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=85',1,1,1),
('cs','Nový život městského bytu','byt-vinohrady','Rekonstrukce','Praha 2',2024,'Citlivá rekonstrukce bytu, která zachovala charakter původního prostoru.','Kompletní rekonstrukce zahrnovala nové rozvody, úpravu dispozice, obnovu parket a zakázkové truhlářské prvky. Výsledek propojuje původní architekturu se současným komfortem.','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=85',1,1,2),
('cs','Administrativní prostory Karlín','kancelare-karlin','Komerční objekty','Praha 8',2024,'Funkční modernizace kanceláří pro rostoucí tým.','Modernizace pracovního prostředí s důrazem na akustiku, kvalitní světlo a flexibilní členění prostoru. Práce probíhaly za provozu po etapách.','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=85',1,1,3),
('en','Hilltop Family House','hilltop-family-house','Construction','West Prague',2025,'A contemporary new home shaped around light, space and the landscape.','A complete build from site preparation to final finishes, with careful attention to detail, natural light and long-term efficiency.','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=85',1,1,1),
('en','A New Chapter for a City Apartment','vinohrady-apartment','Renovation','Prague 2',2024,'A thoughtful renovation that preserved the spirit of the original apartment.','New utilities, a more practical layout, restored timber floors and bespoke joinery bring contemporary comfort to the original architecture.','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1600&q=85',1,1,2),
('en','Karlín Workspaces','karlin-workspaces','Commercial','Prague 8',2024,'A flexible workplace modernisation for a growing team.','An improved workplace focused on acoustics, quality light and flexible zones. Works were delivered in carefully planned phases while the office remained open.','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=85',1,1,3)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO page_sections (section_key,lang,title,subtitle,content,image_path,active,sort_order) VALUES
('about','cs','Spolehlivý stavební partner','TIARA s.r.o.','Pracujeme pro soukromé i firemní klienty. Spojujeme zkušené řemeslo s moderními technologiemi, držíme se dohod a termínů a každému projektu věnujeme individuální pozornost. Od úvodní konzultace až po předání hotového díla máte jednoho partnera, který za výsledek odpovídá.',NULL,1,1),
('about','en','Your dependable construction partner','TIARA s.r.o.','We work with private and commercial clients. Experienced craftsmanship meets modern technology, clear commitments and an individual approach. From the first consultation to handover, you have one partner responsible for the result.',NULL,1,1),
('home_intro','cs','Stavíme vaši lepší budoucnost','Veškeré stavební práce','Kvalitní výstavba, rekonstrukce a modernizace pro váš domov i podnikání.',NULL,1,1),
('home_intro','en','Building a better future','Construction, renovation, modernisation','Quality construction, renovation and modernisation for your home and business.',NULL,1,1),
('cta','cs','Plánujete stavbu nebo rekonstrukci?','Pojďme ji společně proměnit v realitu.','Ozvěte se nám. Připravíme řešení podle vašich představ, rozpočtu a termínu.',NULL,1,1),
('cta','en','Planning a build or renovation?','Let’s bring it to life together.','Tell us what you have in mind. We will shape a plan around your brief, budget and timeline.',NULL,1,1),
('stats','cs','TIARA v číslech','Zkušenost, na kterou se můžete spolehnout.','100+ realizovaných projektů|10+ let zkušeností|100% osobní přístup|Stavíme s jistotou.',NULL,1,1),
('stats','en','TIARA in numbers','Experience you can rely on.','100+ completed projects|10+ years of experience|100% personal approach|Built with confidence.',NULL,1,1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO navigation (lang,title,url,active,sort_order) VALUES
('cs','Domů','/cs/',1,1),('cs','O nás','/cs/o-nas',1,2),('cs','Služby','/cs/sluzby',1,3),('cs','Realizace','/cs/realizace',1,4),('cs','Reference','/cs/reference',1,5),('cs','Kontakt','/cs/kontakt',1,6),
('en','Home','/en/',1,1),('en','About','/en/about',1,2),('en','Services','/en/services',1,3),('en','Projects','/en/projects',1,4),('en','References','/en/references',1,5),('en','Contact','/en/contact',1,6);

INSERT INTO seo_metadata (page_path,lang,meta_title,meta_description,og_title,og_description,robots) VALUES
('/','cs','TIARA s.r.o. | Veškeré stavební práce','Výstavba, rekonstrukce a modernizace pro váš domov i podnikání. Spolehlivý stavební partner z Prahy.','TIARA s.r.o. – stavíme vaši lepší budoucnost','Kvalitní stavby s důrazem na přesnost, kvalitu a termíny.','index,follow'),
('/','en','TIARA s.r.o. | Construction, Renovation & Modernisation','A dependable construction partner for private homes and commercial spaces.','TIARA s.r.o. – building a better future','Quality construction, renovation and modernisation, delivered with care.','index,follow');
