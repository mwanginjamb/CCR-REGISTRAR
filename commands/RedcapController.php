<?php

namespace app\commands;

use app\services\redcap\DictionaryImporter;
use yii\console\Controller;
use yii\console\ExitCode;

final class RedcapController extends Controller
{
    public bool $force = false;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['force']);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'f' => 'force',
        ]);
    }

    public function actionImportDictionary(
        string $file,
        DictionaryImporter $importer
    ): int {
        try {
            $result = $importer->import($file, $this->force);

            $this->stdout(
                json_encode(
                    $result,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ) . PHP_EOL
            );

            return ExitCode::OK;
        } catch (\Throwable $exception) {
            $this->stderr($exception->getMessage() . PHP_EOL);

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}