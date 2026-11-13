-- Galun Design. Estructura y semilla neutra. Sin contraseñas en claro.

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE works (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  frame VARCHAR(16) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  link_url VARCHAR(255) NOT NULL,
  group_label VARCHAR(80) NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  width INT UNSIGNED NOT NULL,
  height INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_works_public (is_published, is_featured, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visuals (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(160) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  poster_path VARCHAR(255) NOT NULL,
  link_url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  width INT UNSIGNED NOT NULL,
  height INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_visuals_public (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  handle VARCHAR(80) NOT NULL,
  photo_path VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_clients_public (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id TINYINT UNSIGNED NOT NULL,
  brand_name VARCHAR(120) NOT NULL,
  tagline VARCHAR(240) NOT NULL,
  whatsapp_e164 VARCHAR(20) NULL,
  whatsapp_message VARCHAR(240) NULL,
  instagram_url VARCHAR(255) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_login_attempts_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (email, password_hash, name) VALUES (
  'galun@galundesign.com',
  '$2y$10$Z0yjMA9IMVQxvvGyCz578O1rZ/FdvnbJw18Sn/A0AjRFj8NOxOql6',
  'Galun'
);

INSERT INTO settings (id, brand_name, tagline, whatsapp_e164, whatsapp_message, instagram_url) VALUES (
  1,
  'Galun Design',
  'Diseño gráfico para artistas, lanzamientos y eventos.',
  '5492346571207',
  'Hola Galun, vi la página y quiero consultar por un trabajo.',
  'https://www.instagram.com/galun.psd/'
);

INSERT INTO works (title, frame, file_path, link_url, group_label, is_featured, sort_order, is_published, width, height) VALUES
('Fuego x Soho', 'feed', 'assets/assets-web-gd/FUEGOFEED2.jpg.jpeg', 'https://www.instagram.com/galun.psd/', 'Fuego', 1, 10, 1, 1080, 1350),
('Amor de su cama', 'story', 'assets/assets-web-gd/ELIASCUMBIA+DOBLEP.jpg.jpeg', 'https://www.instagram.com/eliastomok/', 'Eliastom Kick', 0, 20, 1, 1080, 1920),
('Componemos con Lauta', 'story', 'assets/assets-web-gd/LAUTAFTELIAS.jpg.jpeg', 'https://www.instagram.com/eliastomok/', 'Eliastom Kick', 0, 30, 1, 1080, 1920),
('Zaina x Tomhouse', 'story', 'assets/assets-web-gd/ZAINACOLOR2.jpg.jpeg', 'https://www.instagram.com/eliastomok/', 'Eliastom Kick', 0, 40, 1, 1080, 1920),
('Perro Primo', 'feed', 'assets/assets-web-gd/FEED1.jpg.jpeg', 'https://www.instagram.com/frenesi_fiesta/', 'Eventos', 0, 50, 1, 1080, 1350),
('Tomas Marenzana', 'feed', 'assets/assets-web-gd/FEED1xfrenesi.jpg.jpeg', 'https://www.instagram.com/boveda____/', 'Eventos', 0, 60, 1, 1080, 1350),
('Frenesi', 'feed', 'assets/assets-web-gd/FEEd2.png', 'https://www.instagram.com/frenesi_fiesta/', 'Eventos', 0, 70, 1, 1080, 1350);

INSERT INTO works (title, frame, file_path, link_url, group_label, is_featured, sort_order, is_published, width, height) VALUES
('Fuego x Soho', 'story', 'assets/assets-web-gd/FUEGOXSOHOHISTORIA.jpg.jpeg', 'https://www.instagram.com/galun.psd/', 'Historias', 0, 80, 1, 1080, 1920),
('Fuego x El Club', 'story', 'assets/assets-web-gd/FUEGOXCLUB-HISTORIA.jpg.jpeg', 'https://www.instagram.com/galun.psd/', 'Historias', 0, 90, 1, 1080, 1920);

INSERT INTO visuals (title, file_path, poster_path, link_url, sort_order, is_published, width, height) VALUES
('Lali DJ', 'assets/media/lali-logo.mp4', 'assets/media/lali-logo.jpg', 'https://www.instagram.com/lali__dj/', 10, 1, 1920, 960),
('Lali 360', 'assets/media/lali-360.mp4', 'assets/media/lali-360.jpg', 'https://www.instagram.com/lali__dj/', 20, 1, 1920, 960),
('Luciana Idiart', 'assets/media/luciana.mp4', 'assets/media/luciana.jpg', 'https://www.instagram.com/galun.psd/', 30, 1, 1920, 960),
('Candu', 'assets/media/candu.mp4', 'assets/media/candu.jpg', 'https://www.instagram.com/candu_dominguez/', 40, 1, 1920, 960),
('Lola Bottini', 'assets/media/lola.mp4', 'assets/media/lola.jpg', 'https://www.instagram.com/lolabottini.dj/', 50, 1, 1920, 960);

INSERT INTO clients (name, handle, photo_path, url, sort_order, is_published) VALUES
('Elias Tom', '@eliastomok', 'assets/img/clients/eliastomok.jpg', 'https://www.instagram.com/eliastomok/', 10, 1),
('Candu Dominguez', '@candu_dominguez', 'assets/img/clients/candu_dominguez.jpg', 'https://www.instagram.com/candu_dominguez/', 20, 1),
('Nicole Fernandez', '@__nicolefernandez', 'assets/img/clients/__nicolefernandez.jpg', 'https://www.instagram.com/__nicolefernandez/', 30, 1),
('Lali DJ', '@lali__dj', 'assets/img/clients/lali__dj.jpg', 'https://www.instagram.com/lali__dj/', 40, 1),
('Lola Bottini', '@lolabottini.dj', 'assets/img/clients/lolabottini.jpg', 'https://www.instagram.com/lolabottini.dj/', 50, 1),
('Frenesi Fiesta', '@frenesi_fiesta', 'assets/img/clients/frenesi_fiesta.jpg', 'https://www.instagram.com/frenesi_fiesta/', 60, 1),
('Boveda Fiesta', '@boveda____', 'assets/img/clients/boveda____.jpg', 'https://www.instagram.com/boveda____/', 70, 1);
