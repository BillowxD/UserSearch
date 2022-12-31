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
     * Returning all regions from the regions table
     */
    return $con->query("SELECT * FROM regions")->fetchAll();