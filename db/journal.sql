PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE journal (
        id INTEGER PRIMARY KEY,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP,
        tag TEXT NOT NULL,
	name TEXT,
	league TEXT,
	role TEXT,
	clanRank INTEGER,
        town_hall INTEGER,
        town_hall_weapon INTEGER,
        exp_level INTEGER NOT NULL,
        war_stars INTEGER NOT NULL,
        attack_wins INTEGER NOT NULL,
        versus_battle_wins INTEGER NOT NULL,
        troops_donated INTEGER NOT NULL,
        troops_received INTEGER NOT NULL
);

CREATE INDEX idx_journal_dt ON journal (dt);
CREATE INDEX idx_journal_tag ON journal (tag);

COMMIT;
