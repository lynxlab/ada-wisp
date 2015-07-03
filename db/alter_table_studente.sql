-- --------------------------------------------------------
-- fields for table `studente` to be used in WISP/UNIMC:
-- --------------------------------------------------------

ALTER TABLE `studente`
  ADD `privateEmail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Tipo_Corso_Des` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `CDS_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PDSORD_DESC` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Tipo_Did_Desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `PT_FLG` int(2) DEFAULT NULL,
  ADD `Sta_Occup_Decode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Tipo_Hand_Des` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Perc_Hand` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Tipo_Titolo_Sup_Desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Voto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `Voto_Max` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
  