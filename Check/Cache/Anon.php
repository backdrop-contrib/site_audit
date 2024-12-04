<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Anon.
 */

/**
 * Class SiteAuditCheckCacheAnon.
 */
class SiteAuditCheckCacheAnon extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Anonymous caching');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Verify Backdrop\'s anonymous page caching is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Anonymous page caching is not enabled!');
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
    return dt('Anonymous caching is enabled.');
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
      return dt('Go to /admin/config/development/performance and check "Cache pages for anonymous users".');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Load the 'system.core' configuration.
    $config = config('system.core');

    // Check if the 'cache' setting is enabled.
    $cache_enabled = $config->get('cache');

    if ($cache_enabled) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    // Return fail if caching is not enabled.
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
