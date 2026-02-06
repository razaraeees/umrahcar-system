<div>
    <div class="page-content">
        <div class="container-fluid">

            <livewire:admin.components.breadcrumbs title="Airlines" bredcrumb1="Booking Management"
                bredcrumb2="Airlines" />

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
                                        <i class="fas fa-plus me-2"></i>Add Airline
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="font-weight: 600; color: #000;">Airline Name</th>
                                            <th style="font-weight: 600; color: #000;">Status</th>
                                            <th style="font-weight: 600; color: #000;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($airlines as $airline)
                                            <tr>
                                                <td>{{ $airline->name ?? '' }}</td>
                                                <td>
                                                    @if ($airline->status == 'active')
                                                        <span class="badge badge-status-active">Active</span>
                                                    @else
                                                        <span class="badge badge-status-inactive">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                                        wire:click="edit({{ $airline->id }})">
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
                                                                    $wire.delete({{ $airline->id }});
                                                                }
                                                            });
                                                        ">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4">
                                                    <p class="text-muted mb-0">No airlines found.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $airlines->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="modal fade" x-data="{ show: true }" x-init="show = true" 
             :class="{ 'show': show }" :style="{ 'display': show ? 'block' : 'none' }" 
             wire:ignore>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editMode ? 'Edit Airline' : 'Add Airline' }}
                        </h5>
                        <button type="button" class="btn-close" @click="show = false; $wire.closeModal()"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Airline Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" wire:model="name" placeholder="Enter airline name">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" 
                                                id="status" wire:model="status">
                                            <option value="">Select Status</option>
                                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
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
