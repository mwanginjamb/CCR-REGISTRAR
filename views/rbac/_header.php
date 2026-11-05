<?php

use app\library\FormUi;
use yii\helpers\Html;

/**
 * Page header shared by the RBAC screens.
 *
 * @var yii\web\View $this
 * @var string $title
 * @var string $section    breadcrumb section, e.g. 'Roles'
 * @var string|null $subtitle
 * @var array|null $cta    [label, icon, url]
 */

$this->title = $title;
$this->params['breadcrumbs'][] = Yii::t('app', 'RBAC Mgt');
$this->params['breadcrumbs'][] = $title;
?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
    <div>
        <?= FormUi::breadcrumb(['RBAC Mgt', $section]) ?>
        <h2 class="text-3xl md:text-4xl font-headline font-extrabold text-primary tracking-tight"><?= Html::encode($title) ?></h2>
        <?php if (!empty($subtitle)): ?>
            <p class="text-on-surface-variant mt-2 text-base"><?= Html::encode($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if (!empty($cta)): ?>
        <?= FormUi::ctaButton($cta[0], $cta[1], $cta[2]) ?>
    <?php endif; ?>
</div>
