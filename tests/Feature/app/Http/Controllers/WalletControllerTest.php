<?php

namespace Tests\Feature\app\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    /** @test */
    public function should_be_able_to_create_a_new_transaction()
    {   
        $payload = [
            'title' => 'Projeto Freelancer',
            'value' => 10,
            'type'  => 'income'
        ];

        $response = $this->postJson(route('/transactions'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'Projeto Freelancer',
            'value' => 10,
            'type'  => 'income'
        ]);
    }

    /** @test */
    public function should_be_able_to_list_the_transactions()
    {
        $response = $this->getJson(route('/transactions'));

        $response->assertStatus(200);
        $response->assertJson([
            'transactions' => [],
            'balance' => []
        ]);
    }

    /** @test */
    public function should_not_be_able_to_create_outcome_transaction_without_a_valid_balance()
    {
        $payload = [
            'title' => 'Projeto Freelancer',
            'value' => 99999999999,
            'type'  => 'outcome'
        ];

        $response = $this->postJson(route('/transactions'), $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Saldo insuficiente para sacar'
        ]);
    }
}
