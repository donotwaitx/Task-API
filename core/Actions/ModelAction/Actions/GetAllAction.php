<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class GetAllAction extends EloquentAction
{
	public function handle(Request $request)
	{
		$config = $this->config;
		$select = '*';
		if ($request->input('select')) {
			$select = explode(',', $request->input('select'));
		}
		$result = QueryBuilder::for($this->getQuery($request))
			->defaultSort($config['defaultSort'] ?? '-id')
			->allowedIncludes($config['allowedIncludes'] ?? [])
			->allowedFilters($config['allowedFilters'] ?? [])
			->allowedSorts($config['allowedSorts'] ?? 'id')
			->groupBy(\DB::raw($config['groupBy'] ?? 'id'))
			->select($select)
			->limit($request->input('limit', $config['limit'] ?? 100))
			->get();
		return $this->getResponse($result, $request);
	}
}
