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
    $content_types = config_get('node.type');

    $this->registry['content_type_counts'] = $this->registry['content_types_unused'] = [];
    $this->registry['node_count'] = 0;

    foreach ($content_types as $type => $type_info) {
      // Assuming you're only interested in published nodes, adjust the path accordingly if needed.
      $nodes = config_get('node.type.' . $type . '.path', 'published');

      $count = count($nodes);
      if ($count == 0) {
        $this->registry['content_types_unused'][] = $type;
      }

      $this->registry['content_type_counts'][$type] = $count;
      $this->registry['node_count'] += $count;
    }

    // Check to see if no nodes exist.
    $content_type_counts = array_count_values($this->registry['content_type_counts']);
    if (count($content_type_counts) == 1 && isset($content_type_counts[0]) && $content_type_counts[0] > 0) {
      $this->registry['content_type_counts'] = [];
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }


}
