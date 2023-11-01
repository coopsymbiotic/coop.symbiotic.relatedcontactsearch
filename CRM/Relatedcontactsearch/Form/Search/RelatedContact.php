<?php
use CRM_Relatedcontactsearch_ExtensionUtil as E;

DEFINE("RELATEDCONTACTSEARCH_MAX_RELATIONSHIP", 3);

/**
 * A custom contact search
 */
class CRM_Relatedcontactsearch_Form_Search_RelatedContact extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {

    $this->_formValues = $formValues;

    // backward compatibility
    if (isset($formValues['relationship_type'])) {
      $formValues['relationship_type_1'] = $formValues['relationship_type'];
    }

    $this->_includeGroups = CRM_Utils_Array::value('includeGroups', $this->_formValues, array());
    $this->_excludeGroups = CRM_Utils_Array::value('excludeGroups', $this->_formValues, array());
    //$this->_includeTags = CRM_Utils_Array::value('includeTags', $this->_formValues, array());
    //$this->_excludeTags = CRM_Utils_Array::value('excludeTags', $this->_formValues, array());

    //define variables
    $this->_allSearch = FALSE;
    $this->_groups = FALSE;
    //$this->_tags = FALSE;
    //$this->_andOr = CRM_Utils_Array::value('andOr', $this->_formValues, 1);

    //make easy to check conditions for groups and tags are
    //selected or it is empty search
    if (empty($this->_includeGroups) && empty($this->_excludeGroups) &&
      empty($this->_includeTags) && empty($this->_excludeTags)
    ) {
      //empty search
      $this->_allSearch = TRUE;
    }

    $this->_groups = (!empty($this->_includeGroups) || !empty($this->_excludeGroups));

    //$this->_tags = (!empty($this->_includeTags) || !empty($this->_excludeTags));

    //$this->_debug = 1;

    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(E::ts('Search Related Contacts'));

    // origin contact name
    $form->add('text', 'contactname',
      "Nom du contact d'origine"//E::ts('Origin Contact Name')
    );

    // include / exclude groups
    $groups = CRM_Core_PseudoConstant::nestedGroup();

    $select2style = array(
      'multiple' => TRUE,
      'style' => 'width: 100%; max-width: 60em;',
      'class' => 'crm-select2',
      'placeholder' => ts('- select -'),
    );

    $form->add('select', 'includeGroups',
      'Limiter aux contacts des groupes',  //'ts('Include Group(s)'),
      $groups,
      FALSE,
      $select2style
    );

    $form->add('select', 'excludeGroups',
      'Exclure les contacts des groupes',  // ts('Exclude Group(s)'),
      $groups,
      FALSE,
      $select2style
    );

    // add the option to display relationships
    $rTypes = CRM_Core_PseudoConstant::relationshipType();
    $rSelect = array('' => ts('- Select Relationship Type-'));
    foreach ($rTypes as $rid => $rValue) {
      if ($rValue['label_a_b'] == $rValue['label_b_a']) {
        $rSelect[$rid] = $rValue['label_a_b'];
      }
      else {
        $rSelect["{$rid}_ab"] = $rValue['label_a_b'];
        $rSelect["{$rid}_ba"] = $rValue['label_b_a'];
      }
    }

    $elements = [];
    for ($idx = 1; $idx <= RELATEDCONTACTSEARCH_MAX_RELATIONSHIP; $idx++) {
      $form->add('select',
        'relationship_type_' . $idx,
        E::ts('Relationship %1', array(1 => '#' . $idx)),
        $rSelect,
        ($idx == 1) ? TRUE : FALSE,
        array('class' => 'crm-select2')
      );
      $elements[] = 'relationship_type_' . $idx;
    }
    $elements[] = 'contactname';
    $elements[] = 'includeGroups';
    $elements[] = 'excludeGroups';

