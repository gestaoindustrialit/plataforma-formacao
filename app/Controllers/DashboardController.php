<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class DashboardController extends Controller
{
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

    private function contentsStoragePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/contents.json';
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

    private function loadContents(): array
    {
        $path = $this->contentsStoragePath();

        if (!is_file($path)) {
            $contents = isset($_SESSION['contents']) && is_array($_SESSION['contents'])
                ? $_SESSION['contents']
                : $this->defaultContents();
            $this->saveContents($contents);

            $normalized = [];
            foreach ($contents as $content) {
                if (is_array($content)) {
                    $normalized[] = $this->normalizeContent($content, count($normalized) + 1);
                }
            }

            return array_values($normalized);
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $contents = [];
        foreach ($decoded as $content) {
            if (!is_array($content)) {
                continue;
            }

            $normalized = $this->normalizeContent($content, count($contents) + 1);
            if ($normalized['id'] <= 0 || $normalized['title'] === '') {
                continue;
            }

            $contents[] = $normalized;
        }

        return array_values($contents);
    }

    private function saveContents(array $contents): bool
    {
        $path = $this->contentsStoragePath();
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $normalized = [];
        foreach ($contents as $content) {
            if (!is_array($content)) {
                continue;
            }

            $normalized[] = $this->normalizeContent($content, count($normalized) + 1);
        }

        $payload = json_encode(array_values($normalized), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return false;
        }

        if (file_put_contents($path, $payload, LOCK_EX) === false) {
            return false;
        }

        $_SESSION['contents'] = array_values($normalized);
        return true;
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

    private function usersStoragePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/users.json';
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

    private function getUsers(): array
    {
        $path = $this->usersStoragePath();

        if (!is_file($path)) {
            $users = isset($_SESSION['users']) && is_array($_SESSION['users'])
                ? $_SESSION['users']
                : $this->defaultUsers();
            $this->saveUsers($users);

            return $_SESSION['users'] ?? array_values($users);
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            $_SESSION['users'] = [];
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $users = $this->defaultUsers();
            $_SESSION['users'] = $users;
            return $users;
        }

        $users = [];
        foreach ($decoded as $user) {
            if (!is_array($user)) {
                continue;
            }

            $normalized = $this->normalizeUser($user, count($users) + 1);
            if ($normalized['id'] <= 0 || $normalized['name'] === '') {
                continue;
            }

            $users[] = $normalized;
        }

        $_SESSION['users'] = array_values($users);
        return array_values($users);
    }

    private function saveUsers(array $users): bool
    {
        $path = $this->usersStoragePath();
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $normalized = [];
        foreach ($users as $user) {
            if (!is_array($user)) {
                continue;
            }

            $normalized[] = $this->normalizeUser($user, count($normalized) + 1);
        }

        $payload = json_encode(array_values($normalized), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return false;
        }

        if (file_put_contents($path, $payload, LOCK_EX) === false) {
            return false;
        }

        $_SESSION['users'] = array_values($normalized);
        return true;
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
        $users = $this->getUsers();
        $ids = array_column($users, 'id');
        $nextId = empty($ids) ? 1 : (max($ids) + 1);

        $users[] = [
            'id' => $nextId,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'status' => trim($_POST['status'] ?? 'Ativo'),
            'password' => trim($_POST['password'] ?? ''),
        ];

        if ($this->saveUsers($users)) {
            $_SESSION['success'] = 'Utilizador criado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível guardar o utilizador. Verifique permissões da pasta storage.';
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
        $users = $this->getUsers();

        foreach ($users as &$u) {
            if ((int)$u['id'] === $id) {
                $u['name'] = trim($_POST['name'] ?? '');
                $u['email'] = trim($_POST['email'] ?? '');
                $u['role'] = trim($_POST['role'] ?? '');
                $u['department'] = trim($_POST['department'] ?? '');
                $u['status'] = trim($_POST['status'] ?? 'Ativo');
                $newPassword = trim($_POST['password'] ?? '');
                if ($newPassword !== '') {
                    $u['password'] = $newPassword;
                }
            }
        }
        unset($u);

        if ($this->saveUsers($users)) {
            $_SESSION['success'] = 'Utilizador atualizado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível atualizar o utilizador. Verifique permissões da pasta storage.';
        }
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $users = array_values(array_filter($this->getUsers(), fn ($u) => (int)$u['id'] !== $id));
        if ($this->saveUsers($users)) {
            $_SESSION['success'] = 'Utilizador eliminado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível eliminar o utilizador. Verifique permissões da pasta storage.';
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

        $contents = $this->loadContents();
        $ids = array_column($contents, 'id');

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

        $contents[] = [
            'id' => empty($ids) ? 1 : (max($ids) + 1),
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'department' => $department,
            'visible_for' => $visibleFor,
            'editable_by' => $editableBy,
            'video_url' => $uploadedFileUrl !== '' ? $uploadedFileUrl : $manualVideoUrl,
            'training_path' => $trainingPath,
        ];
        if ($this->saveContents($contents)) {
            $_SESSION['success'] = 'Conteúdo adicionado com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível guardar o conteúdo. Verifique permissões da pasta storage.';
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

        $contents = array_values(array_filter($contents, fn ($content) => (int)$content['id'] !== $id));
        if ($this->saveContents($contents)) {
            if ($contentToDelete) {
                $this->deleteLocalContentFile((string)($contentToDelete['video_url'] ?? ''));
            }
            $_SESSION['success'] = 'Conteúdo removido com sucesso.';
        } else {
            $_SESSION['error'] = 'Não foi possível remover o conteúdo. Verifique permissões da pasta storage.';
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

        if ($this->saveContents(array_values($contents))) {
            if ($uploadedFileUrl !== '' && $oldVideoUrl !== '' && $oldVideoUrl !== $uploadedFileUrl) {
                $this->deleteLocalContentFile($oldVideoUrl);
            }
            if (!isset($_SESSION['error'])) {
                $_SESSION['success'] = 'Conteúdo atualizado com sucesso.';
            }
        } else {
            $_SESSION['error'] = 'Não foi possível atualizar o conteúdo. Verifique permissões da pasta storage.';
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
