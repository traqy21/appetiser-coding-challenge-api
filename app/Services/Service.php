<?php

namespace App\Services;

use App\Repositories\Repository;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;

abstract class Service {

    protected $request;
    protected $user;
    protected $repository;
    protected $module = 'default';
    protected $uuid = null;

    const PERPAGE = 10;

    public function __construct(Request $request, Repository $repository) {
        $this->repository = $repository;
        $this->request = $request;
    }

    public function setUuid($uuid){
        $this->uuid = $uuid;
    }

    public function getUser(){
        return $this->user;
    }

    public function getCurrentDate(){
        return Carbon::now();
    }

    public function create($data) {
        $data['recorded_by'] = (isset($this->user)) ? $this->user->uuid : null;
        return (object) [
                    "status" => 200,
                    "message" => __("messages.{$this->module}.create.200"),
                    "model" => $this->repository->create($data)
        ];
    }

    public function add() {
        $data = $this->request->all();
        $this->setMerchant($data);

        return (object) [
            "status" => 200,
            "message" => __("messages.{$this->module}.create.200"),
            "model" => $this->repository->create($data)
        ];
    }

    public function update(Model $model, $data) {
        $data['recorded_by'] = (isset($this->user)) ? $this->user->uuid : null;
        return (object) [
                    "status" => 200,
                    "message" => __("messages.{$this->module}.update.200"),
                    "model" => $this->repository->update($model, $data),
        ];
    }

    public function deleteByUuid(){
        $model = $this->repository->find('uuid', $this->uuid);

        if($model !== null){
            return $this->delete($model);
        }

        return (object) [
            "status" => 400,
            "message" => __("messages.{$this->module}.delete.400")
        ];
    }

    public function delete(Model $model) {
        $delete = $this->repository->delete($model);
        if ($delete) {
            return (object) [
                        "status" => 200,
                        "message" => __("messages.{$this->module}.delete.200")
            ];
        }

        return (object) [
                    "status" => 400,
                    "message" => __("messages.{$this->module}.delete.400")
        ];
    }

    public function view(Model $model) {
        return $this->repository->view($model);
    }

    public function find($field, $id) {
        return (object) [
            "status" => 200,
            "message" => __("messages.{$this->module}.view.200"),
            "model" => $this->repository->find($field, $id)
        ];
    }

    public function findAll() {
        return (object) [
            "status" => 200,
            "message" => __("messages.{$this->module}.list.200"),
            "model" => $this->repository->findAll()
        ];
    }

    public function findNext($givenId, $field, $value) {
        return (object) [
            "status" => 200,
            "message" => __("messages.{$this->module}.view.200"),
            "model" => $this->repository->findNext($givenId, $field, $value)
        ];
    }

    public function findBy($field, $value, $columns) {

        $collection = $this->repository->findBy($field, $value, $columns);
        return (object) [
                    "status" => 200,
                    "message" => __("messages.{$this->module}.view.200"),
                    "list" => $collection
        ];
    }

    public function getResponseList($list, $page){
        $count = $list->count();

        // IF EMPTY PAGE //
        if ($page == 1 && $count == 0) {
            return (object) [
                "message" => __("messages.{$this->module}.index.empty"),
                "status" => 200,
                "list" => [],
                "max_page" => 1,
                "prev_page" => 0,
                "next_page" => 0,
            ];
        }

        $max_page = ceil($count / static::PERPAGE);
        if ($page > $max_page) {
            return (object) [
                "status" => 404,
                "message" => __("messages.{$this->module}.index.404")
            ];
        }

        return (object) [
            "message" => null,
            "status" => 200,
            "list" => $list->byPage($page, self::PERPAGE),
            "max_page" => $max_page,
            "prev_page" => $page == 1 ? 0 : $page - 1,
            "next_page" => $page == $max_page ? 0 : $page + 1
        ];
    }

    public function response($status, $message, $data = []){
        return (object) [
            "status" => $status,
            "message" => __("messages.{$this->module}.{$message}"),
            "data" => $data
        ];
    }
}