    $form->assign('elements', $elements);

  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      E::ts('Contact Id') => 'contact_id',
      E::ts('Contact Type') => 'contact_type',
      E::ts('Contact Name') => 'sort_name',
      E::ts('Relationship Type') => 'relationship_type',
      E::ts('Contact Related To') => 'origin_contact',
      E::ts('Email') => 'email',
      E::ts('Source') => 'source',
    );
    return $columns;
  }


  /**
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   *
   * @return string
   */
  public function all(
    $offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {

    $this->_includeGroups = CRM_Utils_Array::value('includeGroups', $this->_formValues, array());

    $this->_excludeGroups = CRM_Utils_Array::value('excludeGroups', $this->_formValues, array());

    $this->_allSearch = FALSE;
    $this->_groups = FALSE;

    if (empty($this->_includeGroups) && empty($this->_excludeGroups)) {
      //empty search
      $this->_allSearch = TRUE;
    }

    if (!empty($this->_includeGroups) || !empty($this->_excludeGroups)) {
      //group(s) selected
      $this->_groups = TRUE;
    }

    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
      $sort = "contact_a.id";
    }
    else {
      $selectClause = $this->select();
    }
    $groupBy = " GROUP BY contact_a.id";

    $sql = $this->sql($selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, $groupBy
    );

    if ($this->_debug > 0) {
      print "-- Final query: <pre>";
      print "$sql;";
      print "</pre>";
      die();
    }
    return $sql;
  }


  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id           as contact_id  ,
      contact_a.contact_type as contact_type,
      contact_a.sort_name    as sort_name,
      contact_origin.display_name as origin_contact,
      IF(r.contact_id_a = contact_a.id, rt.label_a_b, rt.label_b_a) as relationship_type
      ,contact_a.source as source,email.email as email
      ";
      //CONCAT(rt.label_a_b, ' / ', rt.label_b_a) as relationship_type
  }


  public function from() {
    //define table name
    $randomNum = md5(uniqid());
    $this->_tableName = "civicrm_temp_custom_{$randomNum}";


    //grab all the contacts that corresponds to the query ignoring the groups filters
    $sql = "CREATE TEMPORARY TABLE rel_{$this->_tableName} ( contact_id int primary key ) ENGINE=HEAP";
    if ($this->_debug > 0) {
      print "-- Temp table query: <pre>";
      print "$sql;";
      print "</pre>";
    }
    CRM_Core_DAO::executeQuery($sql);

    $sql  = "SELECT contact_origin.id ";
    $sql .= "FROM " . $this->_from();
    $sql .= "WHERE " . $this->where();
    $sql .= "GROUP BY contact_origin.id";
    $sql = "INSERT INTO rel_{$this->_tableName} (" . $sql . ")";
    if ($this->_debug > 0) {
      print "-- Insert in temp table query: <pre>";
      print "$sql;";
      print "</pre>";
    }
    CRM_Core_DAO::executeQuery($sql);


    // Only include groups in the search query of one or more Include OR Exclude groups has been selected.
    // CRM-6356
    if ($this->_groups) {
      //block for Group search
      $smartGroup = array();
      $group = new CRM_Contact_DAO_Group();
      $group->is_active = 1;
      $group->find();
      while ($group->fetch()) {
        $allGroups[] = $group->id;
        if ($group->saved_search_id) {
          $smartGroup[$group->saved_search_id] = $group->id;
        }
      }
      $includedGroups = implode(',', $allGroups);

      //CRM-15049 - Include child group ids.
      $childGroupIds = CRM_Contact_BAO_Group::getChildGroupIds($this->_includeGroups);
      if (count($childGroupIds) > 0) {
        $this->_includeGroups = array_merge($this->_includeGroups, $childGroupIds);
      }

      if (!empty($this->_includeGroups)) {
        $iGroups = implode(',', $this->_includeGroups);
      }
      else {
        //if no group selected search for all groups
        $iGroups = $includedGroups;
      }

      //CRM-15049 - Exclude child group ids.
      $childGroupIds = CRM_Contact_BAO_Group::getChildGroupIds($this->_excludeGroups);
      if (count($childGroupIds) > 0) {
        $this->_excludeGroups = array_merge($this->_excludeGroups, $childGroupIds);
      }

      if (is_array($this->_excludeGroups)) {
        $xGroups = implode(',', $this->_excludeGroups);
      }
      else {
        $xGroups = 0;
      }

      $sql = "DROP TEMPORARY TABLE IF EXISTS Xg_{$this->_tableName}";
      CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
      $sql = "CREATE TEMPORARY TABLE Xg_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";
      CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

      //used only when exclude group is selected
      if ($xGroups != 0) {
        $excludeGroup = "INSERT INTO  Xg_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_group_contact.contact_id
                  FROM civicrm_group_contact, rel_{$this->_tableName} AS rel
                  WHERE
                     rel.contact_id = civicrm_group_contact.contact_id AND
                     civicrm_group_contact.status = 'Added' AND
                     civicrm_group_contact.group_id IN ({$xGroups})";

        CRM_Core_DAO::executeQuery($excludeGroup, CRM_Core_DAO::$_nullArray);

        //search for smart group contacts
        foreach ($this->_excludeGroups as $keys => $values) {
          if (in_array($values, $smartGroup)) {
            $ssId = CRM_Utils_Array::key($values, $smartGroup);

            $smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL($ssId);

            $smartSql = $smartSql . " AND contact_a.id NOT IN (
                              SELECT contact_id FROM civicrm_group_contact
                              WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";

            $smartGroupQuery = " INSERT IGNORE INTO Xg_{$this->_tableName}(contact_id) $smartSql";

            CRM_Core_DAO::executeQuery($smartGroupQuery, CRM_Core_DAO::$_nullArray);
          }
        }
      }

      $sql = "DROP TEMPORARY TABLE IF EXISTS Ig_{$this->_tableName}";
      CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
      $sql = "CREATE TEMPORARY TABLE Ig_{$this->_tableName}
                ( id int PRIMARY KEY AUTO_INCREMENT,
                  contact_id int,
                  group_names varchar(64)) ENGINE=HEAP";

      if ($this->_debug > 0) {
        print "-- Include groups query: <pre>";
        print "$sql;";
        print "</pre>";
      }

      CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

      $includeGroup = "INSERT INTO Ig_{$this->_tableName} (contact_id, group_names)
                 SELECT      rel.contact_id as contact_id, civicrm_group.name as group_name
                 FROM        rel_{$this->_tableName} AS rel
                 INNER JOIN  civicrm_group_contact
                 ON          civicrm_group_contact.contact_id = rel.contact_id
                 LEFT JOIN   civicrm_group
                 ON          civicrm_group_contact.group_id = civicrm_group.id";

      //used only when exclude group is selected
      if ($xGroups != 0) {
        $includeGroup .= " LEFT JOIN        Xg_{$this->_tableName}
                                          ON        rel.contact_id = Xg_{$this->_tableName}.contact_id";
      }
      $includeGroup .= " WHERE
                                     civicrm_group_contact.status = 'Added'  AND
                                     civicrm_group_contact.group_id IN ($iGroups)";

      //used only when exclude group is selected
      if ($xGroups != 0) {
        $includeGroup .= " AND  Xg_{$this->_tableName}.contact_id IS null";
      }

      if ($this->_debug > 0) {
        print "-- Include groups query: <pre>";
        print "$includeGroup;";
        print "</pre>";
      }

      CRM_Core_DAO::executeQuery($includeGroup, CRM_Core_DAO::$_nullArray);

      //search for smart group contacts
      foreach ($this->_includeGroups as $keys => $values) {
        if (in_array($values, $smartGroup)) {

          $ssId = CRM_Utils_Array::key($values, $smartGroup);

          //$smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL($ssId);
          $smartSql = self::contactIDsSQL($ssId);

          $smartSql .= " AND contact_a.id IN (
                                   SELECT contact_id AS contact_id
                                   FROM rel_{$this->_tableName} )";

          $smartSql .= " AND contact_a.id NOT IN (
                                   SELECT contact_id FROM civicrm_group_contact
                                   WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";

          //used only when exclude group is selected
          if ($xGroups != 0) {
            $smartSql .= " AND contact_a.id NOT IN (SELECT contact_id FROM  Xg_{$this->_tableName})";
          }

          $smartGroupQuery = " INSERT IGNORE INTO
                        Ig_{$this->_tableName}(contact_id)
                        $smartSql";

          CRM_Core_DAO::executeQuery($smartGroupQuery, CRM_Core_DAO::$_nullArray);
          if ($this->_debug > 0) {
            print "-- Smart group query: <pre>";
            print "$smartGroupQuery;";
            print "</pre>";
          }
          $insertGroupNameQuery = "UPDATE IGNORE Ig_{$this->_tableName}
                        SET group_names = (SELECT title FROM civicrm_group
                            WHERE civicrm_group.id = $values)
                        WHERE Ig_{$this->_tableName}.contact_id IS NOT NULL
                            AND Ig_{$this->_tableName}.group_names IS NULL";
          CRM_Core_DAO::executeQuery($insertGroupNameQuery, CRM_Core_DAO::$_nullArray);
          if ($this->_debug > 0) {
            print "-- Smart group query: <pre>";
            print "$insertGroupNameQuery;";
            print "</pre>";
          }
        }
      }
    }
    // end if( $this->_groups ) condition

    //$this->buildACLClause('contact_a');

    // standard from and a few more join for columns display
    $from  = "FROM " . $this->_from();
    $from .= "  INNER JOIN civicrm_relationship_type rt ON (r.relationship_type_id = rt.id)";

    // Only include groups in the search query of one or more Include OR Exclude groups has been selected.
    // CRM-6356
    if ($this->_groups) {
      $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_origin.id = temptable1.contact_id)";
    }

