<?php

use Beautify_Html as BeautifyHtml;
use FileSystem as FS;
use RecursiveIteratorIterator as RIIterator;
use RecursiveDirectoryIterator as RDIterator;

class PublishController extends AppController {

    private $bookPath;

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $slug = Router::getRequestParam('book');
        $book = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        // generate epub by creating all files, archiving them
        // as ZIP (with the epub extension) and serving the output
        $this->setBookPath($book);
        $this->prepareFolder();
        $this->generateCoverPage($book);
        $this->generateTitlePage($book);
        $this->generateCopyrightPage($book);
        $this->generateDedicationPage($book);
        $this->generateChapterPages($book);
        $this->generateContentOpf($book);
        $this->generateTOC($book);

        $fileName = $this->generateEpub($book);
        $this->setDownloadHeaders($fileName);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function setBookPath(Book $book) {
        $this->bookPath = BOOKS_PATH . '/generated/' . $book->getId();
        $this->_setViewVars(['book' => $book]);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function prepareFolder(): void {
        $path     = $this->bookPath;
        $basename = FS::getName($path);

        // try to create the folder
        FS::createFolder($path);
            
        // make sure its writeable
        if ( ! FS::isWriteable($path)) {
            throw new Exception(sprintf("'%s' folder has no write permissions.", $basename));
        }

        // copy template files
        $this->copyBookTemplates($path);

        // set book templates view folder when generating xhtml files
        $this->setViewsPath(BOOKS_PATH . '/book-template/templates');
    }

    ///////////////////////////////////////////////////////////////////////////
    // copy the files that are the same
    // between all books (like stylesheets, mimetype, etc.)
    private function copyBookTemplates(string $targetFolder) {
        $sourceFolder = BOOKS_PATH . '/book-template';

        $itemsToCopy  = [
            '/META-INF',
            '/OPS',
            '/mimetype',
        ];

        foreach ($itemsToCopy as $item) {
            FS::copy($sourceFolder.$item, $targetFolder.$item);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    // generate cover page only if there's a cover image
    private function generateCoverPage(Book $book): void {
        if ($book->getCoverImageName()) {
            $sourcePath = $book->getCoverImageSrc(true);
            $ext        = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $fileName   = sprintf('%s-cover.%s', $book->getSlug(), $ext);
            $targetPath = $this->bookPath . '/OPS/images/' . $fileName;

            // copy cover image
            FS::copy($sourcePath, $targetPath);

            $this->generateXHTMLPage('coverpage', NULL, ['fileName' => $fileName]);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateTitlePage(Book $book): void {
        $this->generateXHTMLPage('titlepage');
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateCopyrightPage(Book $book): void {
        $this->generateXHTMLPage('copyright');
    }

    ///////////////////////////////////////////////////////////////////////////
    // generate dedication page only if there's book dedication
    private function generateDedicationPage(Book $book): void {
        if ($book->getDedication()) {
            $this->generateXHTMLPage('dedication');
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateChapterPages(Book $book): void {
        $chapters = $book->getChapters();
        $i        = 1;

        foreach ($chapters as $item) {
            $footnotes = $this->extractFootnotes($item);
            $viewVars  = [
                'metaTitle' => $item->getTitle() . ' – ' . $book->getTitle(),
                'chapter'   => $item,
                'footnotes' => $footnotes,
            ];

            $fileName = $item->getPartNumber();
            $this->generateXHTMLPage('chapter', $fileName, $viewVars);

            $i++;
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    private function extractFootnotes(Chapter $chapter): array {
        $footnotes  = [];
        $body       = $chapter->getBody();
        $footMarkup = '/(<span[^>]+><button[^>]+data-content="([^"]+)">[^<>]+<\/button><\/span>)/'; // the TinyMCE footnote plugin uses such HTML for notes

        // if there's a body, search for the footnote markup in it
        if ($body) {
            preg_match_all($footMarkup, $body, $matches);

            // cycle through all footnote markup matches (if any)
            foreach ($matches[1] as $key => $note) {
                $id          = $key + 1;

                // replace the note button with actual link
                $placeholder = $matches[0][$key];
                $replacement = '<sup><a href="#meaning-' . $id . '" id="note-' . $id . '">[' . $id . ']</a></sup>';
                $body        = str_replace($placeholder, $replacement, $body);

                // decode the note and append it to the footnotes array
                $footnotes[] = '<a href="#note-' . $id . '" id="meaning-' . $id . '">[' . $id . ']</a> ' . urldecode($note);
            }

            // if there were any footnotes detected,
            // replace the chapter body with the updated one
            if ($footnotes) {
                $chapter->setBody($body);
            }
        }

        return $footnotes;
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateContentOpf(Book $book): void {
        $chapters = $book->getChapters();

        $viewVars = [
            'chapters' => $chapters,
            'tree'     => $book->createTree($chapters),
        ];

        $image = $book->getCoverImageSrc(true);

        // if there's a cover image, extract its mime type
        // and rename it to «slug-cover.extension»
        if ($image) {
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $viewVars['fileName'] = sprintf('%s-cover.%s', $book->getSlug(), $ext);
            $viewVars['mimeType'] = $book->getCoverImageData()['mime'];
        }

        $this->generateMiscPage('content.opf.twig', 'content.opf', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateTOC(Book $book): void {
        $viewVars = [
            'chapters' => $book->getChaptersAsNestedSet(),
        ];

        $this->generateMiscPage('toc.ncx.twig', 'toc.ncx', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateXHTMLPage(string $template, string $fileName = NULL, array $viewVars = []): void {
        // if no filename was specified, use the template name
        if ( ! $fileName) {
            $fileName = $template;
        }

        $file = $this->bookPath . '/OPS/' . $fileName . '.xhtml';
        $html = $this->renderFullPage($template, $viewVars);
        FS::createFile($file, $html);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function generateMiscPage(string $template, string $fileName, array $viewVars = []) {
        $file    = $this->bookPath . '/OPS/' . $fileName;
        $content = $this->renderTemplate($template, $viewVars);

        // misc pages are usually meta pages and it is nice
        // to have their indentation in a beautiful way
        $this->beautifyHTML($content);

        FS::createFile($file, $content);
    }

    ///////////////////////////////////////////////////////////////////////////
    // in essence epubs are zip archives with epub extensions,
    // therefore add all book's contents to a ZIP archive and save it as epub
    private function generateEpub(Book $book): string {
        $path     = realpath($this->bookPath);
        $fileName = $this->bookPath . '/' . $book->getSlug() . '.epub';
        $zip      = new ZipArchive();
        $zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RIIterator(new RDIterator($path), RIIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if ( ! FS::isFolder($file)) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $fileName;
    }

    ///////////////////////////////////////////////////////////////////////////
    private function setDownloadHeaders(string $fileName): void {
        $basename = FS::getName($fileName);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $basename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($fileName));
        ob_end_flush();
        @readfile($fileName);
        exit;
    }

    ///////////////////////////////////////////////////////////////////////////
    // fix indentation of tags
    private function beautifyHTML(&$html): void {
        $beautifier = new BeautifyHtml([
          'indent_char'       => ' ',
          'indent_size'       => 4,
          'unformatted'       => [],
          'preserve_newlines' => false,
          'indent_scripts'    => 'normal' // keep|separate|normal
        ]);

        $html = $beautifier->beautify($html);
    }
}