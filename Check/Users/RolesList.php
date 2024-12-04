<?php
class SiteAuditCheckUsersRolesList extends SiteAuditCheckAbstract {

  public function getLabel() {
    return dt('Roles and User Count');
  }

  public function getDescription() {
    return dt('Show all available roles and user counts.');
  }

  public function getResultFail() {}

  public function getResultInfo() {
    $counts = [];
    foreach ($this->registry['roles'] as $name => $count_users) {
      $counts[] = "$name: $count_users";
    }
    return implode(', ', $counts);
  }

  public function getResultPass() {}

  public function getResultWarn() {}

  public function getAction() {}

  public function calculateScore() {
    // Retrieve all roles from configuration.
    $role_configs = config_get_config_directory('active');
    $roles = [];

    foreach (scandir($role_configs) as $file) {
      if (strpos($file, 'user.role.') === 0) {
        $role_config = config_get(basename($file, '.json'));
        $roles[$role_config['name']] = $role_config;
      }
    }

    $this->registry['roles'] = [];

    // Count users assigned to each role.
    foreach ($roles as $role_machine_name => $role_config) {
      if ($role_machine_name === 'authenticated') {
        // Special case for authenticated users: All users except anonymous (UID 0).
        $count = db_query('SELECT COUNT(uid) FROM {users} WHERE uid > 0')->fetchField();
      } else {
        // For other roles, count users assigned to the role.
        $count = db_query('SELECT COUNT(uid) FROM {users_roles} WHERE role = :role', [':role' => $role_machine_name])->fetchField();
      }
      $this->registry['roles'][$role_config['label']] = $count;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}


