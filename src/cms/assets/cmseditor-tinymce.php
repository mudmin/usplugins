<?php if(count(get_included_files()) ==1) die();
//This is an example of a usersc/includes/cmseditor.php file that could be created
//to use an alternate editor
// For security purposes. summernote has replaced tinymce.
?>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

$('#editor').summernote({
  height: 300,                 // set editor height
  minHeight: null,             // set minimum height of editor
  maxHeight: null,             // set maximum height of editor
  focus: true                  // set focus to editable area after initializing summernote
});
});
</script>
