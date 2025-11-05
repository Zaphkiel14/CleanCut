<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class FileController extends BaseController
{
    public function writable()
    {
        $relPath = $this->request->getGet('path');
        if (!$relPath) {
            return $this->response->setStatusCode(400)->setBody('Missing path');
        }

        // Security: normalize and restrict to allowed directories under writable
        $relPath = str_replace(['..', "\\"], ['','/'], $relPath);
        if (!preg_match('#^(uploads/|uploads\\/).+#i', $relPath)) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $fullPath = rtrim(ROOTPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
        if (!is_file($fullPath)) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Cache-Control', 'public, max-age=31536000');
        return $this->response->setBody(file_get_contents($fullPath));
    }
}


