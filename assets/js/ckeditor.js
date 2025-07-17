import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.querySelector('#editor');
    if (!editorElement) return;

    ClassicEditor.create(editorElement, {
        toolbar: [
            'bold', 'italic', 'underline', 'strikethrough',
            'fontSize', 'fontFamily', 'alignment',
            'numberedList', 'bulletedList'
        ]
    })
    .then(editor => {
        window.editor = editor;
    })
    .catch(error => {
        console.error(error);
    });
});
