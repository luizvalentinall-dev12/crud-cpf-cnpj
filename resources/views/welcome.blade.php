<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNPJ Manager - Documentação</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="text-gray-800 bg-gray-100">
    <div class="container px-4 py-8 mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-bold">CNPJ Manager</h1>
            <p class="text-gray-600">Código desenvolvido por <strong>Luiz Gustavo Valter Valentin</strong></p>
            <p class="text-gray-600">
                <em>Meu objetivo foi realizar os itens solicitados utilizando Laravel, mas para uma aplicação desse
                    porte eu usaria algo como Lumen ou Fastify JS, visando que a complexidade e o tamanho do código
                    ficassem menores. Também adicionei o Redis apenas para demonstrar a implementação de cache
                    utilizando essa tecnologia.</em>
            </p>
        </header>

        <section class="mb-8">
            <h2 class="text-2xl font-bold">Descrição</h2>
            <p>Este é um projeto de API desenvolvido com <strong>Laravel 11</strong> para gerenciamento de fornecedores,
                permitindo:</p>
            <ul class="pl-6 list-disc">
                <li>Autenticação de usuários com Sanctum.</li>
                <li>Cadastro, edição, exclusão e listagem de fornecedores com validações para CPF e CNPJ (incluindo
                    integração com BrasilAPI).</li>
                <li>Paginação, filtros e ordenação na listagem de fornecedores.</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-bold">Pré-requisitos</h2>
            <ul class="pl-6 list-disc">
                <li><strong>Docker</strong> e <strong>Docker Compose</strong> instalados na máquina.</li>
            </ul>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-bold">Configuração do Ambiente</h2>
            <ol class="pl-6 list-decimal">
                <li>
                    <strong>Configurar o arquivo <code>.env</code></strong>
                    <p>Copie o arquivo <code>.env.example</code> para <code>.env</code>:</p>
                    <pre class="p-2 bg-gray-200 rounded">cp .env.example .env</pre>
                    <p>O arquivo <code>.env.example</code> já está configurado com as informações do ambiente de
                        desenvolvimento em Docker.</p>
                </li>
                <li>
                    <strong>Ajustar permissões do Laravel</strong>
                    <pre class="p-2 bg-gray-200 rounded">
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
                    </pre>
                </li>
                <li>
                    <strong>Iniciar o Docker</strong>
                    <pre class="p-2 bg-gray-200 rounded">docker-compose up --build</pre>
                    <p>A aplicação estará disponível em: <a href="http://localhost:8080"
                            class="text-blue-500 underline">http://localhost:8080</a></p>
                </li>
                <li>
                    <strong>Rodar Migrations e Seeds</strong>
                    <pre class="p-2 bg-gray-200 rounded">
docker exec -it revenda_mais_app bash
php artisan migrate --seed
                    </pre>
                    <p>Isso criará o usuário padrão:</p>
                    <table class="mt-2 border border-collapse border-gray-300 table-auto">
                        <tr>
                            <th class="px-4 py-2 border border-gray-300">Email</th>
                            <th class="px-4 py-2 border border-gray-300">Senha</th>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 border border-gray-300">revenda_mais@test.com</td>
                            <td class="px-4 py-2 border border-gray-300">revenda_mais_password</td>
                        </tr>
                    </table>
                </li>
            </ol>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-bold">Testes</h2>
            <h3 class="text-xl font-semibold">Automatizados</h3>
            <p>Execute os testes automatizados dentro do contêiner:</p>
            <pre class="p-2 bg-gray-200 rounded">php artisan test</pre>
            <p><strong>Aviso:</strong> Os testes limpam o banco de dados. Após os testes, rode novamente os seeds:</p>
            <pre class="p-2 bg-gray-200 rounded">php artisan db:seed</pre>

            <h3 class="text-xl font-semibold">Manuais</h3>
            <p>As rotas disponíveis são:</p>
            <table class="mt-2 border border-collapse border-gray-300 table-auto">
                <tr>
                    <th class="px-4 py-2 border border-gray-300">Método</th>
                    <th class="px-4 py-2 border border-gray-300">Rota</th>
                    <th class="px-4 py-2 border border-gray-300">Descrição</th>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">POST</td>
                    <td class="px-4 py-2 border border-gray-300">/api/login</td>
                    <td class="px-4 py-2 border border-gray-300">Login do usuário</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">POST</td>
                    <td class="px-4 py-2 border border-gray-300">/api/logout</td>
                    <td class="px-4 py-2 border border-gray-300">Logout do usuário</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">GET</td>
                    <td class="px-4 py-2 border border-gray-300">/api/user</td>
                    <td class="px-4 py-2 border border-gray-300">Dados do usuário autenticado</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">POST</td>
                    <td class="px-4 py-2 border border-gray-300">/api/suppliers</td>
                    <td class="px-4 py-2 border border-gray-300">Cadastro de fornecedor</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">PUT</td>
                    <td class="px-4 py-2 border border-gray-300">/api/suppliers/{id}</td>
                    <td class="px-4 py-2 border border-gray-300">Atualização de fornecedor</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">DELETE</td>
                    <td class="px-4 py-2 border border-gray-300">/api/suppliers/{id}</td>
                    <td class="px-4 py-2 border border-gray-300">Exclusão de fornecedor</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 border border-gray-300">GET</td>
                    <td class="px-4 py-2 border border-gray-300">/api/suppliers</td>
                    <td class="px-4 py-2 border border-gray-300">Listagem de fornecedores</td>
                </tr>
            </table>
        </section>

        <footer class="mt-8 text-center">
            <p class="text-gray-500">&copy; 2024 Luiz Gustavo Valter Valentin</p>
        </footer>
    </div>
</body>

</html>