<?php
/**
 * This file defines the {@see \Beluga\Web\Http\File} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @subpackage     Http
 * @version        0.1.0
 */


namespace Beluga\Web\Http;


include_once __DIR__ . '/functions.php';


/**
 * A static File-Upload helper class.
 */
class File
{

    /**
     * Prepares a file for upload. To be used inside the parameters declaration for a request.
     *
     * @param  string $filePath The file path.
     * @param  string $mimeType The file mime type.
     * @param  string $postName Name of the file to be used in the upload data.
     * @return mixed
     */
    public static function add( $filePath, $mimeType = '', $postName = '' )
    {
        return curl_file_create( $filePath, $mimeType, $postName );
    }

}

