<?php

/**
 * @file
 * Contains \SiteAudit\Check\BestPractices\FilesDirectory.
 */

/**
 * Class SiteAuditCheckBestPracticesFilesDirectory.
 */
class SiteAuditCheckBestPracticesFilesDirectory extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('files directory');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check if the files directory exists and is not a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('The files directory does not exist!');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('The files directory exists and is not a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('The files directory exists as a symbolic link.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL) {
      return dt('The files directory is necessary; recreate it immediately.');
    }
    if ($this->score == SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN) {
      return dt('Avoid symbolic links for critical directories; recreate the files directory without symlinks.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $backdrop_root = BACKDROP_ROOT;
    $files_dir = $backdrop_root . '/files';

    // Check if the files directory exists.
    if (is_dir($files_dir)) {
      // Check if it's a symbolic link.
      if (is_link($files_dir)) {
        return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
      }
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }
}
