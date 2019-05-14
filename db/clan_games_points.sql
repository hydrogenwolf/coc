BEGIN TRANSACTION;

SELECT tag, exp_level, name, MAX(achievement31) - MIN(achievement31) AS points FROM journal 
INNER JOIN achievements ON journal.id = achievements.journal
WHERE journal.dt > DATE('now', '-10 day')
GROUP BY tag
ORDER BY exp_level DESC;

COMMIT;
