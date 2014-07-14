<?php
namespace www\week2\day1\dinhtuan;

class ImportBySql
{
    private $fileName = "";
    private $database;
    private $chunkSize = 0;
    private $cacheOptVal;
    private $cacheCat;
    
    const TB_PRODUCT = 'product';
    const TB_CATEGORY = 'category';
    const TB_OPTION = 'rubikin_db.option';
    const TB_OPTION_VALUE = 'option_value';
    const TB_PRODUCT_OPTION = 'product_option';
    const TB_PRODUCT_CATEGORY = 'product_category';
    const MAXCHUNKSIZE = 2000;

    // Array for mapping
    private $colIndex = array(
        'prod_id' => 0,
        'prod_name' => 1,
        'prod_slug' => 2,
        'prod_short' =>3,
        'prod_descr' => 4,
        'prod_available' => 5,
        'prod_create' => 6,
        'prod_update' => 7,
        'prod_delete' => 8,
        'prod_method' => 9,
        'category' => 12
    );

    // 6 SQLs for 6 tables
    
    // SQL of PRODUCT table
    private $sqlProduct = "";
    private $defaultSqlProd = 0;

    // SQL of OPTION table
    private $sqlOption = "";

    // SQL of CATEGORY table
    private $sqlCategory = "";
    private $defaultSqlCat = 0;

    // SQL of OPTION_VALUE table
    private $sqlOptionValue = "";
    private $defaultSqlOptVal = 0;

    // SQL of PRODUCT_OPTION table
    private $sqlProductOption = "";
    private $defaultSqlProdOpt = 0;

    // SQL of PRODUCT_CATEGORY table
    private $sqlProductCategory = "";
    private $defaultSqlProdCat = 0;

    // SQL to add unique
    private $sqlAddUnique = "";

    /**
     * To free the result from the database after a multi_query funciton
     * @return void
     */
    private function freeit($database)
    {
        // Ignore the E_STRICT error message
        error_reporting(E_ALL & ~E_STRICT);

        while ($database->next_result()) {
            $database->store_result();
        }

        // Reset to default
        error_reporting(E_ALL);
    }

    /**
     * Perform all the sql querys on all the tables
     * @return void
     */
    private function insertAll($database, &$product, &$optionValue, &$category, &$productOption, &$productCategory)
    {

        if (strlen($product) > $this->defaultSqlProd) {
            $trueproduct = rtrim($product,',') . " ON DUPLICATE KEY UPDATE id = id";
            $database->query($trueproduct) or die("Cannot insert into table PRODUCT. Error: " . $database->error);
            $product = substr($product,0,$this->defaultSqlProd);
        }
        if (strlen($optionValue) > $this->defaultSqlOptVal) {
            $trueoptionvalue = rtrim($optionValue,',') . " ON DUPLICATE KEY UPDATE id = id";
            $database->query($trueoptionvalue) or die("Cannot insert into table OPTION_VALUE. Error: " . $database->error);
            $optionValue = substr($optionValue, 0, $this->defaultSqlOptVal);
        }
        if (strlen($category) > $this->defaultSqlCat) {
            $truecategory = rtrim($category,',') . " ON DUPLICATE KEY UPDATE id = id";
            $database->query($truecategory) or die("Cannot insert into table CATEGORY. Error: " . $database->error);
            $category = substr($category, 0, $this->defaultSqlCat);
        }
        if (strlen($productOption) > $this->defaultSqlOptVal) {
            $trueproductOption = rtrim($productOption,',') . " ON DUPLICATE KEY UPDATE product_id = product_id";
            $database->query($trueproductOption) or die("Cannot insert into table PRODUCT_OPTION_VALUE. Error: " . $database->error);
            $productOption = substr($productOption, 0, $this->defaultSqlProdOpt);
        }
        if (strlen($productCategory) > $this->defaultSqlProdCat) {
            $trueproductCategory = rtrim($productCategory,',') . " ON DUPLICATE KEY UPDATE product_id = product_id";
            $database->query($trueproductCategory) or die("Cannot insert into table PRODUCT_CATEGORY. Error: " . $database->error);
            $productCategory = substr($productCategory, 0, $this->defaultSqlProdCat);
        }
    }
    
    /**
     * Use this to initialize the SQL commands
     * @return void
      */
    private function initSql()
    {
        // SQL of PRODUCT table has 283 characters at default
        $this->sqlProduct = "INSERT INTO " . self::TB_PRODUCT . " (
            id,
            name,
            slug,
            short_description,
            description,
            available_on,
            created_at,
            updated_at,
            deleted_at,
            variant_selection_method) VALUES ";

        // SQL of OPTION table
        $this->sqlOption = "INSERT INTO " . self::TB_OPTION . " (name) VALUES ";
        $this->defaultSqlProd = 283;

