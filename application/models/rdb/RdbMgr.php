<?php

namespace models\rdb;

use Slim\PDO\Database as PDO;
use libraries\log\LogMessage;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RdbMgr
{
    const PROCEDURE_RESULT_CODE_SUCCESS = 0;
//    const PROCEDURE_ERROR_NO = 100;

    private $username;
    private $password;
    private $connection;
    private $connectionString;
    private $dbname;

    public function __construct($config = null)
    {
        if (empty($config)) {
            $config = include APP_DIR . 'config/' . APP_ENV . '.php';
            $rdbInfo = $config['settings']['rdb'];
        } else {
            $rdbInfo = $config;
        }

        /*
         * set center db
         */
        $this->dbname = $rdbInfo['dbname'];
        $this->connectionString = sprintf("%s:host=%s;port=%s;dbname=%s;charset=%s",
            $rdbInfo['driver'],
            $rdbInfo['host'],
            $rdbInfo['port'],
            $rdbInfo['dbname'],
            $rdbInfo['charset']
        );

        $this->username = $rdbInfo['username'];
        $this->password = $rdbInfo['password'];
    }

    public function getDbname()
    {
        return $this->dbname;
    }

    public function makeConnection($connectionString = null)
    {
        if (!empty($connectionString)) {
            $dsn = $connectionString;
        } else {
            $dsn = $this->connectionString;
        }

        if (!empty($this->connection)) {
            $this->closeConnection();
        }

        /*
         * get connection
         */
        $this->connection = new PDO($dsn, $this->username, $this->password);
    }

    public function closeConnection()
    {
        if (!empty($this->connection)) {
            $this->connection = null;
        }
    }

    private function checkConnection()
    {
        if (empty($this->connection)) {
            throw new \Exception('failed to access becuase already disconnect');
        }
    }

    public function startTransaction()
    {
        /*
         * check connection
         */
        $this->checkConnection();

        $resData = $this->connection->beginTransaction();
        if (false === $resData) {
            throw new \Exception('failed to begin transaction');
        }

        LogMessage::debug('startTransaction::' . $resData);
    }

    public function commitTransaction()
    {
        /*
         * check connection
         */
        $this->checkConnection();

        $resData = $this->connection->commit();
        if (false === $resData) {
            throw new \Exception('failed to commit transaction');
        }

        LogMessage::debug('commitTransaction::' . $resData);
    }

    public function rollbackTransaction()
    {
        /*
         * check connection
         */
        $this->checkConnection();

        $resData = $this->connection->rollback();
        if (false === $resData) {
            throw new \Exception('failed to rollback transaction');
        }

        LogMessage::debug('rollbackTransaction::' . $resData);
    }

    public function executeQuery($query)
    {
        /*
         * check connection
         */
        $this->checkConnection();

        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        /*
         * fetch rows
         */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows[0];
    }

    public function executeProcedure($procedureString, $params, $isReturnRows = true, $multiRowset = false)
    {
        //LogMessage::debug('execute ' . $procedureString . ' procedure');

        /*
         * check connection
         */
        $this->checkConnection();

        $stmt = $this->connection->prepare('CALL ' . $procedureString);
        foreach ($params as $param) {
            $stmt->bindParam($param[0], $param[1], $param[2]);
        }

        $errorCode = self::PROCEDURE_RESULT_CODE_SUCCESS;
        $stmt->bindParam(count($params) + 1, $errorCode, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);

        /*
         * execute
         */
        $stmt->execute();

        /*
         * if failed;;
         */
        if (self::PROCEDURE_RESULT_CODE_SUCCESS !== $errorCode) {
            return [
                false,
                $errorCode
            ];
        }

        if (false === $isReturnRows) {
            $resData = [
                true,
                null
            ];
        } else {
            /*
             * fetch rows
             */

            if (!empty($multiRowset)) {

                do {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $resData[] = [
                        true,
                        $rows
                    ];
                } while ($stmt->nextRowset());

            } else {

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $resData = [
                    true,
                    $rows
                ];

            }
        }

        return $resData;
    }

}


