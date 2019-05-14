PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE spells (
        id INTEGER PRIMARY KEY,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP,
        journal INTEGER NOT NULL,
	[Lightning Spell] INTEGER,
	[Healing Spell] INTEGER,
	[Rage Spell] INTEGER,
	[Jump Spell] INTEGER,
	[Freeze Spell] INTEGER,
	[Poison Spell] INTEGER,
	[Earthquake Spell] INTEGER,
	[Haste Spell] INTEGER,
	[Clone Spell] INTEGER,
	[Skeleton Spell] INTEGER,
	[Bat Spell] INTEGER
);

CREATE INDEX idx_spells_journal ON spells (journal);

COMMIT;
