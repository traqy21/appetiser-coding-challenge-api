<?php

/*
  |--------------------------------------------------------------------------
  | Model Factories
  |--------------------------------------------------------------------------
  |
  | Here you may define all of your model factories. Model factories give
  | you a convenient way to create models for testing and seeding your
  | database. Just tell the factory how a default model should look.
  |
 */
$factory->define(App\Models\Event::class, function (Faker\Generator $faker) {
    return [
        'uuid' => $faker->uuid,
        'name' => $faker->name,
        'from' => \Carbon\Carbon::now()->format('Y-m-d'),
        'to' => \Carbon\Carbon::now()->addMonth(1)->format('Y-m-d'),
        'days' => json_encode([0,1,2,3,4,5,6])
    ];
});