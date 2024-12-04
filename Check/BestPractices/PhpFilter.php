<?php
/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\PhpFilter.
 */

/**
 * Class SiteAuditCheckBestPracticesPhpFilter.
 */
class SiteAuditCheckBestPracticesPhpFilter extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('PHP Filter');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if the PHP Filter module is enabled.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    if ($this->getOption('detail')) {
      return dt('PHP Filter is enabled! Storing executable code in the database is a major security risk and should never be used. Disable this module immediately.');
    }
    else {
      return dt('PHP Filter is enabled!');
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
    return dt('PHP Filter is not enabled.');
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
      return dt('Disable the PHP Filter module and remove all executable code from the database. Move code into your Backdrop CMS codebase or custom modules.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (module_exists('php')) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }
}
