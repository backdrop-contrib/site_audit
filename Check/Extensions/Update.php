<?php
/**
 * @file
 * Contains \SiteAudit\Check\Extensions\Update.
 */

/**
 * Class SiteAuditCheckExtensionsUpdate.
 */
class SiteAuditCheckExtensionsUpdate extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Updates');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Determine what projects can be updated.');
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
    return dt('No projects need updating.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    $ret_val = 'The following project(s) have updates available:';
    if ($this->getOption('html')) {
      $ret_val = '<p>' . $ret_val . '</p>';
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Name') . '</th><th>' . dt('Existing') . '</th><th>' . dt('Candidate') . '</th><th>' . dt('Status') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['projects_update'] as $short_info) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $short_info['label'] . '</td>';
        $ret_val .= '<td>' . $short_info['existing_version'] . '</td>';
        $ret_val .= '<td>' . $short_info['candidate_version'] . '</td>';
        $ret_val .= '<td>' . $short_info['status_msg'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    else {
      foreach ($this->registry['projects_update'] as $short_info) {
        $ret_val .= PHP_EOL;
        if (!$this->getOption('json')) {
          $ret_val .= str_repeat(' ', 6);
        }
        $ret_val .= "- {$short_info['label']}: {$short_info['existing_version']} to {$short_info['candidate_version']} - {$short_info['status_msg']}";
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    return dt('Back up your site, review each project change, ensure compatibility, then update affected project(s).');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $this->registry['projects_update'] = $this->registry['projects_security'] = array();

    if ($available = update_get_available(TRUE)) {
      module_load_include('inc', 'update', 'update.compare');
      $data = update_calculate_project_data($available);
      foreach ($data as $project_name => $project) {
        // Discard custom projects.
        if ($project['status'] == UPDATE_UNKNOWN) {
          unset($data[$project_name]);
          continue;
        }
        // Discard projects with unknown installation path.
//          if ($project_name != 'drupal' && !isset($projects[$project_name]['path'])) {
//            unset($data[$project_name]);
//            continue;
//          }

        // Add some info from the project to $data.
//          $data[$project_name] += array(
//            'path' => isset($projects[$project_name]['path']) ? $projects[$project_name]['path'] : '',
//            'label' => $projects[$project_name]['label'],
//          );
        // Store all releases, not just the ones selected by update.module.
        // We use it to allow the user to update to a specific version.
        if (isset($available[$project_name]['releases'])) {
          $data[$project_name]['releases'] = $available[$project_name]['releases'];
        }
      }
      $values = $data;
    }
    $update_info = $values;
//
//    // Only show updatable projects.
//    foreach ($update_info as $name => $project) {
//      if ((!isset($project['updateable']) || !$project['updateable'])) {
//        unset($update_info[$name]);
//      }
//    }
    // Build only useful data.
    foreach ($update_info as $project_name => $project_data) {
      if ($project_data['existing_version'] == $project_data['recommended']) {
        continue;
      }

      $short_info = array(
        'existing_version' => $project_data['existing_version'],
        'candidate_version' => $project_data['recommended'] ?? '',
        'status_msg' => $project_data['link'] ? l($project_data['link'], $project_data['link']): '',
        'label' => $project_data['title'],
      );
      if (stripos($short_info['status_msg'], 'security') !== FALSE) {
        $this->registry['projects_security'][$project_name] = $short_info;
      }
      else {
        $this->registry['projects_update'][$project_name] = $short_info;
      }
    }

    if (!empty($this->registry['projects_update'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
  }

}
