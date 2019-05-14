PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE troops (
        id INTEGER PRIMARY KEY,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP,
        journal INTEGER NOT NULL,
	[Barbarian] INTEGER,
	[Archer] INTEGER,
	[Goblin] INTEGER,
	[Giant] INTEGER,
	[Wall Breaker] INTEGER,
	[Balloon] INTEGER,
	[Wizard] INTEGER,
	[Healer] INTEGER,
	[Dragon] INTEGER,
	[P.E.K.K.A] INTEGER,
	[Minion] INTEGER,
	[Hog Rider] INTEGER,
	[Valkyrie] INTEGER,
	[Golem] INTEGER,
	[Witch] INTEGER,
	[Lava Hound] INTEGER,
	[Bowler] INTEGER,
	[Baby Dragon] INTEGER,
	[Miner] INTEGER,
	[Wall Wrecker] INTEGER,
	[Battle Blimp] INTEGER,
	[Ice Golem] INTEGER,
	[Electro Dragon] INTEGER,
	[Stone Slammer] INTEGER
);

CREATE INDEX idx_troops_journal ON troops (journal);

COMMIT;
