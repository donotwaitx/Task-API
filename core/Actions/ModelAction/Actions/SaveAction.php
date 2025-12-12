<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SaveAction extends EloquentAction
{
	public function handle(Request $request)
	{
		$config = $this->config;
		$action = $request->route('action');

		$class = $config['class'];
		$model = new $class;
//        $data = $request->all();
		$data = $this->getData($config, $request);
		$id = $request->input('id', $request->input($model->getKeyName()));
		$saveDataWithMethods = [];
		foreach ($config['saveMethods'] ?? [] as $field => $method) {
			$saveDataWithMethods[$field] = $data[$field] ?? [];
			unset($data[$field]);
		}
		$result = $class::where([$model->getKeyName() => $id])->first();

		foreach ($config['allowedIncludes'] ?? [] as $relation) {
			$saveDataWithMethods[$relation] = $data[$relation] ?? [];
			unset($data[$relation]);

		}
		if ($result) {
			$result->fill($data)->save();
		} else {
			$result = $class::create($data);
		}
		foreach ($config['allowedIncludes'] ?? [] as $relation) {
			if (method_exists($result, 'save' . ucfirst($relation))) {
				$result->{'save' . ucfirst($relation)}($saveDataWithMethods[$relation]);
			}
		}
		foreach ($config['saveMethods'] ?? [] as $field => $method) {
			$result->{$method}($request->input($field));
		}
		$modelName = $this->modelName;
		$this->updateVersion($modelName);
		$this->updateVersion($modelName . '.' . $request->input($model->getKeyName()), false);
		return [
			'result' => $result,
			'message' => $id ? 'Update Successfully!' : 'Create Successfully!'
		];
	}
}
