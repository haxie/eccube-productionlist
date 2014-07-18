# ECCube Production List

Custom ECCube class to prepare order data for a production list. Uses mPDF as default.

### Usage

``` php
<?php
$objProd = new SC_Helper_ProductionList_Ex();

$html = "";
$shippingDate = "2014-07-14";
$categoryMap = array(
        3 => 'Category Name',
        14 => 'Category Name 2'
        15 => 'Category Name 3'
    );

$objProd->generateProductionList($categoryMap, $shippingDate, &$html);
$objProd->downloadMPDF($html, 'Test PDF');
?>
```

### Sample Output

| 商品名            | 台数|
| ------------------|:----|
| Category Name     | 20  |
| Category Name 2   | 18  |
| Category Name 3   | 3   |
|                   |     |
| *合計台数          | 41  |
