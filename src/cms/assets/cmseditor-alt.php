<?php if(count(get_included_files()) ==1) die();
//You do not have to use the built in editor! You can create a file called
//cmseditor.php in usersc/includes and put your own editor javascript in there!
//make sure to have it target #editor
?>


<script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>

 <style media="screen">
 .ck-editor__editable {
   min-height: 400px;
 }
 </style>
 <script>
     ClassicEditor
         .create( document.querySelector( '#editor' ) )
         .catch( error => {
             console.error( error );
         } );
 </script>
