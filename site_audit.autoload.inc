<?php
/**
 * @file
 * Autoloader for SiteAudit classes.
 */

// Dynamically determine the base path of the module.
define('SITE_AUDIT_BASE_PATH', BACKDROP_ROOT . '/' . backdrop_get_path('module', 'site_audit'));

// Register the autoloader.
spl_autoload_register(function ($class) {
  // Log every class to debug.
  error_log("Attempting to autoload: $class");

  // Handle SiteAuditReport classes.
  if (strpos($class, 'SiteAuditReport') === 0) {
    $report_class = substr($class, strlen('SiteAuditReport'));
    $file_path = SITE_AUDIT_BASE_PATH . '/Report/' . $report_class . '.php';
    if (file_exists($file_path)) {
      require_once $file_path;
      return;
    }
    error_log("Report class not found: $class ($file_path)");
  }

  // Handle SiteAuditCheck classes.
  if (strpos($class, 'SiteAuditCheck') === 0) {
    $check_class = substr($class, strlen('SiteAuditCheck'));
    $parts = preg_split('/(?=[A-Z])/', $check_class, -1, PREG_SPLIT_NO_EMPTY);
    $folder = $parts[0] ?? '';
    $file = $parts[1] ?? '';
    $file_path = SITE_AUDIT_BASE_PATH . '/Check/' . $folder . '/' . $file . '.php';

    if (file_exists($file_path)) {
      require_once $file_path;
      return;
    }
    error_log("Check class not found: $class ($file_path)");
  }
});

