<?php

namespace MyCore\Traits;

use Carbon\Carbon;

trait Filterable
{
    public function scopeApplyFilters($query, array $params = [])
    {
        $table = $this->getTable();
        $filter_columns = $this->meta_data['allowedFilter']['filter'] ?? '';
        $filter_columns = $filter_columns !== '' ? explode(',', $filter_columns) : [];

        $query->where(function ($sub_query) use ($params, $table, $filter_columns) {

        if(isset($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $column => $value) {

                    if (!empty($filter_columns) && !in_array($column, $filter_columns)) {
                        continue;
                    }

                    // Kiểm tra nếu column có dạng relation.field (ví dụ: user.phone)
                    if (str_contains($column, '.')) {
                        [$relation, $field] = explode('.', $column, 2);
                        
                        // Kiểm tra xem method relation có tồn tại không
                        if (method_exists($this, $relation)) {
                            $relationMethod = $this->$relation();
                            $relatedTable = $relationMethod->getRelated()->getTable();
                            $fullColumn = "{$relatedTable}.{$field}";
                        } else {
                            continue; // Bỏ qua nếu relation không tồn tại
                        }
                    } else {
                        // Column thuộc bảng chính
                        $fullColumn = "{$table}.{$column}";
                    }

                    $sub_query->where(function ($subQuery) use ($fullColumn, $value) {
                        if (is_array($value)) {
                            $subQuery->whereIn($fullColumn, $value);
                        } elseif (strpos($value, '%') !== false) {
                            $subQuery->where($fullColumn, 'LIKE', $value);
                        } else {
                            $subQuery->where($fullColumn, $value);
                        }
                    });
                }
            }
        });

        return $query;
    }

    public function scopeApplySearch($query, array $params = [])
    {
        $table = $this->getTable();
        $search_columns = $this->meta_data['allowedFilter']['search'] ?? '';
        $search_columns = $search_columns !== '' ? explode(',', $search_columns) : [];

        $query->where(function ($sub_query) use ($params, $table, $search_columns) {
            if(isset($params['search']) && $params['search'] && is_string($params['search'])) {

                $searchTerm = '%' . $params['search'] . '%';

                $sub_query->where(function ($sub_query) use ($search_columns, $table, $searchTerm) {
                    foreach ($search_columns as $column) {
                        $column = trim($column);
                        
                        // Kiểm tra nếu column có dạng relation.field (ví dụ: user.phone)
                        if (str_contains($column, '.')) {
                            [$relation, $field] = explode('.', $column, 2);
                            
                            // Kiểm tra xem method relation có tồn tại không
                            if (method_exists($this, $relation)) {
                                $relationMethod = $this->$relation();
                                $relatedTable = $relationMethod->getRelated()->getTable();
                                $sub_query->orWhere("{$relatedTable}.{$field}", 'LIKE', $searchTerm);
                            }
                        } else {
                            // Column thuộc bảng chính
                            $sub_query->orWhere("{$table}.{$column}", 'LIKE', $searchTerm);
                        }
                    }
                });
            }
        });

        return $query;
    }

    public function scopeApplyDate($query, array $params = [], $column = 'created_at')
    {
        $table = $this->getTable();
        $date = $params['date'] ?? null;
    
        if (!$date) {
            return $query;
        }
    
        // Múi giờ của frontend (UTC+7) và database (UTC)
        $frontendTimezone = 'Asia/Ho_Chi_Minh'; // UTC+7
        $databaseTimezone = 'UTC';
    
        // Chuyển đổi ngày thành dạng 'Y-m-d'
        if (is_string($date)) {
            $value = [date('Y-m-d', strtotime($date))];
        } elseif (is_array($date) && count($date) > 0) {
            $value = array_map(fn($d) => date('Y-m-d', strtotime($d)), $date);
        } else {
            return $query; // Nếu không hợp lệ, không làm gì
        }
    
        $field = "{$table}.{$column}";
        
        // Xử lý khi chỉ có một ngày
        if (count($value) === 1) {
            // Tạo Carbon instance với múi giờ frontend, sau đó chuyển về UTC
            $startOfDay = Carbon::createFromFormat('Y-m-d H:i:s', $value[0] . ' 00:00:00', $frontendTimezone)
                               ->setTimezone($databaseTimezone)
                               ->format('Y-m-d H:i:s');
            $endOfDay = Carbon::createFromFormat('Y-m-d H:i:s', $value[0] . ' 23:59:59', $frontendTimezone)
                             ->setTimezone($databaseTimezone)
                             ->format('Y-m-d H:i:s');
            return $query->whereBetween($field, [$startOfDay, $endOfDay]);
        }
    
        // Xử lý khi có khoảng ngày (từ ngày - đến ngày)
        $startOfDay = Carbon::createFromFormat('Y-m-d H:i:s', $value[0] . ' 00:00:00', $frontendTimezone)
                           ->setTimezone($databaseTimezone)
                           ->format('Y-m-d H:i:s');
        $endOfDay = Carbon::createFromFormat('Y-m-d H:i:s', $value[1] . ' 23:59:59', $frontendTimezone)
                         ->setTimezone($databaseTimezone)
                         ->format('Y-m-d H:i:s');
    
        return $query->whereBetween($field, [$startOfDay, $endOfDay]);
    }
}

