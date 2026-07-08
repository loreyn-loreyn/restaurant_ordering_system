<?php

namespace Tests\Feature;

use App\Livewire\Manager\Dishes;
use App\Models\Dish;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerDishesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('UserName', 'M004')->firstOrFail());
        Storage::fake('public');
    }

    public function test_creating_a_dish_persists_all_fields(): void
    {
        Livewire::test(Dishes::class)
            ->call('openCreate')
            ->set('DishName', 'Lechon Kawali')
            ->set('Description', 'Crispy fried pork belly')
            ->set('Price', '220.00')
            ->set('DishCode', 'MD-010')
            ->set('CategoryID', 1)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('dishes', [
            'DishName' => 'Lechon Kawali',
            'DishCode' => 'MD-010',
            'CategoryID' => 1,
        ]);
    }

    public function test_dish_code_must_be_unique(): void
    {
        Livewire::test(Dishes::class)
            ->call('openCreate')
            ->set('DishName', 'Duplicate Code Dish')
            ->set('Description', 'Whatever')
            ->set('Price', '10.00')
            ->set('DishCode', 'MD-001') // already used by seeded DishID 1
            ->set('CategoryID', 1)
            ->call('save')
            ->assertHasErrors('DishCode');
    }

    public function test_editing_a_dish_keeps_its_own_code_valid(): void
    {
        // Editing DishID 1 and resubmitting its own DishCode ("MD-001")
        // should not trip the unique rule against itself.
        Livewire::test(Dishes::class)
            ->call('openEdit', 1)
            ->set('Price', '199.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(199.00, Dish::find(1)->Price);
    }

    public function test_photo_upload_must_be_an_image(): void
    {
        $badFile = UploadedFile::fake()->create('not-an-image.txt', 10);

        Livewire::test(Dishes::class)
            ->call('openCreate')
            ->set('Photo', $badFile)
            ->assertHasErrors('Photo');
    }

    public function test_choices_are_cleaned_of_blanks_and_stored_as_json(): void
    {
        Livewire::test(Dishes::class)
            ->call('openCreate')
            ->set('DishName', 'Spicy Sisig')
            ->set('Description', 'With choices')
            ->set('Price', '180.00')
            ->set('DishCode', 'MD-011')
            ->set('CategoryID', 1)
            ->set('choices', ['Spicy', '', 'Extra Rice', ''])
            ->call('save');

        $dish = Dish::where('DishCode', 'MD-011')->firstOrFail();
        $this->assertSame(['Spicy', 'Extra Rice'], $dish->Choices);
    }

    public function test_no_choices_results_in_null_choices_and_empty_choice_list(): void
    {
        Livewire::test(Dishes::class)
            ->call('openCreate')
            ->set('DishName', 'Plain Rice')
            ->set('Description', 'No choices')
            ->set('Price', '20.00')
            ->set('DishCode', 'SD-020')
            ->set('CategoryID', 4)
            ->call('save');

        $dish = Dish::where('DishCode', 'SD-020')->firstOrFail();
        $this->assertNull($dish->Choices);
        $this->assertSame([], $dish->ChoiceList);
    }

    public function test_choice_list_is_capped_at_four_and_trims_blanks(): void
    {
        $dish = Dish::create([
            'CategoryID' => 1,
            'DishName' => 'Overloaded Choices',
            'Description' => 'Too many choices',
            'Price' => 50,
            'DishCode' => 'MD-099',
            'Choices' => ['One', ' ', 'Two', 'Three', 'Four', 'Five'],
        ]);

        $this->assertSame(['One', 'Two', 'Three', 'Four'], $dish->ChoiceList);
    }

    public function test_deleting_a_dish_removes_its_stored_photo(): void
    {
        Storage::disk('public')->put('dish-photos/existing.jpg', 'fake-bytes');

        $dish = Dish::create([
            'CategoryID' => 1,
            'DishName' => 'To Delete',
            'Description' => 'Will be removed',
            'Price' => 10,
            'DishCode' => 'MD-098',
            'Photo' => 'dish-photos/existing.jpg',
        ]);

        Livewire::test(Dishes::class)
            ->call('confirmDelete', $dish->DishID)
            ->call('deleteDish');

        $this->assertDatabaseMissing('dishes', ['DishID' => $dish->DishID]);
        Storage::disk('public')->assertMissing('dish-photos/existing.jpg');
    }

    public function test_menu_order_scope_pushes_unavailable_dishes_last(): void
    {
        // Seeded DishID 5 (Bottled Water) has Availability => 0.
        $dishes = Livewire::test(Dishes::class)->viewData('dishes');

        $this->assertSame(5, $dishes->last()->DishID);
    }

    public function test_category_filter_narrows_the_list(): void
    {
        $dishes = Livewire::test(Dishes::class)
            ->call('filterByCategory', 3) // Drinks
            ->viewData('dishes');

        $this->assertTrue($dishes->every(fn ($d) => $d->CategoryID === 3));
    }
}