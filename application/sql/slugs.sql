CREATE TABLE `slugs` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`content_type` varchar(150) NOT NULL,
`content_id` int(11) NOT NULL,
`translation_id` int(11) NOT NULL,
`language_slug` varchar(5) NOT NULL,
`url` varchar(255) NOT NULL,
`redirect` int(11) NOT NULL DEFAULT '0',
`created_at` datetime DEFAULT NULL,
`updated_at` datetime DEFAULT NULL,
`deleted_at` datetime DEFAULT NULL,
`created_by` int(11) NOT NULL,
`updated_by` int(11) NOT NULL,
`deleted_by` int(11) NOT NULL,
PRIMARY KEY (`id`),
KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;