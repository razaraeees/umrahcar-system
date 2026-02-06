<div>
    <div class="page-content">
        <div class="container-fluid">

            <livewire:admin.components.breadcrumbs title="Pick Up Location" bredcrumb1="Booking Managemnet"
                bredcrumb2="Pick Up Location" />


            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3 align-items-center">
                                <div class="col">
                                    <div class="input-group" style="max-width: 250px;">
                                        <input type="text" class="form-control" placeholder="Search..."
                                            wire:model.live="search">
                                        <span class="input-group-text bg-transparent border-0" wire:loading
                                            wire:target="search">
                                            <span class="spinner-border spinner-border-sm text-primary"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary" wire:click="openModal">
                                        <i class="fas fa-plus me-2"></i>Add More
                                    </button>
                                </div>
                            </div>


                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="font-weight: 600; color: #000;">Pick Up Location
                                            </th>
                                            <th style="font-weight: 600; color: #000;">City</th>
                                            <th style="font-weight: 600; color: #000;">Type</th>
                                            <th style="font-weight: 600; color: #000;">Status</th>
                                            <th style="font-weight: 600; color: #000;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pickUps as $pickUp)
                                            <tr>
                                                <td>{{ $pickUp->pickup_location ?? '' }}</td>
                                                <td>{{ $pickUp->city->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-status-active">{{ ucfirst($pickUp->type ?? '') }}</span>
                                                </td>
                                                <td>
                                                    @if ($pickUp->status == 'active')
                                                        <span class="badge badge-status-active">Active</span>
                                                    @else
                                                        <span class="badge badge-status-inactive">Inactive</span>
                                                    @endif

                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                                        wire:click="edit({{ $pickUp->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" x-data
                                                        @click="
                                                            $event.preventDefault();
                                                            Swal.fire({
                                                                title: 'Are you sure?',
                                                                text: 'You won\'t be able to revert this!',
                                                                icon: 'warning',
                                                                showCancelButton: true,
                                                                confirmButtonColor: '#d33',
                                                                cancelButtonColor: '#3085d6',
                                                                confirmButtonText: 'Yes, delete it!',
                                                                cancelButtonText: 'Cancel'
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    $wire.delete({{ $pickUp->id }});
                                                                }
                                                            });
                                                        ">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <p class="text-muted mb-0">No pick-up locations found.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $pickUps->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="modal fade" id="pickupLocationModal" tabindex="-1" aria-labelledby="pickupLocationModalLabel" aria-hidden="true" x-data="{ show: true }" x-init="show = true" 
             :class="{ 'show': show }" :style="{ 'display': show ? 'block' : 'none' }" 
             wire:ignore.self data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-backdrop fade show" @click="show = false; $wire.closeModal()"></div>
            <div class="modal-dialog modal-lg" style="position: relative; z-index: 1050;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editMode ? 'Edit Pick Up Location' : 'Add Pick Up Location' }}
                        </h5>
                        <button type="button" class="btn-close" @click="show = false; $wire.closeModal()"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="city_id" class="form-label">
                                            City <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('city_id') is-invalid @enderror" id="city_id"
                                            wire:model="city_id">
                                            <option value="">Select City</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}" {{ $city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pickupLocation" class="form-label">
                                            Pick Up Location <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('pick_up_location') is-invalid @enderror"
                                            id="pickupLocation" wire:model="pick_up_location"
                                            placeholder="Enter pick up location">
                                        @error('pick_up_location')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">
                                            Type <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type"
                                            wire:model="type">
                                            <option value="">Select Type</option>
                                            <option value="airport">Airport</option>
                                            <option value="hotel">Hotel</option>
                                            <option value="city">City</option>
                                            <option value="ziyarat">Ziyarat</option>
                                            <option value="guide">Guide</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pickup_status" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('pickup_status') is-invalid @enderror"
                                            id="pickup_status" wire:model="pickup_status">
                                            <option value="">Select Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        @error('pickup_status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="show = false; $wire.closeModal()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editMode ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


</div>
