<?php

/**
 * @file
 * Views hooks.
 */

use Drupal\views_advance_date_filter\Plugin\views\filter\ViewsExtraFilterDate;
use Drupal\views_advance_date_filter\Plugin\views\filter\ViewsExtraFilterDatetime;

/**
 * Implements hook_views_plugins_filter_alter().
 */
function views_advance_date_filter_views_plugins_filter_alter(&$info) {
  if (isset($info['date'])) {
    $info['date']['class'] = ViewsExtraFilterDate::class;
  }
  if (isset($info['datetime'])) {
    $info['datetime']['class'] = ViewsExtraFilterDatetime::class;
  }
}
