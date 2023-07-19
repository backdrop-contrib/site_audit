<?php
/**
 * @file
 * Autoloader.
 */


define('SITE_AUDIT_BASE_PATH', __DIR__);

// Autoload Report classes.
spl_autoload_register(
  function ($class) {
    if (strpos($class, 'SiteAuditReport') === 0) {
      require_once SITE_AUDIT_BASE_PATH . '/Report/' . substr($class, strlen('SiteAuditReport')) . '.php';
    }
    if (strpos($class, 'SiteAuditCheck') === 0) {
      // Figure out what folder the check is in.
      require_once SITE_AUDIT_BASE_PATH . '/Check/' . $report . '/' . substr($class, strlen('SiteAuditCheck')) . '.php';
    }
  }
);


