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

/**
 * Implements hook_civicrm_searchColumns().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_searchColumns/
 */
function relatedcontactsearch_civicrm_searchColumns( $objectName, &$headers, &$rows, &$selector ) {
  if (empty($rows)) {
    return;
  }

global $user;
if ($user->uid != 1) return;

return;
//print_r($headers); die();

  // add one column that allows to sort by organization
  // it helps grouping all related contacts
  if ( $objectName == 'contact' ) {
    $new_headers = array(
      'org_with_relations' => array('name' => 'Relation (tri)'),
    );

    // remove the column if it's already there
    foreach ($headers as $header) {
      if (isset($header['sort'])) {
        $key = $header['sort'];
        if (array_key_exists($key, $new_headers)) {
          unset($new_headers[$key]);
        }
      }
    }

    // only if the is something to add
    if (count($new_headers) > 0) {
      // insert new headers before organization
      array_splice($headers, 2, 0, $new_headers);

      $ids = array();
      foreach ( $rows as $id => $row ) {
        $ids[] = $row['contact_id'];
      }
      $ids = implode(',', $ids);

      $sql = "
SELECT c.id as contact_id, CONCAT(
  c.organization_name, ' (', IF(c.contact_type = 'Individual', c.employer_id, c.id), ') - ', 
  GROUP_CONCAT(REPLACE(rt.label_a_b, ' de', '') ORDER BY rt.id)) AS org_with_relations
FROM civicrm_contact c 
  LEFT JOIN civicrm_relationship r ON r.contact_id_a = c.id AND contact_id_b = c.employer_id 
  LEFT JOIN civicrm_relationship_type rt ON rt.id = r.relationship_type_id 
WHERE c.id IN (" . $ids . ") AND rt.is_active = 1 
GROUP BY c.id";
      $dao = CRM_Core_DAO::executeQuery($sql);

      // sometimes ids are not contact_id (custom searches) so we need to have an equivalence
      $contactRow = array();
      foreach ($rows as $id => $row) {
        $contactRow[$row['contact_id']] = $id;
      }

      // add results to rows
      while ($dao->fetch()) {
        foreach ($new_headers as $key => $header) {
          $id = $contactRow[$dao->contact_id];
          $name = $header['name'];

          if ($rows[$id]['contact_id'] == $dao->contact_id) {

            $rows[$id][$name] = '';

            if (isset($dao->$key)) {
              $rows[$id][$name] = $dao->$key;
            }
          }
        }
      }


    }

  }

}

