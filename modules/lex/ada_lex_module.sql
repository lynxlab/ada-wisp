-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_COMPOUND_NON_PT`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_COMPOUND_NON_PT` (
  `use_descripteur_id_1` int(11) NOT NULL,
  `use_descripteur_id_2` int(11) NOT NULL,
  `uf_el` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_DESCRIPTEUR`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_DESCRIPTEUR` (
  `descripteur_id` int(11) NOT NULL,
  `libelle` text COLLATE utf8_unicode_ci,
  `libelle_form` enum('fullname','shortname','acronym') COLLATE utf8_unicode_ci DEFAULT NULL,
  `def` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`descripteur_id`,`lng`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_DESCRIPTEUR_THESAURUS`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_DESCRIPTEUR_THESAURUS` (
  `thesaurus_id` int(11) NOT NULL,
  `descripteur_id` int(11) NOT NULL,
  `country` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `iso_country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `topterm` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`thesaurus_id`,`descripteur_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_DOMAINES`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_DOMAINES` (
  `domaine_id` int(11) NOT NULL,
  `libelle` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`domaine_id`,`lng`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_LANGUES`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_LANGUES` (
  `libelle` text COLLATE utf8_unicode_ci,
  `courte` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tri` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_RELATIONS_BT`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_RELATIONS_BT` (
  `source_id` int(11) NOT NULL,
  `cible_id` int(11) NOT NULL,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`source_id`,`cible_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='relation bt: broader term';

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_RELATIONS_RT`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_RELATIONS_RT` (
  `descripteur1_id` int(11) NOT NULL,
  `descripteur2_id` int(11) NOT NULL,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`descripteur1_id`,`descripteur2_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='relation rt: related to';

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_RELATIONS_UI`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_RELATIONS_UI` (
  `source_id` int(11) NOT NULL,
  `cible_id` int(11) NOT NULL,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`source_id`,`cible_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='relation ui: use instead';

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_SCOPE_NOTE`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_SCOPE_NOTE` (
  `descripteur_id` int(11) NOT NULL,
  `scope_note` text COLLATE utf8_unicode_ci,
  `history_note` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`descripteur_id`,`lng`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_THESAURUS`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_THESAURUS` (
  `thesaurus_id` int(11) NOT NULL,
  `libelle` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`thesaurus_id`,`lng`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_lex_EUROVOC_USED_FOR`
--

CREATE TABLE IF NOT EXISTS `module_lex_EUROVOC_USED_FOR` (
  `descripteur_id` int(11) NOT NULL,
  `uf_el` text COLLATE utf8_unicode_ci,
  `uf_el_form` enum('fullname','shortname','acronym') COLLATE utf8_unicode_ci DEFAULT NULL,
  `def` text COLLATE utf8_unicode_ci,
  `version` decimal(4,2) NOT NULL DEFAULT '0.00',
  `lng` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`descripteur_id`,`lng`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
