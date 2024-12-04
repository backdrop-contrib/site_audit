<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\DefaultClass.
 */

/**
 * Class SiteAuditCheckCacheDefaultClass.
 */
class SiteAuditCheckCacheDefaultClass extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Default cache class');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine the default cache class, used whenever no alternative is specified.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Using @cache_default_class as the default cache class.', [
      '@cache_default_class' => $this->registry['cache_default_class'],
    ]);
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The default cache class is BackdropDatabaseCache, but alternate caching backends are available. Consider specifying $settings["cache_default_class"] in settings.php for better performance.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    // Provide an action only if the score indicates a warning or failure.
    if ($this->score === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Specify an alternate caching backend like Redis or Memcache in settings.php by setting $settings["cache_default_class"].');
    }

    // Return an empty string or null when no action is needed.
    return '';
  }


  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Default to BackdropDatabaseCache if no other cache class is defined.
    $this->registry['cache_default_class'] = settings_get('cache_default_class', 'BackdropDatabaseCache');

    // Warn if BackdropDatabaseCache is used but other backends are available.
    if ($this->registry['cache_default_class'] == 'BackdropDatabaseCache' &&
      !empty($this->registry['cache_backends']) &&
      is_array($this->registry['cache_backends']) &&
      !empty($this->registry['cache_backends'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }

    // Default info level if no alternate cache class is specified.
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}

