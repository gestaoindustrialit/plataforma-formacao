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

    private function defaultKnowledgeNodes(): array
    {
        return [
            ['id' => 1, 'path' => 'Software > Solune > RH'],
            ['id' => 2, 'path' => 'Software > Solune > Logística'],
        ];
    }

    private function defaultUsers(): array
    {
        return [
            ['id' => 1, 'name' => 'Ana Martins', 'email' => 'ana@empresa.pt', 'role' => 'Formadora', 'department' => 'Produção', 'status' => 'Ativo', 'password' => 'Ana@1234'],
            ['id' => 2, 'name' => 'Carlos Silva', 'email' => 'carlos@empresa.pt', 'role' => 'Operador', 'department' => 'Qualidade', 'status' => 'Ativo', 'password' => 'Carlos@1234'],
            ['id' => 3, 'name' => 'Rita Costa', 'email' => 'rita@empresa.pt', 'role' => 'Gestora RH', 'department' => 'RH', 'status' => 'Pendente', 'password' => 'Rita@1234'],
        ];
    }

    private function getUsers(): array
    {
        if (empty($_SESSION['users'])) {
            $_SESSION['users'] = $this->defaultUsers();
        }

        return $_SESSION['users'];
    }

    private function saveUsers(array $users): void
    {
        $_SESSION['users'] = array_values($users);
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

        $this->saveUsers($users);
        $_SESSION['success'] = 'Utilizador criado com sucesso.';
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

        $this->saveUsers($users);
        $_SESSION['success'] = 'Utilizador atualizado com sucesso.';
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $users = array_filter($this->getUsers(), fn ($u) => (int)$u['id'] !== $id);
        $this->saveUsers($users);
        $_SESSION['success'] = 'Utilizador eliminado com sucesso.';
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

        foreach ($users as $user) {
            $department = trim((string)($user['department'] ?? ''));
            $name = trim((string)($user['name'] ?? ''));
            if ($department !== '') {
                $departments[] = $department;
            }
            if ($name !== '') {
                $userNames[] = $name;
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
        $extraVisibleOptions = array_values(array_unique($extraVisibleOptions));
        $extraEditableOptions = array_values(array_unique($extraEditableOptions));

        sort($departments);
        sort($userNames);
        sort($extraVisibleOptions);
        sort($extraEditableOptions);

        return [
            'departments' => $departments,
            'userNames' => $userNames,
            'extraVisibleOptions' => $extraVisibleOptions,
            'extraEditableOptions' => $extraEditableOptions,
        ];
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

        if ($normalizedType === 'pdf') {
            $allowed = ['pdf'];
            $uploadDir = APP_ROOT . '/public/uploads/pdfs';
            $prefix = 'pdf_';
            $errorMessage = 'Formato de ficheiro não suportado para PDF. Use apenas PDF.';
            $publicDir = '/uploads/pdfs/';
        } else {
            $allowed = ['mp4', 'webm', 'mov', 'm4v'];
            $uploadDir = APP_ROOT . '/public/uploads/videos';
            $prefix = 'video_';
            $errorMessage = 'Formato de vídeo não suportado. Use MP4, WEBM, MOV ou M4V.';
            $publicDir = '/uploads/videos/';
        }

        if (!in_array($extension, $allowed, true)) {
            $_SESSION['error'] = $errorMessage;
            return '';
        }

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $fileName = uniqid($prefix, true) . '.' . $extension;
        $destination = $uploadDir . '/' . $fileName;
        if (!@move_uploaded_file($tmpName, $destination)) {
            $_SESSION['error'] = 'Não foi possível guardar o ficheiro no servidor.';
            return '';
        }

        return url($publicDir . $fileName);
    }

    private function findContentById(int $id): ?array
    {
        $contents = $_SESSION['contents'] ?? $this->defaultContents();
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

    private function getKnowledgePathOptions(): array
    {
        $nodes = $_SESSION['knowledge_nodes'] ?? $this->defaultKnowledgeNodes();
        $paths = array_map(fn ($node) => trim((string)($node['path'] ?? '')), $nodes);

        return array_values(array_filter($paths, fn ($path) => $path !== ''));
    }

    public function contents(): void
    {
        Middleware::auth();
        if (!isset($_SESSION['contents'])) {
            $_SESSION['contents'] = $this->defaultContents();
        }
        $users = $_SESSION['users'] ?? $this->defaultUsers();
        $options = $this->collectContentOptions($_SESSION['contents'], $users);

        $this->view('admin/contents/index', [
            'title' => 'Conteúdos de Formação',
            'contents' => $_SESSION['contents'],
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
        $contents = $_SESSION['contents'] ?? $this->defaultContents();
        $ids = array_column($contents, 'id');
        $uploadedFileUrl = $this->handleContentUpload(trim($_POST['type'] ?? 'Vídeo'));
        $manualVideoUrl = trim($_POST['video_url'] ?? '');

        $contents[] = [
            'id' => empty($ids) ? 1 : (max($ids) + 1),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'type' => trim($_POST['type'] ?? 'Vídeo'),
            'department' => trim($_POST['department'] ?? ''),
            'visible_for' => trim($_POST['visible_for'] ?? ''),
            'editable_by' => trim($_POST['editable_by'] ?? ''),
            'video_url' => $uploadedFileUrl !== '' ? $uploadedFileUrl : $manualVideoUrl,
            'training_path' => trim($_POST['training_path'] ?? ''),
        ];
        $_SESSION['contents'] = $contents;
        if (!isset($_SESSION['error'])) {
            $_SESSION['success'] = 'Conteúdo adicionado com sucesso.';
        }
        $this->redirect('/admin/contents');
    }

    public function deleteContent(): void
    {
        Middleware::auth();
        $id = (int)($_POST['id'] ?? 0);
        $contents = array_filter($_SESSION['contents'] ?? [], fn ($content) => (int)$content['id'] !== $id);
        $_SESSION['contents'] = array_values($contents);
        $_SESSION['success'] = 'Conteúdo removido com sucesso.';
        $this->redirect('/admin/contents');
    }


    public function editContent(): void
    {
        Middleware::auth();
        $id = (int)($_GET['id'] ?? 0);
        $contents = $_SESSION['contents'] ?? $this->defaultContents();
        $users = $_SESSION['users'] ?? $this->defaultUsers();
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
        $contents = $_SESSION['contents'] ?? $this->defaultContents();
        $uploadedFileUrl = $this->handleContentUpload(trim($_POST['type'] ?? 'Vídeo'));

        foreach ($contents as &$content) {
            if ((int)($content['id'] ?? 0) !== $id) {
                continue;
            }

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

        $_SESSION['contents'] = array_values($contents);
        if (!isset($_SESSION['error'])) {
            $_SESSION['success'] = 'Conteúdo atualizado com sucesso.';
        }
        $this->redirect('/admin/contents');
    }

    public function listContents(): void
    {
        Middleware::auth();
        if (!isset($_SESSION['contents'])) {
            $_SESSION['contents'] = $this->defaultContents();
        }

        $this->view('contents/index', ['title' => 'Conteúdos Disponíveis', 'contents' => $_SESSION['contents']]);
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
        if (!isset($_SESSION['knowledge_nodes'])) {
            $_SESSION['knowledge_nodes'] = $this->defaultKnowledgeNodes();
        }
        $this->view('admin/knowledge/index', ['title' => 'Departamentos e Pastas de Conhecimento', 'knowledgeNodes' => $_SESSION['knowledge_nodes']]);
    }

    public function storeKnowledgeNode(): void
    {
        Middleware::auth();
        $path = trim($_POST['path'] ?? '');
        if ($path !== '') {
            $nodes = $_SESSION['knowledge_nodes'] ?? $this->defaultKnowledgeNodes();
            $ids = array_column($nodes, 'id');
            $nodes[] = ['id' => empty($ids) ? 1 : (max($ids) + 1), 'path' => $path];
            $_SESSION['knowledge_nodes'] = $nodes;
            $_SESSION['success'] = 'Estrutura criada com sucesso.';
        }
        $this->redirect('/admin/knowledge');
    }
}
