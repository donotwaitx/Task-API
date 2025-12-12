<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FindByAction extends EloquentAction
{
	public function handle(Request $request)
	{
		$config = $this->config;
		if (!$config) {
			throw new \Exception('Model not yet registered!');
		}

		// if $request not isset key start at filter, then return empty array
		if (!isset($request->filter)) {
			return [];
		}

		$result = QueryBuilder::for($this->getQuery($request), $request)
			->defaultSort($config['defaultSort'] ?? '-id')
			->allowedIncludes($config['allowedIncludes'] ?? [])
			->allowedFilters($config['allowedFilters'] ?? [])
			->allowedSorts($config['allowedSorts'] ?? 'id')
			->first();
		$result = $this->getResponse($result, $request);
		return $result;
	}
}
