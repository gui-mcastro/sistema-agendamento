<?php
/**
 * @link      https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class AppAsset extends AssetBundle
{

    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
      'css/site.css',
    ];

    public $js = [
    ];
    public $jsOptions = [
      'position' => View::POS_HEAD,
      'appendTimestamp' => true
    ];

    public $depends = [
      'yii\web\YiiAsset',
      'yii\web\JqueryAsset',
      'yii\bootstrap5\BootstrapAsset',
      'rmrevin\yii\fontawesome\AssetBundle',
    ];

}
