<?php
class SiteAuditCheckViewsCacheResults extends SiteAuditCheckAbstract {

  public function getLabel() {
    return dt('Query results caching');
  }

  public function getDescription() {
    return dt('Check the length of time raw query results should be cached.');
  }

  public function getResultFail() {
    return dt('No View is caching query results!');
  }

  public function getResultInfo() {
    return $this->getResultWarn();
  }

  public function getResultPass() {
    return dt('Caching query results for all applicable Views.');
  }

  public function getResultWarn() {
    return dt('The following Views are not caching query results: @views_without_results_caching', [
      '@views_without_results_caching' => implode(', ', $this->registry['views_without_results_caching']),
    ]);
  }

  public function getAction() {
    if (!in_array($this->score, [SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO, SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS])) {
      $ret_val = t('Query results should be cached for performance improvement. Set a caching option other than None.');
      if ($this->getOption('detail')) {
        $steps = [
          t('Go to /admin/structure/views.'),
          t('Edit the View in question.'),
          t('Select the Display you want to configure.'),
          t('Click Advanced in the View editor.'),
          t('Next to Caching, click to edit.'),
          t('Set Query results to Time-based or another caching mechanism, ensuring it is not set to None.'),
        ];
        if ($this->getOption('html')) {
          $ret_val .= '<ol><li>' . implode('</li><li>', $steps) . '</li></ol>';
        } elseif ($this->getOption('json')) {
          $ret_val = [
            'Summary' => $ret_val,
            'Steps' => $steps,
          ];
        } else {
          foreach ($steps as $step) {
            $ret_val .= PHP_EOL;
            $ret_val .= str_repeat(' ', 8);
            $ret_val .= '- ' . $step;
          }
        }
      }
      return $ret_val;
    }
  }

  public function calculateScore() {
    $this->registry['views_without_results_caching'] = [];
    $views = views_get_all_views();

    foreach ($views as $view) {
      if ($view->disabled) {
        continue;
      }
      foreach ($view->display as $display_name => $display) {
        if (!empty($display->display_options['cache']) && $display->display_options['cache']['type'] === 'none') {
          $this->registry['views_without_results_caching'][] = $view->name;
        }
      }
    }

    if (empty($this->registry['views_without_results_caching'])) {
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_PASS;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
  }
}

