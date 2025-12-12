<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DeleteAction extends EloquentAction
{
    public function handle(Request $request)
    {
        $config = $this->config;
        $class = $config['class'];
        $query = $class::query();
        $result = $query->find($request->input('id'))->delete();
        $this->updateVersion($this->modelName);
        return [
            'result' => $result,
            'message' => 'Delete Successfully!'
        ];
    }
}
