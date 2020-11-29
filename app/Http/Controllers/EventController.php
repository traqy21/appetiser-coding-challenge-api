<?php


namespace App\Http\Controllers;


use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(Request $request, EventService $service)
    {
        parent::__construct($request, $service);
    }

    public function create(){
        $this->validate($this->request, [
            "name" => "required",
            "from" => "required",
            "to" => "required",
            "days" => "required",
        ]);

        $result = $this->service->create($this->request->all());

        return response()->json([
            "message" => $result->message,
            "model" => $result->model,
        ], $result->status);
    }

    public function update($uuid){
        $this->validate($this->request, [
            "name" => "required",
            "from" => "required",
            "to" => "required",
            "days" => "required",
        ]);
        $model = $this->service->find('uuid', $uuid)->model;
        $result = $this->service->update($model, $this->request->all());

        return response()->json([
            "message" => $result->message,
            "model" => $result->model,
        ], $result->status);
    }

    public function show($uuid)
    {
        $result = $this->service->find('uuid', $uuid);
        return response()->json([
            "message" => $result->message,
            "model" => $result->model,
        ], $result->status);
    }
}