<?php

use app\components\ReviewInbox;
use yii\helpers\Html;

/**
 * Header notification bell: number of review items waiting on the current
 * user (see app\components\ReviewInbox), linking to the flagged-case list.
 *
 * @var yii\web\View $this
 * @var string $buttonClass  classes for the round icon button (per layout)
 */

$scope = ReviewInbox::scopeForCurrentUser();
if ($scope === null) {
    return;
}

$count = (new ReviewInbox())->countForCurrentUser();
$label = ReviewInbox::describe($scope, $count);
?>
<?= Html::a(
    '<span class="material-symbols-outlined">' . ($count > 0 ? 'notifications_active' : 'notifications') . '</span>'
    . ($count > 0
        ? '<span class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 rounded-full bg-error text-on-error text-[11px] font-bold leading-5 text-center ring-2 ring-[#f2f4f6]" aria-hidden="true">'
            . ($count > 99 ? '99+' : $count) . '</span>'
        : ''),
    ['/patient/index', 'review' => 1],
    [
        'class' => 'relative ' . $buttonClass,
        'title' => $label,
        'aria-label' => $label,
        'encode' => false,
    ]
) ?>
