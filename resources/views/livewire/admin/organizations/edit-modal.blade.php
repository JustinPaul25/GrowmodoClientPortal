<div x-cloak wire:key="{{ $organization->id }}">
    <div class="fixed z-50 bottom-0 inset-x-0 px-4 pb-4 sm:inset-0 sm:flex sm:items-center sm:justify-center">
        <div  x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div  x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-gray-100 rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full sm:p-6"
            style="max-width: 80%; width: 80%;">
            <div class="w-full">
                <div class="mt-3 text-center">
                    <h3 class="text-lg text-left leading-6 font-medium text-gray-900">
                        @if ($action == 'edit')
                            Edit Organization #{{ $organization->id }}
                        @elseif ($action == 'create')
                            Create new Organization
                        @endif
                    </h3>
                    <div class="mt-2">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 grid grid-cols-1 gap-4">
                            <div class="">
                                <div>
                                    <div class="mb-2">
                                        <label for="owner_id" class="block text-gray-700 text-sm font-bold mb-2 text-left">Owner ID (User # ID):</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="owner_id" wire:model.defer="organization.owner_id" type="number" wire:disabled="action == edit">
                                            @error('organization.owner_id') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2 text-left">Title:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="title" wire:model.defer="organization.title" type="text" >
                                            @error('organization.title') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="address_line_1" class="block text-gray-700 text-sm font-bold mb-2 text-left">Address Line 1:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="address_line_1" wire:model.defer="organization.address_line_1" type="text">
                                            @error('organization.address_line_1') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="address_line_2" class="block text-gray-700 text-sm font-bold mb-2 text-left">Address Line 2:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="address_line_2" wire:model.defer="organization.address_line_2" type="text">
                                            @error('organization.address_line_2') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="state" class="block text-gray-700 text-sm font-bold mb-2 text-left">State:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="state" wire:model.defer="organization.state" type="text">
                                            @error('organization.state') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="city" class="block text-gray-700 text-sm font-bold mb-2 text-left">City:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="city" wire:model.defer="organization.city" type="text">
                                            @error('organization.city') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="zipcode" class="block text-gray-700 text-sm font-bold mb-2 text-left">Zipcode:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="zipcode" wire:model.defer="organization.zipcode" type="text">
                                            @error('organization.zipcode') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="country" class="block text-gray-700 text-sm font-bold mb-2 text-left">Country:</label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="country" wire:model.defer="organization.country" type="text">
                                            @error('organization.country') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-2">
                                        <label for="status" class="block text-gray-700 text-sm font-bold mb-2 text-left">Status:</label>
                                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                            id="status" wire:model.defer="organization.status" type="status">
                                            <option value="">Choose Status<option>
                                            @foreach(config('project.organization_status') as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                            @error('organization.status') <span class="text-red-500">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class=" px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                <button wire:loading.attr="disabled" wire:click.prevent="doTheAction()" type="button"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                    Save
                                </button>
                            </span>
                            <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                                <button wire:loading.attr="disabled" wire:click="closeModal()" type="button"
                                    class="closeModal-btn inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                    Cancel
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

