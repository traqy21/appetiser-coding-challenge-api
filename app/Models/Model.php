<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as CoreModel;

abstract class Model extends CoreModel {

    const SORT = null;
    const FIELDS = null;
    public $deletable = true;

    /**
     * Uuid Scope
     *
     * @param Builder $query
     * @param $uuid
     * @return $this
     */
    public function scopeUuid(Builder $query, $uuid) {
        return $query->where('uuid', $uuid);
    }

    /**
     * Pagination scope
     *
     * @param Builder $query
     * @param $page
     * @param $limit
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function scopeByPage(Builder $query, $page, $limit) {
        return $query->select(static::FIELDS)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy(static::SORT)
            ->get();
    }

    public function scopeByMerchant(Builder $query, $uuid){
        return $query->where('merchant_uuid', $uuid);
    }
}
