# Online ePub generator

 HTML2ePub is a simple online e-book generator which allows the users with the help of a WYSIWYG Word-like editor to export their books in a [validated ePub 3.0](#publishing-epub-structure) document.

![HTML 2 ePub](public/img/html2epub.svg?raw=true)

#### Preview: [https://xroads-bg.com/html2epub](https://xroads-bg.com/html2epub)


## Back-end development

- PHP 7.1+
- MySQL 5.7+
- [MVC](#mvc)
- [Propel ORM 2.0](#propel-orm)
- [Ramsey Uuid 3.9+](#ramsey-uuid)
- [Monolog 1.26+](#error-logging)


### MVC
The project is built according to the **M**odel-**V**iew-**C**ontrol design pattern (respective folders are located in the `/src` directory) and has a single entry point being the file `public/dispatcher.php` where a custom **Router** class tries to match the current URL to any of the predefined routes and calls up the relevant controllers and their actions.

### Propel ORM
All CRUD operations on the database rely on the [Propel ORM 2.0](https://github.com/propelorm/Propel2) library.
In total, there are only three models which constitute the whole project: **Book**, **Chapter** and **Language**, the relationships between them being as follows:
- **Book** → (one-to-many) → **Chapter**
- **Book** → (many-to-one) → **Language**

#### Propel Behaviors

Books typically have covers. There’s an option to upload a cover image (after proper validation) using the custom upload class **ImageUpload** which was inspired by the [samayo/bulletproof](https://github.com/samayo/bulletproof) library.

Said image is referenced by a custom Propel Behavior **SingleImageUpload** which appends a column to each table it is applied to. This column stores meta information about the image (such as size, mime type etc.).

> **NB!**  Later on, when the functionality gets extended to support other models (**Chapter**) and a record could have more than one image associated with it, a new Behavior will be developed which will store all associations into a separate table – preferable than having multiple columns for each table.

#### Nested Set Behavior
Besides using the `Validate` behavior, the **Chapter** model also makes use of Propel’s `Nested Set Behavior`, but only to a limited extend due to the nature of the project. For instance, the behavior has a single starting point (called a *Root*) by default, so deleting it would naturally delete the whole tree. In this project however, all first-level items are considered “equal siblings” and there is no single *root* element: deleting the first element should also delete only its *sub-elements* without affecting any of the siblings.

Also, the getter and setter for children elements are overwritten to avoid unnecessary SQL queries caused by the Behavior’s behavior (pun intended).

### Ramsey/Uuid
Chapters are uniquely identified by UUIDs which get generated in the model’s `preInsert()` method using the [Ramsed/UUIDs](https://github.com/ramsey/uuid) PHP library. In the table, the UUID value is stored as a `binary` for performance reasons. Consequently, the model has a `getSlugAsString()` method which converts the UUID value to string.

### Error Logging
The PHP logging library [Monolog](https://github.com/Seldaek/monolog) is utilized for logging potential errors which may occur during execution. Several of Monolog’s built-in processors are set up to provide additional information about the error (such as URL, IP address, referrer etc.)



## Front-end development

- jQuery 3.4.1
- [Bootstrap 4.4.1](https://github.com/twbs/bootstrap)
- [Twig 2.13+](#twig-template-engine)
- [Nestable2](#chapters)
- [TinyMCE 5.7.0](https://www.tiny.cloud/)


The front-end development of the project revolves around the Bootstrap framework which handles responsive design in a pretty straight-forward manner. The `tooltip` function of bootstrap relies on [Popper Positioning Engine](https://github.com/popperjs). There are some custom CSS declarations (with respective media queries) which cover the nestables and a few minor stylesheet issues (such as the sticky header on mobile).


### Twig Template Engine
To improve readability and enhance code separation, all view files, including the ones used for the actual ePub generation, use the [Twig PHP Template Engine](https://github.com/twigphp/Twig). The `Environment` class gets extended so that additional specific features can be implemented, such as having a separate method to render a full page (header and footer including).


### Chapters
Chapters’ content is formatted using one of the most popular WYSIWYG JS editors – TinyMCE.

The “Add chapter” buttons trigger an AJAX request which initiates the creation of a new record in the database. Only then does the list element get visualized on screen.

> **NB!** If the request fails for some reason, the error gets logged and the user gets notified about it with a bootstrap modal dialog. Same applies to pretty much all failed requests.


The chapter rearrangement is taken care of by the [Nestable2](https://github.com/RamonSmit/Nestable2) jQuery plug-in which extends the [Nestable](https://github.com/dbushell/Nestable) plug-in by allowing the usage of `asNestedSet` – a function which returns `lft`, `rght`, and `level` of the nested items which corresponds with Propel’s [Nested Set](#nested-set-behavior) Behavior’s structure.
The plug-in has been slightly modified to fit the needs of the project: e.g. action buttons were added whose click events do **not** trigger a parent drag start event.

> **NB!** When chapters get rearranged, there’s no need to click any “*Save structure*” buttons: it all happens in real time with the help of a separate AJAX request which gets triggered on element drop event.


To improve UX further, switching between chapters also utilizes asynchronous requests.

> **NB!** It’s worth mentioning that there’s a Change-detection going on behind the scenes when switching between chapters: if any of the fields’ values were altered, yet another AJAX request gets triggered which in turn tries to save those changes.



## Publishing (ePub structure)

In essence, ePub documents are simply Zip archives which have a predefined [structure](https://www.pagina.gmbh/xml-hintergruende/pagina-das-kompendium/themenkomplex-i-cross-media/epub-der-neue-stern-am-e-book-himmel/der-aufbau-eines-epub-dokuments/):
```bash
mimetype
META-INF/
    container.xml
OPS/
    content.opf
    toc.ncx
    toc.xhtml
    part0001.xhtml
    part0002.xhtml
    ...
    partxxxx.xhtml
```

**mimetype** is the first file the devices “see”, so they know that the document is an ePub. As such, it should be the first one added to the archive in order for the [ePub validation](https://github.com/w3c/epubcheck) to pass.


Then there’s **container.xml** located in the **META-INF** folder which is the starting point – it points to the OPF file (Open Package Format) of the ePub; in this case that’s **content.opf**.

**content.opf** holds *information* about all documents which make up the ePub document (texts, images, fonts etc.). It’s an XML file which starts with the `<package>` tag and has the following sub-tags:
- **metadata**: holds meta data about the e-book (title, author, ISBN etc.)
- **manifest**: holds declaration of all documents needed for the presentation of the e-book (fonts, images, XHTML files, TOC)
- **spine**: lists all presentable documents in the order in which they should appear when browsing the e-book

Navigating between non-adjacent documents is possible thanks to Table of Contents. There’s a total of two TOCs. The first one is **toc.ncx** which uses the `navMap` tag to declare all chapters (in a nested manner) and their order. This TOC’s visualization however varies from device to device. This is where the second TOC comes in: **toc.xhtml**, which is an XHTML file and allows a uniform rendering of the Table of Contents.

Finally, the actual content of the e-book: **all other XHTML files** (chapters, cover page, title page) which get generated dynamically every time the user clicks the «Convert» button. The chapters’ file names follow the pattern `part000N.xhtml`, N being the `idref` of the chapter declared in **content.opf**.

> **NB!** Even though ePub doesn’t necessarily care about tag alignment of the files, I prefer to keep at least the internal XML files tidy. The [Beautify HTML PHP library](https://github.com/ivanweiler/beautify-html) takes care of that: it gets triggered right after the page has been rendered, but before it gets written to a file.


### Non-ASCII characters
Some devices have trouble displaying non-ASCII characters. To tackle this issue, the user can tick the option to include the fonts in the ePub. The downside to that is that fonts add extra weight to the file size.


## Future development
This project is in beta phase and currently has limited functionality, but it gets the job done for now. Some of the features which need to be implemented over time include but are not limited to:
- adding pagination to books
- adding images to chapters
- adding image cropping
- importing of existing epubs
- keeping versions of books (*so that new exports don’t wipe out previously exported versions*)
- adding users



## Local project setup:

1. Install dependencies using `composer install`
2. Create database
3. Load SQL structure dump **html2epub.sql** located in `src/models/propel/generated-sql`
4. Load SQL data dump **data-dump.sql** located in `src/models/propel/generated-sql`
5. Edit database configuration in `src/models/propel/propel.xml`
5.1. Navigate to `src/models/propel` and execute `../../../vendor/bin/propel config:convert` to copy DB configuration to `/src/models/propel/generated-conf/config.php` (alternatively, you can edit it manually)



### Disclaimer
The `languages` table of the dump uses slightly modified data from [this](https://stackoverflow.com/a/28357857) post. The sample book provided in the dump is *“The War of the Worlds”* by H.G. Wells which is part of the [public domain](https://www.napier.ac.uk/about-us/news/hg-wells-books-enter-the-public-domain) as of 2017. The text was manually copied from the [Gutenberg project](http://www.gutenberg.org/ebooks/36).
The fonts used in this project are part of the [Liberation](https://github.com/liberationfonts) family.