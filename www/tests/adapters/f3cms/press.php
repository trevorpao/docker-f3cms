<?php

function tests_f3cms_press_initial_status_for_target($targetStatus)
{
    if (\F3CMS\fPress::ST_OFFLINED === $targetStatus) {
        return \F3CMS\fPress::ST_PUBLISHED;
    }

    return \F3CMS\fPress::ST_DRAFT;
}

function tests_f3cms_seed_press_row($pressId, $status)
{
    mh()->delete('tbl_press_log', ['parent_id' => $pressId]);
    mh()->delete('tbl_press', ['id' => $pressId]);

    mh()->insert('tbl_press', [
        'id' => $pressId,
        'status' => $status,
        'mode' => 'Article',
        'on_homepage' => 'No',
        'on_top' => 'No',
        'slug' => 'workflow-engine-smoke-' . $pressId,
        'online_date' => date('Y-m-d'),
        'cover' => '',
        'last_ts' => date('Y-m-d H:i:s'),
        'last_user' => 1,
        'insert_ts' => date('Y-m-d H:i:s'),
        'insert_user' => 1,
    ]);
}

function tests_f3cms_prepare_press_reaction_context($f3, $pressId, $targetStatus, array $staff = [])
{
    $staffSession = array_merge([
        'id' => 1,
        'account' => 'trevor',
        'has_login' => 1,
    ], $staff);

    $f3->set('cache.press', 1);
    $f3->set('sessionBase', 'file');
    $f3->set('CSRF', 'workflow-engine-smoke');
    $f3->set('SESSION.cu_staff', $staffSession);
    $f3->set('BODY', '');
    $f3->set('GET', []);

    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    $_POST = [
        'id' => $pressId,
        'status' => $targetStatus,
    ];
    $_GET = [];
    $_REQUEST = $_POST;

    $f3->set('POST', $_POST);
    $f3->set('REQUEST', $_REQUEST);

    return $staffSession;
}

function tests_f3cms_prepare_press_publish_smoke($f3, $pressId, $targetStatus)
{
    $initialStatus = tests_f3cms_press_initial_status_for_target($targetStatus);
    tests_f3cms_seed_press_row($pressId, $initialStatus);
    tests_f3cms_prepare_press_reaction_context($f3, $pressId, $targetStatus);

    return [
        'press_id' => $pressId,
        'initial_status' => $initialStatus,
        'target_status' => $targetStatus,
    ];
}