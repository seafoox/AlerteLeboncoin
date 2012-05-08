
--
-- Structure de la table `AlertMail`
--

CREATE TABLE IF NOT EXISTS `AlertMail` (
  `process` tinyint(4) NOT NULL DEFAULT '1',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `link` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_created` datetime NOT NULL,
  `date_revalidated` date NOT NULL,
  `date_updated` datetime DEFAULT NULL,
  `validated` tinyint(1) DEFAULT '0',
  `control_key` char(40) COLLATE utf8_bin NOT NULL,
  `date_updated_check` datetime NOT NULL,
  `counter_alerts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `counter_ads` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `stop` tinyint(1) NOT NULL DEFAULT '0',
  `check_interval` smallint(5) unsigned NOT NULL DEFAULT '30',
  `price_min` int(10) unsigned NOT NULL DEFAULT '0',
  `price_max` int(10) unsigned NOT NULL DEFAULT '1000000000',
  `cities` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `control_key` (`control_key`),
  UNIQUE KEY `email` (`email`,`link`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `Feeds`
--

CREATE TABLE IF NOT EXISTS `Feeds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link_md5` char(64) COLLATE utf8_bin NOT NULL,
  `link` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `counter` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_md5` (`link_md5`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Structure de la table `Session`
--

CREATE TABLE IF NOT EXISTS `Session` (
  `id` char(32) COLLATE utf8_bin NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `lifetime` int(11) unsigned NOT NULL,
  `data` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL,
  `date_created` datetime NOT NULL,
  `validation_key` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `default_check_interval` smallint(5) unsigned NOT NULL DEFAULT '30',
  `role` varchar(20) COLLATE utf8_bin NOT NULL DEFAULT 'member',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `validation_key` (`validation_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `AlertMail`
--
ALTER TABLE `AlertMail`
  ADD CONSTRAINT `KeyAlertMailToUser` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

