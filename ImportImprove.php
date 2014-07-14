<?php
namespace www\week2\day1\dinhtuan;

class ImportImprove
{
	private $fileToImportName;
	private $nameOfProdFile = "product.csv";
	private $nameOfOptFile = "option.csv";
	private $nameOfCatFile = "category.csv";
	private $nameOfOptValFile = "optionvalue.csv";
	private $nameOfProdOptFile = "productoption.csv";
	private $nameOfProdCatFile = "productcategory.csv";
	private $pathOfTempRepo = '\temp\\';

    const TB_PRODUCT = 'product';
    const TB_CATEGORY = 'category';
    const TB_OPTION = 'rubikin_db.option';
    const TB_OPTION_VALUE = 'option_value';
    const TB_PRODUCT_OPTION = 'product_option';
    const TB_PRODUCT_CATEGORY = 'product_category';

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

    private function getPath($string)
    {
        $concate = $this->pathOfTempRepo . $string;
        $result = str_replace("\\", "\\\\", $concate);
        return $result;
    }

	/**
	 * constructor
	 * @param string $name name of the file to import
	 * @return void
	 */
	public function __construct($name)
	{
		$this->fileToImportName = __DIR__ . "\\" . $name;
        $this->pathOfTempRepo = __DIR__ . $this->pathOfTempRepo;
	}

	/**
	 * Array use for mapping setter
	 * @param array $array the array to map
	 * @return void
	 */
	public function setMapping($array)
	{
		$this->colIndex = $array;
	}

