<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use MyCore\Http\Response\ResponseFormatTrait;
use Spatie\QueryBuilder\QueryBuilder;
use App\Helpers\General;

class GetOptionsAction extends EloquentAction
{
    use ResponseFormatTrait;
    public function handle(Request $request)
    {
        $data = $request->all();
        $config = $this->config;
        $class = $config['class'];
        $model = new $class;

        $labelKey = $data['labelKey'] ?? 'id';
        $valueKey = $data['valueKey'] ?? 'name';

        $result = QueryBuilder::for($this->getQuery($request))
            ->defaultSort($config['defaultSortOptions'] ?? $config['defaultSort'] ?? '-id')
            ->allowedIncludes($config['allowedIncludes'] ?? [])
            ->allowedFilters($config['allowedFilters'] ?? [])
            ->allowedSorts($config['allowedSorts'] ?? 'id')
            ->limit($request->input('limit', $config['limit'] ?? 100))
            ->pluck($valueKey, $labelKey);

        return $this->responseSuccess(null, $result);
    }
}
