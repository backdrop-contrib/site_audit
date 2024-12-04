<?php
/**
 * @file
 * Contains \SiteAudit\Check\Cache\Backends.
 */

/**
 * Class SiteAuditCheckCacheBackends.
 */
class SiteAuditCheckCacheBackends extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Caching backends');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detail caching backends.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Syntax error in configuration!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('Using the database as a caching backend, which is less efficient than a dedicated key-value store.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('The following caching backends are being used: @backends', array(
      '@backends' => implode(', ', $this->registry['cache_backends']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    // Action for INFO score.
    if ($this->score === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO) {
      if ($this->getOption('vendor') === 'pantheon') {
        return dt('Consider using a caching backend such as redis.');
      }
      elseif ($this->getOption('vendor') === 'acquia') {
        return dt('Consider using a caching backend such as memcache.');
      }
      return dt('Consider using a caching backend such as redis or memcache.');
    }

    // Action for FAIL score.
    if ($this->score === SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('The $settings["cache_backends"] configuration must be an array.');
    }

    // No action for PASS.
    return '';
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Initialize the registry for cache backends.
    $this->registry['cache_backends'] = [];

    // Check for cache backends defined in settings.php.
    if (isset($GLOBALS['settings']['cache_backends'])) {
      // Handle cases where cache_backends is not an array.
      if (!is_array($GLOBALS['settings']['cache_backends'])) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
      }

      // Handle valid cache backends.
      $this->registry['cache_backends'] = $GLOBALS['settings']['cache_backends'];
      if (!empty($this->registry['cache_backends'])) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
      }
    }

    // If no cache backends are defined, fallback to database caching.
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
