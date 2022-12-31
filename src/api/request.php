<?php
    namespace API;

    use DateTime;

    /**
     * Request class
     * @description - used for handling url request and route it to the needed API file
     * 
     */
    class Request {

        /**
         * @var path -- stores the requested uri path
         */
        public $path;

        /**
         * Setting up all needed headers and variables that will be used in the API request
         */
        function __construct()
        {
            header('Content-Type: application/json; charset=utf-8');
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                // allows to enter from all origins
                header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Max-Age: 86400');    // cache for 1 day
            }
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                    // may also be using PUT, PATCH, HEAD etc
                    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            $this->path = preg_replace("/\?.*/", "", $_SERVER["REQUEST_URI"]); // removing GET params from request
            $this->path = substr( // removing root folder name from the url request
                $this->path,
                strlen(
                    substr(
                        __ROOT__,
                        strlen($_SERVER["DOCUMENT_ROOT"]) + 1
                    )
                )
            );
        }

        /**
         * Gets the response from the API file
         * 
         * @return [include of an API file]
         */
        public function getResponse() {
            $api = $this->path.".php";
            if (!file_exists(__API__."{$api}")) return false;
            return include __API__."{$api}";
        }

    }