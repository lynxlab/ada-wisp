<?php
/**
 * CREDITS.
 *
 * @package		main
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.1
 */


/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_ADMIN,AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_VISITOR      => array('layout'),
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_AUTHOR       => array('layout'),
  AMA_TYPE_ADMIN        => array('layout')
);
require_once ROOT_DIR.'/include/module_init.inc.php';


/**
 * Get needed objects
 */
include_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';

$self = 'default';

$credits_data = "<p>"
              . translateFN("ADA-WISP &egrave; un software libero sviluppato da")
              . ' ' ."<a href='http://www.lynxlab.com'; target='_blank'>Lynx s.r.l.</a>"
              .  "<p>".translateFN("E' rilasciato con licenza ")." <a href='".HTTP_ROOT_DIR . "/browsing/external_link.php?file=gpl.txt'; target='_blank'>GNU GPL.</a></p>".
              "Hanno contribuito allo sviluppo:".
              "<ul>
              <li>Maurizio Mazzoneschi</li>
              <li>Stefano Penge</li>
              <li>Vito Modena</li>
              <li>Giorgio Consorti</li>
              <li>Sara Capotosti</li>		
              <li>Valerio Riva</li>
              <li>Guglielmo Celata</li>
              <li>Stamatis Filippis</li>
              <li>Sara Capotosti</li>
              </ul>".
              "Hanno contribuito al disegno dell'interfaccia:".
              "<ul>
              <li>Gianluca Toni</li>
              <li>Francesco Fagnini</li>
              <li>Chiara Codino</li>
              </ul>".
              "</p>";

