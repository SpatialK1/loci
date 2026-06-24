-- Users must be created before media and lists due to foreign key dependencies

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    archive_visibility ENUM('private', 'group', 'public') NOT NULL DEFAULT 'private',
    accept_recommendations TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    reset_token VARCHAR(64) NULL,
    reset_token_expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS recommenders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    type ENUM('url', 'book', 'movie', 'podcast') NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NULL,
    url VARCHAR(2048),
    notes TEXT,
    recommender_id INT UNSIGNED,
    status ENUM('queue', 'consumed') NOT NULL DEFAULT 'queue',
    consumed_at DATETIME,
    is_dead TINYINT(1) NOT NULL DEFAULT 0,
    is_paywalled TINYINT(1) NOT NULL DEFAULT 0,
    visibility ENUM('private', 'group', 'public') NOT NULL DEFAULT 'group',
    recommended_by_user_id INT UNSIGNED NULL,
    -- book-specific
    isbn VARCHAR(13),
    book_format SET('paperback', 'hardcover', 'ebook'),
    -- podcast-specific
    show_name VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX unique_url (url(768)),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recommender_id) REFERENCES recommenders(id) ON DELETE SET NULL,
    FOREIGN KEY (recommended_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS media_tags (
    media_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (media_id, tag_id),
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS media_lists (
    media_id INT UNSIGNED NOT NULL,
    list_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (media_id, list_id),
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invitations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    invited_by_user_id INT UNSIGNED NOT NULL,
    accepted_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invited_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT UNSIGNED NOT NULL,
    to_user_id INT UNSIGNED NOT NULL,
    media_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` TEXT
);

INSERT INTO settings (`key`, `value`) VALUES
    ('site_public', 'false'),
    ('site_title', 'Loci'),
    ('theme', 'light'),
    ('font_size', '1.0'),
    ('contact_url', ''),
    ('items_per_page', '20'),
    ('default_sort', 'created_at'),
    ('default_sort_direction', 'DESC'),
    ('default_status_filter', 'all'),
    ('view_mode', 'list'),
    ('language', 'auto'),
    ('registration_mode', 'invite_only'),
    ('mail_from', ''),
    ('mail_from_name', 'Loci');