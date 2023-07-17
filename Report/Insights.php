<?php
/**
 * @file
 * Contains \SiteAudit\Report\Insights.
 */

/**
 * Class SiteAuditReportInsights.
 */
class SiteAuditReportInsights extends SiteAuditReportAbstract {

  /**
   * Override parent constructor to provide argument support.
   *
   * @param string $url
   *   URL of site to test.
   * @param string $key
   *   Google API key.
   */
  public function __construct($url = null, $key = null) {
    $this->registry['url'] = $url ?: variable_get('site_audit_pagespeed_url');
    $this->registry['key'] = $key ?: variable_get('site_audit_pagespeed_api_key');
    parent::__construct();
  }

  /**
   * Implements \SiteAudit\Report\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Google PageSpeed Insights');
  }

}
