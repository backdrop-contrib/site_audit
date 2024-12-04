<?php
/**
 * @file
 * Contains \SiteAudit\Check\Content\Vocabularies.
 */

/**
 * Class SiteAuditCheckContentVocabularies.
 */
class SiteAuditCheckContentVocabularies extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Taxonomy vocabularies');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('Available vocabularies and term counts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if (!isset($this->registry['vocabulary_counts'])) {
      return dt('The taxonomy module is not enabled.');
    }
    if (empty($this->registry['vocabulary_counts'])) {
      if ($this->getOption('detail')) {
        return dt('No vocabularies exist.');
      }
      return '';
    }
    $ret_val = '';
    if ($this->getOption('html') == TRUE) {
      $ret_val .= '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Vocabulary') . '</th><th>' . dt('Terms') . '</th></tr></thead>';
      foreach ($this->registry['vocabulary_counts'] as $vocabulary => $count) {
        $ret_val .= "<tr><td>$vocabulary</td><td>$count</td></tr>";
      }
      $ret_val .= '</table>';
    }
    else {
      $ret_val  = dt('Vocabulary: Count') . PHP_EOL;
      if (!$this->getOption('json')) {
        $ret_val .= str_repeat(' ', 4);
      }
      $ret_val .= '-------------------';
      foreach ($this->registry['vocabulary_counts'] as $vocabulary => $count) {
        $ret_val .= PHP_EOL;
        if (!$this->getOption('json')) {
          $ret_val .= str_repeat(' ', 4);
        }
        $ret_val .= $vocabulary . ': ' . $count;
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {}

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return $this->getResultInfo();
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {}

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    if (!module_exists('taxonomy')) {
      $this->abort = true;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
    }

    // Scan for all vocabulary configurations.
    $this->registry['vocabulary_counts'] = [];
    $this->registry['vocabulary_unused'] = [];

    // Directory where configuration files are stored.
    $config_path = config_get_config_directory('active');
    $config_files = scandir($config_path);

    foreach ($config_files as $file) {
      // Look for files starting with `taxonomy.vocabulary.`
      if (strpos($file, 'taxonomy.vocabulary.') === 0) {
        // Load the vocabulary configuration.
        $vocabulary_config = config_get(basename($file, '.json'));
        $vocabulary_name = $vocabulary_config['name'];

        // Query the database for terms in this vocabulary.
        $query = db_query("SELECT COUNT(*) FROM {taxonomy_term_data} WHERE vocabulary = :vocabulary", [':vocabulary' => $vocabulary_name]);
        $count = $query->fetchField();

        if ($count == 0) {
          $this->registry['vocabulary_unused'][] = $vocabulary_name;
        } elseif (!$this->getOption('detail')) {
          continue;
        }

        $this->registry['vocabulary_counts'][$vocabulary_name] = $count;
      }
    }

    // Abort if no vocabularies have terms.
    if (empty($this->registry['vocabulary_counts'])) {
      $this->abort = true;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }

}
