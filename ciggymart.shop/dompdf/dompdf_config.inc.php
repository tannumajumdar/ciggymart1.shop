<?php
/*
 * dompdf entry point.
 *
 * The bundled dompdf 0.6 (kept in ../dompdf-legacy-0.6 for reference) cannot run
 * on PHP 7/8: it relies on each(), create_function() and PHP 5's loose string
 * arithmetic inside the layout engine. This file now loads dompdf 3.x from
 * Composer instead and exposes the same DOMPDF class and snake_case methods the
 * application already calls, so no calling code had to change.
 */

require_once dirname(__FILE__) . '/../vendor/autoload.php';

if (!class_exists('DOMPDF', false)) {

    class DOMPDF extends \Dompdf\Dompdf {

        public function __construct($options = null) {
            parent::__construct($options);
            // dompdf 0.6 resolved local images/CSS relative to the document root.
            $this->getOptions()->setChroot(array(dirname(__FILE__) . '/..'));
            $this->getOptions()->setIsRemoteEnabled(true);
        }

        /** dompdf 0.6 spelling of loadHtml(). */
        public function load_html($str, $encoding = null) {
            return $this->loadHtml($str, $encoding);
        }

        /** dompdf 0.6 spelling of loadHtmlFile(). */
        public function load_html_file($file) {
            return $this->loadHtmlFile($file);
        }

        /** dompdf 0.6 spelling of setPaper(). */
        public function set_paper($size, $orientation = 'portrait') {
            return $this->setPaper($size, $orientation);
        }

        /** dompdf 0.6 spelling of setBasePath(). */
        public function set_base_path($path) {
            return $this->setBasePath($path);
        }
    }
}

// A few dompdf 0.6 constants that calling code may still reference.
if (!defined('DOMPDF_ENABLE_REMOTE'))    { define('DOMPDF_ENABLE_REMOTE', true); }
if (!defined('DOMPDF_DEFAULT_PAPER_SIZE')) { define('DOMPDF_DEFAULT_PAPER_SIZE', 'a4'); }
