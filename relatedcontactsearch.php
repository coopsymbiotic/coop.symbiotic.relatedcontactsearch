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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function relatedcontactsearch_civicrm_xmlMenu(&$files) {
  _relatedcontactsearch_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function relatedcontactsearch_civicrm_postInstall() {
  _relatedcontactsearch_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function relatedcontactsearch_civicrm_uninstall() {
  _relatedcontactsearch_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function relatedcontactsearch_civicrm_enable() {
  _relatedcontactsearch_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function relatedcontactsearch_civicrm_disable() {
  _relatedcontactsearch_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function relatedcontactsearch_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _relatedcontactsearch_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function relatedcontactsearch_civicrm_managed(&$entities) {
  _relatedcontactsearch_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function relatedcontactsearch_civicrm_caseTypes(&$caseTypes) {
  _relatedcontactsearch_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function relatedcontactsearch_civicrm_angularModules(&$angularModules) {
  _relatedcontactsearch_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function relatedcontactsearch_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _relatedcontactsearch_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_searchTasks().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_searchTasks
 */
function relatedcontactsearch_civicrm_searchTasks( $objectType, &$tasks ) {
  // could be nice to have a shortcut to get all related contact from a search results
  // but maybe better to have a powerful custom search first
  /*$tasks[] = array(
    'title' => E::ts('Find Related Contacts'),
    'CRM_Relatedcontactsearch_Form_Task_FindRelated',
  );*/ 

}

