<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Budget;
use App\Expense;

class BudgetsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test budget controller index route
     *
     * @return void
     */
    public function testIndex()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);

        $response = $this->actingAs($user)->withSession(['date' => strtotime(now())])->get(route('budgets'));
        $this->assertAuthenticated();
        $response->assertStatus(200);
        $response->assertViewHas('navBudgets');
        $response->assertViewHas('date');
        $response->assertViewHas('budgets');
    }

    /**
     * Test budget controller create route
     *
     * @return void
     */
    public function testCreate()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);

        $response = $this->actingAs($user)->withSession(['date' => strtotime(now())])->get(route('budget.create'));
        $this->assertAuthenticated();
        $response->assertStatus(200);
        $response->assertViewHas('navBudgets');
        $response->assertViewHas('date');
    }

    /**
     * Test budget controller store route
     *
     * @return void
     */
    public function testStore()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);
        $data = [
            'user_id' => $user->id,
            'name' => 'New Budget',
            'date' => date('Y-m-d', strtotime(now())),
            'amount' => 1000.00,
            'reason' => 'Automated Testing'
        ];

        $response = $this->actingAs($user)->post(route('budget.store'), $data);
        $this->assertAuthenticated();
        $response->assertStatus(302);
        $this->assertDatabaseHas('budgets', ['name' => $data['name']]);
        $response->assertRedirect(route('budgets'));
    }

    /**
     * Test budget controller edit route
     *
     * @return void
     */
    public function testEdit()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);

        $response = $this->actingAs($user)->withSession(['date' => strtotime(now())])->get(route('budget.edit', ['id' => $budget->id]));
        $this->assertAuthenticated();
        $response->assertStatus(200);
        $response->assertViewHas('navBudgets');
        $response->assertViewHas('date');
        $response->assertViewHas('budget');
    }

    /**
     * Test budget controller update route
     *
     * @return void
     */
    public function testUpdate()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);
        $data = [
            'user_id' => $user->id,
            'name' => 'New Budget Updated',
            'date' => strtotime(date('Y-m-01', strtotime('+3 day', strtotime(now())))),
            'amount' => 1000.00,
            'reason' => 'Automated Testing Updated'
        ];

        $response = $this->actingAs($user)->post(route('budget.update', ['id' => $budget->id]), $data);
        $this->assertAuthenticated();
        $response->assertStatus(302);
        $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
        //$response->assertRedirect(route('budgets'));
    }

    /**
     * Test budget delete
     *
     * @return void
     */
    public function testDelete()
    {
        $budget = factory(Budget::class)->create();
        $user = User::find($budget->user_id);

        $response = $this->actingAs($user)->get(route('budget.delete', ['id' => $budget->id]));
        $this->assertAuthenticated();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
        $response->assertStatus(302);
    }
}