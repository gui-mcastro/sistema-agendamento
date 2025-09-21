<?php

/**
 * @var $this         yii\web\View
 * @var $hide         bool
 * @var $titleNegrito bool
 */

?>
<div
  class="panel panel-default
  <?= ($hide ? 'hide' : '') ?>
  <?= ($hasError ? 'has-error' : '') ?>"
>
    <div class="panel-heading">
    <span class="menu-superior collapsed link-menu">
      <a
        class="accordion-toggle<?= $titleNegrito ? ' titulo-painel' : '' ?>"
        data-toggle="collapse"
        data-parent="#accordion"
        href="#<?= $idPanel ?>"
      >
        <span class="glyphicon glyphicon-chevron-down pull-right"></span>
        <?= $title ?>
      </a>
    </span>
        <div
          class="pull-right"
          style="margin-right: 5px"
          id="<?= $idPanel ?>-title-right"
        >
            <?= $titleRight ?>
        </div>
        <?= $showActions ? $this->render('@app/views/site/_lista-acoes') : '' ?>
        <?= !empty($customActions) ? $customActions : '' ?>

    </div>
    <div
      id="<?= $idPanel ?>"
      class="panel-collapse collapse
    <?= $showPanel ? 'in' : '' ?>"
    >
        <div class="panel-body">
            <?= $content ?>
        </div>
    </div>
</div>
