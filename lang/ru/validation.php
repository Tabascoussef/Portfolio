<?php

return [
    'required' => 'Заполните поле «:attribute».',
    'string' => 'Поле «:attribute» должно содержать текст.',
    'email' => 'Укажите корректный адрес в поле «:attribute».',
    'url' => 'Укажите ссылку http или https в поле «:attribute».',
    'timezone' => 'Укажите корректный часовой пояс в поле «:attribute».',
    'regex' => 'Неверный формат поля «:attribute».',
    'unique' => 'Значение поля «:attribute» уже используется.',
    'integer' => 'Поле «:attribute» должно содержать целое число.',
    'digits' => 'Поле «:attribute» должно содержать :digits цифры.',
    'array' => 'Поле «:attribute» должно содержать список.',
    'list' => 'Поле «:attribute» должно содержать упорядоченный список.',
    'between' => [
        'numeric' => 'Значение поля «:attribute» должно быть между :min и :max.',
    ],
    'max' => [
        'string' => 'В поле «:attribute» допустимо не более :max символов.',
        'array' => 'В поле «:attribute» допустимо не более :max элементов.',
    ],
];
