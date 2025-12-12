<?php

namespace MyCore\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Joinable
{
    /**
     * Scope áp dụng LEFT JOIN dựa trên danh sách joins truyền vào.
     *
     * @param Builder $query
     * @param array $params ['joins' => 'products.name,sellers.name']
     * @return Builder
     */
    public function scopeApplyLeftJoins(Builder $query, array $params = [])
    {
        $joins = $params['joins'] ?? ($this->meta_data['allowedJoin'] ?? '');

        if (!$joins) {
            return $query;
        }

        $attributes = is_string($joins) && str_contains($joins, ',') 
            ? explode(',', $joins) 
            : (array) $joins;

        $table = $this->getModel()->getTable();
        $selects = ["{$table}.*"]; // Chọn tất cả cột của bảng chính
        $joinedTables = [];

        foreach ($attributes as $attribute) {
            $attribute = trim($attribute);

            if (!str_contains($attribute, '.')) {
                continue;
            }

            [$relation, $field] = explode('.', $attribute, 2);

            if (!method_exists($query->getModel(), $relation)) {
                continue;
            }

            $relationMethod = $this->getModel()->$relation();
            $relatedTable = $relationMethod->getRelated()->getTable();
            $alias = "{$relation}_{$field}"; // Định dạng alias: relation_field

            if (!in_array($relatedTable, $joinedTables)) {
                // Xác định khóa chính & khóa ngoại

                if ($relationMethod instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                    
                    $foreignKey = $relationMethod->getForeignKeyName();
                    $ownerKey = $relationMethod->getOwnerKeyName();
                    \Log::info($relatedTable .'__'. "$relatedTable.$ownerKey".'__'. '='.'__'."$table.$foreignKey");
                    $query->leftJoin($relatedTable, "$relatedTable.$ownerKey", '=', "$table.$foreignKey");
                } elseif ($relationMethod instanceof \Illuminate\Database\Eloquent\Relations\HasOne || 
                          $relationMethod instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                    $foreignKey = $relationMethod->getForeignKeyName();
                    $localKey = $relationMethod->getLocalKeyName();
                    $query->leftJoin($relatedTable, "$relatedTable.$foreignKey", '=', "$table.$localKey");
                }

                $joinedTables[] = $relatedTable;
            }

            // Thêm field vào select với alias
            $selects[] = "$relatedTable.$field as $alias";
        }
        return $query->select(array_values($selects));
    }
}
