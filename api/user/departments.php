<?php
    /**
     * Using the Connection class
     */
    use Database\Connection;

    /**
     * @var con
     * Getting PDO instance from Connection class and attaching it to a new $con variable
     */
    $con = Connection::getConnection();

    /**
     * Returning all departments from the departments table
     */
    return $con->query("SELECT * FROM departments")->fetchAll();