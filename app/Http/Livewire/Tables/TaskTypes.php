<?php

namespace App\Http\Livewire\Tables;

use App\TaskType;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class TaskTypes extends LivewireDatatable
{
    public $model = TaskType::class;

    public function columns()
    {
        //
    }
}