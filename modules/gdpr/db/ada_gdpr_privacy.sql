-- phpMyAdmin SQL Dump
-- version 4.6.6deb1+deb.cihar.com~xenial.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 02, 2018 alle 10:44
-- Versione del server: 5.7.22
-- Versione PHP: 5.6.35-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ada_common`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_privacy_content`
--

CREATE TABLE `module_gdpr_privacy_content` (
  `privacy_content_id` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `content` text COLLATE utf8_unicode_ci,
  `tester_pointer` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mandatory` tinyint(3) UNSIGNED DEFAULT '0',
  `lastEditTS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_gdpr_privacy_content`
--
ALTER TABLE `module_gdpr_privacy_content`
  ADD PRIMARY KEY (`privacy_content_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_gdpr_privacy_content`
--
ALTER TABLE `module_gdpr_privacy_content`
  MODIFY `privacy_content_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;