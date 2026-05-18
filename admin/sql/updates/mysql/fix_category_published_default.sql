-- Fix categories that were saved with published=0 due to wrong DB default
ALTER TABLE `#__pja_categories` 
    MODIFY COLUMN `published` tinyint(1) NOT NULL DEFAULT 1;

-- Publish any unpublished non-root categories (likely saved incorrectly)
UPDATE `#__pja_categories` 
SET published = 1 
WHERE published = 0 AND alias != 'root';
