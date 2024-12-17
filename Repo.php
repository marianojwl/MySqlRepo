<?php
namespace marianojwl\MySqlRepo {
    class Repo {
        protected $conn;
        protected $db;
        protected $table;
        
        public function __construct() {
            $conn = new  \mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"]);
            $this->db = $_ENV["DB_NAME"];
            $this->conn = $conn;
            
        }

        // function that will validate parameters in $_GET
        public function validateGetParam(string $paramName) {
          switch ($paramName) {
            case 'page':
              return (int)($_GET[$paramName]??1);
            case 'rowsPerPage':
              return (int)($_GET[$paramName]??10);
            case 'search':
              return $this->conn->real_escape_string($_GET[$paramName]??'');
            default:
              return '';
          }
        }

        public function beginTransaction() {
          $this->conn->begin_transaction();
        }

        public function commit() {
          $this->conn->commit();
        }

        public function rollback() {
          $this->conn->rollback();
        }

        public function conn() {
          return $this->conn;
        }

        public function close() {
          $this->conn->close();
        }

        public function escape($string) {
          return $this->conn->real_escape_string($string);
        }
        
        public function db($db_name=null) {
          if ($db_name) {
            $this->db = $db_name;
            $this->conn->select_db($db_name);
          }
          return $this->db;
        }

        public function query($query) {
          // data to return
          $data = [];

          // query time
          $start = microtime(true);

          // run query
          $result = $this->conn->query($query);

          // query time
          $end = microtime(true);
          $query_time = $end - $start;

          // query time rounded
          $data["query_time"] = round($query_time, 2);


          // check for errors
          if (!$result) {
            return ["success"=>false, "data"=>$data, "error"=>$this->conn->error];
          }

          // query type
          switch (explode(" ", $query)[0]) {
            case 'SELECT':
            case 'WITH':
              // $rows = [];
              // while($row = $result->fetch_assoc()) {
              //     $rows[] = $row;
              // }
              $rows = $result->fetch_all(MYSQLI_ASSOC);
              $data["num_rows"] = $result->num_rows;
              $data["rows"] = $rows;
              break;
            case 'INSERT':
              // insert id
              $data["insert_id"] = $this->conn->insert_id;
              break;
            case 'UPDATE':
            case 'DELETE':
              // affected rows
              $data["affected_rows"] = $this->conn->affected_rows;
              break;
            default:
              $data = null;
              break;
            }

            //$this->conn->close();
            return ["success"=>true, "data"=>$data];
        }
    }
}