<?php

namespace Drupal\cci_cpnt_slider\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;

/**
 * Provides a 'SliderBlock' block.
 *
 * @Block(
 *  id = "cci_slider_block",
 *  admin_label = @Translation("CCI slider"),
 * )
 */
class sliderBlock extends BlockBase {

  public function build() {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('cci_cpnt_slider')->getPath();

    $content = '';
    if (\Drupal::service('path.matcher')->isFrontPage()) {
      $current_path = '<front>';
    }
    else {
      $current_path = \Drupal::request()->getRequestUri();
      $current_path = str_replace('/', '\/', $current_path);
    }

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'slider')
      ->condition('status', 1)
      ->condition('field_emplacement', '%' . $current_path . '%', 'LIKE')
      ->range(0, 1);

    $result = $query->execute();

    if (!empty($result)) {
      $node = Node::load(current($result));

      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
      $view_builder_view = $view_builder->view($node, 'default');
      $content = render($view_builder_view);
    }

    return [
      '#theme' => 'sliderblock',
      '#content' => $content,
      '#attached' => [
        'library' => [
          'cci_cpnt_slider/slider'
        ],
        'drupalSettings' => [
          'slider_arrow_path' => '/' . trim($module_path) . '/images/arrow.svg'
        ]
      ],
      '#attributes' => [
        'class' => ['container']
      ]
    ];
  }

  public function getCacheTags() {
    //With this when your node change your block will rebuild
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      //if there is node add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      //Return default tags instead.
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}