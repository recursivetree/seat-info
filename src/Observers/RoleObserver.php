<?php

namespace RecursiveTree\Seat\InfoPlugin\Observers;

use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\InfoPlugin\Model\AclRole;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;

class RoleObserver
{
    public function deleted($role){
        AclRole::where("role",$role->id)->delete();
    }
}