	/**
	 * Generate CSV files for each tables for high-speed import later
	 *@return void
	 */
	public function generateCSVs()
	{
		$in = fopen($this->fileToImportName, 'r') or die("Cannot open file " . $this->fileToImportName);
		$outProduct = fopen($this->getPath($this->nameOfProdFile), 'w') or die("Cannot open file " . $this->nameOfProdFile);
		$outOption = fopen($this->getPath($this->nameOfOptFile), 'w') or die("Cannot open file " . $this->nameOfOptFile);
		$outCategory = fopen($this->getPath($this->nameOfCatFile), 'w') or die("Cannot open file " . $this->nameOfCatFile);
		$outOptVal = fopen($this->getPath($this->nameOfOptValFile), 'w') or die("Cannot open file " . $this->nameOfOptValFile);
		$outProdOpt = fopen($this->getPath($this->nameOfProdOptFile), 'w') or die("Cannot open file " . $this->nameOfProdOptFile);
		$outProdCat = fopen($this->getPath($this->nameOfProdCatFile), 'w') or die("Cannot open file " . $this->nameOfProdCatFile);

		$firstline = fgets($in);
		$fields = str_getcsv($firstline);

		// Arrays, variables use for getting IDs and stuffs
        $listOptionIndex = array();
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
                $listOptionIndex []= $i;
            }
        }

        // Insert all the Options
        foreach ($listOptionName as $name) {
        	fwrite($outOption, "'" . $name . "'\n");
        }

        while (!feof($in)) {
        	$row = fgets($in);
            if ("" != $row) {

            	$data = str_getcsv($row);

            	// Format date/time data
                $data[$this->colIndex['prod_available']] = ("" == $data[$this->colIndex['prod_available']]) ? $data[$this->colIndex['prod_available']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_available']]));
                $data[$this->colIndex['prod_create']] = ("" == $data[$this->colIndex['prod_create']]) ? $data[$this->colIndex['prod_create']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_create']]));
                $data[$this->colIndex['prod_update']] = ("" == $data[$this->colIndex['prod_update']]) ? $data[$this->colIndex['prod_update']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_update']]));
                $data[$this->colIndex['prod_delete']] = ("" == $data[$this->colIndex['prod_delete']]) ? $data[$this->colIndex['prod_delete']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_delete']]));

                // Insert into the  PRODUCT table
                $lineOfProduct = "'" . $data[$this->colIndex['prod_id']] . "','"
                                       . $data[$this->colIndex['prod_name']] . "','"
                                       . $data[$this->colIndex['prod_slug']] . "','"
                                       . $data[$this->colIndex['prod_short']] . "','"
                                       . $data[$this->colIndex['prod_descr']] . "','"
                                       . $data[$this->colIndex['prod_available']] . "','"
                                       . $data[$this->colIndex['prod_create']] . "','"
                                       . $data[$this->colIndex['prod_update']] . "','"
                                       . $data[$this->colIndex['prod_delete']] . "','"
                                       . $data[$this->colIndex['prod_method']] . "'\n";

                fwrite($outProduct, $lineOfProduct);

                // Loop through the OPTION columns
                $numOpt = count($listOptionIndex);
                for ($i = 0; $i < $numOpt; $i++) {

                	$optionColIndex = $listOptionIndex[$i];

                	if ("" != $data[$optionColIndex]) {

                		$optionId = $i + 1;
                		$optionValues = explode(";", $data[$optionColIndex]);

                		foreach ($optionValues as $value) {
                			
                			$optionValueId = "";

                			if (!isset($listOptionValue[$optionId])) {
                                $listOptionValue[$optionId] = array();
                                $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                                $optionValueId = $indexOfOptionValue;
                                $indexOfOptionValue++;

                                //Insert the option value
                                fwrite($outOptVal, "'" . $optionId . "','" . $value . "'\n");

                            } elseif (!isset($listOptionValue[$optionId][$value])) {
                                $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                                $optionValueId = $indexOfOptionValue;
                                $indexOfOptionValue++;

                                //Insert the option value
                                fwrite($outOptVal, "'" . $optionId . "','" . $value . "'\n");

                            } else {
                                $optionValueId = $listOptionValue[$optionId][$value];
                            }

                            // Insert into the Product Option table
                            fwrite($outProdOpt, "'" . $data[$this->colIndex['prod_id']] . "','" . $optionValueId . "'\n");
                		}
                	}
                }

                // category
                if ("" != $data[$this->colIndex['category']]) {

                    $categoryName = explode(";", $data[$this->colIndex['category']]);

                    foreach ($categoryName as $name) {
                        
                        $idOfCategory = "";

                        if (!isset($listCategoryName[$name])) {
                            $listCategoryName[$name]= $indexOfCategory;
                            $idOfCategory = $indexOfCategory;
                            $indexOfCategory++;

                            // Insert into the CATEGORY table
                            fwrite($outCategory, "'" . $name . "'\n");
                        } else {
                            $idOfCategory = $listCategoryName[$name];
                        }

                        // Insert into the PRODUCT_CATEGORY table
                        fwrite($outProdCat, "'" . $data[$this->colIndex['prod_id']] . "','" . $idOfCategory . "'\n");
                    }
                }
            }
        }
	}

    public function import($db)
    {
        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfProdFile) . "' IGNORE INTO TABLE " . self::TB_PRODUCT .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (id,name,slug,short_description,description,available_on,created_at,updated_at,deleted_at,variant_selection_method)";
        $db->query($sql) or die("Cannot import into table Product. Error: " . $db->error);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfOptFile) . "' IGNORE INTO TABLE " . self::TB_OPTION .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (name)";
        $db->query($sql) or die("Cannot import into table Option. Error: " . $db->error);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfCatFile) . "' IGNORE INTO TABLE " . self::TB_CATEGORY .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (name)";
        $db->query($sql) or die("Cannot import into table Category. Error: " . $db->error);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfOptValFile) . "' IGNORE INTO TABLE " . self::TB_OPTION_VALUE .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (option_id,value)";
        $db->query($sql) or die("Cannot import into table Option Value. Error: " . $db->error);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfProdOptFile) . "' IGNORE INTO TABLE " . self::TB_PRODUCT_OPTION .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (product_id,option_value_id)";
        $db->query($sql) or die("Cannot import into table Product Option. Error: " . $db->error);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->getPath($this->nameOfProdCatFile) . "' IGNORE INTO TABLE " . self::TB_PRODUCT_CATEGORY .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n' 
                (product_id,category_id)";
        $db->query($sql) or die("Cannot import into table Product Category. Error: " . $db->error);
    }
	
}