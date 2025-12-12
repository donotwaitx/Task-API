<?php

namespace MyCore\Actions\ModelAction;

use MyCore\Actions\ModelAction\Actions\GetOptionsAction;

class ModelActions
{
    public static function createActions($modelName, $config)
    {
        $actions=[];
        if (!empty($config['class'])) {
            $actions = [
                'options' => new GetOptionsAction($modelName, $config)
            ];
        }
        return array_merge($actions, $config['actions'] ?? []);
    }
}
