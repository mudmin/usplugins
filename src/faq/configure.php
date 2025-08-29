<?php
require_once __DIR__ . '/assets/vendor/htmlpurifier/HTMLPurifier.standalone.php';
$purifier_config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($purifier_config);

if (count(get_included_files()) == 1) {
    die();
} //Direct Access Not Permitted
if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root . 'users/admin.php');
}
$errors = [];
if (!empty($_POST)) {
    if (!Token::check(Input::get('csrf'))) {
        include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
    }
}
//Handle category form submissions
if (!empty($_POST['add_category'])) {

    $name = Input::get('name');
    $menu_text = Input::get('menu_text');
    if (empty($name) || strlen($name) > 255) {
        $errors[] = "Category name must be between 1 and 255 characters.";
    }
    if (empty($menu_text) || strlen($menu_text) > 255) {
        $errors[] = "Menu text must be between 1 and 255 characters.";
    }
    if (count($errors) == 0) {
        $db->insert('plg_faq_categories', ['name' => $name, 'menu_text' => $menu_text, 'display_order' => 9999]);
        Redirect::to('?view=plugins_config&plugin=faq&err=Category+added.');
    }
} elseif (!empty($_POST['edit_category'])) {

    $id = Input::get('id');
    $name = Input::get('name');
    $menu_text = Input::get('menu_text');
    if (empty($name) || strlen($name) > 255) {
        $errors[] = "Category name must be between 1 and 255 characters.";
    }
    if (empty($menu_text) || strlen($menu_text) > 255) {
        $errors[] = "Menu text must be between 1 and 255 characters.";
    }
    if (!empty($id) && count($errors) == 0) {
        $db->update('plg_faq_categories', $id, ['name' => $name, 'menu_text' => $menu_text]);
        Redirect::to('?view=plugins_config&plugin=faq&err=Category+updated.');
    }
} elseif (!empty($_POST['delete_category'])) {

    $id = Input::get('id');
    if (!empty($id)) {
        $db->query("UPDATE plg_faqs SET category_id = 0 WHERE category_id = ?", [$id]);
        $db->delete('plg_faq_categories', ['id', '=', $id]);
        Redirect::to('?view=plugins_config&plugin=faq&err=Category+deleted.+FAQs+moved+to+uncategorized.');
    }
}

//Handle FAQ form submissions
if (!empty($_POST['add_faq'])) {

    $category_id = Input::get('category_id');
    $question = Input::get('question');
    $answer = Input::get('answer');
    if (empty($category_id)) {
        $errors[] = "You must select a category.";
    }
    if (empty($question) || strlen($question) > 65535) {
        $errors[] = "Question must be between 1 and 65535 characters.";
    }
    if (empty($answer)) {
        $errors[] = "Answer cannot be empty.";
    }
    if (count($errors) == 0) {
        $answer = $purifier->purify($answer);
        $db->insert('plg_faqs', ['category_id' => $category_id, 'question' => $question, 'answer' => $answer, 'display_order' => 9999]);
        Redirect::to('?view=plugins_config&plugin=faq&err=FAQ+added.');
    }
} elseif (!empty($_POST['edit_faq'])) {

    $id = Input::get('id');
    $category_id = Input::get('category_id');
    $question = Input::get('question');
    $answer = Input::get('answer');
    if (empty($category_id)) {
        $errors[] = "You must select a category.";
    }
    if (empty($question) || strlen($question) > 65535) {
        $errors[] = "Question must be between 1 and 65535 characters.";
    }
    if (empty($answer)) {
        $errors[] = "Answer cannot be empty.";
    }
    if (!empty($id) && count($errors) == 0) {
        $answer = $purifier->purify($answer);
        $db->update('plg_faqs', $id, ['category_id' => $category_id, 'question' => $question, 'answer' => $answer]);
        Redirect::to('?view=plugins_config&plugin=faq&err=FAQ+updated.');
    }
} elseif (!empty($_POST['delete_faq'])) {

    $id = Input::get('id');
    if (!empty($id)) {
        $db->delete('plg_faqs', ['id', '=', $id]);
        Redirect::to('?view=plugins_config&plugin=faq&err=FAQ+deleted.');
    }
} elseif (!empty($_POST['recategorize_faq'])) {
    $faq_id = Input::get('faq_id');
    $category_id = Input::get('category_id');
    if (empty($category_id)) {
        $errors[] = "You must select a category to move the FAQ to.";
    }
    if (!empty($faq_id) && count($errors) == 0) {
        $db->update('plg_faqs', $faq_id, ['category_id' => $category_id]);
        Redirect::to('?view=plugins_config&plugin=faq&err=FAQ+recategorized.');
    }
}

