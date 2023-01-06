<?php

$start = microtime(true);
require_once 'config.php';
ini_set("memory_limit", "256 M");
set_time_limit(0);
$email = "";
$password = "";
$mbox = imap_open("{imap.yandex.ru:993/imap/ssl}INBOX|Prices", $email, $password);

$emailNumbers = imap_search($mbox, 'TEXT "Rossko" UNSEEN', SE_FREE, "utf-8");
if ($emailNumbers) {
    $emailNumber = $emailNumbers[0];
    //$emailNumber = $emailNumbers[array_key_last($emailNumbers)];
    $overview = imap_fetch_overview($mbox, $emailNumber, 0);
    $structure = imap_fetchstructure($mbox, $emailNumber);
    $attachments = [];
    if (isset($structure->parts) && count($structure->parts)) {
        for ($i = 0; $i < count($structure->parts); $i++) {
            $attachments[$i] = array(
                'is_attachment' => false,
                'filename' => '',
                'name' => '',
                'attachment' => ''
            );
            if ($structure->parts[$i]->ifdparameters) {
                foreach ($structure->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['filename'] = $object->value;
                    }
                }
            }
            if ($structure->parts[$i]->ifparameters) {
                foreach ($structure->parts[$i]->parameters as $object) {
                    if (strtolower($object->attribute) == 'name') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['name'] = $object->value;
                    }
                }
            }
            if ($attachments[$i]['is_attachment']) {
                $attachments[$i]['attachment'] = imap_fetchbody($mbox, $emailNumber, $i + 1);
                // 3 = BASE64 encoding
                if ($structure->parts[$i]->encoding == 3) {
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                }
                // 4 = QUOTED-PRINTABLE encoding
                elseif ($structure->parts[$i]->encoding == 4) {
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
        }
    }

    foreach ($attachments as $attachment) {
        if ($attachment['is_attachment'] == 1) {
            $filename = $attachment['name'];
            if (empty($filename))
                $filename = $attachment['filename'];
            $filename = mb_decode_mimeheader($filename);
            $folder = "/var/www/attachment";
            $fp = fopen($folder . "/" . $filename . ".zip", "w+");
            fwrite($fp, $attachment['attachment']);
            fclose($fp);
        }
    }
    imap_close($mbox);

    echo 'Закончили работу с почтой на времени: <strong>' . round(microtime(true) - $start, 4) . '</strong> сек.<br>';

    $zip = zip_open('/var/www/attachment/' . $filename . ".zip");

    if ($zip) {
        while ($zip_entry = zip_read($zip)) {
            echo "Name:               " . zip_entry_name($zip_entry) . "\n";
            $fileName = zip_entry_name($zip_entry);
            echo "Actual Filesize:    " . zip_entry_filesize($zip_entry) . "\n";
            echo "Compressed Size:    " . zip_entry_compressedsize($zip_entry) . "\n";
            echo "Compression Method: " . zip_entry_compressionmethod($zip_entry) . "\n";

            if (zip_entry_open($zip, $zip_entry, "r")) {
                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                file_put_contents('/var/www/attachment/' . zip_entry_name($zip_entry), $buf);
                //            $spreadsheet = IOFactory::load('attachment/' . zip_entry_name($zip_entry));
                //            $sheetData = $reader->getActiveSheet()->toArray(null, true, true, true);
                zip_entry_close($zip_entry);
            }
            zip_close($zip);


            //        while ($sheetData[++$i]['A'] != null) {
            //            $position->order = $sheetData[$i]['B'];
            //            $position->article = Api::cleanArticle($sheetData[$i]['C']);
            //            $position->brand = $MoySklad->searchBrand($sheetData[$i]['D']);
            //            $position->name = $sheetData[$i]['E'];
            //            $position->uom = $sheetData[$i]['F'];
            //            $position->okei = $sheetData[$i]['G'];
            //            $position->quantity = $sheetData[$i]['H'];
            //            $position->fullPrice = str_replace(',', '', $sheetData[$i]['I']);
            //            $position->countryCode = $sheetData[$i]['P'];
            //            $position->countryName = $sheetData[$i]['Q'];
            //            $position->gtd = $sheetData[$i]['R'];
            //            $position->barcode = $sheetData[$i]['S'];
            //        }
        }
    }

    echo 'Закончили работу с извлечением архива на времени: <strong>' . round(microtime(true) - $start, 4) . '</strong> сек.<br>';

    $Rossko = new Rossko();
    $DbArticle = new DbArticle();
    $DbBrand = new DbBrand();
    $DbName = new DbName();
    $DbProduct = new DbProduct();
    $DbProductNames = new DbProductNames();
    $DbProductSupplier = new DbProductSupplier();
    $DbStock = new DbStock();
    $DbSupplierStock = new DbSupplierStock();
    $handle = fopen('attachment/' . $fileName, "r");
    echo "<pre>";
    //Печатаем первую строку с названием стобцов
    $data = fgetcsv($handle, 0, ';');
    echo "</pre>";
    $i = 0;
    echo "<pre>";
    $DbSupplierStock->deleteDb([], true);
    while (($data = fgetcsv($handle, 0, ';')) !== false) {


        $position->supplierProductCode = $data[0];
        $position->brand = ucwords(strtolower($data[1]));
        $position->article = $data[2];
        $position->name = str_replace('Снят с производства ', '', $data[3]);
        $position->WeightOrVolume = $data[4];
        $position->minQuantity = $data[5];
        $position->price = str_replace(',', '.', $data[6]);
        $position->basePrice = str_replace(',', '.', $data[7]);
        if (strpos($data[8], '-'))
            $position->quantity = str_replace('>', '', strstr($data[8], '-', true));
        else
            $position->quantity = str_replace('>', '', $data[8]);
        $position->expectedDays = $data[9];
        //TODO кроссы
        $position->description = $data[12];

        $articleId = $DbArticle->checkDbWhere([":article" => $position->article]);
        $brandId = $DbBrand->checkDbWhere([":name" => $position->brand]);
        $nameId = $DbName->checkDbWhere([":name" => $position->name], true);
        $productId = $DbProduct->selectDbWhere([":articleId" => $articleId, ":brandId" => $brandId]);
        if (!$productId)
            $productId = $DbProduct->insertDb([":articleId" => $articleId, ":brandId" => $brandId, ":mainNameId" => $nameId, ":incomePrice" => $position->price, ":description" => $position->description]);
        $stockPosition = $DbSupplierStock->selectDbWhere([":productId" => $productId, ":supplierId" => $Rossko->supplierId, ":days" => $position->expectedDays], false, true);
        if (empty($stockPosition))
            $DbSupplierStock->insertDb([":productId" => $productId, ":supplierId" => $Rossko->supplierId, ":quantity" => $position->quantity, ":price" => $position->price, ":days" => $position->expectedDays, ":priceTimestamp" => $overview[0]->date], false);
        else {
            if ($stockPosition['quantity'] != $position->quantity) {
                print_r($position);
                print_r($stockPosition);
                echo '<b>Quantity changed!</b> <br>';
            }
            if ($stockPosition['price'] != $position->price) {
                print_r($position);
                print_r($stockPosition);
                echo '<b>Price changed!</b> <br>';
            }
        }
        echo "ArticleId is $articleId, BrandId is $brandId, NameId is $nameId, ProductId is $productId <br><br>";
        $DbProductNames->checkDbWhere([":productId" => $productId, ":nameId" => $nameId], false);
        $DbProductSupplier->checkDbWhere([":productId" => $productId, ":supplierId" => $Rossko->supplierId, ":supplierProductCode" => $position->supplierProductCode], false);
        /*

          //TODO история наличия и цен!!!
          $DbStock->checkDbWhere([":productId" => $productId, ":supplierId" => $Rossko->supplierId, ":price" => $position->price, ":quantity" => (integer) $position->quantity, ":expectedDays" => (integer) $position->expectedDays]);
         */
        //    if ($i++ == 1000)
        //        break;
    }
    echo "</pre>";
    echo 'Закончили обновление базы на времени: <strong>' . round(microtime(true) - $start, 4) . '</strong> сек.<br>';
}
$folder = "attachment/archive/";
if (!is_dir($folder)) {
    mkdir($folder);
}
rename('attachment/' . $entry, $folder . "archive_" . date("d.m.Y H:i:s") . ".zip");





echo 'Время выполнения скрипта: <strong>' . round(microtime(true) - $start, 4) . '</strong> сек.';
