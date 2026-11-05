<?php

use app\library\FormUi;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Chooses which permissions a permission includes.
 *
 * @var yii\web\View $this
 * @var yii\rbac\Permission $item
 * @var yii\rbac\Permission[] $allItems
 * @var yii\rbac\Item[] $currentChildren
 */

echo $this->render('_header', [
    'title' => 'Included Permissions: ' . $item->name,
    'section' => 'Permissions',
    'subtitle' => 'Anyone granted "' . $item->name . '" also gets the permissions ticked here.',
]);
?>

<section class="bg-surface-container-lowest rounded-2xl p-6 md:p-8 shadow-[0_12px_32px_rgba(0,26,72,0.04)] max-w-4xl">
    <?= Html::beginForm(['manage-hierarchy', 'name' => $item->name], 'post') ?>

    <?= Html::checkboxList(
        'children',
        array_keys($currentChildren),
        ArrayHelper::map($allItems, 'name', 'name'),
        [
            'class' => 'grid grid-cols-1 md:grid-cols-2 gap-3',
            'item' => FormUi::checkboxCard(ArrayHelper::map($allItems, 'name', 'description')),
            'unselect' => '',
        ]
    ) ?>

    <?= $this->render('//setup/_buttons', ['cancelUrl' => ['permissions']]) ?>

    <?= Html::endForm() ?>
</section>
