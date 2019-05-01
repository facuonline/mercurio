<!DOCTYPE html>
<html>
<head>
    <?php
        /* Here we can directly import the following tags:
            <meta charset=$utf>
            <title>$title</title>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <link rel='stylesheet' type='text/css' media='screen' href='$css'>
            <script src='$js'></script>
         *
         * Where we can declare the values of the variables as parameters of the method
         * You can declare other html tags after or before this method
         */
        Vista::htmlHead([
            'utf' => 'utf-8', // utf-8 will be set as default if we don't declare otherwise
            'title' => 'Skeleton'
        ]);
    ?>
</head>
<body>
    <h1>Skeleton</h1>