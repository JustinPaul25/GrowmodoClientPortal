<div class="">
    <button wire:click="openCreateUserModal()" class="p-3 text-white bg-green-600 hover:bg-green-800  rounded ">
        Create
    </button>
</div>
<div class="mb-4"></div>

@if($isOpen && $createUserModal)
    @include('livewire.admin.users.edit-modal')
@endif