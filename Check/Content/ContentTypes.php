<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\ContentTypes.
 */

/**
 * Class SiteAuditCheckContentContentTypes.
 */
class SiteAuditCheckContentContentTypes extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Content types');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Available content types and counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $ret_val = '';

    if (empty($this->registry['content_type_counts'])) {
      if ($this->getOption('detail')) {
        return dt('No nodes exist.');
      }
      return $ret_val;
    }

    $ret_val .= "Total: {$this->registry['node_count']} nodes";
    if ($this->getOption('html') == TRUE) {
      $ret_val = "<p>$ret_val</p>";
    }
    else {
      $ret_val .= PHP_EOL;
    }

    if ($this->getOption('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Content Type') . '</th><th>' . dt('Node Count') . '</th></tr></thead>';
      foreach ($this->registry['content_type_counts'] as $content_type => $count) {
        $ret_val .= "<tr><td>$content_type</td><td>$count</td></tr>";
      }
      $ret_val .= '</table>';
    }
    else {
      if (!$this->getOption('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-------------------';
      foreach ($this->registry['content_type_counts'] as $content_type => $count) {
        $ret_val .= PHP_EOL;
        if (!$this->getOption('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= $content_type . ': ' . $count;
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
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('node')) {
      $this->abort = true;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    // Retrieve all content type configurations.
    $this->registry['content_type_counts'] = [];
    $this->registry['content_types_unused'] = [];
    $this->registry['node_count'] = 0;

    // Directory where configuration files are stored.
    $config_path = config_get_config_directory('active');
    $config_files = scandir($config_path);

    foreach ($config_files as $file) {
      // Look for files starting with `node.type.`
      if (strpos($file, 'node.type.') === 0) {
        // Load the content type configuration.
        $content_type_config = config_get(basename($file, '.json'));
        $type = $content_type_config['type'];

        // Query the database for nodes of this content type.
        $query = db_query("SELECT COUNT(*) FROM {node} WHERE type = :type AND status = 1", [':type' => $type]);
        $count = $query->fetchField();

        if ($count == 0) {
          $this->registry['content_types_unused'][] = $type;
        }

        $this->registry['content_type_counts'][$type] = $count;
        $this->registry['node_count'] += $count;
      }
    }

    // Check if no nodes exist.
    if (empty($this->registry['content_type_counts'])) {
      $this->abort = true;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
