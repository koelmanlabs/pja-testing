-- Performance indexes for com_planjeagenda
-- Run this on existing installations to benefit from the query optimisations.
-- All statements use IF NOT EXISTS / ignore errors so they are safe to re-run.

-- Composite index: most list queries filter published=1 AND dates >= today.
-- This index covers both conditions in one seek instead of two.
ALTER TABLE `#__pja_events`
    ADD KEY IF NOT EXISTS `idx_pub_dates`    (`published`, `dates`),
    ADD KEY IF NOT EXISTS `idx_pub_featured` (`published`, `featured`),
    ADD KEY IF NOT EXISTS `idx_pub_catid`    (`published`, `catid`);
