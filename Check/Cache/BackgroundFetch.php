<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\BackgroundFetch.
 */

/**
 * Class SiteAuditCheckCacheBackgroundFetch.
 */
class SiteAuditCheckCacheBackgroundFetch extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Background fetch for cached pages');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify if background fetch for cached pages is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Background fetch for cached pages is not enabled!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Background fetch for cached pages is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    // Only provide an action if the score indicates a failure.
    if ($this->score === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Go to /admin/config/development/performance and check "Use background fetch for cached pages".');
    }

    // Return an empty string or null when no action is needed.
    return '';
  }


  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Load the 'page_cache_background_fetch' setting from 'system.core'.
    $page_cache_background_fetch = config('system.core')->get('page_cache_background_fetch');

    // Fail if 'page_cache_background_fetch' is not enabled.
    if ($page_cache_background_fetch) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
