<?php
class SiteAuditCheckUsersRolesPermissions extends SiteAuditCheckAbstract {

  public function getLabel() {
    return dt('Roles and Permissions');
  }

  public function getDescription() {
    return dt('Percentage of permissions assigned to individual roles.');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    $roles = [];
    $total = array_sum($this->registry['roles']);
    foreach ($this->registry['roles'] as $name => $count_permissions) {
      $percentage = $total > 0 ? number_format(($count_permissions / $total) * 100, 0) : 0;
      $roles[] = "$name: $count_permissions ($percentage%)";
    }
    return implode(', ', $roles);
  }

  public function getResultPass() {}

  public function getResultWarn() {}

  public function getAction() {}

  public function calculateScore() {
    // Retrieve all role configurations.
    $role_configs = config_get_config_directory('active');
    $roles = [];

    foreach (scandir($role_configs) as $file) {
      if (strpos($file, 'user.role.') === 0) {
        $role_config = config_get(basename($file, '.json'));
        $roles[$role_config['label']] = $role_config;
      }
    }

    $this->registry['roles'] = [];

    // Count permissions for each role.
    foreach ($roles as $role_label => $role_config) {
      $permissions = isset($role_config['permissions']) ? count($role_config['permissions']) : 0;
      $this->registry['roles'][$role_label] = $permissions;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
