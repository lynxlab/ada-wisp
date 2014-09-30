<?php
/**
 * Standard configuration file for ADA
 *
 * DO NOT MODIFY THIS FILE
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright           Copyright (c) 2014, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

$GLOBALS['user_home_service_types'] = array(
    ADA_SERVICE_HELP,
    ADA_SERVICE_LEG,
    ADA_SERVICE_LEG_NO_TIMELINE,
    ADA_SERVICE_GIUR,
    ADA_SERVICE_ORG,
    ADA_SERVICE_TEMI_RISOLTI,
    ADA_SERVICE_ELEARNING
);
/**
 * HOLIS user sub types
 * 
 */

define('AMA_TYPE_USER_MAGISTRATE','31');
define('AMA_TYPE_USER_LAWYER','32');
define('AMA_TYPE_USER_AUX','33');
define('AMA_TYPE_USER_GENERIC','34');


$GLOBALS['user_service_access']= array(
    ADA_SERVICE_HELP => array(AMA_TYPE_USER_MAGISTRATE,AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX),
    ADA_SERVICE_LEG => array(AMA_TYPE_USER_MAGISTRATE),
    ADA_SERVICE_LEG_NO_TIMELINE => array(AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX),
    ADA_SERVICE_GIUR => array(AMA_TYPE_USER_MAGISTRATE,AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX),
    ADA_SERVICE_ORG => array(AMA_TYPE_USER_MAGISTRATE),
    ADA_SERVICE_TEMI_RISOLTI => array(AMA_TYPE_USER_MAGISTRATE,AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX),
    ADA_SERVICE_ELEARNING => array(AMA_TYPE_USER_MAGISTRATE,AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX, AMA_TYPE_USER_GENERIC),
    ADA_SERVICE_MANUALE => array(AMA_TYPE_USER_MAGISTRATE,AMA_TYPE_USER_LAWYER,AMA_TYPE_USER_AUX, AMA_TYPE_USER_GENERIC),
);    