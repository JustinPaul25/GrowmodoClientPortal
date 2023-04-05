<?php

namespace App\Http\Livewire\Tables;

use App\Models\Organization;
use App\Models\User;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Organizations extends LivewireDatatable
{
    public $model = Organization::class;

    public $isOpen = false;
    public $editOrganizationModal = false;
    public $createOrganizationModal = false;
    public $afterTableSlot = 'livewire.admin.organizations.after-tableslot';
    public $beforeTableSlot = 'livewire.admin.organizations.before-tableslot';
    public $organization;
    public $action = '';


    protected $rules = [
        'organization.owner_id' => 'integer|exists:users,id',
        'organization.title' => 'string|max:500',
        'organization.address_line_1' => 'string|max:500',
        'organization.address_line_2' => 'string|max:500',
        'organization.state' => 'string|max:500',
        'organization.city' => 'string|max:500',
        'organization.zipcode' => 'string|max:500',
        'organization.country' => 'string|max:500',
        'organization.status' => 'string|max:500',
    ];

    public function columns()
    {
        return [
            NumberColumn::name('id')->sortable(),

            Column::callback(['id'], function ($id) {
                $organization = Organization::find($id);
                $owner = User::find($organization->owner_id);
                return $owner->firstname . ' ' .  $owner->lastname;
                // dd($owner->firstname);
            })->unsortable()->label('Client'),

            Column::name('title')->filterable()->searchable(),
            Column::name('country')->filterable()->searchable(),
            // Column::name('state')->filterable()->searchable(),
            Column::name('status')->filterable()->searchable(),

            DateColumn::name('created_at')->sortable(),

            Column::callback(['id'], 'column_actions_callback')->unsortable()->label('Actions'),
        ];
    }

    function column_actions_callback($id) {
        return view('livewire.admin.organizations.table-actions', ['id' => $id]);
    }


    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->editOrganizationModal = false;
        $this->organization = null;
        $this->password = '';
        $this->action = '';
    }

    public function doTheAction() {
        switch ($this->action) {
            case 'create':
                $this->createorganization();
                break;

            case 'edit':
                $this->updateorganization();
                break;

            default:
                # code...
                break;
        }
    }

    public function openEditOrganizationModal($organizationId) {
        $this->openModal();
        $this->editOrganizationModal = true;
        $this->organization = Organization::find($organizationId);
        $this->action = 'edit';
    }

    public function updateOrganization() {
        $this->validate([
            'organization.title' => 'required|string|max:500',
            'organization.address_line_1' => 'required|string|max:500',
            'organization.address_line_2' => 'string|max:500',
            'organization.state' => 'string|max:500',
            'organization.city' => 'string|max:500',
            'organization.zipcode' => 'string|max:500',
            'organization.country' => 'required|string|max:500',
            'organization.status' => 'required|string|in:' . implode(',', array_keys(config('project.organization_status')) ),
        ]);

        $this->organization->save();

        $this->closeModal();
    }


    public function openCreateOrganizationModal() {
        $this->openModal();
        $this->createOrganizationModal = true;
        $this->organization = new Organization;
        $this->action = 'create';
    }

    public function createorganization() {
        $this->validate([
            'organization.owner_id' => 'required|integer|exists:users,id',
            'organization.title' => 'required|string|max:500',
            'organization.address_line_1' => 'required|string|max:500',
            'organization.address_line_2' => 'string|max:500',
            'organization.state' => 'string|max:500',
            'organization.city' => 'string|max:500',
            'organization.zipcode' => 'string|max:500',
            'organization.country' => 'required|string|max:500',
            'organization.status' => 'required|string|in:' . implode(',', array_keys(config('project.organization_status')) ),
        ]);

        $this->organization->save();

        $this->closeModal();
    }
}