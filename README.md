
# Laravel CSV Processor with Queue Jobs

Este projeto é uma API desenvolvida com Laravel para processar arquivos CSV, utilizando filas para gerenciar o processamento assíncrono de grandes volumes de dados. A aplicação foi containerizada com Docker e inclui suporte para filas utilizando o driver de banco de dados.

---

## 📋 Funcionalidades

1. **Upload de Arquivo CSV:**
   - API para fazer upload de arquivos CSV, retornando o número de registros e processando os dados em segundo plano.
   
2. **Processamento em Filas:**
   - O processamento de cada linha do arquivo CSV é delegado a um Job, garantindo escalabilidade e desempenho.

3. **Controle de Jobs:**
   - Suporte para monitoramento de Jobs processados e Jobs com falha.

4. **Estrutura Modular:**
   - Uso de Services e Jobs para organizar a lógica de processamento.

---

## 🛠️ Pré-requisitos

1. **Instalar o Docker e Docker Compose**
   - Certifique-se de que o Docker e o Docker Compose estão instalados em sua máquina.

2. **Instalar Composer**
   - Certifique-se de que o Composer está instalado para gerenciar as dependências do Laravel.

3. **Requisitos do Sistema:**
   - PHP >= 8.1
   - MySQL >= 8.0

---

## 🚀 Configuração do Ambiente

1. **Clone o repositório:**

   ```bash
   git clone <url-do-repositorio>
   cd <nome-do-repositorio>
   ```

2. **Configurar o `.env`:**
   Copie o arquivo `.env.example` para `.env`:

   ```bash
   cp .env.example .env
   ```

   Certifique-se de configurar os seguintes valores no `.env`:
   ```env
   QUEUE_CONNECTION=database
   DB_CONNECTION=mysql
   DB_HOST=kanastra_db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=laravel
   ```

3. **Subir os containers:**

   ```bash
   docker-compose up -d --build
   ```

4. **Instalar as dependências do Laravel:**

   Acesse o contêiner `kanastra_app` e instale as dependências:

   ```bash
   docker-compose exec kanastra_app composer install
   ```

5. **Gerar a chave da aplicação:**

   ```bash
   docker-compose exec kanastra_app php artisan key:generate
   ```

6. **Migrar o banco de dados:**

   Execute as migrations para criar as tabelas necessárias, incluindo `jobs` e `failed_jobs`:

   ```bash
   docker-compose exec kanastra_app php artisan migrate
   ```

7. **Iniciar o worker das filas:**

   Para processar os Jobs em background, execute o comando:

   ```bash
   docker-compose exec kanastra_app php artisan queue:work --verbose
   ```

---

## 🔥 Como Usar a API

### 1. **Fazer Upload de um Arquivo CSV**

Endpoint: **`POST /api/upload`**

- **Descrição:** Faz o upload de um arquivo CSV, retorna o número de registros e inicia o processamento das linhas em segundo plano.
- **Formato Aceito:** `csv`, `txt`

#### Exemplo de Requisição:

```bash
curl -X POST -F "file=@input.csv" http://localhost:8081/api/upload
```

#### Exemplo de Resposta:

```json
{
    "message": "File uploaded and processing started",
    "lines": 1000
}
```

### 2. **Estrutura do Arquivo CSV**

O arquivo CSV deve conter as seguintes colunas:

```csv
name,governmentId,email,debtAmount,debtDueDate,debtID
John Doe,11111111111,johndoe@example.com,1000.00,2023-10-10,ea23f2ca-663a-4266-a742-9da4c9f4fcb3
Jane Doe,22222222222,janedoe@example.com,2000.00,2023-11-11,1adb6ccf-ff16-467f-bea7-5f05d494280f
```

---

## 📂 Estrutura do Projeto

- **Controller (`DebtController`):**
  - Gerencia o upload do arquivo e delega o processamento ao `DebtService`.

- **Service (`DebtService`):**
  - Responsável por adicionar o processamento do arquivo CSV à fila.

- **Jobs:**
  - **`ProcessCSVJob`**: Processa o arquivo CSV linha por linha e delega cada linha para o Job `ProcessDebtRow`.
  - **`ProcessDebtRow`**: Processa individualmente cada linha do CSV.

---

## 🛠️ Monitoramento das Filas

### 1. **Verificar Jobs Pendentes**

Os Jobs que aguardam processamento são armazenados na tabela `jobs`. Você pode verificar manualmente:

```sql
SELECT * FROM jobs;
```

### 2. **Verificar Jobs com Falha**

Os Jobs que falharem serão armazenados na tabela `failed_jobs`. Para listar:

```bash
php artisan queue:failed
```

### 3. **Reprocessar Jobs com Falha**

Reprocessar todos os Jobs com falha:

```bash
php artisan queue:retry all
```

### 4. **Logs do Laravel**

Os logs são gravados no arquivo `storage/logs/laravel.log`. Para monitorar em tempo real:

```bash
docker-compose exec kanastra_app tail -f storage/logs/laravel.log
```

---

## 🌟 Logs Esperados

- **Log de Linhas Processadas:**
  ```plaintext
  [2024-12-02 14:00:00] local.INFO: Processed debt row: ea23f2ca-663a-4266-a742-9da4c9f4fcb3
  [2024-12-02 14:01:00] local.INFO: Skipped duplicate debt row: 1adb6ccf-ff16-467f-bea7-5f05d494280f
  ```

- **Log de Conclusão do Arquivo:**
  ```plaintext
  [2024-12-02 14:05:00] local.INFO: Finished processing file: /path/to/file.csv
  ```

---

## 🧪 Testando o Sistema

1. Suba o ambiente com Docker:
   ```bash
   docker-compose up -d --build
   ```

2. Faça o upload do arquivo CSV:
   ```bash
   curl -X POST -F "file=@input.csv" http://localhost:8081/api/upload
   ```

3. Inicie o worker das filas:
   ```bash
   docker-compose exec kanastra_app php artisan queue:work --verbose
   ```

4. Verifique os logs para acompanhar o processamento:
   ```bash
   docker-compose exec kanastra_app tail -f storage/logs/laravel.log
   ```

---

## 🐳 Comandos Úteis no Docker

- **Reiniciar o ambiente:**
  ```bash
  docker-compose restart
  ```

- **Acessar o contêiner `kanastra_app`:**
  ```bash
  docker-compose exec kanastra_app bash
  ```

- **Acessar o banco de dados:**
  ```bash
  docker-compose exec kanastra_db mysql -u laravel -plaravel laravel
  ```

---

Com isso, você terá um ambiente funcional e a documentação para rodar, monitorar e verificar o processamento do CSV! Se precisar de ajustes, avise!
