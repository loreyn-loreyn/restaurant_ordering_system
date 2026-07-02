<?php

namespace App\Livewire\Manager;

use App\Models\Category;
use App\Models\Dish;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.manager')]
class Dishes extends Component
{
    use WithFileUploads;

    // ---- filter state ----
    public ?int $categoryFilter = null; // null = "All"

    // ---- modal state ----
    public bool $showModal = false;
    public ?int $editingDishId = null;

    // ---- form fields ----
    public string $DishName = '';
    public string $Description = '';
    public string $Price = '';
    public string $DishCode = '';
    public ?int $CategoryID = null;
    public $Photo = null;           // new upload (temporary file)
    public ?string $existingPhoto = null; // stored path, kept if not replaced

    protected function rules(): array
    {
        return [
            'DishName' => ['required', 'string', 'max:150'],
            'Description' => ['required', 'string', 'max:255'],
            'Price' => ['required', 'numeric', 'min:0'],
            'DishCode' => [
                'required', 'string', 'max:50',
                'unique:dishes,DishCode,' . ($this->editingDishId ?? 'NULL') . ',DishID',
            ],
            'CategoryID' => ['required', 'exists:categories,CategoryID'],
            'Photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function mount(): void
    {
        $this->categoryFilter = null;
    }

    public function filterByCategory(?int $categoryId): void
    {
        $this->categoryFilter = $categoryId;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $dishId): void
    {
        $dish = Dish::findOrFail($dishId);

        $this->editingDishId = $dish->DishID;
        $this->DishName = $dish->DishName;
        $this->Description = $dish->Description;
        $this->Price = (string) $dish->Price;
        $this->DishCode = $dish->DishCode;
        $this->CategoryID = $dish->CategoryID;
        $this->existingPhoto = $dish->Photo;
        $this->Photo = null;

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->Photo) {
            // Replace old photo if editing
            if ($this->editingDishId && $this->existingPhoto) {
                Storage::disk('public')->delete($this->existingPhoto);
            }
            $validated['Photo'] = $this->Photo->store('dish-photos', 'public');
        } else {
            $validated['Photo'] = $this->existingPhoto;
        }

        Dish::updateOrCreate(
            ['DishID' => $this->editingDishId],
            $validated
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function discard(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function deleteDish(): void
    {
        if (! $this->editingDishId) {
            return;
        }

        $dish = Dish::find($this->editingDishId);

        if ($dish) {
            if ($dish->Photo) {
                Storage::disk('public')->delete($dish->Photo);
            }
            $dish->delete();
        }

        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingDishId', 'DishName', 'Description', 'Price',
            'DishCode', 'CategoryID', 'Photo', 'existingPhoto',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $categories = Category::orderBy('CategoryName')->get();

        $dishes = Dish::with('category')
            ->when($this->categoryFilter, fn ($q) => $q->where('CategoryID', $this->categoryFilter))
            ->menuOrder()
            ->get();

        return view('livewire.manager.dishes', [
            'categories' => $categories,
            'dishes' => $dishes,
        ]);
    }
}