        // SQL of CATEGORY table has 35 characters at default
        $this->sqlCategory = "INSERT INTO " . self::TB_CATEGORY . " (name) VALUES ";
        $this->defaultSqlCat = 35;

        // SQL of OPTION_VALUE table has 51 characters at default
        $this->sqlOptionValue = "INSERT INTO " . self::TB_OPTION_VALUE . " (option_id, value) VALUES ";
        $this->defaultSqlOptVal = 51;

        // SQL of PRODUCT_OPTION table has 59 characters at default
        $this->sqlProductOption = "INSERT INTO " . self::TB_PRODUCT_OPTION . " (product_id, option_value_id)  VALUES ";
        $this->defaultSqlProdOpt = 59;

        // SQL of PRODUCT_CATEGORY table has 62 characters at default
        $this->sqlProductCategory = "INSERT INTO " . self::TB_PRODUCT_CATEGORY . " (product_id, category_id) VALUES ";
        $this->defaultSqlProdCat = 62;

        // SQL to add unique
        $this->sqlAddUnique = "ALTER TABLE " . self::TB_OPTION_VALUE . " ADD UNIQUE unique_option_value(option_id,value);
                               ALTER TABLE " . self::TB_CATEGORY . " ADD UNIQUE (name)";
    }
    
    /**
     * constructor with name for the file and the database connection for query
     * @param string $fname name of the file to import
     * @param mysqli $db database to import to
     */
    public function __construct($fname, $db)
    {
        $this->fileName = $fname;
        $this->database = $db;
        $this->cacheOptVal = new CacheHandler();
        $this->cacheCat = new CacheHandler();
        $this->initSql();
    }

    /**
     * Database setter
     * @return void
     */
    public function setDatebase($db)
    {
        $this->database = $db;
    }


    /**
     * in case user want to remapping the data columns and value
     * @param array $array the array use for mapping
     * @return void
     */
    public function setMapping($array)
    {
        $this->colIndex = $array;
    }

    /**
     * to start the main import function
     */
    public function running()
    {
        // Start transaction
        $this->database->autocommit(false);

        // Add unique keys to the database
        $this->database->multi_query($this->sqlAddUnique) or die("Cannot add unique keys to database. Error: " . $this->database->error);
        $this->freeit($this->database);

        // Open the file
        $handle = fopen($this->fileName, 'r') or die("Cannot open the file!!!");

        $firstline = fgets($handle);
        // Get the number of fields
        $fields = str_getcsv($firstline);

        // Arrays, variables use for getting IDs and stuffs
        $listOption = array();
        $listOptionName = array();
        $listOptionValue = array();
        $indexOfOptionValue = 1;
        $listCategoryName = array();
        $indexOfCategory = 1;

        // Get all the OPTION
        $numfields = count($fields);
        for ($i = 0; $i < $numfields; $i++ ) {
            if ("option_" == substr($fields[$i], 0, 7)) {
                $optName = substr($fields[$i], 7);
                $listOptionName []= $optName;
                $listOption []= $i;
            }
        }

        // Insert into the sql of OPTION table
        $this->sqlOption .= "('" . implode("'),('", $listOptionName) . "')";

        // Insert into the OPTION table
        $this->database->query($this->sqlOption) or die("Cannot insert into table OPTION. Error: " . $this->database->error);

        // Loop through the file
        while (!feof($handle)) {

            $row = fgets($handle);
            if ("" != $row) {

                $data = str_getcsv($row);
                $this->chunkSize++;
            
                // Format date/time data
                $data[$this->colIndex['prod_available']] = ("" == $data[$this->colIndex['prod_available']]) ? $data[$this->colIndex['prod_available']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_available']]));
                $data[$this->colIndex['prod_create']] = ("" == $data[$this->colIndex['prod_create']]) ? $data[$this->colIndex['prod_create']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_create']]));
                $data[$this->colIndex['prod_update']] = ("" == $data[$this->colIndex['prod_update']]) ? $data[$this->colIndex['prod_update']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_update']]));
                $data[$this->colIndex['prod_delete']] = ("" == $data[$this->colIndex['prod_delete']]) ? $data[$this->colIndex['prod_delete']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_delete']]));

                // Insert into the sql of PRODUCT table
                $this->sqlProduct .= "('" . $data[$this->colIndex['prod_id']] . "','"
                                          . $data[$this->colIndex['prod_name']] . "','"
                                          . $data[$this->colIndex['prod_slug']] . "','"
                                          . $data[$this->colIndex['prod_short']] . "','"
                                          . $data[$this->colIndex['prod_descr']] . "','"
                                          . $data[$this->colIndex['prod_available']] . "','"
                                          . $data[$this->colIndex['prod_create']] . "','"
                                          . $data[$this->colIndex['prod_update']] . "','"
                                          . $data[$this->colIndex['prod_delete']] . "','"
                                          . $data[$this->colIndex['prod_method']] . "'),";
            
                // Loop through the OPTION columns
                $numOpt = count($listOption);
                for ($i = 0; $i < $numOpt; $i++) {
                    
                    $optColIndex = $listOption[$i];

                    if ("" != $data[$optColIndex]) {
                        $optionId = $i + 1;
                        $optionValues = explode(";", $data[$optColIndex]);
                
                        foreach ($optionValues as $value) {

                            $optionValueId = "";
                            $searchCode = $optionId . $value;

                            // The old way
                            // if (!array_key_exists($searchCode, $listOptionValue)) {
                            //     $listOptionValue[$searchCode] = $indexOfOptionValue;
                            //     $optionValueId = $indexOfOptionValue;
                            //     $indexOfOptionValue++;

                            // // Insert into the sql of OPTION_VALUE table
                            // $this->sqlOptionValue .= "('" . $optionId . "','" . $value . "'),";
                            // } else {
                            //     $optionValueId = $listOptionValue[$searchCode];
                            // }

                            // The better old way
                            // if (!isset($listOptionValue[$optionId])) {
                            //     $listOptionValue[$optionId] = array();
                            //     $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                            //     $optionValueId = $indexOfOptionValue;
                            //     $indexOfOptionValue++;

                                // // Insert into the sql of OPTION_VALUE table
                                // $this->sqlOptionValue .= "('" . $optionId . "','" . $value . "'),";

                            // } elseif (!isset($listOptionValue[$optionId][$value])) {
                            //     $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                            //     $optionValueId = $indexOfOptionValue;
                            //     $indexOfOptionValue++;

                            //     // Insert into the sql of OPTION_VALUE table
                            //     $this->sqlOptionValue .= "('" . $optionId . "','" . $value . "'),";

                            // } else {
                            //     $optionValueId = $listOptionValue[$optionId][$value];
                            // }

                            // The Cache way
                            if (!$this->cacheOptVal->check_exist($searchCode)) {
                                $this->cacheOptVal->add($searchCode, $indexOfOptionValue);
                                $optionValueId = $indexOfOptionValue;
                                $indexOfOptionValue++;

                                 // Insert into the sql of OPTION_VALUE table
                                $this->sqlOptionValue .= "('" . $optionId . "','" . $value . "'),";
                            } else {
                                $optionValueId = $this->cacheOptVal->get($searchCode);
                            }

                            // Insert into the sql of PRODUCT_OPTION_VALUE table
                            $this->sqlProductOption .= "('" . $data[$this->colIndex['prod_id']] . "','" . $optionValueId . "'),";
                        }
                    }
                }
            
                // The category column
                if ("" != $data[$this->colIndex['category']]) {

                    $categoryName = explode(";", $data[$this->colIndex['category']]);

                    foreach ($categoryName as $name) {
                        
                        $idOfCategory = "";

                        // if (!isset($listCategoryName[$name])) {
                        //     $listCategoryName[$name]= $indexOfCategory;
                        //     $idOfCategory = $indexOfCategory;
                        //     $indexOfCategory++;

                        //     // Insert into the sql of CATEGORY table
                        //     $this->sqlCategory .= "('" . $name . "'),";
                        // } else {
                        //     $idOfCategory = $listCategoryName[$name];
                        // }
            
                        // The Cache way
                        if (!$this->cacheCat->check_exist($name)) {
                            $this->cacheCat->add($name, $indexOfCategory);
                            $idOfCategory = $indexOfCategory;
                            $indexOfCategory++;

                            // Insert into the sql of CATEGORY table
                            $this->sqlCategory .= "('" . $name . "'),";
                        } else {
                            $idOfCategory = $this->cacheCat->get($name);
                        }

                        // Insert into the sql of PRODUCT_CATEGORY table
                        $this->sqlProductCategory .= "('" . $data[$this->colIndex['prod_id']] . "','" . $idOfCategory . "'),";
                    }
                }
            
                if (self::MAXCHUNKSIZE == $this->chunkSize) {
                    $this->insertAll($this->database, $this->sqlProduct, $this->sqlOptionValue, $this->sqlCategory, $this->sqlProductOption, $this->sqlProductCategory);
                    $this->chunkSize = 0;
                }
            }
        }

        // For the last part if needed
        if ($this->chunkSize > 0) {
            $this->insertAll($this->database, $this->sqlProduct, $this->sqlOptionValue, $this->sqlCategory, $this->sqlProductOption, $this->sqlProductCategory);
        }

        // End transaction and commit them all
        $this->database->commit();

        // Close file
        fclose($handle);

    }
}
