CREATE TABLE IF NOT EXISTS `kwc_alternatehreflang` (
`id` int(11) NOT NULL,
  `component_id` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `kwc_alternatehreflang`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `kwc_alternatehreflang`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;