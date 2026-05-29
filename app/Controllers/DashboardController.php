<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Middleware;

class DashboardController extends Controller
{
    private ?Database $database = null;

    private function db(): Database
    {
        if ($this->database === null) {
            $this->database = new Database(require __DIR__ . '/../../config/database.php');
            $this->initializePersistenceSchema();
        }

        return $this->database;
    }

    private function initializePersistenceSchema(): void
    {
        $pdo = $this->database->pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS departments (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT, status TEXT DEFAULT "active", created_at TEXT, updated_at TEXT)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT, is_admin INTEGER DEFAULT 0, created_at TEXT, updated_at TEXT)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT UNIQUE, username TEXT UNIQUE NOT NULL, password TEXT NOT NULL, department_id INTEGER NULL, role_id INTEGER NOT NULL, status TEXT DEFAULT "active", must_change_password INTEGER DEFAULT 0, last_login_at TEXT NULL, created_at TEXT, updated_at TEXT, FOREIGN KEY(department_id) REFERENCES departments(id), FOREIGN KEY(role_id) REFERENCES roles(id))');
        $pdo->exec('CREATE TABLE IF NOT EXISTS training_contents (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, description TEXT DEFAULT "", type TEXT NOT NULL DEFAULT "Vídeo", department TEXT NOT NULL DEFAULT "", training_path TEXT NOT NULL DEFAULT "", visible_for TEXT NOT NULL DEFAULT "", editable_by TEXT NOT NULL DEFAULT "", video_url TEXT NOT NULL DEFAULT "", created_at TEXT, updated_at TEXT)');
    }

    private function defaultProfiles(): array
    {
        return ['Formador', 'Operador', 'Gestor', 'Admin'];
    }

    private function defaultScopes(): array
    {
        return ['Produção', 'Qualidade', 'RH', 'Global'];
    }

    private function defaultPermissions(): array
    {
        return ['user' => 'Ana Martins', 'profile' => 'Formador', 'scope' => 'Produção', 'can_view' => true, 'can_edit' => true, 'can_approve' => false];
    }

    private function defaultContents(): array
    {
        return [
            ['id' => 1, 'title' => 'Setup de Máquina X', 'type' => 'Vídeo', 'department' => 'Produção', 'training_path' => 'Software > Solune > RH', 'visible_for' => 'Produção A/B', 'editable_by' => 'Formadores Produção', 'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4'],
            ['id' => 2, 'title' => 'Checklist de Segurança', 'type' => 'PDF', 'department' => 'Global', 'training_path' => 'Software > Solune > Logística', 'visible_for' => 'Todos', 'editable_by' => 'Gestores + HSE', 'video_url' => ''],
        ];
    }

    private function loadInitialContents(): array
    {
        $legacyPath = dirname(__DIR__, 2) . '/storage/contents.json';
        if (is_file($legacyPath)) {
            $decoded = json_decode((string)file_get_contents($legacyPath), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->defaultContents();
    }

    private function normalizeContent(array $content, int $fallbackId): array
    {
        return [
            'id' => (int)($content['id'] ?? $fallbackId),
            'title' => trim((string)($content['title'] ?? '')),
            'description' => trim((string)($content['description'] ?? '')),
            'type' => trim((string)($content['type'] ?? 'Vídeo')),
            'department' => trim((string)($content['department'] ?? '')),
            'training_path' => trim((string)($content['training_path'] ?? '')),
            'visible_for' => trim((string)($content['visible_for'] ?? '')),
            'editable_by' => trim((string)($content['editable_by'] ?? '')),
            'video_url' => trim((string)($content['video_url'] ?? '')),
        ];
    }

    private function ensureContentRows(): void
    {
        $count = (int)$this->db()->fetch('SELECT COUNT(*) AS total FROM training_contents')['total'];
        if ($count > 0) {
            return;
        }

        $this->saveContents($this->loadInitialContents());
    }

    private function loadContents(): array
    {
        $this->ensureContentRows();
        $rows = $this->db()->fetchAll('SELECT id, title, description, type, department, training_path, visible_for, editable_by, video_url FROM training_contents ORDER BY id');

        $contents = [];
        foreach ($rows as $row) {
            $normalized = $this->normalizeContent($row, count($contents) + 1);
            if ($normalized['id'] <= 0 || $normalized['title'] === '') {
                continue;
            }

            $contents[] = $normalized;
        }

        return array_values($contents);
    }

    private function saveContents(array $contents): bool
    {
        $pdo = $this->db()->pdo();

        try {
            $pdo->beginTransaction();
            $pdo->exec('DELETE FROM training_contents');
            $stmt = $pdo->prepare('INSERT INTO training_contents (id, title, description, type, department, training_path, visible_for, editable_by, video_url, created_at, updated_at) VALUES (:id, :title, :description, :type, :department, :training_path, :visible_for, :editable_by, :video_url, datetime("now"), datetime("now"))');

            $normalized = [];
            foreach ($contents as $content) {
                if (!is_array($content)) {
                    continue;
                }

                $item = $this->normalizeContent($content, count($normalized) + 1);
                $stmt->execute([
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'department' => $item['department'],
                    'training_path' => $item['training_path'],
                    'visible_for' => $item['visible_for'],
                    'editable_by' => $item['editable_by'],
                    'video_url' => $item['video_url'],
                ]);
                $normalized[] = $item;
            }

            $pdo->commit();
            $_SESSION['contents'] = array_values($normalized);
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }

    private function insertContent(array $content): bool
    {
        try {
            $item = $this->normalizeContent($content, 0);
            $this->db()->query('INSERT INTO training_contents (title, description, type, department, training_path, visible_for, editable_by, video_url, created_at, updated_at) VALUES (:title, :description, :type, :department, :training_path, :visible_for, :editable_by, :video_url, datetime("now"), datetime("now"))', [
                'title' => $item['title'],
                'description' => $item['description'],
                'type' => $item['type'],
                'department' => $item['department'],
                'training_path' => $item['training_path'],
                'visible_for' => $item['visible_for'],
                'editable_by' => $item['editable_by'],
                'video_url' => $item['video_url'],
            ]);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function replaceContents(array $contents): bool
    {
        return $this->saveContents($contents);
    }

    private function deleteContentById(int $id): bool
    {
        try {
            $this->db()->delete('training_contents', 'id = :id', ['id' => $id]);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function localPublicPathFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return '';
        }

        $uploadsPosition = strpos($path, '/uploads/');
        if ($uploadsPosition === false) {
            return '';
        }

        $relativePath = substr($path, $uploadsPosition + 1);
        $relativePath = str_replace(['..', '\\'], '', $relativePath);

        return dirname(__DIR__, 2) . '/public/' . $relativePath;
    }

    private function deleteLocalContentFile(string $url): void
    {
        $path = $this->localPublicPathFromUrl($url);
        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    private function defaultKnowledgeNodes(): array
    {
        return [
            ['id' => 1, 'path' => 'Software > Solune > RH'],
            ['id' => 2, 'path' => 'Software > Solune > Logística'],
        ];
    }


    private function knowledgeStoragePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/knowledge_nodes.json';
    }

    private function loadKnowledgeNodes(): array
    {
        $path = $this->knowledgeStoragePath();

        if (!is_file($path)) {
            $defaults = $this->defaultKnowledgeNodes();
            $this->saveKnowledgeNodes($defaults);
            return $defaults;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $this->defaultKnowledgeNodes();
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $this->defaultKnowledgeNodes();
        }

        $normalized = [];
        foreach ($decoded as $index => $node) {
            $pathValue = trim((string)($node['path'] ?? ''));
            if ($pathValue === '') {
                continue;
            }

            $normalized[] = [
                'id' => (int)($node['id'] ?? ($index + 1)),
                'path' => $pathValue,
            ];
        }

        usort($normalized, fn ($a, $b) => strnatcasecmp($a['path'], $b['path']));

        return array_values($normalized);
    }

    private function saveKnowledgeNodes(array $nodes): bool
    {
        $path = $this->knowledgeStoragePath();
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $payload = json_encode(array_values($nodes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return false;
        }

        return file_put_contents($path, $payload, LOCK_EX) !== false;
    }

    private function buildKnowledgeTree(array $nodes): array
    {
        $tree = [];

        foreach ($nodes as $node) {
            $segments = array_values(array_filter(array_map('trim', explode('>', (string)($node['path'] ?? '')))));
            if (empty($segments)) {
                continue;
            }

            $cursor = &$tree;
            foreach ($segments as $segment) {
                if (!isset($cursor[$segment])) {
                    $cursor[$segment] = [];
                }
                $cursor = &$cursor[$segment];
            }
            unset($cursor);
        }

        ksort($tree, SORT_NATURAL | SORT_FLAG_CASE);
        return $tree;
    }

    private function defaultUsers(): array
    {
        return [
            ['id' => 1, 'name' => 'Ana Martins', 'email' => 'ana@empresa.pt', 'role' => 'Formadora', 'department' => 'Produção', 'status' => 'Ativo', 'password' => 'Ana@1234'],
            ['id' => 2, 'name' => 'Carlos Silva', 'email' => 'carlos@empresa.pt', 'role' => 'Operador', 'department' => 'Qualidade', 'status' => 'Ativo', 'password' => 'Carlos@1234'],
            ['id' => 3, 'name' => 'Rita Costa', 'email' => 'rita@empresa.pt', 'role' => 'Gestora RH', 'department' => 'RH', 'status' => 'Pendente', 'password' => 'Rita@1234'],
        ];
    }

    private function loadInitialUsers(): array
    {
        $legacyPath = dirname(__DIR__, 2) . '/storage/users.json';
        if (is_file($legacyPath)) {
            $decoded = json_decode((string)file_get_contents($legacyPath), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->defaultUsers();
    }

    private function normalizeUser(array $user, int $fallbackId): array
    {
        return [
            'id' => (int)($user['id'] ?? $fallbackId),
            'name' => trim((string)($user['name'] ?? '')),
            'email' => trim((string)($user['email'] ?? '')),
            'role' => trim((string)($user['role'] ?? '')),
            'department' => trim((string)($user['department'] ?? '')),
            'status' => trim((string)($user['status'] ?? 'Ativo')),
            'password' => trim((string)($user['password'] ?? '')),
        ];
    }

    private function statusToDatabase(string $status): string
    {
        return match ($status) {
            'Ativo' => 'active',
            'Pendente' => 'pending',
            'Inativo' => 'inactive',
            default => $status !== '' ? $status : 'active',
        };
    }

    private function statusFromDatabase(string $status): string
    {
        return match ($status) {
            'active' => 'Ativo',
            'pending' => 'Pendente',
            'inactive' => 'Inativo',
            default => $status !== '' ? $status : 'Ativo',
        };
    }

    private function ensureRoleId(string $name): int
    {
        $name = trim($name) !== '' ? trim($name) : 'Colaborador';
        $role = $this->db()->fetch('SELECT id FROM roles WHERE name = :name LIMIT 1', ['name' => $name]);
        if ($role) {
            return (int)$role['id'];
        }

        return $this->db()->insert('roles', [
            'name' => $name,
            'description' => $name,
            'is_admin' => in_array($name, ['Super Admin', 'Administrador', 'Admin'], true) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function ensureDepartmentId(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $department = $this->db()->fetch('SELECT id FROM departments WHERE name = :name LIMIT 1', ['name' => $name]);
        if ($department) {
            return (int)$department['id'];
        }

        return $this->db()->insert('departments', [
            'name' => $name,
            'description' => '',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function usernameFromEmail(string $email, int $fallbackId): string
    {
        $username = trim((string)strstr($email, '@', true));
        if ($username === '') {
            $username = 'utilizador' . $fallbackId;
        }

        return preg_replace('/[^A-Za-z0-9_.-]/', '', $username) ?: 'utilizador' . $fallbackId;
    }

    private function uniqueUsername(string $baseUsername, ?int $ignoreUserId = null): string
    {
        $baseUsername = $baseUsername !== '' ? $baseUsername : 'utilizador';
        $username = $baseUsername;
        $suffix = 2;

        while (true) {
            $params = ['username' => $username];
            $sql = 'SELECT id FROM users WHERE username = :username';
            if ($ignoreUserId !== null) {
                $sql .= ' AND id != :id';
                $params['id'] = $ignoreUserId;
            }

            $existing = $this->db()->fetch($sql . ' LIMIT 1', $params);
            if (!$existing) {
                return $username;
            }

            $username = $baseUsername . $suffix;
            $suffix++;
        }
    }

    private function passwordForDatabase(string $password): string
    {
        if ($password === '') {
            $password = bin2hex(random_bytes(8));
        }

        $info = password_get_info($password);
        return $info['algo'] !== 0 ? $password : password_hash($password, PASSWORD_DEFAULT);
    }

    private function rowToUser(array $row): array
    {
        return [
            'id' => (int)$row['id'],
            'name' => (string)$row['name'],
            'email' => (string)($row['email'] ?? ''),
            'role' => (string)($row['role'] ?? ''),
            'department' => (string)($row['department'] ?? ''),
            'status' => $this->statusFromDatabase((string)($row['status'] ?? 'active')),
            'password' => '',
        ];
    }

    private function ensureUserRows(): void
    {
        $count = (int)$this->db()->fetch('SELECT COUNT(*) AS total FROM users')['total'];
        if ($count > 0) {
            return;
        }

        $this->saveUsers($this->loadInitialUsers());
    }

    private function getUsers(): array
    {
        $this->ensureUserRows();
        $rows = $this->db()->fetchAll('SELECT u.id, u.name, u.email, u.status, r.name AS role, COALESCE(d.name, "") AS department FROM users u JOIN roles r ON r.id = u.role_id LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.id');

        return array_map(fn ($row) => $this->rowToUser($row), $rows);
    }

    private function insertUserRecord(array $user): bool
    {
        try {
            $item = $this->normalizeUser($user, 0);
            if ($item['name'] === '') {
                return false;
            }

            $this->db()->query('INSERT INTO users (name, email, username, password, department_id, role_id, status, must_change_password, created_at, updated_at) VALUES (:name, :email, :username, :password, :department_id, :role_id, :status, 0, datetime("now"), datetime("now"))', [
                'name' => $item['name'],
                'email' => $item['email'],
                'username' => $this->uniqueUsername($this->usernameFromEmail($item['email'], time())),
                'password' => $this->passwordForDatabase($item['password']),
                'department_id' => $this->ensureDepartmentId($item['department']),
                'role_id' => $this->ensureRoleId($item['role']),
                'status' => $this->statusToDatabase($item['status']),
            ]);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function updateUserRecord(int $id, array $user, string $newPassword): bool
    {
        try {
            $item = $this->normalizeUser($user, $id);
            $data = [
                'name' => $item['name'],
                'email' => $item['email'],
                'department_id' => $this->ensureDepartmentId($item['department']),
                'role_id' => $this->ensureRoleId($item['role']),
                'status' => $this->statusToDatabase($item['status']),
                'updated_at' => date('Y-m-d H:i:s'),
                'username' => $this->uniqueUsername($this->usernameFromEmail($item['email'], $id), $id),
            ];

            if ($newPassword !== '') {
                $data['password'] = $this->passwordForDatabase($newPassword);
            }

            $this->db()->update('users', $data, 'id = :id', ['id' => $id]);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function deleteUserById(int $id): bool
    {
        try {
            $this->db()->delete('users', 'id = :id', ['id' => $id]);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function saveUsers(array $users): bool
    {
        $pdo = $this->db()->pdo();

        try {
            $pdo->beginTransaction();
            $pdo->exec('DELETE FROM users');
            $stmt = $pdo->prepare('INSERT INTO users (id, name, email, username, password, department_id, role_id, status, must_change_password, created_at, updated_at) VALUES (:id, :name, :email, :username, :password, :department_id, :role_id, :status, 0, datetime("now"), datetime("now"))');

            $normalized = [];
            foreach ($users as $user) {
                if (!is_array($user)) {
                    continue;
                }

                $item = $this->normalizeUser($user, count($normalized) + 1);
                if ($item['name'] === '') {
                    continue;
                }

                $stmt->execute([
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'username' => $this->uniqueUsername($this->usernameFromEmail($item['email'], $item['id'])),
                    'password' => $this->passwordForDatabase($item['password']),
                    'department_id' => $this->ensureDepartmentId($item['department']),
                    'role_id' => $this->ensureRoleId($item['role']),
                    'status' => $this->statusToDatabase($item['status']),
                ]);
                $normalized[] = $item;
            }

            $pdo->commit();
            $_SESSION['users'] = array_values($normalized);
            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }

    public function index(): void
    {
        Middleware::auth();
        $this->view('dashboard/index', ['title' => 'Centro de Formação']);
    }


    public function profile(): void
    {
        Middleware::auth();
        $history = $_SESSION['content_history'] ?? [];
        $this->view('profile/index', ['title' => 'Meu Perfil', 'contentHistory' => $history]);
    }

    public function users(): void
    {
        Middleware::auth();
        $users = $this->getUsers();

        $this->view('admin/users/index', ['title' => 'Utilizadores', 'users' => $users]);
    }

    public function createUserForm(): void
    {
        Middleware::auth();
        $this->view('admin/users/create', ['title' => 'Novo Utilizador']);
    }

    public function storeUser(): void
    {
        Middleware::auth();
        $user = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'status' => trim($_POST['status'] ?? 'Ativo'),
            'password' => trim($_POST['password'] ?? ''),
        ];

        if ($this->insertUserRecord($user)) {
            $_SESSION['success'] = 'Utilizador criado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível guardar o utilizador. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/users');
    }

    public function editUserForm(): void
    {
        Middleware::auth();
        $id = (int)($_GET['id'] ?? 0);
        $user = null;

        foreach ($this->getUsers() as $candidate) {
            if ((int)$candidate['id'] === $id) {
                $user = $candidate;
                break;
            }
        }

        if (!$user) {
            $_SESSION['error'] = 'Utilizador não encontrado.';
            $this->redirect('/admin/users');
        }

        $this->view('admin/users/edit', ['title' => 'Editar Utilizador', 'user' => $user]);
    }

    public function updateUser(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $user = [
            'id' => $id,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'status' => trim($_POST['status'] ?? 'Ativo'),
        ];
        $newPassword = trim($_POST['password'] ?? '');

        if ($this->updateUserRecord($id, $user, $newPassword)) {
            $_SESSION['success'] = 'Utilizador atualizado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível atualizar o utilizador. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        if ($this->deleteUserById($id)) {
            $_SESSION['success'] = 'Utilizador eliminado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível eliminar o utilizador. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/users');
    }

    public function permissions(): void
    {
        Middleware::auth();
        $users = $this->getUsers();

        if (!isset($_SESSION['permission_profiles'])) {
            $_SESSION['permission_profiles'] = $this->defaultProfiles();
        }

        if (!isset($_SESSION['permission_scopes'])) {
            $_SESSION['permission_scopes'] = $this->defaultScopes();
        }

        if (!isset($_SESSION['permissions_form'])) {
            $_SESSION['permissions_form'] = $this->defaultPermissions();
        }

        if (!empty($users)) {
            $selectedUser = $_SESSION['permissions_form']['user'] ?? '';
            $matched = null;

            foreach ($users as $user) {
                if (($user['name'] ?? '') === $selectedUser) {
                    $matched = $user;
                    break;
                }
            }

            if (!$matched) {
                $matched = $users[0];
                $_SESSION['permissions_form']['user'] = $matched['name'];
            }

            if (empty($_SESSION['permissions_form']['profile']) && !empty($matched['role'])) {
                $_SESSION['permissions_form']['profile'] = $matched['role'];
            }

            if (empty($_SESSION['permissions_form']['scope']) && !empty($matched['department'])) {
                $_SESSION['permissions_form']['scope'] = $matched['department'];
            }

            if (!in_array($_SESSION['permissions_form']['profile'], $_SESSION['permission_profiles'], true)) {
                $_SESSION['permission_profiles'][] = $_SESSION['permissions_form']['profile'];
            }

            if (!in_array($_SESSION['permissions_form']['scope'], $_SESSION['permission_scopes'], true)) {
                $_SESSION['permission_scopes'][] = $_SESSION['permissions_form']['scope'];
            }
        }

        $this->view('admin/permissions/index', [
            'title' => 'Permissões por Utilizador',
            'permissions' => $_SESSION['permissions_form'],
            'users' => $users,
            'profiles' => $_SESSION['permission_profiles'],
            'scopes' => $_SESSION['permission_scopes'],
        ]);
    }

    public function savePermissions(): void
    {
        Middleware::auth();
        $users = $this->getUsers();
        $selectedUser = trim($_POST['user'] ?? '');
        $selectedProfile = trim($_POST['profile'] ?? '');
        $selectedScope = trim($_POST['scope'] ?? '');

        foreach ($users as $user) {
            if (($user['name'] ?? '') !== $selectedUser) {
                continue;
            }

            if ($selectedProfile === '' && !empty($user['role'])) {
                $selectedProfile = $user['role'];
            }

            if ($selectedScope === '' && !empty($user['department'])) {
                $selectedScope = $user['department'];
            }

            break;
        }

        if (!isset($_SESSION['permission_profiles'])) {
            $_SESSION['permission_profiles'] = $this->defaultProfiles();
        }

        if (!isset($_SESSION['permission_scopes'])) {
            $_SESSION['permission_scopes'] = $this->defaultScopes();
        }

        if ($selectedProfile !== '' && !in_array($selectedProfile, $_SESSION['permission_profiles'], true)) {
            $_SESSION['permission_profiles'][] = $selectedProfile;
        }

        if ($selectedScope !== '' && !in_array($selectedScope, $_SESSION['permission_scopes'], true)) {
            $_SESSION['permission_scopes'][] = $selectedScope;
        }

        $_SESSION['permissions_form'] = [
            'user' => $selectedUser,
            'profile' => $selectedProfile,
            'scope' => $selectedScope,
            'can_view' => isset($_POST['can_view']),
            'can_edit' => isset($_POST['can_edit']),
            'can_approve' => isset($_POST['can_approve']),
        ];
        $_SESSION['success'] = 'Permissões atualizadas com sucesso.';
        $this->redirect('/admin/permissions');
    }


    private function collectContentOptions(array $contents, array $users): array
    {
        $departments = [];
        $userNames = [];
        $roleOptions = [];

        foreach ($users as $user) {
            $department = trim((string)($user['department'] ?? ''));
            $name = trim((string)($user['name'] ?? ''));
            $role = trim((string)($user['role'] ?? ''));
            if ($department !== '') {
                $departments[] = $department;
            }
            if ($name !== '') {
                $userNames[] = $name;
            }
            if ($role !== '') {
                $roleOptions[] = $role;
            }
        }

        foreach ($contents as $content) {
            $department = trim((string)($content['department'] ?? ''));
            if ($department !== '') {
                $departments[] = $department;
            }
        }

        $extraVisibleOptions = [];
        $extraEditableOptions = [];
        foreach ($contents as $content) {
            $visible = trim((string)($content['visible_for'] ?? ''));
            $editable = trim((string)($content['editable_by'] ?? ''));
            if ($visible !== '') {
                $extraVisibleOptions[] = $visible;
            }
            if ($editable !== '') {
                $extraEditableOptions[] = $editable;
            }
        }

        $departments = array_values(array_unique($departments));
        $userNames = array_values(array_unique($userNames));
        $roleOptions = array_values(array_unique($roleOptions));
        $extraVisibleOptions = array_values(array_unique($extraVisibleOptions));
        $extraEditableOptions = array_values(array_unique($extraEditableOptions));

        sort($departments);
        sort($userNames);
        sort($roleOptions);
        sort($extraVisibleOptions);
        sort($extraEditableOptions);

        return [
            'departments' => $departments,
            'userNames' => $userNames,
            'roleOptions' => $roleOptions,
            'extraVisibleOptions' => $extraVisibleOptions,
            'extraEditableOptions' => $extraEditableOptions,
        ];
    }

    private function findExecutable(string $name): string
    {
        if (!function_exists('shell_exec')) {
            return '';
        }

        $command = 'command -v ' . escapeshellarg($name) . ' 2>/dev/null';
        $path = trim((string)shell_exec($command));

        return is_executable($path) ? $path : '';
    }

    private function transcodeVideoForBrowser(string $sourcePath, string $uploadDir): string
    {
        if (!function_exists('exec')) {
            return '';
        }

        $ffmpeg = $this->findExecutable('ffmpeg');
        if ($ffmpeg === '') {
            return '';
        }

        $outputFileName = uniqid('video_', true) . '.mp4';
        $outputPath = $uploadDir . '/' . $outputFileName;
        $command = implode(' ', [
            escapeshellarg($ffmpeg),
            escapeshellarg('-y'),
            escapeshellarg('-i'),
            escapeshellarg($sourcePath),
            escapeshellarg('-map') . ' ' . escapeshellarg('0:v:0'),
            escapeshellarg('-map') . ' ' . escapeshellarg('0:a?'),
            escapeshellarg('-c:v') . ' ' . escapeshellarg('libx264'),
            escapeshellarg('-profile:v') . ' ' . escapeshellarg('high'),
            escapeshellarg('-level') . ' ' . escapeshellarg('4.1'),
            escapeshellarg('-pix_fmt') . ' ' . escapeshellarg('yuv420p'),
            escapeshellarg('-preset') . ' ' . escapeshellarg('veryfast'),
            escapeshellarg('-movflags') . ' ' . escapeshellarg('+faststart'),
            escapeshellarg('-c:a') . ' ' . escapeshellarg('aac'),
            escapeshellarg('-b:a') . ' ' . escapeshellarg('128k'),
            escapeshellarg($outputPath),
            '2>&1',
        ]);

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }

            return '';
        }

        return $outputFileName;
    }

    private function handleContentUpload(string $type): string
    {
        if (!isset($_FILES['content_file']) || !is_array($_FILES['content_file'])) {
            return '';
        }

        $file = $_FILES['content_file'];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($error !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Falha no upload do ficheiro. Tente novamente.';
            return '';
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $_SESSION['error'] = 'Ficheiro inválido.';
            return '';
        }

        $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $normalizedType = strtolower(trim($type));

        $appRoot = dirname(__DIR__, 2);

        if ($normalizedType === 'pdf') {
            $allowed = ['pdf'];
            $uploadDir = $appRoot . '/public/uploads/pdfs';
            $prefix = 'pdf_';
            $errorMessage = 'Formato de ficheiro não suportado para PDF. Use apenas PDF.';
            $publicDir = '/uploads/pdfs/';
        } else {
            $allowed = ['mp4', 'webm', 'mov', 'm4v'];
            $uploadDir = $appRoot . '/public/uploads/videos';
            $prefix = 'video_';
            $errorMessage = 'Formato de vídeo não suportado. Use MP4, WEBM, MOV ou M4V.';
            $publicDir = '/uploads/videos/';
        }

        if (!in_array($extension, $allowed, true)) {
            $_SESSION['error'] = $errorMessage;
            return '';
        }

        if (!is_dir($uploadDir)) {
            $parentDir = dirname($uploadDir);
            if (!is_dir($parentDir) || !is_writable($parentDir) || !mkdir($uploadDir, 0775, true)) {
                $_SESSION['error'] = 'A pasta de upload não está disponível para escrita. Verifique permissões (chmod/chown).';
                return '';
            }
        }

        if (!is_writable($uploadDir)) {
            $_SESSION['error'] = 'A pasta de upload não tem permissões de escrita. Verifique chmod/chown.';
            return '';
        }

        $fileName = uniqid($prefix, true) . '.' . $extension;
        $destination = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $destination)) {
            $_SESSION['error'] = 'Não foi possível guardar o ficheiro no servidor. Verifique permissões da pasta de upload.';
            return '';
        }

        if ($normalizedType !== 'pdf') {
            $transcodedFileName = $this->transcodeVideoForBrowser($destination, $uploadDir);
            if ($transcodedFileName === '') {
                @unlink($destination);
                $_SESSION['error'] = 'O vídeo foi recebido, mas não foi possível normalizá-lo para MP4 com codecs H.264/AAC. Ative o FFmpeg no servidor para uploads de vídeo ou use uma URL externa já compatível.';
                return '';
            }

            @unlink($destination);
            $fileName = $transcodedFileName;
        }

        return url($publicDir . $fileName);
    }

    private function parseSizeToBytes(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }

        $unit = strtolower(substr($trimmed, -1));
        $number = (float)$trimmed;

        return match ($unit) {
            'g' => (int)($number * 1024 * 1024 * 1024),
            'm' => (int)($number * 1024 * 1024),
            'k' => (int)($number * 1024),
            default => (int)$number,
        };
    }

    private function isRequestTooLarge(): bool
    {
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postMaxSize = $this->parseSizeToBytes((string)ini_get('post_max_size'));

        return $postMaxSize > 0 && $contentLength > $postMaxSize;
    }

    private function findContentById(int $id): ?array
    {
        $contents = $this->loadContents();
        foreach ($contents as $content) {
            if ((int)($content['id'] ?? 0) === $id) {
                return $content;
            }
        }

        return null;
    }

    private function pushViewHistory(array $content): void
    {
        $history = $_SESSION['content_history'] ?? [];
        $history = array_values(array_filter($history, fn ($item) => (int)($item['id'] ?? 0) !== (int)$content['id']));
        array_unshift($history, [
            'id' => (int)$content['id'],
            'title' => (string)($content['title'] ?? ''),
            'type' => (string)($content['type'] ?? ''),
            'viewed_at' => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['content_history'] = array_slice($history, 0, 20);
    }


    private function sortContentTrainingTree(array &$node): void
    {
        $groups = array_filter(array_keys($node), fn ($key) => $key !== '_contents');
        natcasesort($groups);

        $sorted = [];
        foreach ($groups as $group) {
            if (is_array($node[$group])) {
                $this->sortContentTrainingTree($node[$group]);
            }
            $sorted[$group] = $node[$group];
        }

        if (isset($node['_contents'])) {
            usort($node['_contents'], fn ($a, $b) => strnatcasecmp((string)($a['title'] ?? ''), (string)($b['title'] ?? '')));
            $sorted['_contents'] = $node['_contents'];
        }

        $node = $sorted;
    }

    private function buildContentTrainingTree(array $contents): array
    {
        $tree = [];

        foreach ($contents as $content) {
            $path = trim((string)($content['training_path'] ?? ''));
            $segments = array_values(array_filter(array_map('trim', explode('>', $path)), fn ($segment) => $segment !== ''));

            if (empty($segments)) {
                $segments = ['Sem formação'];
            }

            $cursor = &$tree;
            foreach ($segments as $segment) {
                if (!isset($cursor[$segment])) {
                    $cursor[$segment] = [];
                }
                $cursor = &$cursor[$segment];
            }

            if (!isset($cursor['_contents'])) {
                $cursor['_contents'] = [];
            }
            $cursor['_contents'][] = $content;
            unset($cursor);
        }

        $this->sortContentTrainingTree($tree);

        return $tree;
    }

    private function getKnowledgePathOptions(): array
    {
        $nodes = $this->loadKnowledgeNodes();
        $paths = array_map(fn ($node) => trim((string)($node['path'] ?? '')), $nodes);

        return array_values(array_filter($paths, fn ($path) => $path !== ''));
    }

    public function contents(): void
    {
        Middleware::auth();
        $contents = $this->loadContents();
        $users = $this->getUsers();
        $options = $this->collectContentOptions($contents, $users);

        $this->view('admin/contents/index', [
            'title' => 'Conteúdos de Formação',
            'contents' => $contents,
            'departments' => $options['departments'],
            'visibleDepartmentOptions' => $options['departments'],
            'visibleUserOptions' => $options['userNames'],
            'visibleExtraOptions' => $options['extraVisibleOptions'],
            'editableDepartmentOptions' => $options['departments'],
            'editableUserOptions' => $options['userNames'],
            'editableExtraOptions' => $options['extraEditableOptions'],
            'knowledgePathOptions' => $this->getKnowledgePathOptions(),
        ]);
    }

    public function storeContent(): void
    {
        Middleware::auth();

        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
            $_SESSION['error'] = 'Upload rejeitado pela configuração do servidor. Garanta limite mínimo de 15MB em post_max_size/upload_max_filesize.';
            $this->redirect('/admin/contents');
        }

        $type = trim($_POST['type'] ?? 'Vídeo');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $visibleFor = trim($_POST['visible_for'] ?? '');
        $editableBy = trim($_POST['editable_by'] ?? '');
        $trainingPath = trim($_POST['training_path'] ?? '');
        $manualVideoUrl = trim($_POST['video_url'] ?? '');
        $uploadedFileUrl = $this->handleContentUpload($type);

        if ($title === '' || $description === '' || $department === '' || $visibleFor === '' || $editableBy === '' || $trainingPath === '') {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios antes de adicionar o conteúdo.';
            $this->redirect('/admin/contents');
        }

        if (isset($_SESSION['error']) && $_SESSION['error'] !== '') {
            $this->redirect('/admin/contents');
        }

        if ($uploadedFileUrl === '' && $manualVideoUrl === '') {
            if (isset($_SESSION['error']) && $_SESSION['error'] !== '') {
                $this->redirect('/admin/contents');
            }

            $_SESSION['error'] = $type === 'PDF'
                ? 'Adicione um ficheiro PDF para guardar o conteúdo.'
                : 'Adicione um ficheiro de vídeo ou uma URL de vídeo para guardar o conteúdo.';
            $this->redirect('/admin/contents');
        }

        $content = [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'department' => $department,
            'visible_for' => $visibleFor,
            'editable_by' => $editableBy,
            'video_url' => $uploadedFileUrl !== '' ? $uploadedFileUrl : $manualVideoUrl,
            'training_path' => $trainingPath,
        ];

        if ($this->insertContent($content)) {
            $_SESSION['success'] = 'Conteúdo adicionado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível guardar o conteúdo. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/contents');
    }

    public function deleteContent(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $contents = $this->loadContents();
        $contentToDelete = null;
        foreach ($contents as $content) {
            if ((int)($content['id'] ?? 0) === $id) {
                $contentToDelete = $content;
                break;
            }
        }

        if ($this->deleteContentById($id)) {
            if ($contentToDelete) {
                $this->deleteLocalContentFile((string)($contentToDelete['video_url'] ?? ''));
            }
            $_SESSION['success'] = 'Conteúdo removido com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível remover o conteúdo. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/contents');
    }


    public function editContent(): void
    {
        Middleware::auth();
        $id = (int)($_GET['id'] ?? 0);
        $contents = $this->loadContents();
        $users = $this->getUsers();
        $options = $this->collectContentOptions($contents, $users);
        $content = $this->findContentById($id);

        if (!$content) {
            $_SESSION['error'] = 'Conteúdo não encontrado.';
            $this->redirect('/admin/contents');
        }

        $this->view('admin/contents/edit', [
            'title' => 'Editar Conteúdo',
            'content' => $content,
            'departments' => $options['departments'],
            'visibleDepartmentOptions' => $options['departments'],
            'visibleUserOptions' => $options['userNames'],
            'visibleRoleOptions' => $options['roleOptions'],
            'visibleExtraOptions' => $options['extraVisibleOptions'],
            'editableDepartmentOptions' => $options['departments'],
            'editableUserOptions' => $options['userNames'],
            'editableRoleOptions' => $options['roleOptions'],
            'editableExtraOptions' => $options['extraEditableOptions'],
            'knowledgePathOptions' => $this->getKnowledgePathOptions(),
        ]);
    }

    public function updateContent(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $contents = $this->loadContents();
        $uploadedFileUrl = $this->handleContentUpload(trim($_POST['type'] ?? 'Vídeo'));
        if (isset($_SESSION['error']) && $_SESSION['error'] !== '') {
            $this->redirect('/admin/contents/edit?id=' . $id);
        }

        $oldVideoUrl = '';
        $contentFound = false;
        foreach ($contents as &$content) {
            if ((int)($content['id'] ?? 0) !== $id) {
                continue;
            }

            $contentFound = true;
            $oldVideoUrl = (string)($content['video_url'] ?? '');

            $content['title'] = trim($_POST['title'] ?? '');
            $content['description'] = trim($_POST['description'] ?? '');
            $content['type'] = trim($_POST['type'] ?? 'Vídeo');
            $content['department'] = trim($_POST['department'] ?? '');
            $content['visible_for'] = trim($_POST['visible_for'] ?? '');
            $content['editable_by'] = trim($_POST['editable_by'] ?? '');
            $content['training_path'] = trim($_POST['training_path'] ?? '');

            $manualVideoUrl = trim($_POST['video_url'] ?? '');
            if ($uploadedFileUrl !== '') {
                $content['video_url'] = $uploadedFileUrl;
            } elseif ($manualVideoUrl !== '') {
                $content['video_url'] = $manualVideoUrl;
            }
        }
        unset($content);

        if (!$contentFound) {
            $_SESSION['error'] = 'Conteúdo não encontrado.';
            $this->redirect('/admin/contents');
        }

        if ($this->replaceContents(array_values($contents))) {
            if ($uploadedFileUrl !== '' && $oldVideoUrl !== '' && $oldVideoUrl !== $uploadedFileUrl) {
                $this->deleteLocalContentFile($oldVideoUrl);
            }
            if (!isset($_SESSION['error'])) {
                $_SESSION['success'] = 'Conteúdo atualizado com sucesso.';
            }
        } else {
            $_SESSION['error'] = 'Não foi possível atualizar o conteúdo. Verifique a base de dados SQLite.';
        }
        $this->redirect('/admin/contents');
    }

    public function listContents(): void
    {
        Middleware::auth();
        $contents = $this->loadContents();

        $this->view('contents/index', ['title' => 'Conteúdos Disponíveis', 'contents' => $contents]);
    }

    public function showContent(): void
    {
        Middleware::auth();
        $id = (int)($_GET['id'] ?? 0);
        $content = $this->findContentById($id);

        if (!$content) {
            $_SESSION['error'] = 'Conteúdo não encontrado.';
            $this->redirect('/contents');
        }

        $this->pushViewHistory($content);
        $this->view('contents/show', ['title' => $content['title'] ?? 'Conteúdo', 'content' => $content]);
    }

    public function knowledge(): void
    {
        Middleware::auth();
        $nodes = $this->loadKnowledgeNodes();
        $this->view('admin/knowledge/index', [
            'title' => 'Departamentos e Pastas de Conhecimento',
            'knowledgeNodes' => $nodes,
            'knowledgeTree' => $this->buildKnowledgeTree($nodes),
        ]);
    }

    public function storeKnowledgeNode(): void
    {
        Middleware::auth();
        $path = trim($_POST['path'] ?? '');

        if ($path !== '') {
            $nodes = $this->loadKnowledgeNodes();

            foreach ($nodes as $node) {
                if (strcasecmp((string)$node['path'], $path) === 0) {
                    $_SESSION['error'] = 'A estrutura indicada já existe.';
                    $this->redirect('/admin/knowledge');
                }
            }

            $ids = array_column($nodes, 'id');
            $nodes[] = ['id' => empty($ids) ? 1 : (max($ids) + 1), 'path' => $path];
            usort($nodes, fn ($a, $b) => strnatcasecmp($a['path'], $b['path']));

            if ($this->saveKnowledgeNodes($nodes)) {
                $_SESSION['success'] = 'Estrutura criada com sucesso.';
            } else {
                $_SESSION['error'] = 'Não foi possível guardar a estrutura. Verifique permissões da pasta storage.';
            }
        }

        $this->redirect('/admin/knowledge');
    }
}
