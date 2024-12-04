<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PreprocessCss.
 */

/**
 * Class SiteAuditCheckCachePreprocessCss.
 */
class SiteAuditCheckCachePreprocessCss extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Aggregate and compress CSS files in Backdrop');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Backdrop is aggregating and compressing CSS.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('CSS aggregation and compression is not enabled!');
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
    return dt('CSS aggregation and compression is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and check "Aggregate and compress CSS files".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Load the 'preprocess_css' setting from 'system.core'.
    $preprocess_css = config('system.core')->get('preprocess_css');

    // Check if CSS aggregation and compression is enabled.
    if ($preprocess_css) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    // Fail if 'preprocess_css' is not enabled.
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
