PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;

CREATE TABLE achievements (
        id INTEGER PRIMARY KEY,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP,
        journal INTEGER NOT NULL,
	achievement0 INTEGER,
	achievement1 INTEGER,
	achievement2 INTEGER,
	achievement3 INTEGER,
	achievement4 INTEGER,
	achievement5 INTEGER,
	achievement6 INTEGER,
	achievement7 INTEGER,
	achievement8 INTEGER,
	achievement9 INTEGER,
	achievement10 INTEGER,
	achievement11 INTEGER,
	achievement12 INTEGER,
	achievement13 INTEGER,
	achievement14 INTEGER,
	achievement15 INTEGER,
	achievement16 INTEGER,
	achievement17 INTEGER,
	achievement18 INTEGER,
	achievement19 INTEGER,
	achievement20 INTEGER,
	achievement21 INTEGER,
	achievement22 INTEGER,
	achievement23 INTEGER,
	achievement24 INTEGER,
	achievement25 INTEGER,
	achievement26 INTEGER,
	achievement27 INTEGER,
	achievement28 INTEGER,
	achievement29 INTEGER,
	achievement30 INTEGER,
	achievement31 INTEGER,
	achievement32 INTEGER,
	achievement33 INTEGER,
	achievement34 INTEGER
);

CREATE INDEX idx_achievements_journal ON achievements (journal);

COMMIT;
