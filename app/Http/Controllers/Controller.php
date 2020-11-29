<?php
/**
 * Core Class for the controller
 */
namespace App\Http\Controllers;

use App\Services\Service;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    protected $request;
    protected $service;
    public function __construct(Request $request, Service $service)
    {
        $this->request = $request;
        $this->service = $service;
    }
}
