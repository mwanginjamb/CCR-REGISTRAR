<?php
namespace app\library;

class AuthUi {
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
            w-full
            px-4
            py-3
            bg-surface-container
            border-none
            rounded-lg
            focus:ring-2
            focus:ring-primary-container
            transition-all
            text-on-surface
            placeholder:text-outline-variant
            font-medium
        ';
    }

    public static function buttonClass(): string
    {
        return '
            w-full
            py-4
            bg-gradient-to-r
            from-primary
            to-primary-container
            text-on-primary
            font-headline
            font-bold
            text-lg
            rounded-lg
            shadow-lg
            hover:shadow-xl
            transition-all
            active:scale-[0.98]
            flex
            items-center
            justify-center
            gap-3
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


}