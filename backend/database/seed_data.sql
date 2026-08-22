-- ============================================
-- FFJS Seed Data
-- Run this file AFTER schema.sql and relationships.sql
-- All passwords are: password123
-- ============================================

USE ffjs_db;

-- ── Users ────────────────────────────────────
-- Password hash for 'password123'
INSERT INTO users (full_name, email, password, role, security_question, security_answer) VALUES
(
    'System Admin',
    'admin@ffjs.com',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty',
    'administrator',
    'What is your favourite football team?',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty'
),
(
    'John Peter',
    'referee@ffjs.com',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty',
    'referee',
    'What primary school did you attend?',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty'
),
(
    'Michael James',
    'manager@ffjs.com',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty',
    'manager',
    'In what city were you born?',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty'
),
(
    'Jane Viewer',
    'viewer@ffjs.com',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty',
    'viewer',
    'What is the name of your first pet?',
    '$2y$10$TKh8H1.PfbuNMhIfGy01QeHMSMJzLRLnXNkFEjPHCoNKdR7l9s4ty'
);

-- ── Teams ────────────────────────────────────
INSERT INTO teams (name, manager_id, total_players, status) VALUES
('Lions FC',  3, 18, 'active'),
('Eagles FC', NULL, 20, 'active'),
('Stars FC',  NULL, 16, 'active'),
('United FC', NULL, 22, 'active');

-- ── Players ──────────────────────────────────
INSERT INTO players (team_id, name, jersey_number, position) VALUES
(1, 'Ali Hassan',    10, 'Forward'),
(1, 'James Mwita',   5, 'Midfielder'),
(1, 'Peter Juma',    1, 'Goalkeeper'),
(2, 'David Kato',    9, 'Forward'),
(2, 'Samuel Osei',   4, 'Defender'),
(3, 'John Mkapa',    7, 'Midfielder'),
(4, 'Eric Tembo',   11, 'Forward');

-- ── Matches ──────────────────────────────────
INSERT INTO matches (team_a_id, team_b_id, referee_id, match_date, venue, status) VALUES
(1, 2, 2, '2026-05-01', 'Mbeya Stadium',    'completed'),
(3, 4, 2, '2026-05-03', 'Sokoine Stadium',  'completed'),
(1, 3, 2, '2026-05-15', 'Mbeya Stadium',    'upcoming'),
(2, 4, 2, '2026-05-18', 'Sokoine Stadium',  'upcoming');

-- ── Results ──────────────────────────────────
INSERT INTO results (match_id, score_a, score_b, notes, submitted_by, status) VALUES
(1, 2, 1, 'Good match, no incidents.',  2, 'verified'),
(2, 1, 1, 'Draw after 90 minutes.',     2, 'verified');

-- ── Prizes ───────────────────────────────────
INSERT INTO prizes (title, criteria, amount, prize_type) VALUES
('Championship Trophy',  'Team with most points at end of season',  1000000.00, 'money'),
('Golden Boot',          'Player with most goals in the season',           0.00, 'trophy'),
('Fair Play Award',      'Team with least yellow and red cards',            0.00, 'certificate'),
('Best Referee Award',   'Referee with most verified matches',              0.00, 'award');

-- ── Prize Distribution ────────────────────────
INSERT INTO prize_distribution (prize_id, winner_name, winner_type, team_id, awarded_by, status) VALUES
(1, 'Lions FC',   'team',   1,    1, 'approved'),
(2, 'Ali Hassan', 'player', NULL, 1, 'approved');

-- ── Settings ─────────────────────────────────
INSERT INTO settings (setting_key, setting_value) VALUES
('system_name',      'Fair Football Judgement System'),
('admin_email',      'admin@ffjs.com'),
('season',           '2026 Season'),
('tournament_name',  'FFJS Cup 2026');

-- ── Activity Log ─────────────────────────────
INSERT INTO activity_log (user_id, activity, status) VALUES
(2, 'Match result verified — Lions FC vs Eagles FC',       'completed'),
(1, 'Prize allocated — Championship Trophy to Lions FC',   'approved'),
(2, 'Match result verified — Stars FC vs United FC',       'completed'),
(1, 'Prize allocated — Golden Boot to Ali Hassan',         'approved');