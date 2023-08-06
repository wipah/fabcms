<?php
namespace FabCMS;

class antispam {
    public function getChallenge(int $type, string $hash) :string {
        $array = [];

        srand($hash);
        $max    =   rand(1, 4);      // Max number of
        $color  =   mt_rand(0,4);

        $colorsArray =  [
                            ['rosso' , 'BE2200'],
                            ['Verde' , '00BE2B'],
                            ['Giallo', 'E4F01F'],
                            ['Bianco', 'FFFFFF'],
                            ['Nero'  , '000000']
                        ];

        $name = $colorsArray[$color][0];
        $hex  = $colorsArray[$color][1];

        $correct = 0;
        for ($i = 0; $i < $max ; $i++) {
            $correct += mt_rand(0, 1000);
            $array[] = ['value' => $correct, 'color' => $hex];
        }

        unset ($colorsArray[$color]); // Removes the array

        for ($i = 0; $i < 10 - $max; $i++ ) {
            $array[] = ['value' => mt_rand(0, 1000), 'color' => 'CDAECA'];
        }

        shuffle($array[]);

        $output = '';

        foreach ($array as $singleItem){
            var_dump($singleItem);
        }
    }
}