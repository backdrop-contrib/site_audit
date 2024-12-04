<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\Settings.
 */

/**
 * Class SiteAuditCheckBestPracticesSettings.
 */
class SiteAuditCheckBestPracticesSettings extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('settings.php');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if the configuration file exists. It does.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('No settings.php found!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    return dt('settings.php does not exist, but this is a multi-site configuration.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('settings.php exists and is not a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('settings.php is a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Don\'t rely on symbolic links for core configuration files; copy settings.php where it should be and remove the symbolic link.');
    }
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('Even if environment settings are injected, create a stub settings.php file for compatibility.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $backdrop_root = BACKDROP_ROOT;
    $settings_file = $backdrop_root . '/settings.php';

    // Check if the settings.php file exists.
    if (file_exists($settings_file)) {
      // Check if it is a symbolic link.
      if (is_link($settings_file)) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    // Check if this is a multisite setup.
    if (isset($this->registry['multisites']) && !empty($this->registry['multisites'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    // If no settings.php is found, return FAIL.
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
