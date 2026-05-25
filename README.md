# Centro de Formação Operacional

Aplicação web em **PHP 8+ MVC puro** com **Bootstrap 5** e **SQLite** para gestão de vídeos de formação interna.

## Requisitos
- PHP 8+
- Extensão `pdo_sqlite`
- Servidor web (Apache/Nginx) ou `php -S`

## Instalação rápida
1. Criar permissões de escrita em `storage/`.
2. Abrir `http://seu-host/install.php`.
3. Clicar em **Instalar agora**.
4. Remover/proteger a pasta `install/` em produção.

## Configuração
- App: `config/config.php`
- Base de dados SQLite: `config/database.php`
- Ficheiro DB: `storage/database.sqlite`

## Executar localmente
```bash
php -S localhost:8000 -t public
```

## Login inicial
- Username: `admin`
- Password: `admin123`
- Email: `admin@empresa.local`

## Estrutura
- `app/`: MVC e Core
- `public/`: front controller e assets
- `database/`: schema e seeds SQLite
- `install/` + `install.php`: instalador
- `storage/`: base SQLite, backups e logs

## Permissões e módulos
- Permissões base são inseridas no instalador.
- CRUD completos podem ser evoluídos por módulos dentro de `app/Controllers`, `app/Models` e `app/Views`.

## Backups SQLite
- Guardar cópia de `storage/database.sqlite` em `storage/backups/`.
- Restaurar substituindo o ficheiro principal com a app desligada.

## Segurança (produção)
- HTTPS obrigatório
- Sessões seguras com `httponly`, `secure` e `samesite`
- Remover/proteger `/install`
- Rever limites de upload e MIME


## Deploy em alojamento partilhado (403 Forbidden)
Se o servidor abrir a raiz do projeto e mostrar **403 Forbidden**, use uma destas opções:

1. **Recomendado:** apontar o DocumentRoot para a pasta `public/`.
2. Se não for possível alterar o DocumentRoot, esta base já inclui:
   - `index.php` na raiz (proxy para `public/index.php`)
   - `.htaccess` na raiz e em `public/` para reescrita de rotas

Também confirme permissões de leitura para o projeto e escrita em `storage/`.


## Diagnóstico de 404 após instalação
Se a app ficar em subpasta (ex.: `/formacao`) e devolver 404, esta versão já remove automaticamente o base path no `public/index.php` antes do dispatch de rotas.

Também foi adicionado log em:
- `storage/logs/app.log`

O log regista:
- URI recebida e rota despachada
- Rotas não encontradas (404)
- Erros/exceções PHP

Confirme também:
- `storage/` com escrita
- `mod_rewrite` ativo (Apache)
- `.htaccess` permitido (`AllowOverride All`)
