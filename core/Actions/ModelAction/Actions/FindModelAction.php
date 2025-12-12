<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FindModelAction extends EloquentAction
{
    public function handle(Request $request)
    {
        $config = $this->config;

        return QueryBuilder::for($this->getQuery($request))
            ->defaultSort($config['defaultSort'] ?? '-id')
            ->allowedIncludes($config['allowedIncludes'] ?? [])
            ->allowedFilters($config['allowedFilters'] ?? [])
            ->allowedSorts($config['allowedSorts'] ?? 'id')
            ->first();
    }
}
