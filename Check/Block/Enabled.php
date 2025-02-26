<?php
/**
 * @file
 * Contains \SiteAudit\Check\Block\Enabled.
 */

/**
 * Class SiteAuditCheckBlockEnabled.
 */
class SiteAuditCheckBlockEnabled extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Block status');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check to see if enabled');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Block is not enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Block is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt("Block is enabled, but there is no default theme. Consider disabling block if you don't need it.");
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Go to /admin/appearance and set a default theme to ensure proper rendering of site elements.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('block')) {
      watchdog('site_audit', 'Block module is not enabled.', [], WATCHDOG_DEBUG);
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    $this->registry['theme_default'] = config_get('system.core', 'theme_default');

    if (empty($this->registry['theme_default'])) {
      watchdog('site_audit', 'No default theme set.', [], WATCHDOG_DEBUG);
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }

    watchdog('site_audit', 'Block Enabled check passed.', [], WATCHDOG_DEBUG);
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
