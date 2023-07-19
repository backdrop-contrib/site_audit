<?php
/**
 * @file
 * Contains \SiteAudit\Check\Users\RolesList.
 */

/**
 * Class SiteAuditCheckRolesRolesList.
 */
class SiteAuditCheckRolesRolesList extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Roles and User Count');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Show all available roles and user counts.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    $counts = array();
    foreach ($this->registry['roles'] as $name => $count_users) {
      $counts[] = "$name: $count_users";
    }
    return implode(', ', $counts);
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    $roles = config_get('user.role');

    $this->registry['roles'] = [];

    foreach ($roles as $rid => $role) {
      if ($role['name'] === 'authenticated user') {
        $count = user_count_users();
        $this->registry['roles'][$role['name']] = $count;
      } else {
        $count = db_query('SELECT COUNT(uid) FROM {users_roles} WHERE rid = :rid', [':rid' => $rid])->fetchField();
        $this->registry['roles'][$role['name']] = $count;
      }
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }


}
