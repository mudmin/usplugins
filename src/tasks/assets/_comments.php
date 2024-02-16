<?php

$comments = $db->query("SELECT 
c.*,
u.fname,
u.lname
FROM plg_tasks_comments c 
LEFT OUTER JOIN users u ON u.id = c.created_by
WHERE c.task_id = ? 
ORDER BY c.created_on ASC", [$id])->results();
//add a comment with the ability to attach multiple photos which get uploade to $abs_us_root . $us_url_root . usersc/task_media/ and they begin with the $id . "_";

if (!empty($_POST['comment'])) {

    if (!Token::check($_POST['csrf'])) {
        usError("Token Failed");
        Redirect::to($basePage . "method=" . $method . "&id=" . $id);
    }
    $comment = Input::get('comment');
    $db->insert("plg_tasks_comments", [
        'task_id' => $id,
        'comment' => $comment,
        'created_by' => $user->data()->id,
        'created_on' => date("Y-m-d H:i:s"),
    ]);
    $commentId = $db->lastId();

    $photos = $_FILES['photos'];
    $photoNames = [];
    if (!empty($photos['name'][0])) {
        $comment .= "<br>{{Photos Attached to this comment}}";
        foreach ($photos['name'] as $key => $name) {
            $tmp = $photos['tmp_name'][$key];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            //validate file type
            $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($tmp);
            if (in_array($fileType, $validImageTypes)) {
                $newName = $id . "_" . $commentId . "_" . $key . "." . $ext;
                $photoNames[] = $newName;
                move_uploaded_file($tmp, $abs_us_root . $us_url_root . "usersc/task_media/" . $newName);
            } else {

                usError("Invalid file type. Only JPG, PNG, and GIF files are allowed.");
                Redirect::to($basePage . "method=" . $method . "&id=" . $id);
            }
        }
    }

    if (!empty($photoNames)) {
        $db->update("plg_tasks_comments", $db->lastId(), ['photos' => json_encode($photoNames)]);
    }
    notifyTask($task, "comment", $user->data()->id, $comment);
    usSuccess("Comment Added");
    Redirect::to($basePage . "method=" . $method . "&id=" . $id);
}

foreach ($comments as $comment) { ?>
    <div class="card mb-2">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <i class="fa fa-user me-2"></i>
                    <?php if ($comment->created_by == $user->data()->id) {
                        echo "You";
                    } else {
                        echo "<b>" . $comment->fname . " " . $comment->lname . "</b>";
                    }
                    ?>

                </div>
                <div>
                    <i class="fa fa-clock me-2"></i>
                    <?php echo date("M j, Y g:i a", strtotime($comment->created_on)); ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php echo $comment->comment; ?>
            <?php if (!empty($comment->photos)) { ?>
                <div class="row">
                    <?php foreach (json_decode($comment->photos) as $photo) { ?>
                        <div class="col-6 col-md-3">
                            <img src="<?php echo $us_url_root . "usersc/task_media/" . $photo; ?>" class="img-fluid photo taskPhotoLink" data-bs-toggle="modal" data-bs-target="#photoModal" data-src="<?php echo $us_url_root . "usersc/task_media/" . $photo; ?>">
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>
    </div>
<?php } ?>

<div class="card mb-2">
    <div class="card-header">
        <h5>New Comment</h5>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo Token::generate(); ?>">
            <input type="hidden" name="task_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label for="">Comment*</label>
                <textarea name="comment" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="">Attach Photos</label>
                <input type="file" name="photos[]" class="form-control" accept="image/jpeg, image/png, image/gif" multiple>

            </div>
            <div class="form-group">
                <button class="btn btn-primary">Add Comment</button>
            </div>
        </form>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Use modal-xl for extra large modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" id="taskModalImage" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var images = document.querySelectorAll('.photo');

        images.forEach(function(img) {
            img.addEventListener('click', function(event) {
                var src = img.getAttribute('data-src');
                console.log('Image clicked, src:', src); // Add this line for debugging
                var modalImage = document.getElementById('taskModalImage');
                modalImage.src = src;
            });
        });
    });
</script>