<?php

namespace App\Http\Livewire\Tables;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class Users extends LivewireDatatable
{
    public $model = User::class;
    public $isOpen = false;
    public $editUserModal = false;
    public $createUserModal = false;
    public $afterTableSlot = 'livewire.admin.users.after-tableslot';
    public $beforeTableSlot = 'livewire.admin.users.before-tableslot';
    public $user;
    public $password = '';
    public $action = '';


    protected $rules = [
        'user.firstname' => 'string',
        'user.lastname' => 'string',
        'user.email' => 'email',
        'user.status' => 'required|string|in:active,inactive,pending,suspended,banned',
        'user.username' => 'string|max:100',
        // 'user.password' => 'string|max:255|min:8'
    ];


    public function columns()
    {
        //
        return [
            NumberColumn::name('id')->sortable(),

            Column::name('firstname')->filterable()->searchable(),
            Column::name('lastname')->filterable()->searchable(),

            Column::name('email')->filterable()->searchable(),

            Column::name('status')->filterable()->searchable(),

            DateColumn::name('created_at')->sortable(),

            Column::callback(['id'], function ($id)  {
                return view('livewire.admin.users.users-table_actions', [
                    'id' => $id,
                    'user' => User::find($id),
                ]);
            })->unsortable()
        ];
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->editUserModal = false;
        $this->user = null;
        $this->password = '';
        $this->action = '';
    }

    public function doTheAction() {
        switch ($this->action) {
            case 'create':
                $this->createUser();
                break;

            case 'edit':
                $this->updateUser();
                break;

            default:
                # code...
                break;
        }
    }

    public function openEditUserModal($userId) {
        $this->openModal();
        $this->editUserModal = true;
        $this->user = User::find($userId);
        $this->action = 'edit';
    }

    public function updateUser() {
        $this->validate([
            'user.firstname' => 'required|string|max:255',
            'user.lastname' => 'required|string|max:255',
            'user.email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'user.status' => 'required|string|in:' . implode(',', array_keys(config('project.user_status')) ),
            'password' => 'string|max:255|min:8'
        ]);

        if (! empty($this->password)) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->closeModal();
    }


    public function openCreateUserModal() {
        $this->openModal();
        $this->createUserModal = true;
        $this->user = new User;
        $this->action = 'create';
    }

    public function createUser() {
        $this->validate([
            'user.firstname' => 'required|string|max:255',
            'user.lastname' => 'required|string|max:255',
            'user.email' => 'required|email|max:255|unique:users,email',
            'user.status' => 'required|string|in:' . implode(',', array_keys(config('project.user_status')) ),
            'password' => 'required|string|max:255|min:8',
            'user.username' => 'required|string|max:100|unique:users,username',
        ]);

        if (! empty($this->password)) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->closeModal();
    }

}