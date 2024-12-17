<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class SupplierController extends Controller
{

    /**
     * Geralmente, prefiro manter todo o código, incluindo comentários e mensagens de retorno, em inglês,
     * seguindo boas práticas de padronização. No entanto, optei por utilizar comentários e mensagens
     * em português neste projeto, considerando que a empresa é brasileira. Acredito que isso facilite
     * os testes e a visualização durante a avaliação.
     */

    public function index(Request $request)
    {

        $cacheKey = $this->generateCacheKey($request);

        $suppliers = Cache::remember($cacheKey, 60, function () use ($request) {
            $query = Supplier::query();

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }

            if ($request->has('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortOrder = $request->input('sort_order', 'asc');
                $query->orderBy($sortBy, $sortOrder);
            }

            return $query->paginate(10);
        });

        return response()->json([
            'status' => true,
            'message' => 'Lista de fornecedores recuperada com sucesso.',
            'data' => $suppliers,
        ], 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $validationResult = $this->validateCpfOrCnpj($validated['cpf_cnpj']);
        if (!$validationResult['status']) {
            return $this->errorResponse($validationResult['message'], 422);
        }

        try {
            $supplier = Supplier::create($validated);
            Cache::tags(['suppliers'])->flush();
            return response()->json([
                'status' => true,
                'message' => 'Fornecedor cadastrado com sucesso.',
                'data' => $supplier,
            ], 201);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse('Fornecedor já cadastrado.', 422);
            }

            return $this->errorResponse('Erro ao cadastrar o fornecedor.', 500);
        }
    }

    public function update(Request $request, $id)
    {

        $supplier = Supplier::find($id);

        if (!$supplier) {
            return $this->errorResponse('Fornecedor não encontrado.', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        // Validação de CPF ou CNPJ
        $validationResult = $this->validateCpfOrCnpj($validated['cpf_cnpj']);
        if (!$validationResult['status']) {
            return $this->errorResponse($validationResult['message'], 422);
        }

        try {
            $supplier->update($validated);
            Cache::tags(['suppliers'])->flush();
            return response()->json([
                'status' => true,
                'message' => 'Fornecedor atualizado com sucesso.',
                'data' => $supplier,
            ], 200);
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse('O CPF ou CNPJ já está sendo utilizado por outro fornecedor.', 422);
            }

            return $this->errorResponse('Erro ao atualizar o fornecedor.', 500);
        }
    }

    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'status' => false,
                'message' => 'Fornecedor não encontrado.',
            ], 404);
        }

        try {
            $supplier->delete();
            Cache::tags(['suppliers'])->flush();
            return response()->json([
                'status' => true,
                'message' => 'Fornecedor excluído com sucesso.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir o fornecedor.',
            ], 500);
        }
    }





    //---------------------------------------------- private function ------------------------------------------------//

    /**
     * Normalmente, essas validações seriam abstraídas para um Helper ou Service,
     * garantindo uma melhor organização e reutilização do código. No entanto,
     * para fins de simplificação neste teste, as validações foram mantidas
     * diretamente na Controller.
     */



    private function validateCpfOrCnpj(string $cpfCnpj)
    {
        if (strlen($cpfCnpj) === 14) {
            // CNPJ
            $validationResult = $this->validateCnpj($cpfCnpj);
            if (!$validationResult['status']) {
                return [
                    'status' => false,
                    'message' => $validationResult['message'],
                ];
            }
        } elseif (strlen($cpfCnpj) === 11) {
            if (!$this->validateCpf($cpfCnpj)) {
                return [
                    'status' => false,
                    'message' => 'CPF inválido.',
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'CPF deve conter 11 dígitos ou CNPJ 14 dígitos.',
            ];
        }

        return ['status' => true];
    }

    /**
     * Valida o CNPJ usando a BrasilAPI.
     */
    private function validateCnpj(string $cnpj)
    {
        try {
            $client = new Client();
            $response = $client->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if ($response->getStatusCode() === 200) {
                return ['status' => true];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'CNPJ inválido ou não encontrado na BrasilAPI.',
            ];
        }

        return [
            'status' => false,
            'message' => 'Erro ao validar o CNPJ.',
        ];
    }



    private function validateCpf(string $cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }


    private function errorResponse(string $message, int $statusCode)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Gera uma chave de cache única com base nos parâmetros da requisição.
     */
    private function generateCacheKey(Request $request)
    {
        $key = 'suppliers:';
        $key .= 'search=' . $request->input('search', '') . ';';
        $key .= 'sort_by=' . $request->input('sort_by', '') . ';';
        $key .= 'sort_order=' . $request->input('sort_order', 'asc') . ';';
        $key .= 'page=' . $request->input('page', 1);

        return md5($key);
    }
}
