<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\FieldInstances.
 */

/**
 * Class SiteAuditCheckContentFieldInstances.
 */
class SiteAuditCheckContentFieldInstances extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Field instance counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('For each bundle, entity and instance, get the count of populated fields');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (empty($this->registry['field_api_map'])) {
      return dt('Function field_info_field_map does not exist, cannot analyze.');
    }

    $ret_val = '';
    if ($this->getOption('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<tr><th>' . dt('Entity Type') . '</th><th>' . dt('Field Name') . '</th><th>' . dt('Bundle Name') . '</th><th>' . dt('Count') . '</th></tr>';
      foreach ($this->registry['field_instance_counts'] as $bundle_name => $entity_types) {
        foreach ($entity_types as $entity_type => $fields) {
          foreach ($fields as $field_name => $count) {
            $ret_val .= "<tr><td>$entity_type</td><td>$field_name</td><td>$bundle_name</td><td>$count</td></tr>";
          }
        }
      }
      $ret_val .= '</table>';
    }
    else {
      $rows = 0;
      foreach ($this->registry['field_instance_counts'] as $bundle_name => $entity_types) {
        if ($rows++ > 0) {
          $ret_val .= PHP_EOL;
          if (!$this->getOption('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
        }
        $ret_val .= dt('Bundle: !bundle_name', array(
          '!bundle_name' => $bundle_name,
        ));
        foreach ($entity_types as $entity_type => $fields) {
          $ret_val .= PHP_EOL;
          if (!$this->getOption('json')) {
            $ret_val .= str_repeat(' ', 6);
          }
          $ret_val .= dt('Entity Type: !entity_type', array(
            '!entity_type' => $entity_type,
          ));
          foreach ($fields as $field_name => $count) {
            $ret_val .= PHP_EOL;
            if (!$this->getOption('json')) {
              $ret_val .= str_repeat(' ', 8);
            }
            $ret_val .= "$field_name: $count";
          }
        }
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('field')) {
      $this->abort = true;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    $this->registry['field_instance_counts'] = [];
    $this->registry['field_api_map'] = [];
    $this->registry['missing_tables'] = [];

    // Get active configuration directory.
    $config_path = config_get_config_directory('active');
    $config_files = scandir($config_path);

    foreach ($config_files as $file) {
      // Look for field instance configurations.
      if (strpos($file, 'field.instance.') === 0) {
        $field_instance_config = config_get(basename($file, '.json'));

        $field_name = $field_instance_config['field_name'];
        $entity_type = $field_instance_config['entity_type'];
        $bundle_name = $field_instance_config['bundle'];

        // Ensure the field map includes this field.
        if (!isset($this->registry['field_api_map'][$field_name])) {
          $this->registry['field_api_map'][$field_name] = [
            'entity_type' => $entity_type,
            'bundles' => [],
          ];
        }
        $this->registry['field_api_map'][$field_name]['bundles'][$bundle_name] = $bundle_name;

        // Check if the database table for this field exists.
        $table_name = 'field_data_' . $field_name;
        $table_exists = db_table_exists($table_name);

        if (!$table_exists) {
          // Log missing table information.
          $this->registry['missing_tables'][$field_name] = $table_name;
          continue;
        }

        // Query the database to count field data.
        $query = db_query("SELECT COUNT(*) FROM {$table_name} WHERE bundle = :bundle", [':bundle' => $bundle_name]);
        $field_count = $query->fetchField();

        // Store field instance counts.
        $this->registry['field_instance_counts'][$bundle_name][$entity_type][$field_name] = $field_count;
      }
    }

    // If any tables are missing, consider logging or alerting about them.
    if (!empty($this->registry['missing_tables'])) {
      watchdog('site_audit', 'Missing field tables: @tables', [
        '@tables' => implode(', ', $this->registry['missing_tables']),
      ], WATCHDOG_WARNING);
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
