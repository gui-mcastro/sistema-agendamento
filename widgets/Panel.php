<?php

namespace app\widgets;


use yii\bootstrap5\Widget;

class Panel extends Widget
{
    public $title;
    public $idPanel;
    public $titleRight = '';
    public $showActions = false;
    public $customActions = '';
    public $hide = false;
    public $titleNegrito = false;
    public $showPanel = true;
    public $hasError = false;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        ob_start();
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $content = ob_get_clean();
        parent::run();
        return $this->render('panel/_layout-panel', [
          'content' => $content,
          'title' => $this->title,
          'idPanel' => $this->idPanel,
          'titleRight' => $this->titleRight,
          'showActions' => $this->showActions,
          'customActions' => $this->customActions,
          'hide' => $this->hide,
          'titleNegrito' => $this->titleNegrito,
          'showPanel' => $this->showPanel,
          'hasError' => $this->hasError,
        ]);
    }
}
