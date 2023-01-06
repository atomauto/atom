<?php

$connectionString = '';
$dbUser = '';
$dbUserPassword = '';
$user = '';
$password = '';
//Отключаем вывод ошибок
//error_reporting(-1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Samara');
header('Content-Type: text/html; charset=utf-8');

class Api
{
    protected static $_instance = null;
    public $pdo;

    public static function getInstance()
    {
        if (static::$_instance != null) {
            return static::$_instance;
        }

        return new static;
    }

    public function __construct($connectionString, $dbUser, $dbUserPassword)
    {
        $this->pdo = new PDO($connectionString, $dbUser, $dbUserPassword, [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    }

    //Очистка артикула от мусора по нашим правилам
    public static function cleanArticle($article)
    {
        return preg_replace("/[^a-zA-ZА-Яа-я0-9]/", "", $article);
    }
}

class Db extends Api
{

    const table = 'undefined';

    public function selectDbAll($condition = "*", $joinCondition = "")
    {
        $sql = "SELECT " . $condition . " FROM " . static::table . " " . $joinCondition;
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute();
        return $sqlQuery->fetchAll();
    }

    public function searchDbById($id)
    {
        $sql = "SELECT * FROM " . static::table . " WHERE id = :id";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute([":id" => $id]);
        return $sqlQuery->fetchColumn();
    }

    public function getDbById($id)
    {
        $sql = "SELECT * FROM " . static::table . " WHERE id = :id";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute([":id" => $id]);
        return $sqlQuery->fetch();
    }

    public function selectDbWhere($params, $idOnly = true, $fetchRow = false, $all = false)
    {
        if ($idOnly)
            $sql = "SELECT id FROM " . static::table . " WHERE ";
        else
            $sql = "SELECT * FROM " . static::table . " WHERE ";
        foreach ($params as $key => $value) {
            $sql .= str_replace(':', '', $key) . " = $key AND ";
        }
        $sql .= "1 = 1";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute($params);
        if ($fetchRow)
            return $sqlQuery->fetch();
        if ($all)
            return $sqlQuery->fetchAll();

        return $sqlQuery->fetchColumn();
    }

    public function getDbWhere($params, $idOnly = true, $all = false)
    {
        if ($idOnly)
            $sql = "SELECT id FROM " . static::table . " WHERE ";
        else
            $sql = "SELECT * FROM " . static::table . " WHERE ";
        foreach ($params as $key => $value) {
            $sql .= str_replace(':', '', $key) . " = $key AND ";
        }
        $sql .= "1 = 1";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute($params);
        if ($all)
            return $sqlQuery->fetchAll();
        else
            return $sqlQuery->fetch();
    }

    public function insertDb($params, $returnId = true)
    {
        $sql = "INSERT INTO " . static::table . " (" . str_replace(':', '', join(',', array_keys($params))) . ") VALUES (" . join(',', array_keys($params)) . ")";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute($params);
        if ($returnId)
            return $this->pdo->lastInsertId();
    }

    public function checkDbWhere($params, $idOnly = true)
    {
        $result = static::selectDbWhere($params, $idOnly);
        if (empty($result))
            if ($idOnly)
                return static::insertDb($params);
            else
                return static::insertDb($params, false);
        else
            return $result;
    }

    public function searchDbByUuid($uuid)
    {
        $sql = "SELECT * FROM " . static::table . " WHERE uuid = :uuid";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute([":uuid" => $uuid]);
        return $sqlQuery->fetchColumn();
    }

    public function getDbByUuid($uuid)
    {
        $sql = "SELECT * FROM " . static::table . " WHERE uuid = :uuid";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute([":uuid" => $uuid]);
        return $sqlQuery->fetch();
    }

    public function checkDb($entity, $params)
    {
        $dbEntity = static::searchDbByUuid($entity['id']);
        if (empty($dbEntity)) {
            $dbEntity = static::insertDb($params);
            return $this->pdo->lastInsertId();
        }
        return $dbEntity;
    }

    public function deleteDb($params, $all = false)
    {
        if (!$all) {
            $sql = "DELETE FROM " . static::table . " WHERE ";
            foreach ($params as $key => $value) {
                $sql .= str_replace(':', '', $key) . " = $key AND ";
            }
            $sql .= "1 = 1";
        } else
            $sql = "DELETE FROM " . static::table;
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute($params);
    }
}

class DbSupplier extends Db
{
}

class DbCounterparty extends Db
{

    const table = 'counterparty';
}

class DbArticle extends Db
{

    const table = 'article';
}

class DbBrand extends Db
{

    const table = 'brand';
}

class DbName extends Db
{

    const table = 'name';
}

class DbProduct extends Db
{

    const table = 'product';
}

class DbProductNames extends Db
{

    const table = 'product_names';
}

class DbBarcode extends Db
{

    const table = 'barcode';
}

class DbSalePrices extends Db
{

    const table = 'sale_prices';
}

class DbProductSupplier extends Db
{

    const table = 'product_supplier';
}

class DbIncome extends Db
{

    const table = 'income';
}

class DbIncomePositions extends Db
{

    const table = 'income_positions';
}

class DbStock extends Db
{

    const table = 'stock';
}

class DbSupplierStock extends Db
{

    const table = 'supplier_stock';
}

class DbCustomerOrder extends Db
{

    const table = 'customer_order';
}

class DbCustomerOrderPositions extends Db
{

    const table = 'customer_order_positions';
}

class Supplier extends Api
{

    public function searchArticle($article)
    {
        $sql = "SELECT id FROM article WHERE article = :article";
        $sqlQuery = $this->pdo->prepare($sql);
        $sqlQuery->execute([":article" => $article]);
        $articleId = $sqlQuery->fetchColumn();
        if (empty($articleId)) {
            $sql = "INSERT INTO article (article) VALUES (:article)";
            $sqlQuery = $this->pdo->prepare($sql);
            $sqlQuery->execute([':article' => $article]);
            $articleId = $this->pdo->lastInsertId();
        }
        return $articleId;
    }

    public function searchProduct($articleId, $brandId, $nameId)
    {
        $searchProductQuery = $this->pdo->prepare("SELECT id FROM product WHERE articleId = :articleId AND brandId = :brandId");
        $insertProductQuery = $this->pdo->prepare("INSERT INTO product (articleId, brandId, nameId) VALUES (:articleId,:brandId,:nameId)");
        $searchProductQuery->execute([':articleId' => $articleId, ':brandId' => $brandId]);
        $productId = $searchProductQuery->fetchColumn();
        if (empty($productId)) {
            $insertProductQuery->execute([':articleId' => $articleId, ':brandId' => $brandId, ':nameId' => $nameId]);
            $productId = $this->pdo->lastInsertId();
        }
        return $productId;
    }

    private function searchProvider($supplierId, $providerId)
    {
        $searchProviderQuery = $this->pdo->prepare("SELECT id FROM provider WHERE supplierId = :supplierId AND supplierCode = :supplierCode");
        $insertProviderQuery = $this->pdo->prepare("INSERT INTO provider (supplierId, supplierCode) VALUES (:supplierId, :supplierCode)");
        $searchProviderQuery->execute([':supplierId' => $supplierId, ':supplierCode' => $providerId]);
        $providerId = $searchProviderQuery->fetchColumn();
        if (empty($providerId)) {
            $insertProviderQuery->execute([':supplierId' => $supplierId, ':supplierCode' => $providerId]);
            $providerId = $this->pdo->lastInsertId();
        }
    }
}

class Rossko extends Supplier
{

    public $supplierId = 3;
}
