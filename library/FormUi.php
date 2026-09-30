<?php
namespace app\library;

use yii\helpers\Html;

/**
 * FormUi
 *
 * Centralised Tailwind class and Yii2 ActiveForm / DataTable config factory.
 *
 * Split into two logical sections:
 *
 *   ① Form UI  — ActiveForm config, field templates, inputs, buttons, links
 *   ② Grid UI  — Table shell, th/td/tr classes, badges, chips, action buttons,
 *                stat chips, and CTA helpers for DataTables-powered views
 *
 * ┌──────────────────────────────────────────────────────────────────────┐
 * │  Design decisions                                                    │
 * │                                                                      │
 * │  • All methods are static — no instantiation needed.                 │
 * │  • Class strings use multi-line indentation for readability;         │
 * │    Tailwind's CDN JIT scans PHP strings so all classes resolve.      │
 * │  • badge() / chip() render full <span> HTML so views stay           │
 * │    expression-only: <?= FormUi::badge($label, $variant) ?>          │
 * │  • actionBtn() bakes in the delete confirmation + CSRF method        │
 * │    automatically when intent === 'delete'.                           │
 * └──────────────────────────────────────────────────────────────────────┘
 */
class FormUi
{

    /* ══════════════════════════════════════════════════════════════════════
     │  ①  FORM UI
     ╚══════════════════════════════════════════════════════════════════════ */

    /*
    |--------------------------------------------------------------------------
    | ActiveForm config
    |--------------------------------------------------------------------------
    */

