var App = {
    hasInited: false,
    isAjaxInProgress: false,
    formSnapshot: false,
    tocSnapshot: false,
    
    ///////////////////////////////////////////////////////////////////////////
    init: function() {
        if (App.hasInited) {
            return;
        }
        App.bind();
        App.initNestable(4);
        App.initTinyMce();
        App.setFormSnapshot();
    },

    ///////////////////////////////////////////////////////////////////////////
    bind: function() {
        $(document).keyup(App.onKeyUp);
        $('a.delete').on('click', App.confirmDeletion);
        $('#toc-toggler').on('click', App.toggleTocPanel);
        $(document).on('click', '.toc-wrapper a.edit-chapter, .breadcrumb a', App.loadChapterOnClick);
        $(window).on('popstate', App.loadChapterOnPopstate);
        $(document).on('submit', '.ajax-form', App.onFormSubmit);
        $(document).on('click', 'button.close', App.dismissFlashMessage);
        $(document).on('click', '.add-chapter', App.addChapter);
    },

    ///////////////////////////////////////////////////////////////////////////
    // serialize all form input fields and store them in a var
    // to check against before submitting new requests
    setFormSnapshot: function() {
        App.formSnapshot = $('.ajax-form').serialize();
    },

    ///////////////////////////////////////////////////////////////////////////
    getFormSnapshot: function() {
        return App.formSnapshot;
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
    initNestable: function(maxDepth) {
        $('.dd').nestable({
            maxDepth: maxDepth,
            contentClass: 'title',
            includeActionsTab: true,
            callback: function(l, e) {
                App.updateTocStructure();
            }
        });

        // add global CSS selector to hide Add chapter button
        // from elements which have reached maximum depth
        var selector = '.dd-item '.repeat(maxDepth) + ' .add-chapter'
        var styleTag = $('<style> '+ selector +' { display: none; }</style>');
        $('html > head').append(styleTag);

        App.setTocSnapshot(JSON.stringify(App.getTocStructure()));
    },
    
    ///////////////////////////////////////////////////////////////////////////
    updateTocStructure: function() {
        var data     = App.getTocStructure(),
            dataJson = JSON.stringify(data)
            url      = $('.dd').attr('href');

        // if there are any changes in the structure,
        // send ajax request to save structure
        if (App.getTocSnapshot() !== dataJson) {

            return $.ajax({
                url:      url,
                type:     'POST',
                data:     {'chapters': data},
                dataType: 'JSON',

                success: function(response)  {
                    App.setTocSnapshot(dataJson);
                }
            });
        }
    },

    ///////////////////////////////////////////////////////////////////////////
    getTocStructure: function() {
        var chapters = $('.dd').nestable('asNestedSet'),
            data     = {};

        if ( ! chapters.length) {
            return;
        }

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

        return data;
    },

    ///////////////////////////////////////////////////////////////////////////
    setTocSnapshot: function(json) {
        App.tocSnapshot = json;
    },

    ///////////////////////////////////////////////////////////////////////////
    getTocSnapshot: function() {
        return App.tocSnapshot;
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

                // automatically update raw textarea value
                editor.on('change', function () {
                    editor.save();
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

        // if there were any changes in the form,
        // try to save it before switching to another chapter
        if (App.getFormSnapshot() !== $('.ajax-form').serialize()) {
            App.submitFormAjax($('form'), true, url);
        }

        // otherwise simply load new chapter via AJAX
        else {
            App.fadeOutContentAndCallFunction(App.initAjaxGetRequest, url);
        }
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
        App.markTOCItemAsActive(url);

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
        App.dismissFlashMessage();

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

        App.setFormSnapshot();

        // if the side panel is open on mobile,
        // hide it so the user knows that something has changed
        if ($('.toc-wrapper').hasClass('open')) {
            App.toggleTocPanel();
        }
    },

    ///////////////////////////////////////////////////////////////////////////
    fadeInContent: function() {
        $('#content, .breadcrumbs-wrapper').animate({opacity: 1}, 150);
    },

    ///////////////////////////////////////////////////////////////////////////
    onFormSubmit: function(e) {
        e.preventDefault();
        App.submitFormAjax($(this));
    },

    ///////////////////////////////////////////////////////////////////////////
    submitFormAjax: function($form, manuallyTriggered, newChapterUrl) {
        // don't send new requests before finishing already started requests
        if (App.isAjaxInProgress) {
            return false;
        }

        var url   = $form.attr('action') || window.location.href,
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
                    App.updateAllBookUrls(response.old_url, response.url);
                    App.updateTocAndHeaderLabels();

                    // when switching between chapters, current one needs to be saved first
                    // therefore submit form *manually* and if saving was successful,
                    // load the desired chapter
                    if (manuallyTriggered) {
                        App.fadeOutContentAndCallFunction(App.initAjaxGetRequest, newChapterUrl);
                        return;
                    }

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
    // in case of a book slug change, all URLs need to be updated
    updateAllBookUrls: function(old_url, new_url) {
        if ( ! old_url) {
            return;
        }

        $('[href^="' + old_url + '"]').each(function() {
            var oldHref = $(this).attr('href');
            var newHref = oldHref.replace(old_url, new_url);

            $(this).attr('href', newHref);
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    updateTocAndHeaderLabels: function() {
        var $section = $('#section-heading');

        if ( ! $section.length) {
            return;
        }

        var id       = $section.attr('data-id'),
            type     = $section.attr('data-type'),
            newTitle = $('input[name="title"]').val();

        // when updating a chapter, update its right column heading
        // and the corresponding TOC element
        if (type == 'chapter') {
            $section.text(newTitle);
            $('.dd-item[data-id="' + id + '"').find('.title a:first').text(newTitle);
        }

        // when updating a book, change the header title
        else if ($type == 'book') {
            $('header .title').text(newTitle);
        }
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

    ///////////////////////////////////////////////////////////////////////////
    addChapter: function(e) {
        e.preventDefault();

        if (App.isAjaxInProgress) {
            return false;
        }

        // mark current request as «in progress»
        App.isAjaxInProgress = true;

        var add_url   = $(this).attr('href'),
            parent_id = $(this).closest('.dd-item[data-id]').attr('data-id')
            prepend   = $('#prepend').is(':checked'); // whether to prepend or append new items

        return $.ajax({
            url:      add_url,
            type:     'POST',
            dataType: 'JSON',

            success: function(response) {
                if (response.status) {

                    // add new element, either as a parent or as a root
                    $('.dd').nestable('add', {
                        'id':         response.id,
                        'parent_id':  parent_id,
                        'content':    response.title,
                        'add_url':    add_url,
                        'edit_url':   response.edit_url,
                        'prepend':    prepend,
                    });

                    // if there were no chapters, the cucrent page is the book page
                    // mark it as active in the TOC
                    if ( ! $('.dd-handle.active').length) {
                        $('.dd-handle:first').addClass('active');
                    }

                    App.updateTocStructure();
                }
            },
            // on server error show a generic message (the error was logged)
            error: function() {
                alert('There was an error');
            }
        }).always(function() {
            App.isAjaxInProgress = false;
        });
    },

}

$(document).ready(function() {
    App.init();
});