<?php
namespace app\library;

use yii\helpers\Html;

/**
 * AuthUi
 *
 * Centralised Tailwind class and Yii2 ActiveForm config factory for all
 * guest / authentication views (login, register, forgot-password, etc.).
 *
 * Designed to be consumed alongside the `guest` layout which provides the
 * page shell, brand header, card wrapper, and footer.
 *
 * ┌──────────────────────────────────────────────────────────────────────┐
 * │  Key design decisions                                                │
 * │                                                                      │
 * │  • fieldConfig($icon)   — template embeds a Material Symbol icon     │
 * │    directly into the {input} wrapper so no extra markup is needed    │
 * │    in the view. Pass null for plain fields (no icon).                │
 * │                                                                      │
 * │  • inputClass($hasIcon) — left-padding widens automatically when an  │
 * │    icon is present so text never collides with the symbol.           │
 * │                                                                      │
 * │  • buttonClass()        — corrected gradient direction (to-br),      │
 * │    brand-coloured shadows, and responsive sizing to match the        │
 * │    boilerplate's btn-gradient spec.                                  │
 * └──────────────────────────────────────────────────────────────────────┘
 */
class FormUi
{

    /*
    |--------------------------------------------------------------------------
    | ActiveForm config
    |--------------------------------------------------------------------------
    */

    /**
     * Top-level ActiveForm options.
     *
     * Usage:
     *   $form = ActiveForm::begin(AuthUi::formConfig());
     */
    public static function formConfig(string $id = 'auth-form'): array
    {
        return [
            'id' => $id,
            'options' => [
                'class' => 'space-y-6 md:space-y-8',
            ],

            /*
             * fieldConfig here acts as the *global default* for every field
             * in the form. Views that need an icon should call
             * fieldConfig($icon) on the individual field instead:
             *
             *   $form->field($model, 'email', AuthUi::fieldConfig('mail'))
             *        ->textInput(...)
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
     * container with a left-anchored Material Symbol, matching the
     * boilerplate's .input-group pattern exactly.
     *
     * @param string|null $icon  Material Symbols name, e.g. 'mail', 'lock',
     *                           'person', 'badge', 'phone'. Pass null (default)
     *                           for a plain field without an icon.
     *
     * Usage — plain field:
     *   $form->field($model, 'username', AuthUi::fieldConfig())
     *
     * Usage — icon field:
     *   $form->field($model, 'email',    AuthUi::fieldConfig('mail'))
     *   $form->field($model, 'password', AuthUi::fieldConfig('lock'))
     */
    public static function fieldConfig(?string $icon = null): array
    {
        $template = $icon
            ? self::iconTemplate($icon)
            : "{label}\n{input}\n{error}";

        return [
            'template' => $template,

            'options' => [
                // Outer wrapper spacing — consumed by the form's space-y
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
     * Builds the full Yii2 ActiveField template string for an icon field.
     *
     * The .input-group class on the relative wrapper triggers the CSS rule
     * in guest.php that shifts the icon colour on focus-within, so no JS
     * is required.
     *
     * @param string $icon  Material Symbols icon name.
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
            [
                'class' => 'absolute inset-y-0 left-0 pl-3 md:pl-4
                         flex items-center pointer-events-none'
            ]
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
    | Input class — adapts left-padding for icon presence
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for <input> elements.
     *
     * @param bool $hasIcon  Set true when the field was created with an icon
     *                       via fieldConfig($icon). Widens the left padding so
     *                       typed text does not overlap the Material Symbol.
     *
     * Usage:
     *   ->textInput(['class' => AuthUi::inputClass()])          // plain
     *   ->textInput(['class' => AuthUi::inputClass(true)])      // with icon
     *   ->passwordInput(['class' => AuthUi::inputClass(true)])  // password
     */
    public static function inputClass(bool $hasIcon = false): string
    {
        // Left padding: pl-4 without icon → pl-10 md:pl-12 with icon
        $leading = $hasIcon
            ? 'pl-10 md:pl-12 pr-4'
            : 'pl-4 pr-4';

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
    | Icon class
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for the Material Symbol span inside an input-group.
     *
     * The colour transition is handled by the CSS rule in guest.php:
     *   .input-group:focus-within .input-icon { color: var(--primary); }
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
    | Button class — CTA primary action
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for the primary submit button.
     *
     * Gradient direction is `to-br` (bottom-right) to match the boilerplate's
     * `.btn-gradient` spec. Shadows use brand-primary rgba values for
     * chromatic depth rather than generic Tailwind shadow utilities.
     *
     * Usage:
     *   Html::submitButton('Sign In <span …>login</span>', [
     *       'class' => AuthUi::buttonClass(),
     *   ])
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
    | Link class
    |--------------------------------------------------------------------------
    */

    /**
     * Tailwind classes for inline anchor links (e.g. "Forgot Password?",
     * "Request Access").
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
     * Renders a styled anchor tag.
     *
     * Usage:
     *   <?= AuthUi::link('Back to Login', ['site/login']) ?>
     *   <?= AuthUi::link('Request Access', ['site/register'], 'ml-1') ?>
     */
    public static function link(
        string $label,
        array $url,
        string $extraClass = ''
    ): string {
        return Html::a($label, $url, [
            'class' => trim(self::linkClass() . ' ' . $extraClass),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Divider helper (the "Or" separator in the boilerplate)
    |--------------------------------------------------------------------------
    */

    /**
     * Renders the horizontal "Or" divider used between the form and
     * secondary navigation links.
     *
     * Usage:
     *   <?= AuthUi::divider() ?>
     *   <?= AuthUi::divider('or continue with') ?>
     */
    public static function divider(string $label = 'Or'): string
    {
        return '
            <div class="mt-8 md:mt-10 flex items-center justify-center space-x-2">
                <div class="h-[1px] flex-1 bg-surface-container-high"></div>
                <span class="font-label text-xs md:text-sm text-outline
                             uppercase tracking-widest">
                    ' . Html::encode($label) . '
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
     * ActiveField config for checkbox fields (e.g. "Remember me").
     * No icon is used for checkboxes; the template omits the label token
     * because Yii2 renders the label inline with the checkbox by default.
     */
    public static function checkboxFieldConfig(): array
    {
        return [
            'template' => "{input}\n{error}",

            'options' => [
                'class' => 'mb-0',
            ],

            'errorOptions' => [
                'class' => 'text-sm text-red-600 mt-2',
            ],
        ];
    }

    /**
     * Options array for ->checkbox() calls.
     *
     * Usage:
     *   $form->field($model, 'rememberMe', AuthUi::checkboxFieldConfig())
     *        ->checkbox(AuthUi::checkboxConfig('Remember me for 30 days'))
     */
    public static function checkboxConfig(string $label): array
    {
        return [
            'label' => $label,

            'labelOptions' => [
                'class' => '
                    text-sm
                    text-on-surface-variant
                    select-none
                    cursor-pointer
                ',
            ],

            'class' => '
                w-4
                h-4
                rounded
                border-outline
                text-primary
                focus:ring-primary-container
                bg-surface-container-lowest
            ',

            'container' => [
                'class' => 'flex items-center gap-3',
            ],
        ];
    }
}