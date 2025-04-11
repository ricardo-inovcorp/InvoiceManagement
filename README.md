# Sistema de Gestão de Faturas

Sistema desenvolvido para gerenciar faturas e fornecedores de uma empresa.

## Funcionalidades

- Gestão de Fornecedores
- Gestão de Faturas
- Notificações por email
- Tema claro/escuro
- Integração com API NIF.pt para preenchimento automático de dados de fornecedores

## Requisitos

- PHP >= 8.1
- Laravel 10
- Node.js >= 16
- MySQL ou SQLite
- Composer

## Instalação

1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/invoice-management.git
cd invoice-management
```

2. Instale as dependências
```bash
composer install
npm install
```

3. Configure o arquivo .env
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o banco de dados no arquivo .env
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_management
DB_USERNAME=root
DB_PASSWORD=
```

5. Migre o banco de dados
```bash
php artisan migrate --seed
```

6. Compile os assets
```bash
npm run dev
```

7. Inicie o servidor
```bash
php artisan serve
```

## Configuração da API NIF.pt

Para utilizar a funcionalidade de consulta automática de NIF, é necessário obter uma API key do site [NIF.pt](https://www.nif.pt/):

1. Acesse https://www.nif.pt/
2. Faça o registro e obtenha sua API key
3. Adicione a API key no arquivo .env:
   ```
   NIF_PT_API_KEY=sua_api_key_aqui
   ```

## Configuração de Email

Para utilizar as notificações por email, configure as variáveis de ambiente no arquivo .env:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_username
MAIL_PASSWORD=sua_senha
MAIL_FROM_ADDRESS=noreply@empresa.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Licença

Este projeto está licenciado sob a licença MIT. 