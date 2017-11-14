<?php
use CRM_Relatedcontactsearch_ExtensionUtil as E;

/**
 * A custom contact search
 */
class CRM_Relatedcontactsearch_Form_Search_RelatedContact extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {

    $this->_formValues = $formValues;
    $this->_columns = array(
      ts('Contact ID') => 'contact_id',
      ts('Contact Type') => 'contact_type',
      ts('Name') => 'sort_name',
      ts('Group Name') => 'gname',
      ts('Tag Name') => 'tname',
    );

    $this->_includeGroups = CRM_Utils_Array::value('includeGroups', $this->_formValues, array());
    $this->_excludeGroups = CRM_Utils_Array::value('excludeGroups', $this->_formValues, array());
    $this->_includeTags = CRM_Utils_Array::value('includeTags', $this->_formValues, array());
    $this->_excludeTags = CRM_Utils_Array::value('excludeTags', $this->_formValues, array());

    //define variables
    $this->_allSearch = FALSE;
    $this->_groups = FALSE;
    $this->_tags = FALSE;
    $this->_andOr = CRM_Utils_Array::value('andOr', $this->_formValues, 1);

    //make easy to check conditions for groups and tags are
    //selected or it is empty search
    if (empty($this->_includeGroups) && empty($this->_excludeGroups) &&
      empty($this->_includeTags) && empty($this->_excludeTags)
    ) {
      //empty search
      $this->_allSearch = TRUE;
    }

    $this->_groups = (!empty($this->_includeGroups) || !empty($this->_excludeGroups));

    $this->_tags = (!empty($this->_includeTags) || !empty($this->_excludeTags));

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

    $groups = CRM_Core_PseudoConstant::nestedGroup();

    // start with a simple version (only one group and only one relation type)
    $form->add('select', 'includeGroups',
      ts('Include Group(s) -- works only with static group'),
      $groups,
      FALSE
    );

    // add the option to display relationships
    $rTypes = CRM_Core_PseudoConstant::relationshipType();
    $rSelect = array('' => ts('- Select Relationship Type-'));
    foreach ($rTypes as $rid => $rValue) {
      if ($rValue['label_a_b'] == $rValue['label_b_a']) {
        $rSelect[$rid] = $rValue['label_a_b'];
      }
      else {
        $rSelect["{$rid}_a_b"] = $rValue['label_a_b'];
        $rSelect["{$rid}_b_a"] = $rValue['label_b_a'];
      }
    }

    $form->addElement('select',
      'relationship_type',
      ts('Relationship #1'),
      $rSelect,
      array('class' => 'crm-select2')
    );

    $form->addElement('select',
      'relationship_type_2',
      ts('Relationship #2'),
      $rSelect,
      array('class' => 'crm-select2')
    );

    $form->assign('elements', array('includeGroups', 'relationship_type', 'relationship_type_2'));

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
      E::ts('Contact Related To') => 'origin_contact',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    
    // add a group by to avoid duplicates
    $groupBy = " GROUP BY contact_a.id";

    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    $sql = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, $groupBy);
    //print $sql; die();
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
      rt.label_a_b as rt_a_b,
      rt.label_b_a as rt_b_a
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {

    // base tables
    $from = "
      FROM      civicrm_contact contact_a
      LEFT JOIN civicrm_address address ON ( address.contact_id       = contact_a.id AND
                                             address.is_primary       = 1 )
      LEFT JOIN civicrm_email           ON ( civicrm_email.contact_id = contact_a.id AND
                                             civicrm_email.is_primary = 1 )
    ";

    // adding relationships tables
    $from .= "  INNER JOIN civicrm_relationship r ON (r.contact_id_a = contact_a.id OR r.contact_id_b = contact_a.id)";
    $from .= "  INNER JOIN civicrm_contact contact_origin ON (r.contact_id_a = contact_origin.id OR r.contact_id_b = contact_origin.id)";
    $from .= "  INNER JOIN civicrm_relationship_type rt ON (r.relationship_type_id = rt.id)";

    // adding group table
    $from .= "  INNER JOIN civicrm_group_contact group_contact ON group_contact.contact_id = contact_origin.id";

//print $from; die();

    return $from;
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

    // relationship
    $clauserel = array();
    $relationship_type = CRM_Utils_Array::value('relationship_type', $this->_formValues);
    $clauserel[] = "
         (r.contact_id_a = contact_a.id AND r.contact_id_b = contact_origin.id AND CONCAT(r.relationship_type_id, '_a_b') = %{$count})
      OR (r.contact_id_b = contact_a.id AND r.contact_id_a = contact_origin.id AND CONCAT(r.relationship_type_id, '_b_a') = %{$count})";
    $params[$count] = array($relationship_type, 'String');
    $count += 1;

    $relationship_type = CRM_Utils_Array::value('relationship_type_2', $this->_formValues);
    if (!empty($relationship_type)) {
      $clauserel[] = "
           (r.contact_id_a = contact_a.id AND r.contact_id_b = contact_origin.id AND CONCAT(r.relationship_type_id, '_a_b') = %{$count})
        OR (r.contact_id_b = contact_a.id AND r.contact_id_a = contact_origin.id AND CONCAT(r.relationship_type_id, '_b_a') = %{$count})";
      $params[$count] = array($relationship_type, 'String');
      $count += 1;
    }
    
    $clause[] = '(' . implode(' OR ', $clauserel) . ')';


    // group id
    $groups = CRM_Utils_Array::value('includeGroups', $this->_formValues);
    if ($groups != NULL) {
      if (!is_array($groups)) $groups = array($groups);
      $clause[] = "group_contact.group_id IN (" . implode(',', $groups) . ")";
    }
    
    if (!empty($clause)) {
      $where = implode(' AND ', $clause);
    }
    else {
      $where = ' (1) ';
    }

    //print $where;
    return $this->whereClause($where, $params);
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
}
