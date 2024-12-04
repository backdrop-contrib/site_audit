<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\PageExpire.
 */

/**
 * Class SiteAuditCheckCachePageExpire.
 */
class SiteAuditCheckCachePageExpire extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Expiration of cached pages');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify that Backdrop\'s cached pages last for at least 15 minutes.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Expiration of cached pages not set!');
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
    return dt('Expiration of cached pages is set to @minutes min.', array(
      '@minutes' => round($this->registry['page_cache_maximum_age'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('Expiration of cached pages only set to @minutes min.', array(
      '@minutes' => round($this->registry['page_cache_maximum_age'] / 60),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (!in_array($this->score, array(SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS))) {
      return dt('Go to /admin/config/development/performance and set "Expiration of cached pages" to 15 min or above.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Get the 'page_cache_maximum_age' from system.core configuration.
    $system_core_config = config_get('system.core', 'page_cache_maximum_age');

    if (!$system_core_config) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }

    $this->registry['page_cache_maximum_age'] = $system_core_config;

    if ($system_core_config >= 900) { // 900 seconds = 15 minutes
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }

}
