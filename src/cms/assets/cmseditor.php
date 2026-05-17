<?php if(count(get_included_files()) ==1) die();
//This is an example of a usersc/includes/cmseditor.php file that could be created
//to use an alternate editor
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
$(document).ready(function(){
    $('#editor').summernote({
            stickyToolbar: {
                enabled: true, // enable/disable sticky toolbar
                offset: 0, //y offset from top
                zIndex: 9999 //z-index of the toolbar
            },
            height: 400,
            toolbar: [
                ['insert', ['link', 'video', 'picture']], // Image editing options at the top
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['style', ['style']],
                ['fontsize', ['fontsize']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['color', ['color']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['height', ['height']],
                ['codeview', ['codeview']],
                ['fullscreen', ['fullscreen']]
            ],
            styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'] // Heading styles
        });
});
</script>
