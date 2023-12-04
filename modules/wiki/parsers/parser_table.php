<?php

if (!$core->loaded)
    die('Direct call detected. table');

/* Parse a wiki-table syntax.
{| Caption
|* border="1" style="width:100%;background-color:red;" *|
|* First heading---style="color:white"---||Second heading||Third heading *|
|* This is the first row||This is a cell||This is also cell*|
|* This is a row with attributes in both <tr> and <td> |--class="hello"--| ---style="background-color:green"---|| me || again *|
|* Etc.||Etc.||Etc.*|
|}
*/
$theRegex = '#\{\|(.*?)\|\}#ism';
$content = preg_replace_callback($theRegex,
    function ($theMatch){

        $theAttributeRegex = '/\-\-\-(.*?)\-\-\-/mis';
        $theRowRegex = '/\|\-\-(.*?)\-\-\|/miu';

        // Match all the lines
        $lines = preg_split('/(?:(?=[\r\n])\r?\n?|<br[^>]+>){1,}/mis', $theMatch[1], 2);
        $theCaption = $lines[0]; // Find the first line, used as a caption

        // Find the subset
        $theRegex = '#\|\*(.*?)\*\|#mis';
        preg_match_all($theRegex, $theMatch[1], $theSecondMatch);

        $i = 0;
        foreach ($theSecondMatch[1] as $singleLine){
            $i++;
            if ($i === 1){
                $theTable = '<div class="table-responsive">
                                <table ' . $singleLine . '>'. (strlen($theCaption) > 0 ? '<caption>' . $theCaption .  '</caption>' : '') . PHP_EOL;
                continue;
            }

            // Headings
            if ($i === 2){
                $theTable .= '<thead>' . PHP_EOL . '
                        <tr class="d-flex">';

                $theHeadings = explode('||', $theSecondMatch[1][1]);
                foreach ($theHeadings as $singleHead){
                    if (!preg_match($theAttributeRegex, $singleHead, $theInfraTag)){
                        $theInfraTag[1] = '';
                    }else{
                        $singleHead = preg_replace($theAttributeRegex,'', $singleHead);
                    }
                    $theTable .= '<th class="col" ' . $theInfraTag[1] . '>' . $singleHead . '</th>' .PHP_EOL;
                }
                $theTable .= '</tr></thead> ' . PHP_EOL .'
                        <tbody>' . PHP_EOL;

                continue;
            }

            // Process the data
            $tableData = ''; // Reset the table data <td>
            $theCells = explode('||', $theSecondMatch[1][$i - 1]);

            foreach ($theCells as $singleCell){

                // Check if a tr attribute has been passed. IE: |--class="myClass"--|
                if (!isset($theTrAttribute[1]) && preg_match($theRowRegex, $singleCell, $theTrAttribute)){
                    $singleCell = preg_replace($theRowRegex,'',$singleCell);
                }

                // Check if a td attribute has been passed. IE: ---class="myClass"---
                if (preg_match($theAttributeRegex, $singleCell, $theInfraTag)){
                    $singleCell = preg_replace($theAttributeRegex,'',$singleCell);
                }

                // Build the cells
                if (isset($theInfraTag[1])){
                    $tableData .= '<td class="col" ' . $theInfraTag[1] . '>' . $singleCell . '</td>' . PHP_EOL;
                    unset ($theInfraTag);
                }else{
                    $tableData .= '<td class="col">' . $singleCell . '</td>' . PHP_EOL;
                }

            }

            if (isset($theTrAttribute[1])){
                $theTable .= '<tr class="d-flex" '. $theTrAttribute[1] .'>' . $tableData .  '</tr>' . PHP_EOL;
                unset ($theTrAttribute);
            }else{
                $theTable .= '<tr class="d-flex">' . $tableData .  '</tr>';
            }

        }

        $theTable .= '</tbody> ' . PHP_EOL . '
                </table>
                </div>';

        return $theTable;
    }, $content
);