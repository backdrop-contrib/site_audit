<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Missing.
 */

/**
 * Class SiteAuditCheckExtensionsMissing.
 */
class SiteAuditCheckExtensionsMissing extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Missing');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Detect missing extensions (modules and themes) in a site, which degrades performance.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('The following extensions are missing from your codebase: @extensions_missing', array(
      '@extensions_missing' => implode(', ', $this->registry['extensions_missing']),
    ));
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No extensions are missing from your codebase.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Download the extensions to your codebase, disable, uninstall, then remove the code.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_missing'] = [];
    $drupal_root = BACKDROP_ROOT; // Use BACKDROP_ROOT for Backdrop CMS.

    $result = db_select('system', 's') // Use an alias to avoid ambiguity.
    ->fields('s', ['name', 'filename']) // Reference the alias in fields.
    ->condition('status', 1, '=') // Use integer values for status.
    ->execute();

    foreach ($result as $row) {
      if (!file_exists($drupal_root . '/' . $row->filename)) {
        $this->registry['extensions_missing'][] = $row->name;
      }
    }

    if (!empty($this->registry['extensions_missing'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
