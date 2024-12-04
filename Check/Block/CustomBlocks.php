<?php

/**
 * @file
 * Contains \SiteAudit\Check\Block\CustomBlocks.
 */

/**
 * Class SiteAuditCheckBlockCustomBlocks.
 */
class SiteAuditCheckBlockCustomBlocks extends SiteAuditCheckAbstract {

  /**
   * Implements \SiteAudit\Check\Abstract\getLabel().
   */
  public function getLabel() {
    return dt('Custom blocks and their layouts');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getDescription().
   */
  public function getDescription() {
    return dt('List all custom blocks configured in the site and the layouts they are used in.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultFail().
   */
  public function getResultFail() {
    return dt('Failed to retrieve custom block configurations.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultInfo().
   */
  public function getResultInfo() {
    if ($this->getOption('html')) {
      $ret_val = '<table class="table table-condensed">';
      $ret_val .= '<thead><tr><th>' . dt('Block ID') . '</th><th>' . dt('Block Title') . '</th><th>' . dt('Layout') . '</th></tr></thead>';
      $ret_val .= '<tbody>';
      foreach ($this->registry['blocks'] as $block) {
        $ret_val .= '<tr>';
        $ret_val .= '<td>' . $block['id'] . '</td>';
        $ret_val .= '<td>' . $block['title'] . '</td>';
        $ret_val .= '<td>' . $block['layout'] . '</td>';
        $ret_val .= '</tr>';
      }
      $ret_val .= '</tbody>';
      $ret_val .= '</table>';
    } else {
      $ret_val = dt('Block ID - Block Title: Layout') . PHP_EOL;
      foreach ($this->registry['blocks'] as $block) {
        $ret_val .= "{$block['id']} - {$block['title']}: {$block['layout']}" . PHP_EOL;
      }
    }
    return $ret_val;
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultPass().
   */
  public function getResultPass() {
    return dt('Custom blocks and their layouts have been successfully retrieved.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getResultWarn().
   */
  public function getResultWarn() {
    return dt('No custom blocks are configured.');
  }

  /**
   * Implements \SiteAudit\Check\Abstract\getAction().
   */
  public function getAction() {
    if (empty($this->registry['blocks'])) {
      return dt('Add custom blocks to layouts to better manage your site content.');
    }
  }

  /**
   * Implements \SiteAudit\Check\Abstract\calculateScore().
   */
  public function calculateScore() {
    // Load all custom block configurations.
    $custom_blocks = config_get_names_with_prefix('block.custom');
    $this->registry['blocks'] = [];

    // Iterate through all layouts to map blocks to their layouts.
    $layouts = config_get_names_with_prefix('layout.layout');
    foreach ($custom_blocks as $block_config_name) {
      $block_config = config($block_config_name);
      $block_id = $block_config_name;
      $block_title = $block_config->get('info') ?? dt('Untitled');
      $layout_name = dt('Not placed in a layout');

      foreach ($layouts as $layout_config_name) {
        $layout_config = config($layout_config_name);
        $layout_content = $layout_config->get('content') ?? [];
        foreach ($layout_content as $content_uuid => $content_item) {
          if ($content_item['data']['module'] === 'block' && $content_item['data']['delta'] === $block_config->get('delta')) {
            $layout_name = $layout_config->get('title') ?? dt('Unnamed layout');
            break 2; // Stop searching once found.
          }
        }
      }

      $this->registry['blocks'][] = [
        'id' => $block_id,
        'title' => $block_title,
        'layout' => $layout_name,
      ];
    }

    if (empty($this->registry['blocks'])) {
      $this->abort = TRUE;
      return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_WARN;
    }

    return SiteAuditCheckAbstract::AUDIT_CHECK_SCORE_INFO;
  }
}
