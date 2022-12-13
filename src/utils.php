<?php
abstract class FileUtils
{

    public static function get_file_error_message_from_code($code)
    {
        $msg = FileUtils::FILE_ERROR_MESSAGES[$code] ?? null;
        if ($code === UPLOAD_ERR_INI_SIZE) {
            $msg .= ' (' . ini_get("upload_max_filesize") . 'B)';
        }
        return $msg;
    }

    public static function check_and_get_base_mime_type($file_tmp_name,$file_error_name,$ALLOWED_BASE_MIME_TYPES,&$mime_type){
        //get concrete mime type of file
        $mime_type = mime_content_type($file_tmp_name);
        if ($mime_type === false) {
            return "Could not find mime type of file $file_error_name";
        }

        //extract base mime type from concrete mime type
        $mime_base_pos = mb_strpos($mime_type, '/');
        if ($mime_base_pos !== false) {
            $mime_type = mb_substr($mime_type, 0, $mime_base_pos);
        }

        //check if is allowed mime type
        if (!in_array($mime_type, $ALLOWED_BASE_MIME_TYPES)) {
            return "Invalid base mime type '$mime_type', allowed base mime types are: '" . implode("', '", $ALLOWED_BASE_MIME_TYPES) . "'";
        }
    }
    public static function upload_file_html($file, $MAX_FILE_SIZE, $HTML_FILE_GEN_BY_MIME_TYPE, &$new_file_name, &$content)
    {
        if ($file !== null) {
            //check error
            if (($error = $file['error'] ?? null)) {
                return FileUtils::get_file_error_message_from_code($error);
            }
            //check size
            if (($file_size = $file['size']) > $MAX_FILE_SIZE) {
                return "File size '$file_size' exceeds maximum allowed size ($MAX_FILE_SIZE"."B)";
            }

            //get file name
            $file_name = $file['name'];
            $file_tmp_name = $file['tmp_name'];
           if(($error = FileUtils::check_and_get_base_mime_type($file_tmp_name,$file_name,array_keys($HTML_FILE_GEN_BY_MIME_TYPE),$mime_type)) !== null){
            return $error;
           }
            
            $file_basename = basename($file_name);
            $target_dir = "uploads";
            $target_file = $target_dir . "/" . $file_basename;

            if (!is_dir($target_dir)) {
                mkdir($target_dir);
            } // elseif (file_exists($target_file)) {
            //     return "File '$file_name' already exists";
            // }

            //move file
            if (!move_uploaded_file($file_tmp_name, $target_file)) {
                return "File '$file_name' could not be moved";
            }

            $new_file_name = $target_file;
           $content = $HTML_FILE_GEN_BY_MIME_TYPE[$mime_type]($new_file_name,$file_basename) ?? null;
           if($content === null){
            return "Internal server error occured: Invalid mime type '$mime_type'";
           }
        }
        return null;
    }

    private const FILE_ERROR_MESSAGES = array(
        // UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
    );
}
