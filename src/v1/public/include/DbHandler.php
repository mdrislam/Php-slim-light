<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
A domain Class to demonstrate RESTful web services
*/

class DbHandler
{
    const NORMAL_USER = 0;

    const JOB_CARD_CREATED = 152;
	const QA_TESTING = 113;
	const QC_COMPLETED = 155;
	const JOB_CARD_CLOSED = 156;
	
	const HOME = 145;
    const OFFICE = 146;
	
	
    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /**
     * Validating user
     *
     * @param id
     * @param pass
     *
     * @return user
     * @author risad <risadmit05@email.com>
     */
    function getVersionInformation()
    {
        $stmt = $this->conn->prepare("SELECT * FROM users where id =3 ORDER BY ID DESC LIMIT 1");
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }


}