$categories = $db->query("SELECT * FROM plg_faq_categories ORDER BY display_order")->results();
$faqs = $db->query("SELECT f.*, c.name as category_name FROM plg_faqs f JOIN plg_faq_categories c ON f.category_id = c.id ORDER BY c.display_order, f.display_order")->results();
$uncategorized_faqs = $db->query("SELECT * FROM plg_faqs WHERE category_id = 0 ORDER BY question")->results();


$edit_category = null;
if (isset($_GET['edit_category_id'])) {
    $edit_category = $db->query("SELECT * FROM plg_faq_categories WHERE id = ?", [$_GET['edit_category_id']])->first();
}

$edit_faq = null;
if (isset($_GET['edit_faq_id'])) {
    $edit_faq = $db->query("SELECT * FROM plg_faqs WHERE id = ?", [$_GET['edit_faq_id']])->first();
}
?>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .fa-bars {
        cursor: grab;
    }

    .note-editor .note-editing-area .note-editable {
        background-color: #fff;
    }
</style>

<div class="content mt-3">
    <?php if (!empty($errors)) { ?>
        <div class="alert alert-danger">
            <strong>Error:</strong><br>
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= $error ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <div class="alert alert-info">
        This plugin allows you to create a FAQ section for your site with categories and questions. You can reorder categories and questions by dragging the <i class="fa fa-bars"></i> icon. FAQs in a deleted category will be moved to "Uncategorized" until you recategorize them. 
        <a href="#" data-bs-toggle="modal" data-bs-target="#faqDocumentationModal" class="btn btn-sm btn-outline-primary">View Documentation</a>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">Manage Categories</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"></th>
                                    <th>Name</th>
                                    <th>Menu Text</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categories-table-body">
                                <?php foreach ($categories as $category) { ?>
                                    <tr data-id="<?= $category->id ?>">
                                        <td><i class="fa fa-bars"></i></td>
                                        <td><?= hed($category->name) ?></td>
                                        <td><?= hed($category->menu_text) ?></td>
                                        <td class="text-right">
                                            <a href="?view=plugins_config&plugin=faq&edit_category_id=<?= $category->id ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this category? All its FAQs will be moved to Uncategorized.');" class="d-inline">
                                                <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                                                <input type="hidden" name="id" value="<?= $category->id ?>">
                                                <button type="submit" name="delete_category" value="1" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0"><?= $edit_category ? '<i class="fa fa-edit"></i> Edit' : '<i class="fa fa-plus"></i> Add New'; ?> Category</h4>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                        <?php if ($edit_category) { ?>
                            <input type="hidden" name="id" value="<?= $edit_category->id ?>">
                        <?php } ?>
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= hed($edit_category->name ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="menu_text">Menu Text (Short name for menu)</label>
                            <input type="text" name="menu_text" id="menu_text" class="form-control" value="<?= hed($edit_category->menu_text ?? '') ?>" required>
                        </div>
                        <div class="text-right">
                            <?php if ($edit_category) { ?>
                                <a href="?view=plugins_config&plugin=faq" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="edit_category" value="1" class="btn btn-primary">Update Category</button>
                            <?php } else { ?>
                                <button type="submit" name="add_category" value="1" class="btn btn-primary">Add Category</button>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0">Manage FAQs</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"></th>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="faqs-table-body">
                                <?php foreach ($faqs as $faq) { ?>
                                    <tr data-id="<?= $faq->id ?>">
                                        <td><i class="fa fa-bars"></i></td>
                                        <td><?= hed($faq->question) ?></td>
                                        <td><?= hed($faq->category_name) ?></td>
                                        <td class="text-right">
                                            <a href="?view=plugins_config&plugin=faq&edit_faq_id=<?= $faq->id ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this FAQ?');" class="d-inline">
                                                <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                                                <input type="hidden" name="id" value="<?= $faq->id ?>">
                                                <button type="submit" name="delete_faq" value="1" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0"><?= $edit_faq ? '<i class="fa fa-edit"></i> Edit' : '<i class="fa fa-plus"></i> Add New'; ?> FAQ</h4>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                        <?php if ($edit_faq) { ?>
                            <input type="hidden" name="id" value="<?= $edit_faq->id ?>">
                        <?php } ?>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="" disabled selected>-- Select a Category --</option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?= $category->id ?>" <?= (($edit_faq->category_id ?? '') == $category->id) ? 'selected' : '' ?>>
                                        <?= hed($category->name) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="question">Question</label>
                            <input type="text" name="question" id="question" class="form-control" value="<?= hed($edit_faq->question ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="answer">Answer</label>
                            <textarea name="answer" id="answer" class="form-control" rows="5" required><?= hed($edit_faq->answer ?? '') ?></textarea>
                        </div>
                        <div class="text-right">
                            <?php if ($edit_faq) { ?>
                                <a href="?view=plugins_config&plugin=faq" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="edit_faq" value="1" class="btn btn-primary">Update FAQ</button>
                            <?php } else { ?>
                                <button type="submit" name="add_faq" value="1" class="btn btn-primary">Add FAQ</button>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($uncategorized_faqs)) { ?>
        <hr class="my-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fa fa-folder-open"></i> Uncategorized FAQs</h4>
                    </div>
                    <div class="card-body">
                        <p>These FAQs belong to a deleted category. Please move them to a new category.</p>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Question</th>
                                        <th style="width: 40%;">Recategorize</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uncategorized_faqs as $faq) { ?>
                                        <tr>
                                            <td><?= hed($faq->question) ?></td>
                                            <td>
                                                <form action="" method="post" class="form-inline">
                                                    <div class="input-group">
                                                        <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                                                        <input type="hidden" name="faq_id" value="<?= $faq->id ?>">
                                                        <select name="category_id" class="form-control mr-2" required>
                                                            <option value="" disabled selected>-- Select a Category --</option>
                                                            <?php foreach ($categories as $category) { ?>
                                                                <option value="<?= $category->id ?>"><?= hed($category->name) ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <button type="submit" name="recategorize_faq" value="1" class="btn btn-primary">Move</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="modal fade" id="faqDocumentationModal" tabindex="-1" aria-labelledby="faqDocumentationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqDocumentationModalLabel"><i class="fa fa-book"></i> FAQ Plugin Documentation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Welcome! 👋</h4>
                <p>This document provides instructions on how to use and manage the FAQ plugin for your website.</p>

                <hr>
                <h4>Managing Categories</h4>
                <p>Categories are used to group your FAQs. Users can filter by these categories on the front-end page.</p>
                <ul>
                    <li><strong>Add a Category:</strong> Use the "Add New Category" form. The "Category Name" is the full title, while the "Menu Text" is a shorter version used for the filter menu on the FAQ page.</li>
                    <li><strong>Edit a Category:</strong> Click the "Edit" button next to any category. The form will be populated with its details for you to update.</li>
                    <li><strong>Delete a Category:</strong> Clicking "Delete" will remove the category. Any FAQs within that category will be automatically moved to an "Uncategorized" section for you to re-assign. They are <strong>not</strong> deleted.</li>
                    <li><strong>Reorder Categories:</strong> Click and drag the <i class="fa fa-bars"></i> icon to change the order in which categories appear on the FAQ page. The order is saved automatically.</li>
                </ul>

                <hr>

                <h4>Managing FAQs</h4>
                <p>This is where you manage the actual questions and answers.</p>
                <ul>
                    <li><strong>Add an FAQ:</strong> Use the "Add New FAQ" form. You must select a category, provide a question, and write the answer. The answer field supports rich text, including links, images, and lists.</li>
                    <li><strong>Edit an FAQ:</strong> Click the "Edit" button next to any FAQ to modify its category, question, or answer.</li>
                    <li><strong>Delete an FAQ:</strong> This will permanently remove the question and answer from the database.</li>
                    <li><strong>Reorder FAQs:</strong> Click and drag the <i class="fa fa-bars"></i> icon to change the display order of FAQs within their categories. The order is saved automatically.</li>
                </ul>

                <hr>

                <h4>Handling Uncategorized FAQs</h4>
                <p>If you delete a category, its FAQs are not lost. A new section titled "Uncategorized FAQs" will appear at the bottom of this admin page. From there, you can easily re-assign each orphaned FAQ to an existing category using the dropdown menu next to it.</p>


                <hr>

                <h4>Viewing the FAQ</h4>
                <p>There are several ways to view the FAQ</p>
                <ol>
                    <li>Visit <a href="<?= $us_url_root ?>usersc/plugins/faq/faq.php">usersc/plugins/faq/faq.php</a>Directly</li>
                    <li>Create your own custom page and include <code>$abs_us_root . $us_url_root . "usersc/plugins/faq/faq.php">usersc/plugins/faq/faq_body.php"</code></li>
                    <li>Calling <code>displayFAQ()</code> on any page.</li>

                </ol>
                <p>Pro tip. If you do the function call, you may want to wrap it in <code>if(function_exists('displayFAQ')){}</code> so it does not throw an error if you disable the plugin.</p>

                <hr>

                <h4>Customizing the  FAQ</h4>
        
                <ol>
                    <li>If you create the file <code>$abs_us_root . $us_url_root . "usersc/plugins/faq/faq_style_custom.php"</code>, your style will be loaded instead of the core style for the plugin.  Of course, you can copy <code>usersc/plugins/faq/faq_style.php</code> as a starting point.</li>
                    <li>Create a file at <code>$abs_us_root . $us_url_root . "usersc/plugins/faq/faq_custom.php"</code> to override core queries or add your own code to the FAQ.</li>
                   

                </ol>
                <p>Pro tip. If you do the function call, you may want to wrap it in <code>if(function_exists('displayFAQ')){displayFAQ();'}</code> so it does not throw an error if you disable the plugin.</p>

                <hr>

                <h4>Adding to UltraMenu</h4>
                <p>Adding the standard FAQ to your UltraMenu.</p>
                <ol>
                    <li>Navigate to the <strong>UltraMenu</strong> in your UserSpice admin panel.</li>
                    <li>Select the menu you wish to add the page to (e.g., Main Menu).</li>
                    <li>Click to add a new menu item.</li>
                    <li>Set the <strong>Item Type</strong> to "Snippet".</li>
                    <li>From the <strong>Snippet File</strong> dropdown, select <code>usersc/plugins/faq/menu_hooks/faq.php</code>.</li>
                    <li>Configure the other settings like the link text (e.g., "FAQ") and permissions, then save the item.</li>
                </ol>
                <p>Your FAQ page will now appear in your site's navigation.</p>

              



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="<?= $us_url_root ?>usersc/plugins/faq/assets/js/Sortable.min.js"></script>

<script>
    $(document).ready(function() {
        $('#answer').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const createSortable = (elementId, type) => {
            const tableBody = document.getElementById(elementId);
            if (!tableBody) return;

            new Sortable(tableBody, {
                animation: 150,
                onEnd: function(evt) {
                    let order = Array.from(tableBody.querySelectorAll('tr')).map(row => row.dataset.id);
                    let formData = new FormData();
                    formData.append('csrf', '<?= Token::generate() ?>');
                    formData.append('type', type);
                    order.forEach(id => formData.append('order[]', id));

                    fetch('<?= $us_url_root ?>usersc/plugins/faq/assets/parsers/order_parser.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert(`Error reordering ${type}: ${data.message}`);
                            }
                        }).catch(error => console.error('Error:', error));
                }
            });
        };

        createSortable('categories-table-body', 'categories');
        createSortable('faqs-table-body', 'faqs');
    });
</script>