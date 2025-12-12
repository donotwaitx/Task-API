<?php

namespace MyCore\Actions\ModelAction\Actions;

abstract class EloquentAction
{
	public function __construct(protected $modelName, protected $config)
	{
	}

	protected function getData($config, $request)
	{
		if (method_exists($request, 'data')) {
			$data = $request->data();
		} else {
			$data = $request->all();

		}
		if (!empty($config['scopes']) && is_array($config['scopes'])) {
			foreach ($config['scopes'] as $scope) {
				$data = app()->call($scope . '@data', [
					'data' => $data,
					'request' => $request
				]);
			}
		}
		return $data;
	}

	protected function getQuery($request)
	{
		$config = $this->config;
		$query = $config['class']::query();
		if (!empty($config['scopes']) && is_array($config['scopes'])) {
			foreach ($config['scopes'] as $scope) {
				$query = app()->call($scope . '@query', [
					'query' => $query
				]);
			}
		}
		if (method_exists($request, 'custom_query')) {
			return $request->custom_query($query);
		}
		return $query;
	}

	protected function getResponse($result, $request)
	{
		if (method_exists($request, 'custom_find_response')) {
			$fields = $request->custom_find_response();
			$result = collect([$result])->map(function ($item) use ($fields) {
				$filteredItem = [];
				foreach ($fields as $field) {
					$keys = explode('.', $field);
								$this->setNestedValue($filteredItem, $keys, $item);
				}
				return (object) $filteredItem;
			})->first();
		}
		if (method_exists($request, 'custom_list_response')) {
			$fields = $request->custom_list_response();
			$result['data'] = collect($result['data'])->map(function ($item) use ($fields) {
				$filteredItem = [];
				foreach ($fields as $field) {
					$keys = explode('.', $field);
					$this->setNestedValue($filteredItem, $keys, $item);
				}
				return (object) $filteredItem;
			})->toArray();
		}
		return $result;
	}

	private function setNestedValue(array &$target, array $keys, array $source)
	{
		if (empty($keys)) {
			return;
		}

		$key = array_shift($keys);

		if (!array_key_exists($key, $source)) {
			$target[$key] = null;
			return;
		}

		if (empty($keys)) {
			$target[$key] = $source[$key];
		} else {
			$target[$key] = $target[$key] ?? [];
			if (is_array($source[$key])) {
				$this->setNestedValue($target[$key], $keys, $source[$key]);
			}
		}
	}
}
