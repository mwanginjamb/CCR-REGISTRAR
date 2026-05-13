<?php
use yii\bootstrap5\Breadcrumbs;

?>
<!-- Breadcrumbs Widget -->
<?php if (!empty($this->params['breadcrumbs'])): ?>
    <nav class="flex items-center gap-2 text-xs text-on-surface-variant font-medium mb-6">
        <?= Breadcrumbs::widget([
            'links' => $this->params['breadcrumbs'] ?? [],
            'homeLink' => ['label' => 'Home', 'url' => Yii::$app->homeUrl],
            'tag' => false,   // render as flat content, no <ul>
            'itemTemplate' => "<span>{link}</span> > \n",
            'activeItemTemplate' => '<span class="text-on-surface">{link}</span>' . "\n",
            'encodeLabels' => true,
            'options' => ['class' => 'contents'],  // no wrapper element
        ]) ?>
    </nav>
<?php endif; ?>