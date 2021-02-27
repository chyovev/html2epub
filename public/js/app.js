var App = {
    hasInited: false,
    
    ///////////////////////////////////////////////////////////////////////////
    init: function() {
        if (App.hasInited) {
            return;
        }
        App.bind();
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
}

$(document).ready(function() {
    App.init();
});