PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE heroes (
        id INTEGER PRIMARY KEY,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP,
        journal INTEGER NOT NULL,
	[Barbarian King] INTEGER,
	[Archer Queen] INTEGER,
	[Grand Warden] INTEGER
);

CREATE INDEX idx_heroes_journal ON heroes (journal);

COMMIT;
