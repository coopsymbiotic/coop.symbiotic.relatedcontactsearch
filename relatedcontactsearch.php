<?php

require_once 'relatedcontactsearch.civix.php';
use CRM_Relatedcontactsearch_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function relatedcontactsearch_civicrm_config(&$config) {
  _relatedcontactsearch_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function relatedcontactsearch_civicrm_install() {
  _relatedcontactsearch_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function relatedcontactsearch_civicrm_enable() {
  _relatedcontactsearch_civix_civicrm_enable();
}
