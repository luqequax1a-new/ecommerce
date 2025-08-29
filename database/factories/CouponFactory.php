<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Coupon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('COUPON???')),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['percentage', 'fixed_amount', 'free_shipping', 'first_order']),
            'value' => $this->faker->randomElement([5, 10, 15, 20, 25, 50, 100]),
            'minimum_cart_amount' => $this->faker->randomElement([0, 50, 100, 200]),
            'usage_limit' => $this->faker->randomElement([null, 100, 500, 1000]),
            'usage_limit_per_user' => $this->faker->randomElement([null, 1, 2, 5]),
            'used_count' => $this->faker->numberBetween(0, 50),
            'valid_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+3 months'),
            'is_active' => $this->faker->boolean(80),
            'priority' => $this->faker->numberBetween(0, 10),
            'is_combinable' => $this->faker->boolean(30),
        ];
    }
}