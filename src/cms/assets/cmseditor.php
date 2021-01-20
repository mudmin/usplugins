<?php if(count(get_included_files()) ==1) die();
//This is an example of a usersc/includes/cmseditor.php file that could be created
//to use an alternate editor
?>


<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
$('#editor').summernote();
});
</script>
