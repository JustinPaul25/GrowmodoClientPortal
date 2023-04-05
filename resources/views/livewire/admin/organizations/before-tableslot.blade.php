<div class="">
    <button wire:click="openCreateOrganizationModal()" class="p-3 text-white bg-green-600 hover:bg-green-800  rounded ">
        Create
    </button>
</div>
<div class="mb-4"></div>

@if($isOpen && $createOrganizationModal)
    @include('livewire.admin.organizations.edit-modal')
@endif