-- Таблица зарегистрированных пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица игровых карт
CREATE TABLE IF NOT EXISTS maps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица игровых режимов
CREATE TABLE IF NOT EXISTS game_modes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица игровых лобби
CREATE TABLE IF NOT EXISTS lobbies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    creator_id INT UNSIGNED NOT NULL,
    max_players TINYINT UNSIGNED NOT NULL DEFAULT 10,
    status ENUM('waiting', 'voting', 'results')
        NOT NULL DEFAULT 'waiting',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lobbies_creator
        FOREIGN KEY (creator_id) REFERENCES users(id),

    CONSTRAINT chk_lobbies_max_players
        CHECK (max_players BETWEEN 2 AND 10)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица участников игровых лобби
CREATE TABLE IF NOT EXISTS lobby_members (
    lobby_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (lobby_id, user_id),

    CONSTRAINT fk_lobby_members_lobby
        FOREIGN KEY (lobby_id) REFERENCES lobbies(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_lobby_members_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица раундов голосования
CREATE TABLE IF NOT EXISTS voting_rounds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lobby_id INT UNSIGNED NOT NULL,
    round_number INT UNSIGNED NOT NULL,

    status ENUM('active', 'completed')
        NOT NULL DEFAULT 'active',

    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at DATETIME NOT NULL,
    completed_at DATETIME DEFAULT NULL,

    winning_map_id INT UNSIGNED DEFAULT NULL,
    winning_mode_id INT UNSIGNED DEFAULT NULL,

    CONSTRAINT uq_voting_round_number
        UNIQUE (lobby_id, round_number),

    CONSTRAINT fk_voting_rounds_lobby
        FOREIGN KEY (lobby_id) REFERENCES lobbies(id),

    CONSTRAINT fk_voting_rounds_map
        FOREIGN KEY (winning_map_id) REFERENCES maps(id),

    CONSTRAINT fk_voting_rounds_mode
        FOREIGN KEY (winning_mode_id) REFERENCES game_modes(id),

    CONSTRAINT chk_voting_round_time
        CHECK (ends_at > started_at),

    CONSTRAINT chk_voting_round_number
        CHECK (round_number > 0)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица голосов за игровые карты
CREATE TABLE IF NOT EXISTS map_votes (
    round_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    map_id INT UNSIGNED NOT NULL,

    voted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (round_id, user_id),

    CONSTRAINT fk_map_votes_round
        FOREIGN KEY (round_id) REFERENCES voting_rounds(id),

    CONSTRAINT fk_map_votes_user
        FOREIGN KEY (user_id) REFERENCES users(id),

    CONSTRAINT fk_map_votes_map
        FOREIGN KEY (map_id) REFERENCES maps(id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
  
  -- Таблица голосов за игровые режимы
CREATE TABLE IF NOT EXISTS mode_votes (
    round_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    mode_id INT UNSIGNED NOT NULL,

    voted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (round_id, user_id),

    CONSTRAINT fk_mode_votes_round
        FOREIGN KEY (round_id) REFERENCES voting_rounds(id),

    CONSTRAINT fk_mode_votes_user
        FOREIGN KEY (user_id) REFERENCES users(id),

    CONSTRAINT fk_mode_votes_mode
        FOREIGN KEY (mode_id) REFERENCES game_modes(id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;