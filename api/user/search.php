<?php
    /**
     * @var start
     * Setting $start variable to store the correct micro time
     * will be used for performance check
     */
    $start = microtime(true);

    /**
     * Checking if user provided input parameter in the request
     */
    if (!isset($_POST['input']))
        /**
         * If user didn't provide an input parameter we return request status and the error details
         */
        return ["status" => false, "error" => "input parameter is missing from the request"];

    /**
     * @var input
     * Getting user input and storing it in $input variable and removing trailing spaces and set them to one space
     */
    $input = preg_replace("/\s+/", " ", $_POST['input']);
    /**
     * @var names
     * Splitting user input by space and storing the array of names in the $names variable
     * Removing all names that are shorten than two chars
     */
    $names = array_filter(explode(" ", $input), function($item) {
        return strlen($item) > 1;
    });

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
     * @var params
     * Will be used to store all parameters that will be used in the prepared statement
     */
    $params = [];

    /**
     * @var userJoin
     * Setting $usersJoin variable to an empty string
     * Will be set only if filter by region / department is set
     */
    $usersJoin = "";

    /**
     * @var department
     * Setting $department variable to the query param department if exists or null
     */
    $department = isset($_POST['department']) ? $_POST['department'] : null;

    /**
     * @var region
     * Setting $region variable to the query param region if exists or null
     */
    $region = isset($_POST['region']) ? $_POST['region'] : null;
    /**
     * Checking if $department or $region are not null
     */
    if ($department != null || $region != null) {
        /**
         * @var usersJoin
         * Setting $usersJoin to hold a sql statement for joining the users table only by a specific department, region and if username, mail, name are containing the user input
         */
        $usersJoin = "INNER JOIN users u ON u.uid = p.uid";
        if ($department != null) {
            $usersJoin .= " AND u.department = ?";
            $params[] = intval($department);
        }
        if ($region != null) {
            $usersJoin .= " AND u.region = ?";
            $params[] = intval($region);
        }
        $usersJoin .= " AND (";
        foreach ($names as $key=>$name) {
            if ($key > 0) $usersJoin .= " OR ";
            $usersJoin .= "concat(u.name,u.mail,u.username) LIKE concat(?, '%')";
        }
        $usersJoin .= ")";
        $params = array_merge($params, $names);
    }
    
    /**
     * @var like
     * Setting the $like variable that will store the query statement for our search
     */
    $like = "";
    foreach ($names as $key=>$name) {
        if ($key > 0) $like .= " OR ";
        $like .= "value LIKE concat(?, '%')";
    }
    $params = array_merge($params, $names);

    /**
     * @var query
     * Setting $query variable to store our query to the database for selecting user profile parts
     * 
     * @note - I didn't joined the user table by default because it damaged the preformance
     * @return resultset of uid and weight
     */
    $query = "
        SELECT
            p.uid,
            SUM(
                CASE
                    WHEN p.type = 'username' THEN 1
                    WHEN p.type = 'mail' THEN 2
                    WHEN p.type = 'name' THEN 4
                    ELSE 0
                END
            ) as weight
        FROM profile_parts p
            {$usersJoin}
        WHERE
            p.value IN (SELECT DISTINCT value FROM profile_parts WHERE {$like})
        GROUP BY
            p.uid, p.type
        ORDER BY
            weight DESC
        LIMIT 5
    ";
    /**
     * @var ps
     * Setting $ps to store access to the prepared statement
     */
    $ps = $con->prepare($query);
    if (!$ps->execute($params))
        /**
         * Returning an error if the prepared statement failed
         */
        return ["status" => false, "error" => "Prepared statement failed on line 72"];
    /**
     * Setting $rs to store the result set we recevied from the last prepared statement
     */
    $rs = $ps->fetchAll();

    /**
     * Checking if the result set is empty if true it will return an empty array no need to continue
     */
    if (count($rs) < 1) return ["status" => true, "data" => []];
    
    /**
     * @var where
     * Setting $where variable to store a where statement for selecting users by a specific uid
     */
    $where = "";
    foreach ($rs as $row) {
        if ($where != "") $where .= " OR ";
        $where .= "uid = ?";
    }

    /**
     * Setting $query variable to store our query to the database for selecting users
     * 
     * @return resultset [uid, username, mail, name, region, department]
     */
    $query = "
        SELECT 
            u.uid, u.username, u.mail, u.name, r.name as region, d.name as department
        FROM users u
            INNER JOIN departments d
            ON d.id = u.department
            INNER JOIN regions r
            ON r.id = u.region
        WHERE {$where}
    ";
    $ps = $con->prepare($query);
    $ps->execute(array_map(function($item) {
        return $item["uid"];
    }, $rs));

    /**
     * Setting $end variable to store the correct micro time
     * will be used for performance check
     */
    $end = microtime(true);
    /**
     * Returning the most relevant users for the search and the time it took
     */
    return ["status" => true, "data" => $ps->fetchAll(), number_format((float) $end-$start, 6, '.', '')];