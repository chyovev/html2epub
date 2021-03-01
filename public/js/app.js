var App = {
    hasInited: false,
    
    ///////////////////////////////////////////////////////////////////////////
    init: function() {
        if (App.hasInited) {
            return;
        }
        App.bind();
        App.initNestable();
    },

    ///////////////////////////////////////////////////////////////////////////
    bind: function() {
        $(document).keyup(App.onKeyUp);
        $('a.delete').on('click', App.confirmDeletion);
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
    
}

$(document).ready(function() {
    App.init();
});