$credits_data = '<div id="tab-contenuto">
													<div class="view view-primopiano-hp view-id-primopiano_hp view-display-id-default view-dom-id-2">
														<div class="view-content">
														<table class="views-view-grid" summary="">
															<tbody>
										                		<tr class="row-1 row-first row-last">
																
																	<!-- **** PRIMA COLONNA 1st tab ***** -->
																	<td class="col-1">
																	<div class="views-field-field-data-value">
																		<div class="field-content">
																		<!-- **** spazio data ***** -->
																		<span class="date-display-single"></span>
																		</div>
																	</div>
																	<!-- **** Titolo prima colonna 1st tab ***** -->
																	<h3 class="views-field-title">
																	<span class="field-content">Partenariato</span></h3>
																	<!-- **** /Titolo prima colonna 1st tab ***** -->
																	<div class="views-field-image-attach-images">
              															<div class="field-content">
																			<!--<div class="image-attach-node-12930" style="width: 100px;">
																			<img src="sespius_files/icon_publish.png" alt="immagine simbolica" title="immagine simbolica" class="image image-thumbnail " height="83" width="100">
																			</div>-->
																		</div>
																	</div>
																	<div class="views-field-teaser">
																		<div class="field-content">
																		<!-- **** Teaser prima colonna 1st tab ***** -->
																		<p>I partner del progetto SESPIUS sono:
																		</p><table>
																		<thead></thead>
																		<tbody>
																		<tr><td><img src="hp/sespius_files/unime.png" alt="logo Unime" title="logo Unime"></td><td><a href="http://www.unime.it/" target="_blank">Università degli Studi di Messina</a></td></tr>
																		<tr><td><img src="hp/sespius_files/cubecurve.png" alt="logo CubeCurve" title="logo CubeCurve"></td><td><a href="http://www.cubecurve.it/" target="_blank">CubeCurve</a></td></tr>
																		<tr><td><img src="hp/sespius_files/colosi.png" alt="logo Libreria Colosi" title="logo Colosi"></td><td><a href="http://www.colosi.it/" target="_blank">Libreria Colosi</a></td></tr>
																		<tr><td><img src="hp/sespius_files/s2italia.png" alt="logo S2i" title="logo S2i"></td><td><a href="http://www.s2i-italia.com/" target="_blank">S2i Italia</a></td></tr>
																		</tbody>
																		</table>
																		<p></p>
																		<!-- **** /Teaser prima colonna 1st tab ***** -->
																		</div>
																	</div>
																	</td>
																	<!-- **** /PRIMA COLONNA 1st tab ***** -->
																	
																	<!-- **** SECONDA COLONNA 2nd tab ***** -->
																	<td class="col-2">
																	<div class="views-field-field-data-value">
																		<div class="field-content">
																		<!-- **** spazio data ***** -->
																		<span class="date-display-single"></span>																	
																		</div>
																	</div>
																	<!-- **** Titolo seconda colonna prima tab ***** -->
																	<h3 class="views-field-title">
																	<span class="field-content"><a href="#"></a></span></h3>
																	<!-- **** /Titolo seconda colonna prima tab ***** -->
																	<div class="views-field-image-attach-images">
																	  <div class="field-content">
																		  <!--<div class="image-attach-node-12935" >
																			
																			<img src="sespius_files/img0.jpg" alt="icona" title="icona" class="image image-thumbnail "  width="450">
																			</div>-->
																		</div>
																	</div>
																<div class="views-field-teaser">
																	<div class="field-content">
																	<!-- **** Teaser seconda colonna prima tab ***** -->
																	
																	</div>
																</div>
																</td>
															</tr>
														</tbody>
													</table>
													</div>
													
													<!-- Link a fine blocco prima tab -->
													<div class="view-footer">
      													<!-- <div class="link_elenco">
														<a href="#">Elenco completo di tutte le notizie</a>
														</div> -->
														<!--<div class="linkrss_elenco">
														<a href="#" title="Feed RSS di tutte le notizie">Feed RSS</a>
														<a href="#" title="Feed RSS delle notizie"><img src="sespius_files/icona_rss.jpg" alt="Feed RSS di tutte le notizie"></a>
														</div>-->
													</div>
													<!-- /Link a fine blocco prima tab -->
												</div>
											</div><hr/><div class="view view-news-hp view-id-news_hp view-display-id-default view-dom-id-3">
									<div class="view-content">
									<table class="views-view-grid" summary="">
									<tbody>
									<tr class="row-1 row-first row-last">
										<!-- seconda tab prima colonna -->
										<td class="col-1">
										<div class="views-field-field-data-value">
											<div class="field-content">
											<!-- data -->
											<span class="date-display-single"></span>
											</div>
										</div>
										<h3 class="views-field-title">
										<!-- titolo -->
										<span class="field-content">Software</span>
										</h3>
										<div class="views-field-image-attach-images">
											<div class="field-content">
												<!--<div class="image-attach-node-11931" style="width: 100px;">
												<img src="sespius_files/icon_full-shopping-cart.png" alt="..." title="..." class="image image-thumbnail " height="83" width="100">
												</div>-->
											</div>
										</div>
										<!-- teaser seconda tab-->
										<div class="views-field-teaser">
											<div class="field-content">
											<p>Il software su cui è basato Sespius è stato sviluppato da <a href="http://www.lynxlab.com" target="_blank">Lynx s.r.l.</a></p>
											<p>Altri software utilizzati nel progetto:</p>
											<ul>
											<li>JEX (JRC Eurovoc Indexer)</li>
											<li>Docebo</li>
											<li>...</li>
											</ul>
											</div>
										</div>
										</td>
										<!-- seconda tab seconda colonna -->
										<td class="col-2">
										<div class="views-field-field-data-value">
											<div class="field-content">
											<!-- data -->
											<span class="date-display-single"></span>
											</div>
										</div>
										<h3 class="views-field-title">
										<!-- Titolo seconda tab seconda colonna -->
										<span class="field-content"><a href="#"></a></span>
										</h3>
										<div class="views-field-image-attach-images">
											<div class="field-content">
												<!--<div class="image-attach-node-12920" >
												
												<img src="sespius_files/pct.png" alt="pct" title="pct" class="image image-thumbnail " width="450">
												</div>-->
											</div>
										</div>
										<div class="views-field-teaser">
											<div class="field-content">
											<!-- teaser seconda tab seconda colonna -->
											
											</div>
										</div>
										</td>
									</tr>
									</tbody>
									</table>
									</div>
									<!-- Link a fine blocco seconda tab -->
									<div class="view-footer">
										<!--<div class="link_elenco">
										<a href="#">Elenco completo delle notizie</a>
										</div>-->
										<!--<div class="linkrss_elenco">
										<a href="#" title="Feed RSS delle notizie">Feed RSS</a>
										<a href="#" title="Feed RSS delle notizie"><img src="sespius_files/icona_rss.jpg" alt="Feed RSS delle notizie"></a>
										</div>-->
									</div>
								</div>';

//$banner = include ROOT_DIR.'/include/banner.inc.php';

$title=translateFN('Credits');

$content_dataAr = array(
  'home'=>$home,
  'user_name' => $user_name,
  'user_type' => $user_type,
  'user_level' => $user_level,
  'status' => $status,
  'help'=>$credits_data,
  'menu'=>$menu,
  'course_title' => translateFN("Credits"),
  //'banner'=>$banner,
  'message'=>$message,
  'agenda_link'=>$agenda_link,
  'msg_link'=>$msg_link
);

if (isset($userObj) && !is_null($userObj)) {
	$content_dataAr['user_avatar'] = CDOMElement::create('img','src:'.$userObj->getAvatar().',class:img_user_avatar')->getHtml();
	$content_dataAr['user_modprofilelink'] = $userObj->getEditProfilePage();
}

ARE::render($layout_dataAr, $content_dataAr);
?>
