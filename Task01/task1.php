<?php
// Указываем путь к файлу с описанием
$specificationFile = '../general_specification.md';

// Проверяем, существует ли файл
if (file_exists($specificationFile)) {
    // Читаем содержимое файла
    $content = file_get_contents($specificationFile);

    // Выводим содержимое на экран
    echo $content;
} else {
    echo "Файл с описанием не найден.\n";
}
?>