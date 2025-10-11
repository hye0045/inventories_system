<?php

/**
 * Class FileUploader
 * Xử lý việc xác thực và lưu trữ file được upload một cách an toàn.
 * Class này không tương tác với cơ sở dữ liệu.
 */
class FileUploader {

    private array $errors = [];

    // Cấu hình các loại file cho phép và kích thước tối đa
    private const ALLOWED_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    private const UPLOAD_ERRORS = [
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];

    /**
     * Phương thức chính để xử lý việc upload file.
     *
     * @param array $fileData Dữ liệu từ mảng $_FILES (ví dụ: $_FILES['my_file']).
     * @param string $destinationPath Đường dẫn đến thư mục lưu file.
     * @return string|false Trả về tên file mới nếu thành công, ngược lại trả về false.
     */
    public function upload(array $fileData, string $destinationPath): string|false {
        // 1. Xác thực file và đường dẫn
        if (!$this->isValidUpload($fileData) || !$this->isValidPath($destinationPath)) {
            return false;
        }

        // 2. Tạo tên file mới, duy nhất và an toàn
        $newFileName = $this->generateUniqueFileName($fileData['name']);
        $fullPath = rtrim($destinationPath, DS) . DS . $newFileName;

        // 3. Kiểm tra xem file đã tồn tại chưa (dù rất khó xảy ra với tên duy nhất)
        if (file_exists($fullPath)) {
            $this->errors[] = "File '{$newFileName}' already exists.";
            return false;
        }

        // 4. Di chuyển file vào thư mục đích
        if (move_uploaded_file($fileData['tmp_name'], $fullPath)) {
            return $newFileName; // Thành công! Trả về tên file để lưu vào CSDL.
        } else {
            $this->errors[] = "Failed to move the uploaded file.";
            return false;
        }
    }

    /**
     * Lấy danh sách các lỗi đã xảy ra.
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Kiểm tra các thông số cơ bản của file upload.
     * @param array $file
     * @return bool
     */
    private function isValidUpload(array $file): bool {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = self::UPLOAD_ERRORS[$file['error'] ?? UPLOAD_ERR_NO_FILE];
            return false;
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->errors[] = "File size exceeds the maximum limit of 5MB.";
            return false;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $this->errors[] = "Invalid file type. Only JPG, PNG, GIF are allowed.";
            return false;
        }

        return true;
    }

    /**
     * Kiểm tra thư mục đích có tồn tại và có quyền ghi không.
     * @param string $path
     * @return bool
     */
    private function isValidPath(string $path): bool {
        if (!is_dir($path) || !is_writable($path)) {
            $this->errors[] = "Destination path '{$path}' is not writable or does not exist.";
            return false;
        }
        return true;
    }
    
    /**
     * Tạo ra một tên file duy nhất để tránh bị ghi đè và các vấn đề bảo mật.
     * @param string $originalFileName
     * @return string
     */
    private function generateUniqueFileName(string $originalFileName): string {
        $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        // Ví dụ: file_60c729a8b1a2b.jpg
        return 'file_' . uniqid() . '.' . $extension;
    }
}
?>