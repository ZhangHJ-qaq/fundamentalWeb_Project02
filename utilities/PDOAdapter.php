<?php


class PDOAdapter
{
    private $pdo;

    function __construct($header, $username, $password, $dbname)
    {
        try {
            $this->pdo = new PDO($header, $username, $password);
            $this->pdo->exec("use $dbname");
        } catch (PDOException $PDOException) {
            exit();
        }

    }

    function exec($sql, $bindValueArray = null)
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($bindValueArray);
    }

    function selectRows($sql, $bindValueArray = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindValueArray);
        $returnArray = array();
        while ($row = $statement->fetch()) {
            $returnArray[] = $row;
        }
        return $returnArray;
    }

    function isRowCountZero($sql, $bindValueArray = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindValueArray);
        return $statement->rowCount() === 0;
    }

    function getRowCount($sql, $bindValueArray = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindValueArray);
        return $statement->rowCount();
    }


    function insertARow($sql, $bindValue)
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($bindValue);
    }

    function deleteRows($sql, $bindValue)
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($bindValue);
    }

    function close()
    {
        $this->pdo = null;
    }

    function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    function commit()
    {
        $this->pdo->commit();
    }

    function rollBack()
    {
        $this->pdo->rollBack();
    }


}