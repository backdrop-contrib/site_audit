<?php
/**
 * @file
 * Contains \SiteAudit\Report\Cache.
 */

/**
 * Class SiteAuditReportCache.
 */
class SiteAuditReportCache extends SiteAuditReportAbstract {

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt("Backdrop's caching settings");
  }

}
