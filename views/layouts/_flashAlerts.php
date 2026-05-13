<?php

use yii\bootstrap5\Html;


?>



<!-- Flash Alerts -->
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
            <?php
            // Map Yii flash types → Tailwind color tokens
            $alertConfig = match($type) { 
                'success' => ['bg' => 'bg-secondary-fixed',      'text' => 'text-on-secondary-fixed', 'icon' => 'check_circle',      'dot' => 'bg-primary'],
                'error',
                'danger'  => ['bg' => 'bg-error-container',      'text' => 'text-on-error-container', 'icon' => 'error',             'dot' => 'bg-error'],
                'warning' => ['bg' => 'bg-tertiary-fixed',        'text' => 'text-on-tertiary-fixed',  'icon' => 'warning',           'dot' => 'bg-tertiary'],
                'info'    => ['bg' => 'bg-primary-fixed',         'text' => 'text-on-primary-fixed',   'icon' => 'info',              'dot' => 'bg-primary'],
                default   => ['bg' => 'bg-surface-container-high','text' => 'text-on-surface-variant', 'icon' => 'notifications',     'dot' => 'bg-outline'],
            };
            $messages = (array) $messages;
            foreach ($messages as $message):
            ?>
            <div
                role="alert"
                class="flex items-start gap-4 mb-4 px-5 py-4 rounded-2xl <?= $alertConfig['bg'] ?> <?= $alertConfig['text'] ?> shadow-sm"
                x-data="{ show: true }"
            >
                <span class="material-symbols-outlined mt-0.5 flex-shrink-0"><?= $alertConfig['icon'] ?></span>
                <span class="flex-1 text-sm font-medium font-['Inter']"><?= Html::encode($message) ?></span>
                <!-- Dismiss button (purely CSS-driven; swap for JS if needed) -->
                <button
                    type="button"
                    class="ml-auto p-1 rounded-lg hover:bg-black/10 transition-colors flex-shrink-0"
                    onclick="this.closest('[role=alert]').remove()"
                    aria-label="Dismiss"
                >
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
