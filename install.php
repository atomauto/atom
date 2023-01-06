<?php
//Creating tables if they not exists and insert initial data
require_once 'config.php';
error_reporting(E_ALL);
$Api = new Api();
$Api->pdo->exec(
        "CREATE TABLE IF NOT EXISTS supplier (id serial primary key, name varchar(255) UNIQUE, description text, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS brand (id serial primary key, uuid UUID DEFAULT uuid_generate_v4(), name varchar (255) UNIQUE NOT NULL, description text, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE brand_supplier (id serial primary key, brandId integer REFERENCES brand, supplierCode integer, supplierId integer REFERENCES supplier, name varchar(255), country varchar(255), description text, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS name (id serial primary key, name varchar(2000), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS article (id serial primary key, article varchar(255) UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE article_supplier (id serial primary key, articleId integer REFERENCES article, supplierId integer REFERENCES supplier, supplierBrandId integer REFERENCES brand_supplier, supplierCode varchar(255), nameId integer REFERENCES name, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE article_cross (id serial primary key, articleOriginalId integer REFERENCES article, articleCrossId integer REFERENCES article, supplierId integer REFERENCES supplier, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE autopiter_sales_rating (id serial primary key, articleSupplierId integer REFERENCES article_supplier, rating smallint, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE store_supplier (id serial primary key, supplierId integer REFERENCES supplier, supplierCode varchar(255), supplierExtraCode varchar(255), name varchar(255), created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE provider (id serial primary key, supplierId integer REFERENCES supplier, supplierCode varchar(255), stat smallint, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS rate (id serial primary key, name varchar(255) UNIQUE, fullName varchar(255) UNIQUE, code integer UNIQUE, isoCode varchar(255) UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS uom (id serial primary key, name varchar(255) UNIQUE, fullName varchar(255) UNIQUE, code integer UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS country (id serial primary key, name varchar(255) UNIQUE, fullName varchar(255) UNIQUE, isoCode integer UNIQUE, iso2 char(2) UNIQUE, iso3 char(3) UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS product (id serial primary key, uuid UUID DEFAULT uuid_generate_v4(), articleId integer REFERENCES article NOT NULL, brandId integer REFERENCES brand NOT NULL, uomId integer REFERENCES uom DEFAULT 1, mainNameId integer REFERENCES name, code varchar(255) UNIQUE DEFAULT NULL, incomePrice numeric(20,4) DEFAULT NULL, description text DEFAULT NULL, weight real DEFAULT NULL, volume real DEFAULT NULL, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS product_names (productId integer references product, nameId integer REFERENCES name NOT NULL);"
        . "CREATE TABLE IF NOT EXISTS product_supplier (productId integer references product, supplierId integer REFERENCES supplier, supplierProductCode varchar(255));"
        . "CREATE TABLE IF NOT EXISTS country (id serial, name varchar(255) UNIQUE, code integer UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
//. "CREATE TABLE search (id serial primary key, productId integer REFERENCES product, supplierId integer REFERENCES supplier, providerId integer REFERENCES provider, nameId integer REFERENCES name, quantity integer, multiplicity smallint, price money, expectedDays smallint, ownStorage boolean, stat smallint, created_at timestamptz NOT NULL DEFAULT NOW(), checked_at timestamptz);"
        . "CREATE TABLE IF NOT EXISTS store (id serial primary key, name varchar(255) UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS cell (id serial primary key, name varchar(255), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS cell_products (productId integer REFERENCES product, cellId integer REFERENCES cell, quantity integer NOT NULL, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS barcode (productId integer REFERENCES product, barcode varchar (255), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS sale_price_type (id serial primary key, name varchar(255) UNIQUE, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS sale_prices (id serial primary key, productId integer REFERENCES product, salePriceTypeId smallint REFERENCES sale_price_type, price money, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS counterparty (id serial primary key, uuid UUID DEFAULT uuid_generate_v4(), name varchar(255) UNIQUE, phone varchar(255) DEFAULT NULL, email varchar(255) DEFAULT NULL, salePriceTypeId smallint REFERENCES sale_price_type DEFAULT NULL, description text, fullName varchar(255), address varchar(255), legalTitle varchar(255), legalAddress varchar(255),inn varchar(12) UNIQUE, kpp varchar(9), ogrn varchar(13), ogrnip varchar(15), okpo varchar(10), organization boolean DEFAULT false, created_at timestamptz NOT NULL DEFAULT NOW(), updated_at timestamptz);"
        . "CREATE TABLE IF NOT EXISTS account (id serial primary key, counterpartyId integer REFERENCES counterparty, account char(20) UNIQUE, bank varchar(255), correspondentAccount char(20), bic char(9), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS income (id serial primary key, organizationId integer REFERENCES counterparty, counterpartyId integer REFERENCES counterparty, storeId integer REFERENCES store DEFAULT 1, incomingNumber varchar(255), сontract varchar(255), applicable boolean DEFAULT true, vatEnabled boolean, vatIncluded boolean, rateId smallint REFERENCES rate DEFAULT 1, description text, incomingDate timestamptz, acceptanceDate timestamptz, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS income_positions (id serial primary key, incomeId integer REFERENCES income, productId integer REFERENCES product, quantity numeric(20,4) NOT NULL, price money, vat smallint DEFAULT NULL, discount smallint DEFAULT NULL, countryId integer REFERENCES country, gtd varchar(255), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS supplier_stock (productId integer REFERENCES product, supplierId integer REFERENCES supplier, quantity numeric(20,4) NOT NULL, price numeric(20,4), days smallint NOT NULL, priceTimestamp timestamptz, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS customer_order (id serial primary key, uuid UUID DEFAULT uuid_generate_v4(), organizationId integer REFERENCES counterparty DEFAULT 1, counterpartyId integer REFERENCES counterparty, storeId integer REFERENCES store DEFAULT 1, moment timestamptz DEFAULT NULL, сontract varchar(255), applicable boolean, vatEnabled boolean, vatIncluded boolean, rateId smallint REFERENCES rate DEFAULT 1, description text, created_at timestamptz NOT NULL DEFAULT NOW(), updated_at timestamptz DEFAULT NULL);"
        . "CREATE TABLE IF NOT EXISTS customer_order_positions (id serial primary key, uuid UUID DEFAULT uuid_generate_v4(), customerOrderId integer REFERENCES customer_order, productId integer REFERENCES product, quantity numeric(20,4) NOT NULL, price money, vat smallint DEFAULT NULL, discount smallint DEFAULT NULL, shipped smallint DEFAULT NULL, reserved smallint DEFAULT NULL, created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS retail_store (id serial primary key, name varchar(255) UNIQUE);"
        . "CREATE TABLE IF NOT EXISTS cashier (id serial primary key, retailStoreId integer REFERENCES retail_store, counterpartyId integer REFERENCES counterparty);"
        . "CREATE TABLE IF NOT EXISTS sale (id serial primary key, organizationId integer REFERENCES counterparty, counterpartyId integer REFERENCES counterparty, storeId integer REFERENCES store DEFAULT 1, rateId smallint REFERENCES rate DEFAULT 1, description text, applicable boolean DEFAULT true, virtual boolean DEFAULT false, saleDate timestamptz NOT NULL DEFAULT NOW(), created_at timestamptz NOT NULL DEFAULT NOW());"
        . "CREATE TABLE IF NOT EXISTS sale_positions (id serial primary key, saleId integer REFERENCES income, incomePositionId integer REFERENCES income, quantity numeric(20,4) NOT NULL, price money, vat smallint DEFAULT NULL, discount smallint DEFAULT NULL, created_at timestamptz NOT NULL DEFAULT NOW());"
);

