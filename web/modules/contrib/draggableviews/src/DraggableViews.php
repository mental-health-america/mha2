<?php

namespace Drupal\draggableviews;

use Drupal\views\ViewExecutable;
use Drupal\Component\Utility\Html;

/**
 * Class DraggableViews.
 */
class DraggableViews {

  /**
   * The view.
   *
   * @var \Drupal\views\ViewExecutable
   */
  public $view;

  /**
   * Constructs DraggableViewsRows object.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   Views object.
   */
  public function __construct(ViewExecutable $view) {
    $this->view = $view;
  }

  /**
   * Get index by name and id.
   */
  public function getIndex($name, $id) {
    foreach ($this->view->result as $item) {
      if ($item->$name == $id) {
        return $item->index;
      }
    }
    return FALSE;
  }

  /**
   * Get depth by index.
   */
  public function getDepth($index) {
    if (!isset($this->view->result[$index])) {
      return FALSE;
    }
    $row = $this->view->result[$index];
    // If parent is available, set parent's depth +1.
    return (!empty($row->draggableviews_structure_parent)) ? $this->getDepth($this->getIndex('nid', $row->draggableviews_structure_parent)) + 1 : 0;
  }

  /**
   * Get parent by index.
   */
  public function getParent($index) {
    return isset($this->view->result[$index]->draggableviews_structure_parent) ? $this->view->result[$index]->draggableviews_structure_parent : 0;
  }

  /**
   * Get ancestor by index.
   */
  public function getAncestor($index) {
    $row = $this->view->result[$index];
    return !empty($row->draggableviews_structure_parent) ? $this->getAncestor($this->getIndex('nid', $row->draggableviews_structure_parent)) : $index;
  }

  /**
   * Return value by it's name and index.
   */
  public function getValue($name, $index) {
    return $this->view->result[$index]->$name;
  }

  /**
   * Return array of field groups titles.
   */
  public function fieldGrouping() {
    $fieldGrouping = [];
    $sets = $this->view->style_plugin->renderGrouping($this->view->result, $this->view->style_plugin->options['grouping'], FALSE);
    $flatten_sets = $this->flattenGroups($sets);
    foreach ($flatten_sets as $title => $rows) {
      $fieldGrouping[] = $title;
    }

    return $fieldGrouping;
  }

  /**
   * Get HTML id for draggableviews table.
   */
  public function getHtmlId($index) {
    return Html::getId('draggableviews-table-' . $this->view->id() . '-' . $this->view->current_display . '-' . $index);
  }

  /**
   * Recursively flatten groups.
   *
   * @param array $sets
   *   Result set.
   *
   * @return array
   *   List of groups keyed by original key.
   */
  protected static function flattenGroups($sets) {
    $flatten = [];

    foreach ($sets as $key => $set) {
      $set_rows = $set['rows'];
      if (!is_numeric(key($set_rows))) {
        $subsets = self::flattenGroups($set_rows);
        if ($subsets) {
          $flatten = array_merge($flatten, $subsets);
        }
      }
      else {
        $flatten[$key] = $set_rows;
      }
    }

    return $flatten;
  }

}
