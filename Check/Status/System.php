<?php
/**
 * @file
 * Contains \SiteAudit\Check\Status\System.
 */

/**
 * Class SiteAuditCheckStatusSystem.
 */
class SiteAuditCheckStatusSystem extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('System Status');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt("Drupal's status report.");
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return $this->getResultPass();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    $items = array();
    foreach ($this->registry['requirements'] as $requirement) {
      // Default to REQUIREMENT_INFO if no severity is set.
      if (!isset($requirement['severity'])) {
        $requirement['severity'] = REQUIREMENT_INFO;
      }

      // Reduce verbosity.
      if (!$this->getOption('detail') && $requirement['severity'] < REQUIREMENT_WARNING) {
        continue;
      }

      // Title: severity - value.
      if ($requirement['severity'] == REQUIREMENT_INFO) {
        $class = 'info';
        $severity = 'Info';
      }
      elseif ($requirement['severity'] == REQUIREMENT_OK) {
        $severity = 'Ok';
        $class = 'success';
      }
      elseif ($requirement['severity'] == REQUIREMENT_WARNING) {
        $severity = 'Warning';
        $class = 'warning';
      }
      elseif ($requirement['severity'] == REQUIREMENT_ERROR) {
        $severity = 'Error';
        $class = 'error';
      }

      if ($this->getOption('html') || $this->getOption('json')) {
        $value = isset($requirement['value']) && $requirement['value'] ? $requirement['value'] : '&nbsp;';
        $uri = url('', array('absolute' => TRUE));
        // Unknown URI - strip all links, but leave formatting.
        if ($uri == 'http://default') {
          $value = strip_tags($value, '<em><i><b><strong><span>');
        }
        // Convert relative links to absolute.
        else {
          $value = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1' . $uri . '$2$3', $value);
        }

        $item = array(
          'title' => $requirement['title'],
          'severity' => $severity,
          'value' => $value,
          'class' => $class,
        );
        if ($this->getOption('json')) {
          foreach ($item as $key => $value) {
            $item[$key] = strip_tags($value);
          }
        }
      }
      else {
        $item = strip_tags($requirement['title']) . ': ' . $severity;
        if (isset($requirement['value']) && $requirement['value']) {
          $item .= ' - ' . dt('@value', array(
              '@value' => strip_tags($requirement['value']),
            ));
        }
      }
      $items[] = $item;
    }
    if ($this->getOption('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Title') . '</th><th>' . dt('Severity') . '</th><th>' . dt('Value') . '</th></thead>';
      $ret_val .= '<tbody>';
      foreach ($items as $item) {
        $ret_val .= '<tr class="' . $item['class'] . '">';
        $ret_val .= '<td>' . $item['title'] . '</td>';
        $ret_val .= '<td>' . $item['severity'] . '</td>';
        $ret_val .= '<td>' . $item['value'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    }
    elseif ($this->getOption('json')) {
      foreach ($items as $item) {
        unset($item['class']);
        $ret_val[] = $item;
      }
    }
    else {
      $separator = PHP_EOL;
      $separator .= str_repeat(' ', 4);
      $ret_val = implode($separator, $items);
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultPass();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Include the install functions if not already included.
    include_once BACKDROP_ROOT . '/core/includes/install.inc';

    // Ensure update information is loaded.
    backdrop_load_updates();

    // Retrieve runtime requirements.
    $this->registry['requirements'] = module_invoke_all('requirements', 'runtime');

    // Sort requirements by severity.
    usort($this->registry['requirements'], function ($a, $b) {
      return ($b['severity'] ?? REQUIREMENT_INFO) - ($a['severity'] ?? REQUIREMENT_INFO);
    });

    $this->percentOverride = 0;
    $requirements_with_severity = [];

    // Filter out requirements with severity.
    foreach ($this->registry['requirements'] as $key => $value) {
      if (isset($value['severity'])) {
        $requirements_with_severity[$key] = $value;
      }
    }

    // Calculate the score based on requirements.
    if (count($requirements_with_severity) > 0) {
      $score_each = 100 / count($requirements_with_severity);

      $worst_severity = REQUIREMENT_INFO;
      foreach ($requirements_with_severity as $requirement) {
        if ($requirement['severity'] > $worst_severity) {
          $worst_severity = $requirement['severity'];
        }
        if ($requirement['severity'] == REQUIREMENT_WARNING) {
          $this->percentOverride += $score_each / 2;
        } elseif ($requirement['severity'] != REQUIREMENT_ERROR) {
          $this->percentOverride += $score_each;
        }
      }
    }

    // Round the percentage score.
    $this->percentOverride = round($this->percentOverride);

    // Determine the final audit score.
    if ($this->percentOverride > 80) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    } elseif ($this->percentOverride > 60) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }
    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_FAIL;
  }

}
