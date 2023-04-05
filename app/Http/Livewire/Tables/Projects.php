<?php

namespace App\Http\Livewire\Tables;

use App\Projects;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Projects extends LivewireDatatable
{
    public $model = Projects::class;

    public function columns()
    {
        //
    }
}