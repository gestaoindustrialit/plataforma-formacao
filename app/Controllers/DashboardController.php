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
            ['id' => 1, 'title' => 'Setup de Máquina X', 'type' => 'Vídeo', 'department' => 'Produção', 'visible_for' => 'Produção A/B', 'editable_by' => 'Formadores Produção', 'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4'],
            ['id' => 2, 'title' => 'Checklist de Segurança', 'type' => 'PDF', 'department' => 'Global', 'visible_for' => 'Todos', 'editable_by' => 'Gestores + HSE', 'video_url' => ''],
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
            ['id' => 1, 'name' => 'Ana Martins', 'email' => 'ana@empresa.pt', 'role' => 'Formadora', 'department' => 'Produção', 'status' => 'Ativo'],
            ['id' => 2, 'name' => 'Carlos Silva', 'email' => 'carlos@empresa.pt', 'role' => 'Operador', 'department' => 'Qualidade', 'status' => 'Ativo'],
            ['id' => 3, 'name' => 'Rita Costa', 'email' => 'rita@empresa.pt', 'role' => 'Gestora RH', 'department' => 'RH', 'status' => 'Pendente'],
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
        $this->view('profile/index', ['title' => 'Meu Perfil']);
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

    private function handleVideoUpload(): string
    {
        if (!isset($_FILES['video_file']) || !is_array($_FILES['video_file'])) {
            return '';
        }

        $file = $_FILES['video_file'];
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($error !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Falha no upload do vídeo. Tente novamente.';
            return '';
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $_SESSION['error'] = 'Ficheiro de vídeo inválido.';
            return '';
        }

        $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowed = ['mp4', 'webm', 'mov', 'm4v'];
        if (!in_array($extension, $allowed, true)) {
            $_SESSION['error'] = 'Formato de vídeo não suportado. Use MP4, WEBM, MOV ou M4V.';
            return '';
        }

        $uploadDir = APP_ROOT . '/public/uploads/videos';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $fileName = uniqid('video_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $fileName;
        if (!@move_uploaded_file($tmpName, $destination)) {
            $_SESSION['error'] = 'Não foi possível guardar o vídeo no servidor.';
            return '';
        }

        return url('/uploads/videos/' . $fileName);
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
            'visibleRoleOptions' => $options['roleOptions'],
            'visibleExtraOptions' => $options['extraVisibleOptions'],
            'editableDepartmentOptions' => $options['departments'],
            'editableUserOptions' => $options['userNames'],
            'editableRoleOptions' => $options['roleOptions'],
            'editableExtraOptions' => $options['extraEditableOptions'],
        ]);
    }

    public function storeContent(): void
    {
        Middleware::auth();
        $contents = $_SESSION['contents'] ?? $this->defaultContents();
        $ids = array_column($contents, 'id');
        $uploadedVideoUrl = $this->handleVideoUpload();
        $manualVideoUrl = trim($_POST['video_url'] ?? '');

        $contents[] = [
            'id' => empty($ids) ? 1 : (max($ids) + 1),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'type' => trim($_POST['type'] ?? 'Vídeo'),
            'department' => trim($_POST['department'] ?? ''),
            'visible_for' => trim($_POST['visible_for'] ?? ''),
            'editable_by' => trim($_POST['editable_by'] ?? ''),
            'video_url' => $uploadedVideoUrl !== '' ? $uploadedVideoUrl : $manualVideoUrl,
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