    /**
     * Top-level ActiveForm options.
     *
     * Usage:
     *   $form = ActiveForm::begin(FormUi::formConfig());
     *   $form = ActiveForm::begin(FormUi::formConfig('patient-form'));
     */
    public static function formConfig(string $id = 'auth-form'): array
    {
        return [
            'id'      => $id,
            'options' => [
                'class' => 'space-y-6 md:space-y-8',
            ],
            /*
             * Global default field config. Override per-field with:
             *   $form->field($model, 'email', FormUi::fieldConfig('mail'))
             */
            'fieldConfig' => self::fieldConfig(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Field config — with optional icon support
    |--------------------------------------------------------------------------
    */

    /**
     * Returns an ActiveField config array.
     *
     * When $icon is supplied the template wraps {input} in a relative
     * container with a left-anchored Material Symbol.
     *
     * @param string|null $icon  Material Symbols name, e.g. 'mail', 'lock',
     *                           'person', 'badge', 'phone'.
     *
     * Usage:
     *   $form->field($model, 'username', FormUi::fieldConfig())
     *   $form->field($model, 'email',    FormUi::fieldConfig('mail'))
     *   $form->field($model, 'password', FormUi::fieldConfig('lock'))
     */
    public static function fieldConfig(?string $icon = null): array
    {
        $template = $icon
            ? self::iconTemplate($icon)
            : "{label}\n{input}\n{error}";

        return [
            'template' => $template,

            'options' => [
                'class' => 'space-y-1.5 md:space-y-2',
            ],

            'labelOptions' => [
                'class' => '
                    block
                    font-label
                    text-sm
                    md:text-base
                    font-medium
                    text-on-surface-variant
                    ml-1
                ',
            ],

            'errorOptions' => [
                'class' => 'text-sm text-red-600 mt-1 ml-1',
            ],
        ];
    }

    /**
     * Builds the Yii2 ActiveField template string for an icon field.
     *
     * The .input-group class triggers the CSS rule in your layout that
     * shifts the icon colour on :focus-within — no JS required.
     */
    private static function iconTemplate(string $icon): string
    {
        $iconHtml = Html::tag(
            'span',
            Html::encode($icon),
            ['class' => self::iconClass()]
        );

        $iconWrapper = Html::tag(
            'div',
            $iconHtml,
            ['class' => 'absolute inset-y-0 left-0 pl-3 md:pl-4 flex items-center pointer-events-none']
        );

        $inputWrapper = Html::tag(
            'div',
            $iconWrapper . "\n{input}",
            ['class' => 'relative input-group']
        );

        return "{label}\n" . $inputWrapper . "\n{error}";
    }


    /*
    |--------------------------------------------------------------------------
    | Input class
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for <input> / <select> elements.
     *
     * @param bool $hasIcon  Widens left-padding to clear the icon glyph.
     *
     * Usage:
     *   ->textInput(['class' => FormUi::inputClass()])
     *   ->textInput(['class' => FormUi::inputClass(true)])
     *   ->passwordInput(['class' => FormUi::inputClass(true)])
     */
    public static function inputClass(bool $hasIcon = false): string
    {
        $leading = $hasIcon ? 'pl-10 md:pl-12 pr-4' : 'pl-4 pr-4';

        return "
            w-full
            {$leading}
            py-3
            md:py-4
            bg-surface-container-low
            border-none
            rounded-lg
            text-on-surface
            text-sm
            md:text-base
            placeholder:text-outline/50
            focus:ring-2
            focus:ring-primary/20
            focus:bg-white
            transition-all
            duration-200
        ";
    }


    /*
    |--------------------------------------------------------------------------
    | Icon class (inside input-group)
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for the Material Symbol span inside an input-group.
     */
    public static function iconClass(): string
    {
        return '
            input-icon
            material-symbols-outlined
            text-outline
            text-lg
            md:text-xl
            transition-colors
            duration-200
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | Primary submit / CTA button (full-width form variant)
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for the full-width submit button used in forms.
     *
     * Usage:
     *   Html::submitButton('Sign In', ['class' => FormUi::buttonClass()])
     */
    public static function buttonClass(): string
    {
        return '
            w-full
            py-3.5
            md:py-4
            px-4
            bg-gradient-to-br
            from-primary
            to-primary-container
            text-on-primary
            font-headline
            font-bold
            text-base
            md:text-lg
            rounded-lg
            shadow-[0_8px_20px_rgba(0,26,72,0.15)]
            hover:shadow-[0_12px_24px_rgba(0,26,72,0.25)]
            hover:scale-[1.01]
            active:scale-95
            transition-all
            duration-150
            flex
            items-center
            justify-center
            gap-2
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | Link helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for inline anchor links.
     */
    public static function linkClass(): string
    {
        return '
            font-bold
            text-primary
            hover:underline
            underline-offset-4
            transition-colors
            duration-150
        ';
    }

    /**
     * Renders a styled <a> tag.
     *
     * Usage:
     *   <?= FormUi::link('Back to Login', ['site/login']) ?>
     */
    public static function link(string $label, array $url, string $extraClass = ''): string
    {
        return Html::a($label, $url, [
            'class' => trim(self::linkClass() . ' ' . $extraClass),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Divider ("Or" separator)
    |--------------------------------------------------------------------------
    */

    /**
     * Renders a horizontal divider with a centred label.
     *
     * Usage:
     *   <?= FormUi::divider() ?>
     *   <?= FormUi::divider('or continue with') ?>
     */
    public static function divider(string $label = 'Or'): string
    {
        return '
            <div class="mt-8 md:mt-10 flex items-center justify-center space-x-2">
                <div class="h-[1px] flex-1 bg-surface-container-high"></div>
                <span class="font-label text-xs md:text-sm text-outline uppercase tracking-widest">'
                    . Html::encode($label) . '
                </span>
                <div class="h-[1px] flex-1 bg-surface-container-high"></div>
            </div>
        ';
    }


    /*
    |--------------------------------------------------------------------------
    | Checkbox field config
    |--------------------------------------------------------------------------
    */

    /**
     * ActiveField config for checkbox fields.
     */
    public static function checkboxFieldConfig(): array
    {
        return [
            'template'     => "{input}\n{error}",
            'options'      => ['class' => 'mb-0'],
            'errorOptions' => ['class' => 'text-sm text-red-600 mt-2'],
        ];
    }

    /**
     * Options array for ->checkbox() calls.
     *
     * Usage:
     *   $form->field($model, 'rememberMe', FormUi::checkboxFieldConfig())
     *        ->checkbox(FormUi::checkboxConfig('Remember me for 30 days'))
     */
    public static function checkboxConfig(string $label): array
    {
        return [
            'label' => $label,

            'labelOptions' => [
                'class' => 'text-sm text-on-surface-variant select-none cursor-pointer',
            ],

            'class' => '
                w-4 h-4 rounded border-outline
                text-primary focus:ring-primary-container
                bg-surface-container-lowest
            ',

            'container' => [
                'class' => 'flex items-center gap-3',
            ],
        ];
    }


    /* ══════════════════════════════════════════════════════════════════════
     │  ②  GRID / DATATABLE UI
     ╚══════════════════════════════════════════════════════════════════════ */

    /*
    |--------------------------------------------------------------------------
    | Grid container shell
    |--------------------------------------------------------------------------
    */

    /**
     * Outer wrapper class for the DataTables grid panel.
     * Provides the card surface, rounded corners, shadow, and border.
     *
     * Usage:
     *   <div class="<?= FormUi::gridContainerClass() ?>">
     */
    public static function gridContainerClass(): string
    {
        return 'bg-surface-container-low rounded-2xl overflow-hidden shadow-sm border border-surface-variant/10';
    }

    /**
     * Toolbar row above the table (export button slot + search input).
     *
     * Usage:
     *   <div class="<?= FormUi::gridToolbarClass() ?>">
     */
    public static function gridToolbarClass(): string
    {
        return 'px-4 md:px-6 pt-5 pb-3 '
             . 'flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 '
             . 'border-b border-surface-variant/20';
    }

    /**
     * Tailwind classes for the search <input> inside the toolbar.
     * Assumes a Material Symbol icon is absolutely positioned to its left;
     * the view must supply the wrapper div and the icon span.
     *
     * Usage:
     *   <input class="<?= FormUi::gridSearchClass() ?>" ... />
     */
    public static function gridSearchClass(): string
    {
        return 'w-full bg-surface-container border border-surface-variant rounded-xl '
             . 'py-2 pl-10 pr-4 text-sm '
             . 'focus:ring-2 focus:ring-primary/30 focus:border-primary/50 '
             . 'outline-none transition';
    }

    /**
     * Grid footer bar (DataTables info text + pagination row).
     *
     * Usage:
     *   <div class="<?= FormUi::gridFooterClass() ?>">
     */
    public static function gridFooterClass(): string
    {
        return 'bg-surface-container px-4 md:px-6 py-4 '
             . 'flex flex-col sm:flex-row items-center justify-between gap-4 '
             . 'border-t border-surface-variant/20';
    }


    /*
    |--------------------------------------------------------------------------
    | Table cell classes
    |--------------------------------------------------------------------------
    */

    /**
     * <th> header cell classes.
     *
     * @param bool $rightAlign  Pass true for the Actions column.
     *
     * Usage:
     *   <th class="<?= FormUi::thClass() ?>">Full Name</th>
     *   <th class="<?= FormUi::thClass(true) ?>">Actions</th>
     */
    public static function thClass(bool $rightAlign = false): string
    {
        return trim(
            'px-6 py-4 text-[11px] font-bold text-on-surface-variant '
          . 'uppercase tracking-widest whitespace-nowrap '
          . ($rightAlign ? 'text-right' : '')
        );
    }

    /**
     * <tr> body row classes — hover highlight via Tailwind group.
     *
     * Usage:
     *   <tr class="<?= FormUi::trClass() ?>">
     */
    public static function trClass(): string
    {
        return 'bg-surface-container-lowest hover:bg-surface-tint/[0.03] transition-colors group';
    }

    /**
     * <td> body cell classes — three visual variants.
     *
     * @param string $variant
     *   'primary' — bold, brand-coloured; use for IDs / key identifiers
     *   'muted'   — subdued on-surface-variant; use for dates and meta
     *   'default' — standard body text
     *
     * Usage:
     *   <td class="<?= FormUi::tdClass('primary') ?>">PT-00042</td>
     *   <td class="<?= FormUi::tdClass('muted')   ?>">12 Jan 2024</td>
     *   <td class="<?= FormUi::tdClass()          ?>">Nairobi</td>
     */
    public static function tdClass(string $variant = 'default'): string
    {
        return 'px-6 py-5 ' . match($variant) {
            'primary' => 'font-headline font-bold text-primary text-sm tracking-tight',
            'muted'   => 'text-sm text-on-surface-variant font-medium whitespace-nowrap',
            default   => 'text-sm text-on-surface',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Badges and chips
    |--------------------------------------------------------------------------
    */

    /**
     * Renders a compact pill badge <span> — for status, category, or enum values.
     *
     * @param string $label    Display text (HTML-escaped internally).
     * @param string $variant  'secondary' | 'tertiary' | 'error' | 'warning' | 'default'
     *
     * Colour map (MD3 tokens):
     *   secondary → bg-secondary-container / text-on-secondary-container  (blue-teal)
     *   tertiary  → bg-tertiary-fixed / text-on-tertiary-fixed-variant     (warm sand)
     *   error     → bg-error-container / text-on-error-container           (red)
     *   warning   → amber tones (custom; no MD3 token in this palette)
     *   default   → bg-surface-container / text-on-surface-variant        (neutral)
     *
     * Usage:
     *   <?= FormUi::badge('Stage IV', 'error') ?>
     *   <?= FormUi::badge('African',  'secondary') ?>
     */
    public static function badge(string $label, string $variant = 'default'): string
    {
        $colors = match($variant) {
            'secondary' => 'bg-secondary-container text-on-secondary-container',
            'tertiary'  => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
            'error'     => 'bg-error-container text-on-error-container',
            'warning'   => 'bg-[#fff3cd] text-[#7a5c00]',
            default     => 'bg-surface-container text-on-surface-variant',
        };

        return Html::tag('span', Html::encode($label), [
            'class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {$colors}",
        ]);
    }

    /**
     * Renders a wider pill chip — for taxonomy / category labels (e.g. ICD codes,
     * ethnic group, place names). Slightly more padding than badge().
     *
     * @param string $label
     * @param string $variant  Same variants as badge().
     *
     * Usage:
     *   <?= FormUi::chip('C50.9 — Breast, NOS') ?>
     *   <?= FormUi::chip('Nairobi', 'secondary') ?>
     */
    public static function chip(string $label, string $variant = 'default'): string
    {
        $colors = match($variant) {
            'secondary' => 'bg-secondary-container text-on-secondary-container',
            'tertiary'  => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
            'error'     => 'bg-error-container text-on-error-container',
            'warning'   => 'bg-[#fff3cd] text-[#7a5c00]',
            default     => 'bg-surface-container text-primary',
        };

        return Html::tag('span', Html::encode($label), [
            'class' => "inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {$colors}",
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Action buttons (grid row: view / edit / delete)
    |--------------------------------------------------------------------------
    */

    /**
     * Renders a single icon action button for a grid row.
     *
     * Bakes in:
     *   • Hover colour + background per intent
     *   • CSRF method:post + JS confirm dialog when intent === 'delete'
     *
     * @param string $icon    Material Symbols icon name, e.g. 'visibility'.
     * @param array  $url     Yii2 URL array, e.g. ['view', 'id' => $model->id].
     * @param string $intent  'view' | 'edit' | 'delete'
     * @param array  $options Extra Html::a options (merged after defaults;
     *                        use to override title, add data-* attrs, etc.).
     *
     * Usage:
     *   <?= FormUi::actionBtn('visibility', ['view',   'id' => $m->id], 'view')   ?>
     *   <?= FormUi::actionBtn('edit_square',['update', 'id' => $m->id], 'edit')   ?>
     *   <?= FormUi::actionBtn('delete',     ['delete', 'id' => $m->id], 'delete') ?>
     */
    public static function actionBtn(
        string $icon,
        array  $url,
        string $intent  = 'view',
        array  $options = []
    ): string {
        $intentClass = match($intent) {
            'edit'   => 'p-2 text-outline hover:text-secondary hover:bg-surface-container rounded-lg transition-all',
            'delete' => 'p-2 text-outline hover:text-error hover:bg-error-container rounded-lg transition-all',
            default  => 'p-2 text-outline hover:text-primary hover:bg-surface-container rounded-lg transition-all',
        };

        $iconSpan = Html::tag('span', $icon, ['class' => 'material-symbols-outlined text-[20px]']);

        $defaults = ['class' => $intentClass, 'encode' => false];

        if ($intent === 'delete') {
            $defaults['data'] = [
                'confirm' => 'Are you sure you want to delete this record?',
                'method'  => 'post',
            ];
        }

        return Html::a($iconSpan, $url, array_merge($defaults, $options));
    }

    /**
     * Renders a condensed (smaller icon) action button for mobile card views.
     * Same signature as actionBtn(); icon renders at text-sm instead of text-[20px].
     *
     * Usage:
     *   <?= FormUi::actionBtnSm('visibility', ['view', 'id' => $m->id]) ?>
     */
    public static function actionBtnSm(
        string $icon,
        array  $url,
        string $intent  = 'view',
        array  $options = []
    ): string {
        $intentClass = match($intent) {
            'edit'   => 'p-2 text-outline hover:text-secondary',
            'delete' => 'p-2 text-outline hover:text-error',
            default  => 'p-2 text-outline hover:text-primary',
        };

        $iconSpan = Html::tag('span', $icon, ['class' => 'material-symbols-outlined text-sm']);
        $defaults = ['class' => $intentClass, 'encode' => false];

        if ($intent === 'delete') {
            $defaults['data'] = [
                'confirm' => 'Are you sure you want to delete this record?',
                'method'  => 'post',
            ];
        }

        return Html::a($iconSpan, $url, array_merge($defaults, $options));
    }


    /*
    |--------------------------------------------------------------------------
    | CTA page-header button ("New Patient", "New Abstract", etc.)
    |--------------------------------------------------------------------------
    */

    /**
     * Renders the gradient "New …" button used in page headers.
     *
     * @param string $label  Visible text, e.g. 'New Patient'.
     * @param string $icon   Material Symbol name, e.g. 'add', 'person_add'.
     * @param array  $url    Yii2 URL array.
     *
     * Usage:
     *   <?= FormUi::ctaButton('New Patient', 'person_add', ['patient/create']) ?>
     */
    public static function ctaButton(string $label, string $icon, array $url): string
    {
        $inner = Html::tag('span', $icon,         ['class' => 'material-symbols-outlined'])
               . Html::tag('span', Html::encode($label));

        return Html::a($inner, $url, [
            'class'  => 'inline-flex items-center justify-center gap-2 px-6 py-3.5 '
                      . 'bg-gradient-to-br from-primary to-primary-container text-white '
                      . 'rounded-xl font-bold shadow-[0_12px_32px_rgba(0,26,72,0.12)] '
                      . 'hover:scale-[1.02] transition-all active:scale-95 w-full sm:w-auto',
            'encode' => false,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Stat chip — summary metric cards above the grid
    |--------------------------------------------------------------------------
    */

    /**
     * Renders a stat chip card (icon circle + label + value).
     *
     * @param string $icon       Material Symbol name, e.g. 'group'.
     * @param string $label      Small uppercase descriptor, e.g. 'Total Patients'.
     * @param string $value      Display value, e.g. '12,842'.
     * @param string $iconBg     Tailwind bg class for the icon circle.
     * @param string $iconColor  Tailwind text class for the icon.
     *
     * Usage:
     *   <?= FormUi::statChip('group', 'Total Patients', number_format($count)) ?>
     *   <?= FormUi::statChip(
     *         'pending_actions', 'Pending Review', '48',
     *         'bg-tertiary-fixed', 'text-on-tertiary-fixed-variant'
     *       ) ?>
     */
    public static function statChip(
        string $icon,
        string $label,
        string $value,
        string $iconBg    = 'bg-secondary-container',
        string $iconColor = 'text-on-secondary-container'
    ): string {
        $circle = Html::tag(
            'div',
            Html::tag('span', $icon, ['class' => 'material-symbols-outlined']),
            ['class' => "h-10 w-10 rounded-full {$iconBg} flex items-center justify-center {$iconColor} shrink-0"]
        );

        $text = Html::tag('div',
            Html::tag('p', Html::encode($label),
                ['class' => 'text-[10px] uppercase font-bold text-outline-variant tracking-wider'])
          . Html::tag('p', Html::encode($value),
                ['class' => 'text-xl font-headline font-bold text-primary']),
        []);

        return Html::tag('div', $circle . $text, [
            'class' => 'bg-surface-container-low p-4 rounded-xl flex items-center gap-4 border border-surface-variant/10',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Breadcrumb helper
    |--------------------------------------------------------------------------
    */

    /**
     * Renders a minimal breadcrumb trail.
     *
     * @param array $crumbs  [ 'Database', 'Patients' ] — last item is styled as active.
     *
     * Usage:
     *   <?= FormUi::breadcrumb(['Database', 'Patients']) ?>
     */
    public static function breadcrumb(array $crumbs): string
    {
        $parts = [];
        $last  = array_pop($crumbs);

        foreach ($crumbs as $crumb) {
            $parts[] = Html::tag('span', Html::encode($crumb));
            $parts[] = Html::tag('span', 'chevron_right', [
                'class' => 'material-symbols-outlined text-[10px]',
            ]);
        }

        $parts[] = Html::tag('span', Html::encode($last), ['class' => 'text-primary']);

        return Html::tag('nav', implode('', $parts), [
            'class' => 'flex items-center gap-2 text-xs font-label font-medium text-outline mb-3 uppercase tracking-widest',
        ]);
    }
}