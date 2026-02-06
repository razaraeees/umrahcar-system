<?php

namespace App\Livewire\Admin\Airlines;

use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class AirlineManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $airlineId;

    public $name = '';
    public $status = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:airlines,name',
        'status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'name.required' => 'Airline name is required.',
        'name.unique' => 'Airline name already exists.',
        'status.required' => 'Status is required.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->dispatch('modalOpened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('modalClosed');
    }

    public function edit($id)
    {
        $airline = Airline::findOrFail($id);

        $this->airlineId = $id;
        $this->name = $airline->name;
        $this->status = $airline->status;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['name'] = 'required|string|max:255|unique:airlines,name,' . $this->airlineId;
        }

        $validated = $this->validate();

        try {
            if ($this->editMode) {
                $airline = Airline::findOrFail($this->airlineId);
                $airline->update([
                    'name' => $validated['name'],
                    'status' => $validated['status'],
                ]);

                $this->dispatch('show-toast', type: 'success', message: 'Airline updated successfully.');

            } else {
                Airline::create([
                    'name' => $validated['name'],
                    'status' => $validated['status'],
                ]);

                $this->dispatch('show-toast', type: 'success', message: 'Airline created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $airline = Airline::findOrFail($id);
            $airline->delete();
            
            $this->dispatch('show-toast', type: 'success', message: 'Airline deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->status = '';
        $this->airlineId = null;
        $this->editMode = false;
    }

    public function render()
    {
        $airlines = Airline::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.airlines.airline-manager', compact('airlines'));
    }
}
