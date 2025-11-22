<?php
// FILE: /app/core/Response.php

class Response {
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function text($text, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: text/plain');
        echo $text;
        exit;
    }

    public function html($html, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: text/html');
        echo $html;
        exit;
    }

    public function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }

    public function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    public function notFound($message = 'Not Found') {
        http_response_code(404);
        echo $message;
        exit;
    }

    public function forbidden($message = 'Forbidden') {
        http_response_code(403);
        echo $message;
        exit;
    }

    public function unauthorized($message = 'Unauthorized') {
        http_response_code(401);
        echo $message;
        exit;
    }

    public function serverError($message = 'Internal Server Error') {
        http_response_code(500);
        echo $message;
        exit;
    }

    public function setStatusCode($code) {
        http_response_code($code);
        return $this;
    }

    public function setHeader($key, $value) {
        header("$key: $value");
        return $this;
    }

    public function download($filePath, $filename = null) {
        if (!file_exists($filePath)) {
            $this->notFound('File not found');
        }

        $filename = $filename ?? basename($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: public');

        readfile($filePath);
        exit;
    }
}
