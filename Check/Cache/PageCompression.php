<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PageCompression.
 */

/**
 * Class SiteAuditCheckCachePageCompression.
 */
class SiteAuditCheckCachePageCompression extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Cached page compression');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    if ($this->getOption('vendor') == 'pantheon') {
      return dt('Verify that Backdrop is not set to compress cached pages.');
    }
    else {
      return dt('Verify that Backdrop is set to compress cached pages.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    if ($this->getOption('vendor') == 'pantheon') {
      return dt('Cached pages are compressed!');
    }
    else {
      return dt('Cached pages are not compressed!');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    if ($this->getOption('vendor') == 'pantheon') {
      return dt('Cached pages are not compressed.');
    }
    else {
      return dt('Cached pages are compressed.');
    }
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
      if ($this->getOption('vendor') == 'pantheon') {
        return dt('Pantheon compresses your pages for you. Don\'t make Backdrop do the work! Go to /admin/config/development/performance and uncheck "Compress cached pages".');
      }
      else {
        return dt('Go to /admin/config/development/performance and check "Compress cached pages".');
      }
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $system_core_config = config_get('system.core');
    $page_compression = $system_core_config['page_compression'] ?? 0;

    // Check if the vendor is Pantheon.
    if ($this->getOption('vendor') == 'pantheon') {
      if (!$page_compression) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }

    // For other environments, ensure page compression is enabled.
    if (empty($page_compression)) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
