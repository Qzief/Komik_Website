CREATE TABLE IF NOT EXISTS mangas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(255) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    manga_url TEXT NOT NULL,
    external_manga_id VARCHAR(64) NULL,
    source_base VARCHAR(255) NOT NULL,
    thumbnail_url TEXT NULL,
    chapter_count INT UNSIGNED NOT NULL DEFAULT 0,
    last_scraped_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mangas_slug (slug),
    UNIQUE KEY uq_mangas_source_external (source_base, external_manga_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chapters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    manga_id BIGINT UNSIGNED NOT NULL,
    chapter_number INT UNSIGNED NOT NULL,
    chapter_label VARCHAR(255) NOT NULL,
    chapter_url TEXT NOT NULL,
    image_count INT UNSIGNED NOT NULL DEFAULT 0,
    first_image_url TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_chapters_manga_number (manga_id, chapter_number),
    CONSTRAINT fk_chapters_manga
        FOREIGN KEY (manga_id) REFERENCES mangas (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chapter_images (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    chapter_id BIGINT UNSIGNED NOT NULL,
    image_order INT UNSIGNED NOT NULL,
    image_url TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_chapter_images_order (chapter_id, image_order),
    CONSTRAINT fk_chapter_images_chapter
        FOREIGN KEY (chapter_id) REFERENCES chapters (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE mangas
    ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER thumbnail_url,
    ADD COLUMN IF NOT EXISTS genres_json LONGTEXT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS author VARCHAR(255) NULL AFTER genres_json,
    ADD COLUMN IF NOT EXISTS artist VARCHAR(255) NULL AFTER author,
    ADD COLUMN IF NOT EXISTS status ENUM('ongoing', 'completed', 'hiatus') NOT NULL DEFAULT 'ongoing' AFTER artist;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comic_likes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    manga_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comic_likes_user_manga (user_id, manga_id),
    CONSTRAINT fk_comic_likes_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comic_likes_manga
        FOREIGN KEY (manga_id) REFERENCES mangas (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comic_bookmarks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    manga_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comic_bookmarks_user_manga (user_id, manga_id),
    CONSTRAINT fk_comic_bookmarks_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comic_bookmarks_manga
        FOREIGN KEY (manga_id) REFERENCES mangas (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comic_votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    manga_id BIGINT UNSIGNED NOT NULL,
    vote_type ENUM('continue', 'stop') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comic_votes_user_manga (user_id, manga_id),
    CONSTRAINT fk_comic_votes_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comic_votes_manga
        FOREIGN KEY (manga_id) REFERENCES mangas (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_mangas_updated_at ON mangas (updated_at);
CREATE INDEX idx_chapters_manga_updated ON chapters (manga_id, updated_at);

-- Setelah schema dibuat, generate password hash dengan:
-- php tools/hash-password.php password-admin
-- Lalu insert admin manual, contoh:
-- INSERT INTO users (name, email, password, role)
-- VALUES ('Administrator', 'admin@local.test', 'HASIL_HASH', 'admin');
