CREATE TABLE IF NOT EXISTS recommenders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('url', 'book', 'movie', 'podcast') NOT NULL,
    title VARCHAR(255) NOT NULL,
    url TEXT,
    notes TEXT,
    recommender_id INT UNSIGNED,
    status ENUM('queue', 'consumed') NOT NULL DEFAULT 'queue',
    consumed_at DATETIME,
    is_dead TINYINT(1) NOT NULL DEFAULT 0,
    is_paywalled TINYINT(1) NOT NULL DEFAULT 0,
    -- book-specific
    isbn VARCHAR(13),
    book_format SET('paperback', 'hardcover', 'ebook'),
    -- podcast-specific
    show_name VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recommender_id) REFERENCES recommenders(id) ON DELETE SET NULL
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
    name VARCHAR(255) NOT NULL,
    description TEXT,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS media_lists (
    media_id INT UNSIGNED NOT NULL,
    list_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (media_id, list_id),
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);