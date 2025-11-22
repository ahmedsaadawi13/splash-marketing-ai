<?php
// FILE: /app/helpers/FileUploadHelper.php

class FileUploadHelper {
    private $allowedTypes = [];
    private $maxSize = 10485760; // 10MB default
    private $uploadPath;
    private $errors = [];

    public function __construct($uploadPath = '/storage/uploads') {
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }

    public function setMaxSize($bytes) {
        $this->maxSize = $bytes;
        return $this;
    }

    public function upload($file, $subfolder = '') {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[] = 'No file was uploaded.';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($this->maxSize) . '.';
            return false;
        }

        // Check file type
        if (!empty($this->allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedTypes)) {
                $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
                return false;
            }
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateUniqueFilename($extension);

        // Create upload directory if it doesn't exist
        $targetPath = __DIR__ . '/../../' . $this->uploadPath;
        if ($subfolder) {
            $targetPath .= '/' . trim($subfolder, '/');
        }

        if (!is_dir($targetPath)) {
            if (!mkdir($targetPath, 0755, true)) {
                $this->errors[] = 'Failed to create upload directory.';
                return false;
            }
        }

        // Move uploaded file
        $targetFile = $targetPath . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            $this->errors[] = 'Failed to move uploaded file.';
            return false;
        }

        // Return file info
        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => ($subfolder ? $subfolder . '/' : '') . $filename,
            'full_path' => $targetFile,
            'size' => $file['size'],
            'mime_type' => $file['type']
        ];
    }

    public function uploadCsv($file, $subfolder = 'imports') {
        $this->setAllowedTypes(['text/csv', 'text/plain', 'application/csv']);
        return $this->upload($file, $subfolder);
    }

    public function uploadImage($file, $subfolder = 'images') {
        $this->setAllowedTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        $this->setMaxSize(5242880); // 5MB for images
        return $this->upload($file, $subfolder);
    }

    public function delete($filepath) {
        $fullPath = __DIR__ . '/../../' . $this->uploadPath . '/' . ltrim($filepath, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }

    private function generateUniqueFilename($extension) {
        return uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds maximum allowed size.';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension.';
            default:
                return 'Unknown upload error.';
        }
    }

    private function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public static function sanitizeFilename($filename) {
        // Remove any path components
        $filename = basename($filename);
        // Remove any non-alphanumeric characters except dots, dashes, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return $filename;
    }
}
