var App = {
    hasInited: false,
    
    ///////////////////////////////////////////////////////////////////////////
    init: function() {
        if (App.hasInited) {
            return;
        }
        App.bind();
        App.initNestable();
        App.initTinyMce();
    },

    ///////////////////////////////////////////////////////////////////////////
    bind: function() {
        $(document).keyup(App.onKeyUp);
        $('a.delete').on('click', App.confirmDeletion);
        $('#toc-toggler').on('click', App.toggleTocPanel);
    },

    ///////////////////////////////////////////////////////////////////////////
    confirmDeletion: function(e) {
        e.preventDefault();

        var $modal      = $('#deleteModal'),
            $confirmBtn = $modal.find('.btn-danger'),
            href        = $(this).attr('href');

        $confirmBtn.attr('href', href);
        $modal.modal('show');

        return false;
    },

    ///////////////////////////////////////////////////////////////////////////
    onKeyUp: function(e) {
        if (e.key === 'Escape') {
            if ($('#deleteModal').is(':visible')) {
                $('#deleteModal').modal('hide');
            }
        }
    },

    ///////////////////////////////////////////////////////////////////////////
    toggleTocPanel: function() {
        $('.toc-wrapper').toggleClass('open');
        $(this).toggleClass('open');
    },
    
    ///////////////////////////////////////////////////////////////////////////
    initNestable: function() {
        $('.dd').nestable({
            callback: function(l, e) {
                var chapters = l.nestable('asNestedSet');
                chapters.forEach(function(element) {
                    var id = element.id;

                    $('input[name="chapters[' + id + '][tree_left]"]').val(element.lft);
                    $('input[name="chapters[' + id + '][tree_right]"]').val(element.rgt);
                    $('input[name="chapters[' + id + '][tree_level]"]').val(element.depth);
                });
            }
        });
    },
    
    ///////////////////////////////////////////////////////////////////////////
    initTinyMce: function() {
        tinymce.init({
            selector: ".tinymce",
            content_css : [_root + 'public/css/tinymce.css'],
            menubar: false,
            plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime media nonbreaking save directionality",
                "template paste textcolor colorpicker textpattern",
            ],
            toolbar: "undo redo | searchreplace | styleselect | bold italic underline | removeformat | bullist numlist | outdent indent | alignleft aligncenter alignright alignjustify | anchor link | subscript superscript | hr | code",
            formats: {
                underline:     {inline : 'u',   exact : true },
                strikethrough: {inline : 'del', exact : true },
            },
            style_formats: [
                { title: 'Font style', items: [
                    { title: 'Small Caps',    inline: 'span', classes: 'small-caps' },
                    { title: 'Bold',          inline: 'strong' },
                    { title: 'Italic',        inline: 'em' },
                    { title: 'Underline',     inline: 'u' },
                    { title: 'Strikethrough', inline: 'del' },
                    { title: 'Superscript',   inline: 'sup' },
                    { title: 'Subscript',     inline: 'sub' },
                ] },

                { title: 'Paragraph style', items: [
                    { title: 'Normal',    block: 'p', classes: 'pre-code', exact: true },
                    { title: 'No indent', block: 'p', classes: 'noindent', exact: true },
                ] },

                { title: 'Headings', items: [
                    { title: 'Heading 1', block: 'h1' },
                    { title: 'Heading 2', block: 'h2' },
                    { title: 'Heading 3', block: 'h3' },
                ] },
            ],
            paste_as_text: true,
            height: 800,
        });
    },

}

$(document).ready(function() {
    App.init();
});