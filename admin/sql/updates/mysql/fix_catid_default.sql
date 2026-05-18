-- Fix catid column if it exists without a default value
ALTER TABLE `#__pja_events` 
    MODIFY COLUMN `catid` int(11) NOT NULL DEFAULT 0;
