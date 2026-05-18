-- Fix events that have time values stored in DATE columns
-- This can happen from a failed save during migration
-- Delete events with invalid dates (time strings in date column)
DELETE FROM `#__pja_events` 
WHERE `dates` IS NOT NULL 
AND `dates` NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
AND `dates` != '';

-- Also fix enddates  
UPDATE `#__pja_events` 
SET `enddates` = NULL
WHERE `enddates` IS NOT NULL 
AND `enddates` NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
AND `enddates` != '';

-- Fix recurrence_limit_date
UPDATE `#__pja_events`
SET `recurrence_limit_date` = NULL
WHERE `recurrence_limit_date` IS NOT NULL
AND `recurrence_limit_date` NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
AND `recurrence_limit_date` != '';
