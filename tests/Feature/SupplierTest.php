<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Supplier;
use Illuminate\Support\Facades\Http;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    /**
     * Configura o ambiente antes dos testes.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Roda o seeder para criar o usuário padrão
        $this->artisan('db:seed');

        // Faz o login e salva o token
        $response = $this->postJson('/api/login', [
            'email' => 'revenda_mais@test.com',
            'password' => 'revenda_mais_password',
        ]);

        $this->token = $response->json('token');
    }

    /**
     * Testa o cadastro de fornecedor com CPF válido.
     */
    public function test_it_should_register_a_supplier_with_valid_cpf()
    {
        $data = [
            'name' => 'Fornecedor CPF',
            'cpf_cnpj' => '24945952078',
            'contact' => 'fornecedor@teste.com',
            'address' => 'Rua Exemplo, 123',
        ];

        $response = $this->postJson('/api/suppliers', $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Fornecedor cadastrado com sucesso.',
            ]);

        $this->assertDatabaseHas('suppliers', ['cpf_cnpj' => '24945952078']);
    }


    /**
     * Testa o erro ao cadastrar um fornecedor com CPF/CNPJ duplicado.
     */
    public function test_it_should_return_error_for_existing_cpf_or_cnpj()
    {
        Supplier::create([
            'name' => 'Fornecedor Existente',
            'cpf_cnpj' => '67752134414',
            'contact' => 'existente@teste.com',
            'address' => 'Rua Existente, 123',
        ]);

        $data = [
            'name' => 'Fornecedor Duplicado',
            'cpf_cnpj' => '67752134414',
            'contact' => 'duplicado@teste.com',
            'address' => 'Rua Duplicada, 123',
        ];

        $response = $this->postJson('/api/suppliers', $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Fornecedor já cadastrado.',
            ]);
    }

    /**
     * Testa a validação de CPF ou CNPJ inválido.
     */
    public function test_it_should_validate_invalid_cpf_or_cnpj()
    {
        $data = [
            'name' => 'Fornecedor Inválido',
            'cpf_cnpj' => '12345', // CPF/CNPJ inválido
            'contact' => 'invalid@teste.com',
            'address' => 'Rua Inválida, 123',
        ];

        $response = $this->postJson('/api/suppliers', $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Testa a validação de CNPJ com a BrasilAPI.
     */
    public function test_it_should_validate_cnpj_with_brasilapi()
    {
        $data = [
            'name' => 'Fornecedor CNPJ',
            'cpf_cnpj' => '19131243000197', // CNPJ válido
            'contact' => 'fornecedor@teste.com',
            'address' => 'Rua Exemplo, 456',
        ];

        $this->mockBrasilApiSuccess();

        $response = $this->postJson('/api/suppliers', $data, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Fornecedor cadastrado com sucesso.',
            ]);

        $this->assertDatabaseHas('suppliers', ['cpf_cnpj' => '19131243000197']);
    }

    /**
     * Testa a edição de um fornecedor existente.
     */
    public function test_it_should_update_a_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Original',
            'cpf_cnpj' => '24945952078',
            'contact' => 'original@teste.com',
            'address' => 'Rua Original, 123',
        ]);

        $updateData = [
            'name' => 'Fornecedor Atualizado',
            'cpf_cnpj' => '19131243000197',
            'contact' => 'atualizado@teste.com',
            'address' => 'Rua Atualizada, 456',
        ];

        $this->mockBrasilApiSuccess();

        $response = $this->putJson("/api/suppliers/{$supplier->id}", $updateData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Fornecedor atualizado com sucesso.',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Fornecedor Atualizado',
            'cpf_cnpj' => '19131243000197',
            'contact' => 'atualizado@teste.com',
            'address' => 'Rua Atualizada, 456',
        ]);
    }

    /**
     * Testa o erro ao editar um fornecedor com CPF/CNPJ duplicado.
     */
    public function test_it_should_return_error_for_duplicate_cpf_or_cnpj_on_update()
    {
        Supplier::create([
            'name' => 'Fornecedor Existente',
            'cpf_cnpj' => '24945952078',
            'contact' => 'existente@teste.com',
            'address' => 'Rua Existente, 123',
        ]);

        $supplierToUpdate = Supplier::create([
            'name' => 'Fornecedor Atualizável',
            'cpf_cnpj' => '19131243000197',
            'contact' => 'atualizavel@teste.com',
            'address' => 'Rua Atualizável, 456',
        ]);

        $updateData = [
            'name' => 'Fornecedor Atualizável',
            'cpf_cnpj' => '24945952078', // CPF já existente
        ];

        $response = $this->putJson("/api/suppliers/{$supplierToUpdate->id}", $updateData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'O CPF ou CNPJ já está sendo utilizado por outro fornecedor.',
            ]);
    }

    /**
     * Testa o erro ao tentar atualizar um fornecedor inexistente.
     */
    public function test_it_should_return_error_for_nonexistent_supplier_on_update()
    {
        $updateData = [
            'name' => 'Fornecedor Inexistente',
            'cpf_cnpj' => '24945952078',
            'contact' => 'inexistente@teste.com',
            'address' => 'Rua Inexistente, 789',
        ];

        $response = $this->putJson('/api/suppliers/9999', $updateData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Fornecedor não encontrado.',
            ]);
    }

    /**
     * Testa a exclusão de um fornecedor existente.
     */
    public function test_it_should_delete_a_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Fornecedor Excluir',
            'cpf_cnpj' => '12345678901',
            'contact' => 'excluir@teste.com',
            'address' => 'Rua Excluir, 123',
        ]);

        $response = $this->deleteJson("/api/suppliers/{$supplier->id}", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Fornecedor excluído com sucesso.',
            ]);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    /**
     * Testa a exclusão de um fornecedor inexistente.
     */
    public function test_it_should_return_error_for_nonexistent_supplier_on_delete()
    {
        $response = $this->deleteJson('/api/suppliers/9999', [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Fornecedor não encontrado.',
            ]);
    }

        /**
     * Testa a listagem de fornecedores com paginação.
     */
    public function test_it_should_list_suppliers_with_pagination()
    {
        // Cria 15 fornecedores
        Supplier::factory()->count(15)->create();

        // Faz a requisição para a rota de listagem de fornecedores
        $response = $this->getJson('/api/suppliers', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'cpf_cnpj',
                            'contact',
                            'address',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'total',
                    'per_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);

        // Verifica se a resposta contém 10 itens por página
        $this->assertCount(10, $response->json('data.data'));
    }

    /**
     * Testa a listagem de fornecedores com filtro de busca.
     */
    public function test_it_should_filter_suppliers_by_search()
    {
        // Cria fornecedores com nomes distintos
        Supplier::create([
            'name' => 'Fornecedor Filtro',
            'cpf_cnpj' => '12345678901',
            'contact' => 'fornecedor@teste.com',
            'address' => 'Rua Filtro, 123',
        ]);

        Supplier::create([
            'name' => 'Fornecedor Atualizável',
            'cpf_cnpj' => '19131243000197',
            'contact' => 'atualizavel@teste.com',
            'address' => 'Rua Atualizável, 456',
        ]);

        // Faz a requisição com o filtro "Filtro"
        $response = $this->getJson('/api/suppliers?search=Filtro', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'data' => [
                        ['name' => 'Fornecedor Filtro'],
                    ],
                ],
            ]);

        // Garante que apenas 1 fornecedor foi retornado
        $this->assertCount(1, $response->json('data.data'));
}





    //---------------------------------------------- private function ------------------------------------------------//

    /**
     * Mock para simular resposta da BrasilAPI com sucesso.
     */
    private function mockBrasilApiSuccess()
    {
        Http::fake([
            'brasilapi.com.br/api/cnpj/*' => Http::response([
                'status' => true,
                'message' => 'CNPJ válido',
            ], 200),
        ]);
    }
}
