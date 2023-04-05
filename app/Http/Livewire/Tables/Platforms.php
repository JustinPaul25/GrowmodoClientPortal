<?php

namespace App\Http\Livewire\Tables;

use App\Models\Platform;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Platforms extends LivewireDatatable
{
    public $model = Platform::class;

    public $isOpen = false;
    public $editPlatformModal = false;
    public $createPlatformModal = false;
    public $afterTableSlot = 'livewire.admin.platforms.after-tableslot';
    public $beforeTableSlot = 'livewire.admin.platforms.before-tableslot';
    public $platform;
    public $action = '';


    protected $rules = [
        'platform.title' => 'string|exists:users,id',
        'platform.description' => 'string|max:500',
        'platform.status' => 'string|max:500',
        'platform.photo_file_id' => 'integer|max:500',
    ];

    public function columns()
    {
        return [
            NumberColumn::name('id')->sortable(),

            Column::name('title')->filterable()->searchable(),
            // Column::name('country')->filterable()->searchable(),
            // Column::name('state')->filterable()->searchable(),
            Column::name('status')->filterable()->searchable(),

            DateColumn::name('created_at')->sortable(),

            Column::callback(['id'], 'column_actions_callback')->unsortable()->label('Actions'),
        ];
    }

    function column_actions_callback($id) {
        return view('livewire.admin.platforms.table-actions', ['id' => $id]);
    }


    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->editPlatformModal = false;
        $this->platform = null;
        $this->password = '';
        $this->action = '';
    }

    public function doTheAction() {
        switch ($this->action) {
            case 'create':
                $this->createPlatform();
                break;

            case 'edit':
                $this->updatePlatform();
                break;

            default:
                # code...
                break;
        }
    }

    public function openEditPlatformModal($platformId) {
        $this->openModal();
        $this->editPlatformModal = true;
        $this->platform = Platform::find($platformId);
        $this->action = 'edit';
    }

    public function updatePlatform() {
        $this->validate([
            'platform.title' => 'required|string|max:255',
            'platform.description' => 'required|string|max:255',
            'platform.status' => 'required|string|in:' . implode(',', array_keys(config('project.organization_status')) ),
            'platform.photo_file_id' => 'required|integer',
        ]);

        $this->platform->save();

        $this->closeModal();
    }


    public function openCreatePlatformModal() {
        $this->openModal();
        $this->createPlatformModal = true;
        $this->platform = new Platform();
        $this->action = 'create';
    }

    public function createPlatform() {
        $this->validate([
            'platform.title' => 'required|string|max:255',
            'platform.description' => 'string|max:1000',
            'platform.status' => 'required|string|in:' . implode(',', array_keys(config('project.organization_status')) ),
            // 'platform.photo_file_id' => 'integer',
        ]);

        $this->platform->photo_file_id = 0;
        $this->platform->save();

        $this->closeModal();
    }
}