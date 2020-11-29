<?php

namespace App\Repositories;

use App\Models\Model;
use Ramsey\Uuid\Uuid;

abstract class Repository {

    const PERPAGE = 25;
    const APPENDS = [];
    const WITH = [];

    protected $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    /**
     * Create of the model function
     *
     * @param $data
     * @return object
     */
    public function create($data) {
        $data['uuid'] = Uuid::uuid4()->toString();
        return $this->model->create($data);
    }

    public function insert($data) {
        return $this->model->insert($data);
    }

    /**
     * Updating of the model
     *
     * @param Model $model
     * @param $data
     * @return object
     */
    public function update(Model $model, $data) {
        $model->fill($data)->save();
        $model->append(static::APPENDS);
        return $model;
    }

    /**
     * Deletion of the model
     *
     * @param Model $model
     * @return object
     */
    public function delete(Model $model) {
        if ($model->deletable) {
            $model->delete();
            return true;
        }
        return false;
    }

    /**
     * View Details
     *
     * @param Model $model
     * @return $this
     */
    public function view(Model $model) {
        return $model->append(static::APPENDS);
    }

    /**
     * Find by ID
     *
     * @param $field
     * @param $id
     * @return mixed
     */
    public function find($field, $id) {
        return $this->model->where($field, $id)->first();
    }

    /**
     * Find by ID next data
     *
     * @param $givenId
     * @param $field
     * @param $id
     * @return mixed
     */
    public function findNext($givenId, $field, $value) {
        return $this->model
                ->where('id', '>', $givenId)
                ->where($field, $value)
                ->first();
    }
    /**
     * Find by value
     *
     * @param $field
     * @param $value
     * @param $columns
     * @return mixed
     */
    public function findBy($field, $value, $columns) {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * Find all
     *
     * @param $field
     * @param $value
     * @param $columns
     * @return mixed
     */
    public function findAll() {
        return $this->model->get();
    }

    public function load($id, $columns = array('*')) {
        return $this->loadArray([$id], $columns);
    }

    public function loadArray($ids, $columns = array('*')) {
        return $this->model->whereIn('id', $ids)->get($columns);
    }

    public function deleteAll($ids, $columns = array('*')) {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
