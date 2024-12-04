<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Bins.
 */

/**
 * Class SiteAuditCheckCacheBins.
 */
class SiteAuditCheckCacheBins extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cache bins');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail explicitly defined cache bins.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (!empty($this->registry['cache_bins'])) {
      if ($this->getOption('html')) {
        $ret_val = '<table class="table table-condensed">';
        $ret_val .= '<thead><tr><th>' . dt('Bin') . '</th><th>' . dt('Class') . '</th></tr></thead>';
        $ret_val .= '<tbody>';
        foreach ($this->registry['cache_bins'] as $bin => $class) {
          $ret_val .= "<tr><td>$bin</td><td>$class</td></tr>";
        }
        $ret_val .= '</tbody>';
        $ret_val .= '</table>';
      }
      else {
        $ret_val  = dt('Bin: Class') . PHP_EOL;
        if (!$this->getOption('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= '----------';
        foreach ($this->registry['cache_bins'] as $bin => $class) {
          $ret_val .= PHP_EOL;
          if (!$this->getOption('json')) {
            $ret_val .= str_repeat(' ', 4);
          }
          $ret_val .= "$bin: $class";
        }
      }
      return $ret_val;
    }
    else {
      return dt('No cache bins defined.');
    }
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
  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    global $settings;
    $this->registry['cache_bins'] = array();

    // Look for defined cache bins in $settings.
    if (!empty($settings)) {
      foreach ($settings as $key => $value) {
        if (strpos($key, 'cache_class_') === 0) {
          $bin = str_replace('cache_class_', '', $key);
          $this->registry['cache_bins'][$bin] = $value;
        }
      }
    }

    if (!empty($this->registry['cache_bins'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
