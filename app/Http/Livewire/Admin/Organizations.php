<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;

class Organizations extends Component
{
    use WithPagination;

    public $organizations;
    public $owner_id,
        $title,
        $address_line_1,
        $address_line_2,
        $state,
        $city,
        $zipcode,
        $country,
        $status;
    public $isOpen = 0;
    public $search = '';

    public function render()
    {
        return view('livewire.admin.organizations');
    }


}
