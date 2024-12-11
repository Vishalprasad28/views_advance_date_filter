<?php

namespace Drupal\views_date_extra\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\views\filter\Date as DateTime;
use Drupal\views_date_extra\Traits\DateViewsExtraTrait;

/**
 * Date/time views filter.
 *
 * Even thought dates are stored as strings, the numeric filter is extended
 * because it provides more sensible operators.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("datetime_with_more_options")
 */
class ViewsExtraFilterDatetime extends DateTime {

  use StringTranslationTrait;
  use DateViewsExtraTrait;

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    if (!$form_state->get('exposed')) {
      $form['value']['type']['#options']['date_year'] = $this->t('A date in CCYY format.');
      $form['value']['type']['#options']['date_month'] = $this->t('A date in CCMM format.');
      $form['value']['type']['#options']['date_quarter'] = $this->t('A date in Quarterly format.');
      // Add js to handle year filter state.
      $form['#attached']['library'][] = 'views_date_extra/year_filter';
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opSimple($field) {
    // If year filter selected.
    if (
      !empty($this->value['type']) && in_array($this->value['type'], array_keys($this->filterMappedOperator)) &&
      isset($this->value['value'])
    ) {
      $value = ltrim($this->value['value'], '0') ?? '';
      if ($this->value['type'] === 'date_quarter') {
        $value = $this->quartersMapping[$value];
      }
      elseif ($this->value['type'] === 'date_month') {
        $value = $this->monthMapping[$value];
      }
      $date_operator = $this->filterMappedOperator[$this->value['type']];
      // Get the value.
      // In Case of changed, created and published on date is timestamp.
      if (
          strpos($field, '.changed') !== FALSE ||
          strpos($field, '.created') !== FALSE ||
          strpos($field, '.published_at') !== FALSE
        ) {
        $this->query->addWhereExpression($this->options['group'], "$date_operator(FROM_UNIXTIME($field)) $this->operator $value");
      }
      else {
        $this->query->addWhereExpression($this->options['group'], "$date_operator($field) $this->operator $value");
      }
    }
    else {
      parent::opSimple($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opBetween($field) {
    // If year filter selected.
    if (
      !empty($this->value['type']) && in_array($this->value['type'], array_keys($this->filterMappedOperator)) &&
      isset($this->value['min']) &&
      isset($this->value['max'])
    ) {
      $min = ltrim($this->value['min'], '0') ?? 0;
      $max = ltrim($this->value['max'], '0') ?? 0;
      $operator = strtoupper($this->operator);
      $date_operator = $this->filterMappedOperator[$this->value['type']];
      // In Case of changed, created and published on date is timestamp.
      if (
        strpos($field, '.changed') !== FALSE ||
        strpos($field, '.created') !== FALSE ||
        strpos($field, '.published_at') !== FALSE
      ) {
        $this->query->addWhereExpression($this->options['group'], "$date_operator(FROM_UNIXTIME($field)) $operator $min AND $max");
      }
      else {
        $this->query->addWhereExpression($this->options['group'], "$date_operator($field) $operator $min AND $max");
      }
    }
    else {
      parent::opBetween($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    if (
      !empty($this->value['type']) && in_array($this->value['type'], array_keys($this->filterMappedOperator))
    ) {
      if ($this->value['type'] == 'date_year') {
        parent::buildExposedForm($form, $form_state);
        $this->applyDatePopupToForm($form);
      }
      elseif ($this->value['type'] == 'date_month') {
        $form[$this->options['expose']['identifier']] = [
          '#type' => 'select',
          '#title' => $this->t('Month'),
          '#options' => $this->months,
        ];
      }
      elseif ($this->value['type'] == 'date_quarter') {
        $form[$this->options['expose']['identifier']] = [
          '#type' => 'select',
          '#title' => $this->t('Month'),
          '#options' => $this->quarters,
        ];
      }
    }
    else {
      parent::buildExposedForm($form, $form_state);
    }
  }

}
