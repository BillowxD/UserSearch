<?php

    namespace Database;

    /**
     * Connection class
     * @description - Used for getting access to database connection
     */
    class Connection {
        
        /**
         * @var con -- stores PDO instance
         */
        public static $con = null;

        /**
         * Creating PDO instance if database connection is currently null
         * 
         * @return PDO
         */
        public static function getConnection() {
            if (self::$con === null) {
                self::$con = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
                self::$con->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            }
            return self::$con;
        }

    }