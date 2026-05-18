-- Register com_planjeagenda.event as a Joomla UCM content type
-- This enables the built-in Joomla tag system for events

INSERT IGNORE INTO `#__content_types` (
    `type_title`, `type_alias`, `table`, `rules`,
    `field_mappings`, `router`, `content_history_options`
) VALUES (
    'Planjeagenda Event',
    'com_planjeagenda.event',
    '{"special":{"dbtable":"#__pja_events","key":"id","type":"EventTable","prefix":"\\\\KoelmanLabs\\\\Component\\\\Planjeagenda\\\\Administrator\\\\Table\\\\","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"\\\\Joomla\\\\CMS\\\\Table\\\\","config":"array()"}}',
    '{}',
    '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext","core_language":"language","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access","core_params":"attribs","core_featured":"featured","core_metadata":"metadata","core_created_user_id":"created_by","core_checked_out_user_id":"checked_out","core_checked_out_time":"checked_out_time","core_version":"version","core_catid":"catid"},"special":{"custom1":"custom1"}}',
    '',
    ''
);
