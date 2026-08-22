-- ============================================
-- FFJS Relationships Reference
-- Run AFTER schema.sql
-- ============================================

USE ffjs_db;

-- Confirm foreign key constraints are in place
-- users.id       → teams.manager_id
-- users.id       → matches.referee_id
-- users.id       → results.submitted_by
-- users.id       → prize_distribution.awarded_by
-- users.id       → activity_log.user_id
-- teams.id       → players.team_id
-- teams.id       → matches.team_a_id
-- teams.id       → matches.team_b_id
-- teams.id       → prize_distribution.team_id
-- matches.id     → results.match_id
-- prizes.id      → prize_distribution.prize_id

-- Standings view (auto-calculated from results)
CREATE OR REPLACE VIEW standings AS
SELECT
    t.id,
    t.name,
    COUNT(r.id) AS played,
    SUM(
        CASE
            WHEN (m.team_a_id = t.id AND r.score_a > r.score_b) THEN 3
            WHEN (m.team_b_id = t.id AND r.score_b > r.score_a) THEN 3
            WHEN (r.score_a = r.score_b) THEN 1
            ELSE 0
        END
    ) AS points,
    SUM(
        CASE
            WHEN (m.team_a_id = t.id AND r.score_a > r.score_b) THEN 1
            WHEN (m.team_b_id = t.id AND r.score_b > r.score_a) THEN 1
            ELSE 0
        END
    ) AS wins,
    SUM(
        CASE
            WHEN r.score_a = r.score_b THEN 1
            ELSE 0
        END
    ) AS draws,
    SUM(
        CASE
            WHEN (m.team_a_id = t.id AND r.score_a < r.score_b) THEN 1
            WHEN (m.team_b_id = t.id AND r.score_b < r.score_a) THEN 1
            ELSE 0
        END
    ) AS losses
FROM teams t
LEFT JOIN matches m ON (m.team_a_id = t.id OR m.team_b_id = t.id)
LEFT JOIN results r ON r.match_id = m.id AND r.status = 'verified'
WHERE t.status = 'active'
GROUP BY t.id, t.name
ORDER BY points DESC, wins DESC;