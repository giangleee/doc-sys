<?php

namespace App\Traits;

trait CommonQuery
{

    public function scopeWhereDateFormat($query, $key, $value, $format = '%Y-%m-%d %H:%i', $compare = '=')
    {
        return $query->whereRaw("DATE_FORMAT($key, $format) $compare $value");
    }

    public function scopeWhereMany($query, array $fieldValues)
    {
        foreach ($fieldValues as $field => $searchValues) {
            if (is_array($searchValues) && count($searchValues) === 3) {
                list($key, $compare, $value) = $searchValues;
                if (!empty($value)) {
                    $query->where($key, $compare, $value);
                }
            } elseif (!is_array($searchValues) && !empty($searchValues)) {
                $query->where($field, $searchValues);
            } else {
                continue;
            }
        }
        return $query;
    }

    public function scopeWhereInMany($query, array $fieldValues)
    {
        foreach ($fieldValues as $field => $searchValues) {
            if (!empty($searchValues)) {
                $values = is_array($searchValues) ? $searchValues : [$searchValues];
                $query->whereIn($field, $values);
            }
        }
        return $query;
    }

    public function scopeWhereBetweenDates($query, $column, $startDate, $endDate)
    {
        return $query->whereDate($column, '>=', $startDate)
            ->whereDate($column, '<=', $endDate);
    }
}
