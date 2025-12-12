<?php


namespace MyCore\Actions;

class ActionHandler
{
	protected $actions = [];
	protected $middlewares = [];
	protected $permissions = [];
	protected $configs = [];

	public function __construct(protected $beforeHandle = null)
	{
	}

	public function middlewares($middlewares = [])
	{
		$this->middlewares = $middlewares;
		return $this;
	}

	public function permissions($permissions)
	{
		$this->permissions = $permissions;
		return $this;
	}

	public function bindRoute($path)
	{
		\Route::any($path, function (\Illuminate\Http\Request $request) {
			$prefix = $request->route('prefix');
			$action = $request->route('action');
			return app()->call([$this, 'handle'], [...$request->route()->parameters(), 'request' => $request]);
		})->middleware([...$this->middlewares]);
	}

	public function register($prefix, $actions, $config = [])
	{
		if (!isset($this->actions[$prefix]) || !is_array($this->actions[$prefix])) {
			$this->actions[$prefix] = [];
		}
		$this->actions[$prefix] = array_merge($this->actions[$prefix], $actions);
		$this->configs[$prefix] = $config;
	}

	public function handle($prefix, $action, $request)
	{
		ob_start();
		$checkAccess = true;
		$config = $this->configs[$prefix] ?? [];

		// \Log::info($middlewares);
		$method = $request->getMethod(); 

		if (!in_array(strtolower($method), ['get', 'post', 'put', 'delete', 'patch', 'any', 'options'])) {
			abort(400, "Invalid HTTP method: $method");
		}
		if ($action === 'options' && $method !== 'POST') {
			abort(400, "Invalid HTTP method: $method");
		}

		//re assign custom request from config
		if (!empty($config['requests']) && !empty($config['requests'][$action])) {
			$request = app($config['requests'][$action]);
		}
		if (!$checkAccess) {
			// throw new \Exception('Access denied!', 403);
			return response()->json(['message' => 'Access denied!', 'status' => 'special'], 403);
		}
		if (is_callable($this->beforeHandle)) {
			$this->beforeHandle($prefix, $action);
		}
		if (!empty($this->actions[$prefix][$action])) {
			if (is_string($this->actions[$prefix][$action])) {
				$result = app()->call($this->actions[$prefix][$action] . '@handle', ['request' => $request]);
				return $result;
			}
			$result = app()->call([$this->actions[$prefix][$action], 'handle'], ['request' => $request]);
			return $result;
		}
		throw new \Exception('Action not found!');
	}
}

