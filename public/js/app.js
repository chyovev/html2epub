var App = {
    hasInited: false,
    isAjaxInProgress: false,
    
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
        $(document).on('click', '.toc-wrapper a, .breadcrumb a', App.loadChapterOnClick);
        $(window).on('popstate', App.loadChapterOnPopstate);
        $(document).on('submit', '.ajax-form', App.submitFormAjax);
        $(document).on('click', 'button.close', App.dismissFlashMessage);
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
                App.updateTocStructure();
            }
        });
    },
    
    ///////////////////////////////////////////////////////////////////////////
    updateTocStructure: function() {
        var chapters = $('.dd').nestable('asNestedSet'),
            url      = $('.dd').data('url'),
            data     = {};

        // iterate through all chapters and add their properties to data object
        chapters.forEach(function(element) {
            var id = element.id;

            data[id] = {
                'id':         id,
                'tree_left':  element.lft,
                'tree_right': element.rgt,
                'tree_level': element.depth,
            };

        });

        // send ajax request to save structure
        return $.ajax({
            url:      url,
            type:     'POST',
            data:     {'chapters': data},
            dataType: 'JSON'
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
            setup: function(editor) {
                editor.on('init', function(e) {
                    App.fadeInContent();
                });
            }
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    loadChapterOnClick: function(e) {
        var url = $(this).attr('href');

        if (App.isBreadCrumbClick(url)) {
            return true;
        }

        e.preventDefault();

        // don't refetch current chapter
        if ($(this).parents('.dd-handle').hasClass('active')) {
            return false;
        }


        // TODO try to save current page
        // if successful, then load new chapter

        App.markTOCItemAsActive(url);
        App.fadeOutContentAndCallFunction(App.initAjaxGetRequest, url);
    },

    ///////////////////////////////////////////////////////////////////////////
    // if there's *no* link with the same URL in the .toc-wrapper,
    // consider the click a breadcrumb click
    isBreadCrumbClick: function(url) {
        return ($('.toc-wrapper').find('a[href="' + url + '"]').length == 0);
    },

    ///////////////////////////////////////////////////////////////////////////
    // remove current active class, find link with same href attribute as URL
    // and mark its parent handle as active
    markTOCItemAsActive: function(url) {
        $('.dd-handle.active').removeClass('active');
        $('.dd-handle').find('a[href="' + url + '"]').parents('.dd-handle').addClass('active')
    },

    ///////////////////////////////////////////////////////////////////////////
    // onclick calls pushState which simulates a page reload,
    // but that doesn't apply to navigation buttons;
    // a separate popstate eventlistener is needed
    loadChapterOnPopstate: function(e) {
        // strip host part from current URL
        var href = (window.location.href).replace(window.location.origin, '');

        App.markTOCItemAsActive(href);

        // the ajax response was previously passed to pushState as a «state» property
        // however, if there's no state property, initiate AJAX request
        e.originalEvent.state
            ? App.fadeOutContentAndCallFunction(App.updatePageContent, e.originalEvent.state)
            : App.fadeOutContentAndCallFunction(App.initAjaxGetRequest, href);
    },

    ///////////////////////////////////////////////////////////////////////////
    initAjaxGetRequest: function(url) {
        return $.ajax({
            url:      url,
            type:     'GET',
            dataType: 'JSON',

            // save the url as a part of the response
            // to use it later in the popstate event
            success: function(response) {
                response.url = url;

                App.updatePageContent(response);

                history.pushState(response, document.title, url);
            },

            // if the request fails, simply redirect the user to the page
            error: function () {
                window.location = url;
            }
        })
    },

    ///////////////////////////////////////////////////////////////////////////
    // fade out breadcrumb and content separately (to avoid double callback call)
    // and call the callback function once the animation is over
    fadeOutContentAndCallFunction: function(callbackFunction, callbackFunctionParams) {
        $('.breadcrumbs-wrapper').animate({opacity:0}, 150);

        $('#content').animate({opacity:0}, 150, function() {
            callbackFunction(callbackFunctionParams);
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    updatePageContent: function(response) {
        if (response == null) {
            return;
        }

        // update breadcrumbs and right column
        $('.breadcrumbs-wrapper').html(response.breadcrumbs);
        $('#content').html(response.html);

        // if there is no tinymce, fade in content,
        // otherwise destroy old tinymce and initiate new one
        // (content gets faded on tinymce onload event)
        $('.tinymce').length
            ? tinymce.remove() || App.initTinyMce()
            : App.fadeInContent();

        // update form action attribute
        $('form').attr('action', response.url);

        // replace the metatitle of the document
        document.title = response.metaTitle;
    },

    ///////////////////////////////////////////////////////////////////////////
    fadeInContent: function() {
        $('#content, .breadcrumbs-wrapper').animate({opacity: 1}, 150);
    },

    ///////////////////////////////////////////////////////////////////////////
    submitFormAjax: function(e) {
        e.preventDefault();

        // don't send new requests before finishing already started requests
        if (App.isAjaxInProgress) {
            return false;
        }

        var $form = $(this),
            url   = $form.attr('action') || window.location.href,
            type  = $form.attr('method'),
            data  = $form.serialize();

        // mark current request as «in progress»
        App.isAjaxInProgress = true;

        // reset all previous errors on submit
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').html('');

        return $.ajax({
            url:      url,
            type:     type,
            data:     data,
            dataType: 'JSON',

            success: function(response) {

                if (response.status) {

                    // if redirect is true, do a new request
                    // otherwise, pushHistory with the new url (in case of a slug change)
                    response.redirect
                        ? window.location = response.url
                        : (response.url && history.pushState(response, document.title, response.url));
                }
                // if the status is false, there were errors – show them
                else {
                    $.each(response.errors, function(field, errors) {
                        $('#' + field).addClass('is-invalid')
                                      .next('.invalid-feedback').html(errors[0]);
                    });
                }

                // update breadcrumbs if any change
                if (response.breadcrumbs) {
                    $('.breadcrumbs-wrapper').html(response.breadcrumbs);
                }

                // in any case, hide previous flash message and show new one
                App.dismissFlashMessage();
                $form.prepend(response.flash);
                $('.flash-message').fadeIn();
            },

            // on server error show a generic message (the error was logged)
            error: function() {
                alert('There was an error');
            }
        }).always(function() {
            App.isAjaxInProgress = false;
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    dismissFlashMessage: function(e) {
        // if the flash was dismissed on click, make a smooth transition
        if (e) {
            $('.flash-message').slideUp('normal', function() {
                $(this).remove();
            });
        }

        // otherwise remove it abruptly
        else {
            $('.flash-message').remove();
        }
    },

}

$(document).ready(function() {
    App.init();
});