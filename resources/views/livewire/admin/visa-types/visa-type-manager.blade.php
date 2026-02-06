<div>
    <div class="page-content">
        <div class="container-fluid">

            <livewire:admin.components.breadcrumbs title="Visa Types" bredcrumb1="Booking Management"
                bredcrumb2="Visa Types" />

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
                                        <i class="fas fa-plus me-2"></i>Add Visa Type
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap"
                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="font-weight: 600; color: #000;">Visa Type Name</th>
                                            <th style="font-weight: 600; color: #000;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($visaTypes as $visaType)
                                            <tr>
                                                <td>{{ $visaType->name ?? '' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                                        wire:click="edit({{ $visaType->id }})">
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
                                                                    $wire.delete({{ $visaType->id }});
                                                                }
                                                            });
                                                        ">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-4">
                                                    <p class="text-muted mb-0">No visa types found.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $visaTypes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
        <div class="custom-modal-wrapper">
            <!-- Backdrop -->
            <div class="custom-modal-backdrop" wire:click="closeModal"></div>

            <!-- Modal Dialog -->
            <div class="custom-modal-dialog custom-modal-{{ $size ?? 'md' }}">
                <div class="custom-modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editMode ? 'Edit Visa Type' : 'Add Visa Type' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label for="name" class="form-label">Visa Type Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" wire:model="name" placeholder="Enter visa type name">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeModal">Close</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save me-1"></i>{{ $editMode ? 'Update' : 'Save' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>Saving...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>
