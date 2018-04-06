CREATE TABLE `languages` (
`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
`language_name` varchar(100) NOT NULL,
`language_directory` varchar(100) NOT NULL,
`slug` varchar(10) NOT NULL,
`language_code` varchar(20),
`default` tinyint(1) NOT NULL DEFAULT '0'
) COMMENT='' ENGINE=InnoDB COLLATE 'utf8_general_ci';