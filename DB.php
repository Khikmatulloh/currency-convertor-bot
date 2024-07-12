<?php


class DB
{
    public static function connect(): PDO
    {
        return new PDO('mysql:host=localhost;dbname=conventor1', 'root', 'root');
    }
}