<?php


namespace App\Services;


use App\Repositories\EventRepository;
use Illuminate\Http\Request;

class EventService extends Service
{
    protected $module = 'events';
    public function __construct(Request $request, EventRepository $repository)
    {
        parent::__construct($request, $repository);
    }
}