//print $from; die();
    return $from;
  }

  function _from() {
    $sql = "civicrm_contact contact_a ";
    $sql .= "  INNER JOIN civicrm_relationship r ON (r.contact_id_a = contact_a.id OR r.contact_id_b = contact_a.id) ";
    $sql .= "  INNER JOIN civicrm_contact contact_origin ON (r.contact_id_a = contact_origin.id OR r.contact_id_b = contact_origin.id) ";
    $sql .= "  LEFT JOIN civicrm_email email ON (email.contact_id = contact_a.id AND email.is_primary = 1) ";
//    $sql .= "  INNER JOIN civicrm_group_contact group_contact ON group_contact.contact_id = contact_origin.id ";
    return $sql;
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();
    $where = "";
    $count = 1;

    $clause = array();
    $clauserel = array();
    $rtids = array();

    // contact name
    $contactname = CRM_Utils_Array::value('contactname', $this->_formValues);
    if (!empty($contactname)) {
      $params[$count] = ["%".$contactname."%", 'String'];
      $clause[] = "contact_origin.sort_name LIKE %{$count}";
      $count +=1;
    }

    // loop on any relationship criteria
    for ($idx = 1; $idx <= RELATEDCONTACTSEARCH_MAX_RELATIONSHIP; $idx++) {
      $rtype = CRM_Utils_Array::value('relationship_type_' . $idx, $this->_formValues);
 
      // relationship fast filter
      $l = explode('_', $rtype);
      if (!empty($l[0])) {
        $rtids[] = $l[0];
      }

      // relationship joins
      if ($l[1] == 'ab') {
        $clauserel[] = "(r.contact_id_a = contact_a.id AND r.contact_id_b = contact_origin.id AND r.relationship_type_id = %{$count})";
      }
      else {
        $clauserel[] = "(r.contact_id_a = contact_a.id AND r.contact_id_b = contact_origin.id AND r.relationship_type_id = %{$count})";
      }
      $params[$count] = array($l[0], 'String');
      $count += 1;
    }
    $clause[] = "r.relationship_type_id IN (" . implode(',', $rtids) . ')';
    $clause[] = '(' . implode(' OR ', $clauserel) . ')';


    // group id
    /*$groups = CRM_Utils_Array::value('includeGroups', $this->_formValues);
    if ($groups != NULL) {
      if (!is_array($groups)) $groups = array($groups);
      $clause[] = "group_contact.group_id IN (" . implode(',', $groups) . ")";
    }*/
    
    if (!empty($clause)) {
      $where = implode(' AND ', $clause);
    }
    else {
      $where = ' (1) ';
    }

    $whereClause = $this->whereClause($where, $params);

//    print $whereClause; die();
    return $whereClause;
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    //$row['origin_contact'] .= ' (' . $row['rt_a_b'] . ')';
  }

  public static function contactIDsSQL($id) {

    $params = self::getSearchParams($id);
    if ($params && !empty($params['customSearchID'])) {
      return CRM_Contact_BAO_SearchCustom::contactIDSQL(NULL, $id);
    }
    else {
      $tables = $whereTables = ['civicrm_contact' => 1];
      $where = CRM_Contact_BAO_SavedSearch::whereClause($id, $tables, $whereTables);
      if (!$where) {
        $where = '( 1 )';
      }
      $from = CRM_Contact_BAO_Query::fromClause($whereTables);
      return "
SELECT contact_a.id
$from
WHERE  $where";
    }

  }

  public static function getSearchParams($id) {
    $savedSearch = \Civi\Api4\SavedSearch::get(FALSE)
      ->addWhere('id', '=', $id)
      ->execute()
      ->first();
    if ($savedSearch['api_entity']) {
      return $savedSearch;
    }
    $fv = CRM_Contact_BAO_SavedSearch::getFormValues($id);
    //check if the saved search has mapping id
    if ($savedSearch['mapping_id']) {
      return CRM_Core_BAO_Mapping::formattedFields($fv);
    }
    elseif (!empty($fv['customSearchID'])) {
      return $fv;
    }
    else {
      return CRM_Contact_BAO_Query::convertFormValues($fv);
    }
  }

}
