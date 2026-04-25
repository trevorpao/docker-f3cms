<?php

require_once __DIR__ . '/_entity_seen_table.php';

function tests_smoke_create_press_seen_table()
{
    tests_smoke_create_entity_seen_table('tbl_press_seen');
}

function tests_smoke_drop_press_seen_table()
{
    tests_smoke_drop_entity_seen_table('tbl_press_seen');
}