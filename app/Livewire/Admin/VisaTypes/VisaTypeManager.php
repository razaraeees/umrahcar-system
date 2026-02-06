<?php

namespace App\Livewire\Admin\VisaTypes;

use App\Models\VisaType;
use Livewire\Component;
use Livewire\WithPagination;

class VisaTypeManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $visaTypeId;

    public $name = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:visa_types,name',
    ];

    protected $messages = [
        'name.required' => 'Visa type name is required.',
        'name.unique' => 'Visa type already exists.',
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
        $visaType = VisaType::findOrFail($id);

        $this->visaTypeId = $id;
        $this->name = $visaType->name;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['name'] = 'required|string|max:255|unique:visa_types,name,' . $this->visaTypeId;
        }

        $validated = $this->validate();

        try {
            if ($this->editMode) {
                $visaType = VisaType::findOrFail($this->visaTypeId);
                $visaType->update([
                    'name' => $validated['name'],
                ]);

                $this->dispatch('show-toast', type: 'success', message: 'Visa type updated successfully.');
            } else {
                VisaType::create([
                    'name' => $validated['name'],
                ]);

                $this->dispatch('show-toast', type: 'success', message: 'Visa type created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $visaType = VisaType::findOrFail($id);
            $visaType->delete();

            $this->dispatch('show-toast', type: 'success', message: 'Visa type deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->visaTypeId = null;
        $this->editMode = false;
    }

    public function render()
    {
        $visaTypes = VisaType::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.visa-types.visa-type-manager', compact('visaTypes'));
    }
}
