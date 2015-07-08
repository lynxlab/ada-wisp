-- --------------------------------------------------------
-- fields for table `studente` to be used in WISP/UNIMC:
-- --------------------------------------------------------

ALTER TABLE `studente`
  ADD `privateEmail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TEL_DOM` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TEL_RES` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `FACOLTA_COD` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TIPO_CORSO_DES` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `CDS_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `CDSORD_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PDSORD_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `DATA_ISCR` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `ANNO_CORSO` smallint(3) unsigned DEFAULT NULL,
  ADD `AA_ISCR_DESC` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TASSE_IN_REGOLA_OGGI` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TIPO_ISCR_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TIPO_DID_DECODE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PT_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `STA_OCCUP_DECODE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TIPO_HAND_DES` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PERC_HAND` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `TIPO_TITOLO_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `VOTO` smallint(3) unsigned DEFAULT NULL,
  ADD `VOTO_MAX` smallint(3) unsigned DEFAULT NULL,
  ADD `ANNO_MATURITA` int(4) unsigned DEFAULT NULL,
  ADD `SCUOLA_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PROVINCIA_SCUOLA_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `REGIONE_SCUOLA_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
  