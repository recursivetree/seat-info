<?php

namespace RecursiveTree\Seat\InfoPlugin\Model;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_info_resources';

    protected $fillable = [
        'id', 'path', 'mime'
    ];
}