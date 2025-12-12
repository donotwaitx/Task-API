<?php

namespace MyCore\Actions\ModelAction\Actions;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class GetListAction extends EloquentAction
{
	public function handle(Request $request)
	{
		$config = $this->config;
		$action = $request->route('action');
		$hasVersionSupport = false;
		$class = $config['class'];
		$model = new $class;

		$actionKey = $action;
		if (in_array(HasVersions::class, class_uses_recursive($this->config['class']))) {
			$versionKey = $model->getVersionKeyAttribute() . '::' . \Auth::user()->username . '::' . md5(json_encode($request->all()));
			$oldVersion = cache('version::' . $actionKey . '::' . $versionKey);
			$newVersion = app(VersionManager::class)->getVersion($model->getVersionKeyAttribute());
			$hasVersionSupport = true;
			if (config('app.cache') && $newVersion === $oldVersion && $cacheData = cache('data::' . $actionKey . '::' . $versionKey)) {
				$cacheData['version_key'] = $model->getVersionKeyAttribute();

				return response($cacheData)->withHeaders(['App-Cache' => $newVersion]);
			}
		}

		if (isset($config['groupBy']) && is_array($config['groupBy']) && isset($config['groupBy']['parent_key']) && isset($config['groupBy']['key'])) {
			$parent_key = $config['groupBy']['parent_key'];
			$child_key = $config['groupBy']['key'];

			$page = (int)$request->input('page', 1);
			$perPage = (int)$request->input('perPage', 15);
			$paginatedParents = QueryBuilder::for($this->getQuery($request))
				->defaultSort($config['defaultSort'] ?? '-created_at')
				->allowedIncludes($config['allowedIncludes'] ?? [])
				->allowedFilters($config['allowedFilters'] ?? [])
				->allowedSorts($config['allowedSorts'] ?? 'id')
				->whereNull('parent_id') // Chỉ lấy cha
				->paginate($perPage, ['*'], 'page', $page);
			$paginatedParents->getCollection()->each->setAppends(['current_stage']);

			// Lấy danh sách cha (dạng collection)
			$parents = collect($paginatedParents->items());

			// 2️⃣ Lấy danh sách ID cha để tìm con
			$parentIds = $parents->pluck($child_key)->toArray();

			// 3️⃣ Truy vấn danh sách con có `parent_id` nằm trong danh sách cha
			$children = QueryBuilder::for($this->getQuery($request))
				->whereIn($parent_key, $parentIds)
				->defaultSort($config['defaultSort'] ?? '-created_at')
				->allowedIncludes($config['allowedIncludes'] ?? [])
				->allowedFilters($config['allowedFilters'] ?? [])
				->allowedSorts($config['allowedSorts'] ?? 'id')
				->get()
				->groupBy($parent_key); // Nhóm con theo `parent_id`

			// 4️⃣ Gắn con vào ngay sau cha
			$sortedResults = [];
			$childrenIds = $children->keys()->toArray();

			foreach ($parents as $parent) {
				$parent->remark = in_array($parent->$child_key, $childrenIds) ? 'parent' : null;

				$sortedResults[] = $parent; // Thêm cha vào danh sách
				if (isset($children[$parent->$child_key])) {
					foreach ($children[$parent->$child_key] as $child) {
						$child->remark = ($child->parent_id !== null && in_array($child->$parent_key, $parentIds)) ? 'child' : null;
						$sortedResults[] = $child; // Thêm các con ngay sau cha
					}
				}
			}

			// 5️⃣ Áp dụng phân trang lại với danh sách mới
			$totalItems = $paginatedParents->total(); // Tổng số cha (không tính con)
			$perPage = $paginatedParents->perPage(); // Số lượng cha trên mỗi trang
			$page = $paginatedParents->currentPage();
			$result = (new LengthAwarePaginator(
				collect($sortedResults),  // Dữ liệu đã phân trang
				$totalItems, // Tổng số bản ghi
				$perPage,    // Số bản ghi trên mỗi trang
				$page,       // Trang hiện tại
				['path' => $request->url(), 'query' => $request->query()] // Giữ query params
			))->toArray();
		} else {
			$result = QueryBuilder::for($this->getQuery($request))
				->defaultSort($config['defaultSort'] ?? '-id')
				->allowedIncludes($config['allowedIncludes'] ?? [])
				->allowedFilters($config['allowedFilters'] ?? [])
				->allowedSorts($config['allowedSorts'] ?? 'id')
				->paginate($request->input('perPage', 15))
				->appends($request->query())->toArray();
		}

		if ($hasVersionSupport) {
			$result['version_key'] = $model->getVersionKeyAttribute();
			cache(['data::' . $actionKey . '::' . $versionKey => $result]);
			cache(['version::' . $actionKey . '::' . $versionKey => $newVersion]);
		}
		return $this->getResponse($result, $request);
	}
}
