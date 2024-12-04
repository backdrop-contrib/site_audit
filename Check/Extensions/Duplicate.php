<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Duplicate.
 */

/**
 * Class SiteAuditCheckExtensionsDuplicate.
 */
class SiteAuditCheckExtensionsDuplicate extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Duplicates');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Check for duplicate extensions in the site codebase.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('No duplicate extensions were detected.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = dt('The following duplicate extensions were found:');
    if ($this->getOption('html')) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Paths') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['extensions_dupe'] as $name => $extension_infos) {
        $ret_val .= '<tr><td>' . $name . '</td>';
        $paths = array();
        foreach ($extension_infos as $extension_info) {
          $extension = $extension_info['path'];
          if ($extension_info['version']) {
            $extension .= ' (' . $extension_info['version'] . ')';
          }
          $paths[] = $extension;
        }
        $ret_val .= '<td>' . implode('<br/>', $paths) . '</td></tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['extensions_dupe'] as $name => $extension_infos) {
        $ret_val .= PHP_EOL;
        if (!$this->getOption('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= $name . PHP_EOL;
        $extension_list = '';
        foreach ($extension_infos as $extension_info) {
          $extension_list .= str_repeat(' ', 8);
          $extension_list .= $extension_info['path'];
          if ($extension_info['version']) {
            $extension_list .= ' (' . $extension_info['version'] . ')';
          }
          $extension_list .= PHP_EOL;
        }
        $ret_val .= rtrim($extension_list);
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if ($this->score != SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS) {
      return dt('Prune your codebase to have only one copy of any given extension. If you are using an installation profile, work with the maintainer to update the relevant modules. If you remove an enabled module, you may have to rebuild the registry.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['extensions_dupe'] = [];
    $drupal_root = BACKDROP_ROOT;

    // Command to find .info files, excluding public files directory.
    $command = "find $drupal_root -xdev -type f -name '*.info' -o -path './"
      . variable_get('file_public_path', conf_path() . '/files') . "' -prune";
    exec($command, $result);

    foreach ($result as $path) {
      $path_parts = explode('/', $path);
      $name = substr(array_pop($path_parts), 0, -5);

      // Skip safe duplicates.
      if (in_array($name, [
        'drupal_system_listing_compatible_test',
        'drupal_system_listing_incompatible_test',
      ])) {
        continue;
      }

      if (!isset($this->registry['extensions_dupe'][$name])) {
        $this->registry['extensions_dupe'][$name] = [];
      }

      $extension_info = [
        'path' => substr($path, strlen($drupal_root) + 1),
        'version' => NULL,
      ];

      // Parse the .info file for the version key.
      $info = file($drupal_root . '/' . $extension_info['path']);
      foreach ($info as $line) {
        if (strpos($line, 'version') === 0) {
          $version = explode('=', $line);
          if (isset($version[1])) {
            $extension_info['version'] = trim(str_replace('"', '', $version[1]));
          }
        }
      }
      $this->registry['extensions_dupe'][$name][] = $extension_info;
    }

    // Review detected extensions for duplicates.
    foreach ($this->registry['extensions_dupe'] as $extension_name => $extension_infos) {
      // If there's only one instance, remove it from duplicates.
      if (count($extension_infos) == 1) {
        unset($this->registry['extensions_dupe'][$extension_name]);
        continue;
      }

      // Ignore extensions entirely within an installation profile.
      $paths_in_profile = 0;
      foreach ($extension_infos as $extension_info) {
        if (strpos($extension_info['path'], 'profiles/') === 0) {
          $paths_in_profile++;
        }
      }
      if ($paths_in_profile == count($extension_infos)) {
        unset($this->registry['extensions_dupe'][$extension_name]);
        continue;
      }

      // Skip overrides of installation profile extensions.
      if (!isset($this->registry['extensions'][$extension_name])) {
        // Log a debug message if needed:
        // watchdog('site_audit', 'Extension "@name" not found in registry.', ['@name' => $extension_name], WATCHDOG_DEBUG);
        continue; // Ensure the extension exists in the registry.
      }

      $extension_object = $this->registry['extensions'][$extension_name];
      if (
        isset($extension_object->info['version'])
        && $extension_object->info['version'] // Enabled extension has a version.
        && $paths_in_profile                 // There is a version in the profile.
        && drush_get_extension_status($extension_object) == 'enabled'
        && strpos($extension_object->uri, 'profiles/') === FALSE // Enabled is not in profile.
      ) {
        $skip = TRUE;
        foreach ($extension_infos as $extension_info) {
          if (
            strpos($extension_info['path'], 'profiles/') !== FALSE
            && $extension_info['version']
            && version_compare($extension_object->info['version'], $extension_info['version']) < 1
          ) {
            $skip = FALSE;
            break;
          }
        }
        if ($skip === TRUE) {
          unset($this->registry['extensions_dupe'][$extension_name]);
        }
      }
    }

    // Determine the score.
    if (count($this->registry['extensions_dupe'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