$Api->pdo->exec(
        "CREATE OR REPLACE view customer_order_positions_view AS select cop.id, cop.customerorderid, a2.article, b2.name as brand, p.code, n2.name as name, cop.quantity, u2.name as uom, cop.price, cop.vat, cop.discount, p.weight, p.volume, cop.shipped, cop.reserved from  customer_order_positions cop, product p, name n2, article a2, brand b2, uom u2 where cop.productid = p.id  and n2.id = p.mainnameid and a2.id = p.articleid and b2.id = p.brandid and u2.id  = p.uomid;"
        . "CREATE OR REPLACE VIEW stock AS select p.id, a.article, b.name as brand, p.code, n.name, ip.quantity, u.name as uom, ip.price as incomePrice, sp.price as retailPrice, ip.vat, p.weight, p.volume, ip.id as incomePositionId from income i left join income_positions ip on ip.incomeid  = i.id and i.applicable is true left join product p on p.id = ip.productid left join brand b on b.id = p.brandid left join article a on a.id = p.articleid left join uom u on u.id  = p.uomid left join name n on n.id  = p.mainnameid left join sale_prices sp on sp.productid = p.id and sp.salepricetypeid = 1;"
        . "CREATE OR REPLACE VIEW sale_positions_view AS select sp.id, sp.saleid, sp.incomepositionid, a.article, b.name as brand, p.code, n.name, sp.quantity,
u.name as uom, sp.price, sp.vat, sp.discount, p.weight, p.volume
from sale_positions sp
left join income_positions ip on ip.id = sp.incomepositionid
left join product p on p.id = ip.productid left join brand b on b.id = p.brandid
left join article a on a.id = p.articleid
left join uom u on u.id  = p.uomid left join name n on n.id  = p.mainnameid;");
//Заполняем начальными данными
$Api->pdo->exec("INSERT INTO brand (id, name) VALUES (-1,'Не установлено');");
$Api->pdo->exec("INSERT INTO counterparty (id,name,organization) VALUES (-1,'Розничный покупатель',false),(0,'Наше юридическое лицо',true),(1,'Наш директор физ.лицо или ИП (для кассы, подотчета и т.д.)',false);");
$Api->pdo->exec("INSERT INTO sale_price_type (name) VALUES ('Цена розница'),('Цена заказ'),('Цена Опт'),('Цена Автопитер');");
$Api->pdo->exec("INSERT INTO supplier (name) VALUES ('Наше имя как поставщика самому себе по умолчанию'),('Армтек'),('Росско'),('Автопитер');");
$Api->pdo->exec("INSERT INTO rate (name, fullName, code ,isoCode) VALUES ('руб','Российский рубль',643,'RUB')");
$Api->pdo->exec("INSERT INTO uom (name, fullName, code) VALUES ('шт','Штука',796)");
$Api->pdo->exec("INSERT INTO country (name, fullName, isoCode, iso2, iso3) VALUES ('Россия','Российская федерация',643,'RU','RUS')");
$Api->pdo->exec("INSERT INTO store (name) VALUES ('Адрес нашего склада');");
$Api->pdo->exec("INSERT INTO retail_store (name) VALUES ('Адрес нашего розничного магазина');");
//$Api->pdo->exec("INSERT INTO cashier (retailStoreId, counterpartyId) VALUES (1,2);");
//TODO накладные расходы