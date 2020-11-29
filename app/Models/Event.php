<?php


namespace App\Models;


class Event extends Model
{
    const FIELDS = [
        'uuid',
        'name',
        'from',
        'to',
        'days'
    ];

    protected $fillable = [
        'uuid',
        'name',
        'from',
        'to',
        'days'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}