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

    // ---- choices (0-4 free-text labels, e.g. "Spicy", "Extra Rice") ----
    public array $choices = [];

    // ---- long-press action menu state ----
    public ?int $actionMenuDishId = null;

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
            // Only real picture files are allowed (no svg/bmp/etc.), capped at 25MB
            'Photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:25600'],
            'choices' => ['array', 'max:4'],
            'choices.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function messages(): array
    {
        return [
            'Photo.image' => 'Only image files are allowed (JPG or PNG).',
            'Photo.mimes' => 'Only image files are allowed (JPG or PNG).',
            'Photo.max' => 'Image must not be larger than 25MB.',
            'choices.max' => 'You can only add up to 4 choices.',
        ];
    }

    public function mount(): void
    {
        $this->categoryFilter = null;
    }

    /**
     * Validate the photo the instant it's selected, so picking a non-image
     * file is rejected immediately instead of waiting for Submit.
     */
    public function updatedPhoto(): void
    {
        try {
            $this->validateOnly('Photo');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->Photo = null;
            throw $e;
        }
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
        $this->choices = $dish->Choices ?? [];

        $this->resetErrorBag();
        $this->showModal = true;
    }

    /**
     * Manager can attach up to 4 free-text choice labels to a dish
     * (e.g. "Spicy", "Extra Rice"). Zero is fine — the Choice section
     * simply won't show for that dish on the cashier's detail page.
     */
    public function addChoice(): void
    {
        if (count($this->choices) < 4) {
            $this->choices[] = '';
        }
    }

    public function removeChoice(int $index): void
    {
        unset($this->choices[$index]);
        $this->choices = array_values($this->choices);
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

        // Drop blank choice fields and re-index before saving
        $cleanedChoices = array_values(array_filter(
            $validated['choices'] ?? [],
            fn ($choice) => trim((string) $choice) !== ''
        ));
        unset($validated['choices']);
        $validated['Choices'] = $cleanedChoices ?: null;

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

    public ?int $pendingDeleteId = null;

    public function confirmDelete(int $dishId): void
    {
        $this->pendingDeleteId = $dishId;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
    }

    public function deleteDish(): void
    {
        if (! $this->pendingDeleteId) {
            return;
        }

        $dish = Dish::find($this->pendingDeleteId);

        if ($dish) {
            if ($dish->Photo) {
                Storage::disk('public')->delete($dish->Photo);
            }
            $dish->delete();
        }

        $this->pendingDeleteId = null;

        // In case the deleted dish was also open in the edit modal
        if ($this->editingDishId === $dish?->DishID) {
            $this->showModal = false;
            $this->resetForm();
        }
    }

    /**
     * Long-press on a dish card opens a small Edit/Delete action menu
     * instead of immediately deleting.
     */
    public function openActionMenu(int $dishId): void
    {
        $this->actionMenuDishId = $dishId;
    }

    public function closeActionMenu(): void
    {
        $this->actionMenuDishId = null;
    }

    public function chooseEditFromMenu(): void
    {
        $dishId = $this->actionMenuDishId;
        $this->actionMenuDishId = null;

        if ($dishId) {
            $this->openEdit($dishId);
        }
    }

    public function chooseDeleteFromMenu(): void
    {
        $dishId = $this->actionMenuDishId;
        $this->actionMenuDishId = null;

        if ($dishId) {
            $this->confirmDelete($dishId);
        }
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingDishId', 'DishName', 'Description', 'Price',
            'DishCode', 'CategoryID', 'Photo', 'existingPhoto', 'choices',
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
            'pendingDeleteDish' => $this->pendingDeleteId ? Dish::find($this->pendingDeleteId) : null,
            'actionMenuDish' => $this->actionMenuDishId ? Dish::find($this->actionMenuDishId) : null,
        ]);
    }
}