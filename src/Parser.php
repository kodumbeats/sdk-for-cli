<?php

namespace Appwrite;

use jc21\CliTable;

class Parser {

    /**
     * List of colors supported by the library 
     *
     * @var array
     */
    private const colors = array(
        'blue',
        'red',
        'green',
        'yellow',
        'magenta',
        'cyan',
        'white',
        'grey'
    );

    /**
     * Color for the table borders
     *
     * @var bool
     */
    private const tableColor = self::colors[0];

    /**
     * Color for the table column headers
     *
     * @var string
     */
    private const headerColor = self::colors[2];

     /**
     * Parse the response from the server 
     *
     * @param string $value 
     * @return void
     */
    public function parseResponse($response) {
        
        foreach ($response as $key => $value) {
            if (is_array($value) && count($value) !== 0 ) {
                $this->drawKeyValue($key, '');
                $this->drawTable($value, self::headerColor, self::tableColor);
            } 
            else {
                $this->drawKeyValue($key, $value);
            }
        }
    }

    /**
     * Print a key value pair
     *
     * @param string $key
     * @param string $value 
     * @return void
     */
    private function drawKeyValue($key, $value){
        if(is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } else if (is_array($value)) {
            $value = '{}';
        }

        printf("%s : %s\n", $key, $value);
    }

    /**
     * Get a column color based on the index
     *
     * @param int $index
     * @return string
     */
    private function getColor($index = -1) : string {
        if ($index != -1) return self::colors[$index % count(self::colors) ];
        return self::colors[array_rand(self::colors)];
    }

    /**
     * Creates a table from the passed data
     *
     * @param array $data
     * @param string $headerColor
     * @param string $tableColor
     * @return void
     */
    private function drawTable($data, $headerColor, $tableColor) {
        if (!is_array($data) || !is_array($data[0])) return;

        $keys = array_keys($data[0]);
        
        $table = new CliTable();
        $table->setTableColor($tableColor);
        $table->setHeaderColor($headerColor);
        
        foreach ($keys as $key => $value) {
            $table->addField(ucwords($value), $value, new Manipulators('truncate'), $this->getColor($key));
        }

        // Convert nested arrays to string representation
        $transformedData = array_map (function ($data) { 
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (count($value) == 0) {
                        $data[$key] = '{}';
                    } else {
                        $data[$key] = 'array('.count($value).')';
                    }
                } else if (is_object($value)) {
                    $data[$key] = 'Object';
                }
            }
            return $data;
        }, $data);

        $table->injectData($transformedData);
        $table->display();
    }

    public function formatArray(Array $arr) {
        $descriptionColumnLimit = 60;
        $commandNameColumnLimit = 20;
        $mask = "\t%-${commandNameColumnLimit}.${commandNameColumnLimit}s %-${descriptionColumnLimit}.${descriptionColumnLimit}s\n";
        
        array_walk($arr, function(&$key) use ($descriptionColumnLimit){
            $key = explode("\n", wordwrap($key, $descriptionColumnLimit));
        });
        
        foreach($arr as $key => $value) {
            foreach($value as $sentence) {
                 printf($mask, $key, $sentence);
                 $key = "";
            }
        }
    }
}