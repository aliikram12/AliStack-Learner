<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$courseRepo = new CourseRepository();
$settingRepo = new SettingRepository();

// Handle Category Actions (create, update, delete, toggle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = Sanitizer::slug($name);
        } else {
            $slug = Sanitizer::slug($slug);
        }

        $icon = trim((string)($_POST['icon'] ?? 'bi-folder2'));
        if (!str_starts_with($icon, 'bi-')) {
            $icon = 'bi-' . $icon;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'icon' => $icon,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0
        ];

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Category name is required.';
        } else {
            $catId = $courseRepo->createCategory($data);
            $settingRepo->logAudit(Auth::id(), 'CATEGORY_CREATE', 'course_categories', $catId, "Created category: {$name}");
            $_SESSION['flash_success'] = "Category '{$name}' created successfully.";
        }
        header('Location: ' . baseUrl('admin/categories.php'));
        exit;

    } elseif ($action === 'update_category') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        if (empty($slug)) {
            $slug = Sanitizer::slug($name);
        } else {
            $slug = Sanitizer::slug($slug);
        }

        $icon = trim((string)($_POST['icon'] ?? 'bi-folder2'));
        if (!str_starts_with($icon, 'bi-')) {
            $icon = 'bi-' . $icon;
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)($_POST['description'] ?? '')),
            'icon' => $icon,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0
        ];

        if ($id && !empty($name)) {
            $courseRepo->updateCategory($id, $data);
            $settingRepo->logAudit(Auth::id(), 'CATEGORY_UPDATE', 'course_categories', $id, "Updated category: {$name}");
            $_SESSION['flash_success'] = "Category '{$name}' updated successfully.";
        }
        header('Location: ' . baseUrl('admin/categories.php'));
        exit;

    } elseif ($action === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $courseRepo->deleteCategory($id);
            $settingRepo->logAudit(Auth::id(), 'CATEGORY_DELETE', 'course_categories', $id, "Deleted category ID {$id}");
            $_SESSION['flash_success'] = 'Category deleted successfully. Associated courses were unlinked.';
        }
        header('Location: ' . baseUrl('admin/categories.php'));
        exit;
    }
}

$categories = $courseRepo->getAllCategoriesAdmin();
$pageTitle = 'Manage Course Categories';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Course Categories</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Organize and classify platform courses by tech discipline and skill domain.</p>
    </div>

    <div>
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i> Add New Category
        </button>
    </div>
</div>

<div class="table-card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1.1rem; margin: 0;">All Categories</h3>
        <span style="font-size: 13px; color: var(--muted);"><?= count($categories) ?> categories active</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Icon</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th style="text-align: center;">Courses</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px; color: var(--muted);">
                            No categories created yet. Click "Add New Category" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(37,99,235,0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi <?= Sanitizer::e($cat['icon'] ?: 'bi-folder2') ?>"></i>
                                </div>
                            </td>
                            <td>
                                <strong style="color: var(--dark); font-size: 14.5px;"><?= Sanitizer::e($cat['name']) ?></strong>
                            </td>
                            <td>
                                <code style="background: var(--bg-subtle); padding: 3px 8px; border-radius: 6px; font-size: 12px; color: var(--primary);"><?= Sanitizer::e($cat['slug']) ?></code>
                            </td>
                            <td style="max-width: 320px; font-size: 13px; color: var(--muted); line-height: 1.4;">
                                <?= Sanitizer::e($cat['description'] ?: 'No description provided') ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= baseUrl('admin/courses.php?category=' . $cat['id']) ?>" class="badge badge-primary" style="text-decoration: none;">
                                    <?= (int)$cat['course_count'] ?> courses
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($cat['is_active'])): ?>
                                    <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="bi bi-pause-circle-fill"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <button type="button" class="btn btn-outline btn-xs" onclick='openEditModal(<?= json_encode($cat, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" action="<?= baseUrl('admin/categories.php') ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category? Courses in this category will become unassigned.');">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-xs" style="color: var(--danger); border-color: rgba(220,38,38,0.25);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add / Edit Category -->
<div id="categoryModal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog" style="max-width: 540px;">
        <div class="modal-content" style="background: var(--bg-surface); border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); overflow: hidden; border: 1px solid var(--border);">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin: 0;">Add New Category</h3>
                <button type="button" onclick="closeModal('categoryModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted);">&times;</button>
            </div>

            <form method="POST" action="<?= baseUrl('admin/categories.php') ?>" style="padding: 24px;">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" id="formAction" value="create_category">
                <input type="hidden" name="id" id="categoryId" value="">

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="catName">Category Name</label>
                    <input type="text" id="catName" name="name" class="form-control" placeholder="e.g. Full-Stack Web Development" required oninput="autoSlug(this.value)">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="catSlug">URL Slug</label>
                    <input type="text" id="catSlug" name="slug" class="form-control" placeholder="full-stack-web-development">
                    <small style="font-size: 11px; color: var(--muted);">Leave blank to generate automatically from name.</small>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="catIcon">Bootstrap Icon</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="catIcon" name="icon" class="form-control" value="bi-code-slash" placeholder="bi-code-slash, bi-robot, bi-cpu..." oninput="updateIconPreview(this.value)">
                        <div id="iconPreview" style="width: 42px; height: 42px; border-radius: 8px; background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--primary);">
                            <i class="bi bi-code-slash"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="catDesc">Description</label>
                    <textarea id="catDesc" name="description" class="form-control" rows="3" placeholder="Brief explanation of what students learn in this domain..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; font-weight: 600;">
                        <input type="checkbox" id="catActive" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <span>Active Category (visible to students)</span>
                    </label>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('categoryModal')">Cancel</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function autoSlug(text) {
    if (document.getElementById('formAction').value === 'create_category') {
        var slug = text.toLowerCase().trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('catSlug').value = slug;
    }
}

function updateIconPreview(val) {
    var iconClass = val.trim();
    if (!iconClass.startsWith('bi-')) iconClass = 'bi-' + iconClass;
    document.getElementById('iconPreview').innerHTML = '<i class="bi ' + iconClass + '"></i>';
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('formAction').value = 'create_category';
    document.getElementById('categoryId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catSlug').value = '';
    document.getElementById('catIcon').value = 'bi-code-slash';
    document.getElementById('catDesc').value = '';
    document.getElementById('catActive').checked = true;
    updateIconPreview('bi-code-slash');
    document.getElementById('categoryModal').style.display = 'flex';
}

function openEditModal(cat) {
    document.getElementById('modalTitle').textContent = 'Edit Category: ' + cat.name;
    document.getElementById('formAction').value = 'update_category';
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catSlug').value = cat.slug;
    document.getElementById('catIcon').value = cat.icon || 'bi-folder2';
    document.getElementById('catDesc').value = cat.description || '';
    document.getElementById('catActive').checked = (cat.is_active == 1);
    updateIconPreview(cat.icon || 'bi-folder2');
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<style>
.modal-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-dialog {
    width: 100%;
}
</style>
