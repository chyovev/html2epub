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
        $(document).on('click', '.delete-book', App.deleteBook);
        $(document).on('click', '.delete-chapter', App.checkChapterDeleteConditions);
        $(document).ajaxSend(App.setAjaxInProgress);
        $(document).ajaxComplete(App.unsetAjaxInProgress);
    },

    ///////////////////////////////////////////////////////////////////////////
    setAjaxInProgress: function() {
        App.isAjaxInProgress = true;
    },

    ///////////////////////////////////////////////////////////////////////////
    unsetAjaxInProgress: function() {
        App.isAjaxInProgress = false;
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
    showError: function() {
        var $modal = $('#informationModal');
        $modal.modal('show');
    },

    ///////////////////////////////////////////////////////////////////////////
    // show confirmation modal and if the user clicks the YES button,
    // hide the modal and call the function passed as parameter
    showConfirmationModal: function (message, yesCallback) {
        var $modal      = $('#confirmationModal'),
            $modalBody  = $modal.find('.modal-body'),
            $modalYes   = $modal.find('.modal-yes');

        $modalBody.html(message);

        $modal.modal('show');

        $modalYes.click(function() {
            $modal.modal('hide');
            yesCallback();
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    confirmDeletion: function(e) {
        e.preventDefault();

        var url = $(this).attr('href');

        App.showConfirmationModal(
            'Are you sure you want to delete this item?<br />This action cannot be reversed.',
            function() {
                window.location = url;
            }
        );
    },

    ///////////////////////////////////////////////////////////////////////////
    onKeyUp: function(e) {
        if (e.key === 'Escape') {
            App.hideModal();
        }
    },

    ///////////////////////////////////////////////////////////////////////////
    hideModal: function() {
        if ($('.modal').is(':visible')) {
            $('.modal').modal('hide');
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
                "template paste textcolor colorpicker textpattern footnotes",
            ],
            toolbar: "undo redo | searchreplace | styleselect | bold italic underline | removeformat | bullist numlist | footnotes | outdent indent | alignleft aligncenter alignright alignjustify | anchor link | subscript superscript | hr | code",
            formats: {
                alignleft:     {selector : '*', classes : 'left'},
                aligncenter:   {selector : '*', classes : 'center'},
                alignright:    {selector : '*', classes : 'right'},
                alignjustify:  {selector : '*', classes : 'full'},
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
            custom_undo_redo_levels: 10,
            paste_as_text: true,
            indentation: '2em',
            min_height: 400,
            height: 600,
            max_height: 900,
            keep_styles: false,
            elementpath: false,
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
        // try to save them before switching to another chapter
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
    // remove current active class, find link with same href attribute as URL
    // and mark its parent handle as active
    markTOCItemAsActive: function(url) {
        $('.dd-item.active').removeClass('active');
        $('.dd-handle a[href="' + url + '"]').closest('.dd-item').addClass('active')
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

                // scroll to flash message
                $('body, html').animate({scrollTop: $('.alert').offset().top - 20 });
            },

            // on server error show a generic message (the error was logged)
            error: function() {
                App.showError();
            }
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
            var $tocItem = $('.dd-item[data-id="' + id + '"');
            $tocItem.addClass('modified').find('.title a:first').text(newTitle);

            $section.text(newTitle);
        }

        // when updating a book, change the header title
        else if (type == 'book') {
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

        // don't send new requests before finishing already started requests
        if (App.isAjaxInProgress) {
            return false;
        }

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
                        'delete_url': response.delete_url,
                        'prepend':    prepend,
                    });

                    // if there were no chapters, the cucrent page is the book page
                    // mark it as active in the TOC
                    if ( ! $('.dd-item.active').length) {
                        $('.dd-item:first').addClass('active');
                    }

                    App.updateTocStructure();
                }
            },
            // on server error show a generic message (the error was logged)
            error: function() {
                App.showError();
            }
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    deleteBook: function(e) {
        e.preventDefault();

        var $this = $(this),
            url   = $this.attr('href');

        // delete book only on confirmation
        App.showConfirmationModal(
            'Are you sure you want to delete the whole book?<br />There’s no turning back from it.',
            function() {
                // don't send new requests before finishing already started requests
                if (App.isAjaxInProgress) {
                    return false;
                }

                return $.ajax({
                    url:      url,
                    type:     'GET',
                    dataType: 'JSON',

                    success: function(response) {
                        if ( ! response.status) {
                            App.showError();
                        }
                        else {
                            // first fade out all elements, then redirect to books index
                            $('.toc-wrapper, #content').animate({opacity: 0}, 600, function() {
                                $('.body-wrap, footer').fadeOut('slow', function() {
                                    window.location = response.url;
                                })
                            });
                        }
                    },
                    // on server error show a generic message (the error was logged)
                    error: function() {
                        App.showError();
                    }
                });      
            }
        );
    },

    ///////////////////////////////////////////////////////////////////////////
    checkChapterDeleteConditions: function(e) {
        e.preventDefault();

        var $this       = $(this),
            url         = $this.attr('href');
            $item       = $this.closest('.dd-item'),
            itemId      = $item.attr('data-id'),
            $item       = $('.dd-item[data-id="' + itemId +'"]'),
            children    = App.getAllChildren(itemId);

            
        // if the item or any of its children were modified or is about to be deleted,
        // ask for confirmation
        var itemOrChildrenModified = $item.hasClass('modified') || children.hasClass('modified'),
            isCurrentPageUnderFire = $item.hasClass('active')   || children.hasClass('active'),
            confirmMsg             = 'Are you sure you want to delete this item?'
                                   + (children.length ? '<br />This will also delete all sub-items.' : ''); 

        if (itemOrChildrenModified || isCurrentPageUnderFire) {
            App.showConfirmationModal(
                confirmMsg,
                function() {
                    App.deleteChapter(url, $item);
                }
            );
        }

        // if item and children are brand new, there's probably no valuable
        // information in them – delete them immediately
        else {
            App.deleteChapter(url, $item);
        }
    },

    ///////////////////////////////////////////////////////////////////////////
    deleteChapter: function(url, $item) {
        // don't send new requests before finishing already started requests
        if (App.isAjaxInProgress) {
            return false;
        }

        var itemId   = $item.attr('data-id'),
            $parent  = $item.closest('.dd-list'),
            children = App.getAllChildren(itemId),

            // if there are no siblings left, remove the whole parent
            $target  = $item.siblings().length ? $item : $parent;


        // if the page-to-be-deleted is the current one or any of its ancestors,
        // the current page will no longer exist; user should not remain on it
        var chapterNoLongerExists = $item.hasClass('active') || children.hasClass('active'),
            bookDetailUrl         = $('.dd-item:first').find('a.edit-chapter:first').attr('href');

        return $.ajax({
            url:      url,
            type:     'GET',
            dataType: 'JSON',

            success: function(response) {
                if ( ! response.status) {
                    App.showError();
                }
                else {
                    // first fade out element, then slide it up and finally remove it from DOM
                    $target.animate({opacity: 0}, 600, function() {
                        $(this).slideUp('normal', function() {
                            $('.dd').nestable('remove', itemId);
                        });
                    });

                    App.updateTocStructure();

                    // if current chapter no longer exists, load book information
                    if (chapterNoLongerExists) {
                        App.unsetAjaxInProgress();
                        App.initAjaxGetRequest(bookDetailUrl);
                    }
                }
            },
            // on server error show a generic message (the error was logged)
            error: function() {
                App.showError();
            }
        });
    },

    ///////////////////////////////////////////////////////////////////////////
    getAllChildren: function(id) {
        return $('.dd').nestable('getAllChildren', id);
    },

}

$(document).ready(function() {
    App.init();
});