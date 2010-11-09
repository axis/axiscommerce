#!/usr/bin/php
<?php
$chapters = array(
    'index.txt',
    'catalog.txt',
    'module.txt'
);

$book = 'book.txt';
$file_book = fopen($book, 'w');
foreach ($chapters as $chapter) {
    fwrite($file_book, file_get_contents($chapter) . "\n\n");
}
fclose($file_book);

echo system('a2x -f chunked book.txt');
