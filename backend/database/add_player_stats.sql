USE ffjs_db;

CREATE TABLE IF NOT EXISTS player_statistics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT NOT NULL,
    player_id INT NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    successful_passes INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    total_points INT DEFAULT 0,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_player_match (match_id, player_id),
    FOREIGN KEY (match_id)    REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id)   REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)   ON DELETE SET NULL
);