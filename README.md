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
