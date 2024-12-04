<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Multisite.
 */

/**
 * Class SiteAuditCheckBestPracticesMultisite.
 */
class SiteAuditCheckBestPracticesMultisite extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Multi-site');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect multi-site configurations.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('The following multi-site configuration(s) were detected: @list', array(
      '@list' => implode(', ', $this->registry['multisites']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return $this->getResultFail();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No multi-sites detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Ensure that multi-site configurations are necessary and properly managed.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $backdrop_root = BACKDROP_ROOT;
    $handle = opendir($backdrop_root);

    $this->registry['multisites'] = array();

    while (FALSE !== ($entry = readdir($handle))) {
      // Look for potential multisite directories containing a settings.php file.
      if (!in_array($entry, array('.', '..', 'core', 'files', 'modules', 'themes'))) {
        $settings_path = $backdrop_root . '/' . $entry . '/settings.php';
        if (is_dir($backdrop_root . '/' . $entry) && file_exists($settings_path)) {
          $this->registry['multisites'][] = $entry;
        }
      }
    }
    closedir($handle);

    if (!empty($this->registry['multisites'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
