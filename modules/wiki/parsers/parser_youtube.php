<?php

$theRegex = '#\[youtube\ ([\a-Z\_\-\,\.0-9]{11})\|\|(.*)?\]#miu';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;
        global $db;

        $options = explode('||', $theFirstMatch['2']);

        foreach ($options as $option) {
            $fragment = explode('==', $option);

            if (strtolower($fragment[0]) == 'id') {
                $media_ID = (int)$fragment[1];

                $query = 'SELECT F.ID,
                                 V.provider_ID
                          FROM ' . $db->prefix . 'fabmedia AS F
                          LEFT JOIN ' . $db->prefix . 'fabmedia_videos AS V
                          ON V.fabmedia_ID = F.ID
                          WHERE F.ID = ' . $media_ID . ' LIMIT 1;';

                

                if (!$result = $db->query($query))
                    return '
                        <div class="panel panel-warning">
                            <div class="panel-heading">
                                <h3 class="panel-title">Internal error</h3>
                            </div>
                            <div class="panel-body">Unfortunately the video was not ready. Please try again later.</div>
                        </div>';

                if (!$db->affected_rows) {
                    return '
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">Video not found</h3>
                            </div>
                            <div class="panel-body">Unfortunately the video was not found.</div>
                        </div>';
                }

                $row = mysqli_fetch_assoc($result);
            }

        }
        return '<object
                    style="width:100%;height:100%; height: 250px; float: none; clear: both; margin: 2px auto;"
                    data="https://www.youtube.com/embed/' . $row['provider_ID']  .'">
               </object>';
    }, $content);
