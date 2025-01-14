<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;


    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'cpf_cnpj' => $this->faker->numerify('###########'),
            'contact' => $this->faker->email,
            'address' => $this->faker->address,
        ];
    }
}
