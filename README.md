# pi

Projeto Integrador - CampVia

## Como testar a inscrição em rotas

1. **Prepare o banco de dados**
   - Crie um banco MySQL chamado `campvia`.
   - Importe o arquivo [`banco de dados/campvia.sql`](banco%20de%20dados/campvia.sql) para popular as tabelas, incluindo `rota_inscricao`.
   - Ajuste `includes/config.php` se o usuário/senha do seu MySQL forem diferentes de `root` sem senha.

2. **Suba o servidor PHP local**
   - Na raiz do projeto, execute `php -S localhost:8000` para iniciar o servidor embutido.
   - Acesse `http://localhost:8000` em um navegador.

3. **Crie ou use duas contas diferentes**
   - Cadastre dois usuários através de `Cadastrar` ou reutilize contas existentes no script SQL (por exemplo, `admin@gmail.com`/`admin123`).
   - Com a primeira conta, crie uma rota em `Criar Roteiro` (se não quiser usar as rotas de exemplo do SQL).

4. **Faça o teste de inscrição**
   - Desconecte-se e faça login com a segunda conta (que não é dona da rota).
   - Abra a rota desejada através da listagem em `Roteiros` ou diretamente em `paginas/ver-rota.php?id=<ID>`.
   - Clique em **Inscrever-se**. Uma mensagem verde deve confirmar a inscrição e o botão muda para **Cancelar inscrição**.

5. **Teste o cancelamento e confira no banco**
   - Clique novamente no botão para cancelar a inscrição; uma nova mensagem informará o resultado.
   - Opcionalmente, confirme em SQL: `SELECT * FROM rota_inscricao WHERE rota_id = <ID> AND usuario_id = <ID_USUARIO>;`.

Seguindo esses passos você valida o fluxo completo de inscrição e cancelamento em rotas criadas por outros usuários.
