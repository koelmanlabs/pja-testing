-- JEM 6.0.0 - Add missing performance indexes
-- These are safe to run on existing installs; IF NOT EXISTS prevents errors

ALTER TABLE `#__pja_events`
    ADD INDEX IF NOT EXISTS `idx_dates`      (`dates`),
    ADD INDEX IF NOT EXISTS `idx_enddates`   (`enddates`),
    ADD INDEX IF NOT EXISTS `idx_recurrence` (`recurrence_type`, `recurrence_first_id`);
