<?php

require_once __DIR__ . '/_entity_seen_table.php';

function tests_smoke_create_post_seen_table()
{
    tests_smoke_create_entity_seen_table('tbl_post_seen');
}

function tests_smoke_drop_post_seen_table()
{
    tests_smoke_drop_entity_seen_table('tbl_post_seen');
}