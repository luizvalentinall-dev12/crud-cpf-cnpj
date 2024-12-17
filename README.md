# CNPJ CPF Manager

## Código desenvolvido por **Luiz Gustavo Valter Valentin**
### Obs: Meu objetivo foi realizar os itens solicitados utilizando Laravel, mas para uma aplicação desse porte eu usaria algo como Lumen ou Fastify JS, visando que a complexidade e o tamanho do código ficassem menores. Também adicionei o Redis apenas para demonstrar a implementação de cache utilizando essa tecnologia.

## Descrição

Este é um projeto de API desenvolvido com **Laravel 11** para gerenciamento de fornecedores, permitindo:
- Autenticação de usuários com Sanctum.
- Cadastro, edição, exclusão e listagem de fornecedores com validações para CPF e CNPJ (incluindo integração com BrasilAPI).
- Paginação, filtros e ordenação na listagem de fornecedores.

---

## Pré-requisitos

- **Docker** e **Docker Compose** instalados na máquina.

---

## Configuração do Ambiente

### 1. Configurar o Arquivo `.env`
Antes de iniciar o ambiente, copie o arquivo `.env.example` para `.env`:

```bash
cp .env.example .env
```

O arquivo `.env.example` já está configurado com as informações do ambiente de desenvolvimento em Docker, facilitando os testes e a inicialização do projeto.

### 2. Iniciar o Docker
Antes de iniciar o Docker, garanta que o Laravel tem as permissões adequadas para acessar as pastas necessárias. Execute o comando abaixo para corrigir permissões:

```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

Em seguida, inicie o Docker com o comando:
Certifique-se de que o **Docker** está em execução. Para iniciar o ambiente, execute:

```bash
docker-compose up --build
```

O comando irá:
- Construir o ambiente com PHP 8.3, MySQL 8.0 e Redis.
- Iniciar os serviços necessários para o projeto.

A aplicação estará disponível em: [http://localhost:8080](http://localhost:8080)

---

### 2. Rodar Migrations e Seeds

Após iniciar o Docker, entre no contêiner da aplicação:

```bash
docker exec -it revenda_mais_app bash
```

Execute os comandos para rodar as migrations e seeds:

```bash
php artisan migrate --seed
```

Isso irá:
- Criar as tabelas no banco de dados.
- Popular o banco com o usuário padrão para login:

| Email                 | Senha                     |
|-----------------------|---------------------------|
| `revenda_mais@test.com` | `revenda_mais_password`   |

---

### 3. Executar Testes Automatizados

**Aviso:** Os testes automatizados limpam o banco de dados antes da execução. Após rodar os testes, será necessário rodar novamente o seeder para recriar o usuário padrão.

Dentro do contêiner, execute os testes automatizados com:

```bash
php artisan test
```

Isso irá:
- Testar todas as funcionalidades das APIs.
- Validar regras de negócios, como cadastro, atualização, exclusão e listagem de fornecedores.

---

### 4. Testes Manuais nas APIs

A API possui as seguintes rotas, todas protegidas por autenticação com **Sanctum** (exceto `/login`).

#### **Rotas da API**

| Método | Rota               | Descrição                                           |
|--------|--------------------|---------------------------------------------------|
| POST   | `/api/login`       | Realiza o login e retorna o token de autenticação. |
| POST   | `/api/logout`      | Realiza o logout e invalida o token atual.         |
| GET    | `/api/user`        | Retorna os dados do usuário autenticado.           |
| POST   | `/api/suppliers`   | Cadastra um novo fornecedor.                       |
| PUT    | `/api/suppliers/{id}` | Atualiza os dados de um fornecedor existente.     |
| DELETE | `/api/suppliers/{id}` | Exclui um fornecedor.                             |
| GET    | `/api/suppliers`   | Lista fornecedores com paginação, filtros e ordenação.|

#### **Como Testar Manualmente**

1. **Login**  
   - Endpoint: `POST /api/login`
   - Enviar:
     ```json
     {
       "email": "revenda_mais@test.com",
       "password": "revenda_mais_password"
     }
     ```
   - Retorna:
     ```json
     {
       "token": "SEU_TOKEN"
     }
     ```

2. **Autenticação**  
   Para todas as rotas protegidas, envie o token no cabeçalho:
   ```bash
   Authorization: Bearer SEU_TOKEN
   ```

3. **Cadastro de Fornecedor**  
   - Endpoint: `POST /api/suppliers`
   - Enviar:
     ```json
     {
       "name": "Fornecedor Exemplo",
       "cpf_cnpj": "12345678901",
       "contact": "exemplo@teste.com",
       "address": "Rua Exemplo, 123"
     }
     ```

4. **Atualização de Fornecedor**  
   - Endpoint: `PUT /api/suppliers/{id}`
   - Enviar:
     ```json
     {
       "name": "Fornecedor Atualizado",
       "cpf_cnpj": "19131243000197",
       "contact": "atualizado@teste.com",
       "address": "Rua Atualizada, 456"
     }
     ```

5. **Exclusão de Fornecedor**  
   - Endpoint: `DELETE /api/suppliers/{id}`

6. **Listagem de Fornecedores**  
   - Endpoint: `GET /api/suppliers`
   - Parâmetros opcionais:
     - `search`: Busca por nome, CPF/CNPJ, contato ou endereço.
     - `sort_by`: Campo para ordenação (`name`, `cpf_cnpj`, etc.).
     - `sort_order`: Direção (`asc` ou `desc`).
     - `page`: Número da página.

---

### Observações Finais
- CORS (Cross-Origin Resource Sharing), CSRF (Cross-Site Request Forgery) e outros métodos de segurança não foram implementados neste projeto, pois o objetivo principal foi demonstrar as funcionalidades solicitadas de maneira funcional e direta.

- Em um ambiente de produção, essas medidas de segurança são essenciais para proteger a aplicação contra ataques comuns e garantir que as requisições sejam realizadas apenas por fontes confiáveis. No entanto, para este projeto de exemplo, priorizei a entrega rápida das funcionalidades solicitadas, deixando a implementação de segurança avançada para etapas futuras, caso seja necessário transformar esta API em um produto final robusto.

- Cabe destacar que o ambiente atual está configurado para ser utilizado apenas em localhost ou em um ambiente controlado de desenvolvimento, reduzindo os riscos associados à ausência dessas proteções.


Se precisar de mais informações ou ajuda, sinta-se à vontade para perguntar! 🚀
