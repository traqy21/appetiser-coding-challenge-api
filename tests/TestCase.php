<?php

use Firebase\JWT\JWT;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication() {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function debug($response, $flag = false) {
        $string = $response->response->getContent();
        if ($flag) {
            $string = preg_replace('/\s+/', ' ', trim($response->response->getContent()));
        }

        dd($string);
    }

    public function decode($response) {
        return json_decode($response->response->getContent());
    }
}
