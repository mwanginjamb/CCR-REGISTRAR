<?php
namespace app\library;

class AuthUi
{
    public static function formConfig($id = 'auth-form'): array
    {
        return [
            'id' => $id,

            'options' => [
                'class' => 'space-y-5 md:space-y-6',
            ],

            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",

                'labelOptions' => [
                    'class' => '
                        block
                        font-label
                        text-sm
                        font-semibold
                        text-on-surface-variant
                        mb-2
                    ',
                ],

                'errorOptions' => [
                    'class' => 'text-sm text-red-600 mt-1',
                ],
            ],
        ];
    }

    public static function inputClass(): string
    {
        return '
            w-full bg-surface-container-low border-none rounded-lg p-3 text-sm md:text-base text-on-surface focus:ring-2 focus:ring-primary transition-all
        ';
    }

    public static function buttonClass(): string
    {
        return '
        w-full
        py-3.5
        md:py-4.5
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

    public static function linkClass(): string
    {
        return '
            text-secondary
            font-bold
            text-sm
            hover:text-on-secondary-fixed-variant
            transition-colors
            underline
            decoration-2
            underline-offset-4
        ';
    }


    /*
   |--------------------------------------------------------------------------
   | Checkbox Field Config
   |--------------------------------------------------------------------------
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



    /*
    |--------------------------------------------------------------------------
    | Checkbox Input Config
    |--------------------------------------------------------------------------
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
                'class' => '
                    flex
                    items-center
                    gap-3
                ',
            ],
        ];
    }


    /**
     * Link element generator
     * Usage:<?= AuthUi::link( 'Back to login',['site/login'],'block text-center mt-6') ?>
     */

    public static function link(
        string $label,
        array $url,
        string $extraClass = ''
    ): string {
        return Html::a($label, $url, [
            'class' => self::linkClass($extraClass),
        ]);
    }





}